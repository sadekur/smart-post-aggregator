# System

## Server

| Property        | Value                                                              |
| ---------------- | ------------------------------------------------------------------- |
| Runtime          | WordPress REST API (`WP_REST_Server`), registered on `rest_api_init` |
| Namespace        | `smart-post-aggregator/v1`                                          |
| Base URL         | `<site>/wp-json/smart-post-aggregator/v1`                           |
| Content-Type     | `application/json`                                                  |
| Registered in    | `app/Controllers/Common/API.php`                                    |

### Base URL example

```
https://example.com/wp-json/smart-post-aggregator/v1/sources
```

The plugin's own admin app reads this from the localized `SPA_PLUGIN_ADMIN.api_base` JS global (see `app/Controllers/Common/Assets.php`), which is just `rest_url( 'smart-post-aggregator/v1' )`.

---

## Authentication

Every route's `permission_callback` is `Traits\Auth::is_admin`, which resolves to:

```php
current_user_can( 'administrator' ) || is_sandbox_mode()
```

`is_sandbox_mode()` returns `true` when the constant `spa_SANDBOX` (note lowercase `spa`) is defined and truthy — useful for local development, but treat it as a security-relevant gate and never define it on a production site.

Because every route sits on core's `rest_api_init`, the standard WordPress REST authentication mechanisms apply:

| Method | Use case | Notes |
| ------ | -------- | ----- |
| Cookie + nonce | The bundled admin React app | Requests are same-origin, sent with the browser's logged-in session cookies plus an `X-WP-Nonce` header. The nonce is localized as `SPA_PLUGIN_ADMIN.nonce` (`wp_create_nonce( 'wp_rest' )`) — see `Assets.php::admin_assets()`. |
| Application Passwords | External scripts / curl | WordPress core's built-in Application Passwords feature (Users → Profile) works out of the box; use HTTP Basic auth with the app password as the credential. |

There is no plugin-specific API key or Bearer-token scheme — auth is entirely delegated to core WordPress.

### curl (Application Passwords)

```bash
curl -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  https://example.com/wp-json/smart-post-aggregator/v1/sources
```

---

## Error format

Validation and lookup failures return a `WP_Error`, which core's REST server serializes to the standard WordPress REST shape:

```json
{
  "code": "spa_invalid_type",
  "message": "Invalid source type.",
  "data": { "status": 400 }
}
```

| Status | Meaning                                                    |
| ------ | ----------------------------------------------------------- |
| `400`  | Bad request — missing, invalid, or disallowed field value   |
| `401`/`403` | Not authenticated, or authenticated but not an administrator |
| `404`  | Resource not found (e.g. unknown `log_id`)                  |
| `500`  | Insert/update failed at the database layer                  |

> `Traits\Rest` also defines `response_success()` / `response_error()` / `response()` helpers built on `wp_send_json_*()`, but none of the current `app/API/*` controllers use them — every endpoint returns via `rest_ensure_response()` or a `WP_Error`, which is what actually reaches the client. Don't rely on the `wp_send_json_*` envelope shape when writing a client against this API.

---

## Response headers / pagination

There is no envelope (`{ items, meta }`); list endpoints return the array directly, and paginated ones (`Content`, `Reports::logs`) attach pagination info as response headers instead of body fields:

| Header            | Meaning                          |
| ------------------ | --------------------------------- |
| `X-WP-Total`       | Total matching rows across all pages |
| `X-WP-TotalPages`  | Total number of pages              |

---

## Background cron sweep

`Controllers\Common\Cron` registers a single recurring WP-Cron event rather than one per source.

| Property             | Value                                    |
| --------------------- | ------------------------------------------ |
| Hook                  | `spa_cron_fetch_sources`                  |
| Interval              | `spa_fifteen_minutes` (custom schedule, 15 minutes) |
| Batch size            | 20 sources per tick (`Cron::BATCH_SIZE`)  |
| Sweep lock            | Cache key `spa_cron_sweep_lock`, 5-minute TTL — prevents two overlapping sweeps from double-ingesting the same feed item |
| Max retries           | 5 (`Cron::MAX_RETRIES`) before a source is flipped to `status = 'error'` and stops being retried |
| Backoff               | Exponential: `MINUTE_IN_SECONDS * 2^retry_count` |

The sweep is scheduled immediately on plugin activation (`Core\Activator`) and unscheduled on deactivation (`Core\Deactivator`); it does not wait for the `Cron` controller's own `plugins_loaded`-time scheduling to run on the next page load.

Each due, active row from `spa_sources` is handed to `Services\ContentAggregator::aggregate_source()`, which in turn triggers `Services\DuplicateDetector::detect()` for every newly created post — see [Sources](sources.md) and [Duplicates](duplicates.md).

---

## Duplicate-detection defaults

Stored in the single option `smart-post-aggregator_settings` (see [Settings](settings.md)); these are the fallback values before anything has been saved:

| Setting               | Default   | Constant                                    |
| ----------------------- | :-------: | --------------------------------------------- |
| `algorithm`            | `jaccard` | `Services\Similarity\SimilarityFactory::DEFAULT_ALGORITHM` |
| `threshold`            | `50`      | `Services\DuplicateDetector::DEFAULT_THRESHOLD` (percent) |
| `review_margin`        | `10`      | `Services\DuplicateDetector::DEFAULT_REVIEW_MARGIN` (percent) |
| `default_resolution`   | `mark`    | `Services\DuplicateDetector::DEFAULT_RESOLUTION` (`mark` or `trash`) |

Candidate comparison window (not configurable via Settings): last **30 days** of published `spa_content` posts, capped at **200** candidates per newly ingested item (`DuplicateDetector::CANDIDATE_WINDOW_DAYS` / `CANDIDATE_LIMIT`).
