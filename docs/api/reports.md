# Reports API

Requires an administrator (see [System → Authentication](system.md#authentication)). Backs the Dashboard stat tiles + recent-activity feed, and the Logs screen's full audit trail.

| Method | Path      | Auth  | Body | Notes                                          |
| ------ | --------- | ----- | ---- | ------------------------------------------------ |
| GET    | `/stats` | Admin | —    | Dashboard counts + last 10 activity events       |
| GET    | `/logs`  | Admin | —    | Paginated, filterable `spa_duplicate_log` history |

---

## `GET /stats`

### Response `200`

```json
{
  "aggregated_today": 4,
  "aggregated_week": 37,
  "aggregated_total": 812,
  "auto_resolved": 690,
  "pending_review": 6,
  "sources_in_error": 1,
  "recent_activity": [
    {
      "log_id": 205,
      "new_title": "Example headline",
      "matched_title": "Similar earlier headline",
      "score": 91.2,
      "resolution": "marked",
      "created_at": "2026-07-06 13:02:00"
    }
  ]
}
```

| Field                | Meaning                                                                                    |
| ----------------------- | ---------------------------------------------------------------------------------------------- |
| `aggregated_today`     | Published `spa_content` posts created today                                                    |
| `aggregated_week`      | Published `spa_content` posts created in the last 7 days                                       |
| `aggregated_total`     | Total published `spa_content` posts                                                            |
| `auto_resolved`        | Log rows resolved automatically at ingest time (`resolution != 'queued'` and `resolved_by IS NULL`) — a hash match, or high-confidence similarity past the review margin |
| `pending_review`       | Log rows still `resolution = 'queued'`                                                          |
| `sources_in_error`     | Rows in `spa_sources` with `status = 'error'` (exceeded `MAX_RETRIES`, see [System](system.md)) |
| `recent_activity`      | Last 10 `spa_duplicate_log` rows, newest first                                                 |

`matched_title` is `null` when the log row has no matched post.

### curl

```bash
curl -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  https://example.com/wp-json/smart-post-aggregator/v1/stats
```

---

## `GET /logs`

Paginated, filterable history of every duplicate-detection event (resolved or not).

### Query parameters

| Param        | Type    | Required | Default | Description                                                |
| ------------- | ------- | :------: | :-----: | -------------------------------------------------------------- |
| `page`       | integer | No       | `1`     | Page number (1-based)                                          |
| `per_page`   | integer | No       | `20`    | Items per page, capped at `100`                                |
| `resolution` | string  | No       | —       | Exact match on `resolution` (e.g. `queued`, `marked`, `merged`, `ignored`, `approved`) |
| `date_from`  | string  | No       | —       | `YYYY-MM-DD`; matches `created_at >= date_from 00:00:00`        |
| `date_to`    | string  | No       | —       | `YYYY-MM-DD`; matches `created_at <= date_to 23:59:59`          |
| `source_id`  | integer | No       | —       | Only log rows whose new post came from this `spa_sources.id`   |

Example:

```
GET /wp-json/smart-post-aggregator/v1/logs?page=1&per_page=20&resolution=queued&date_from=2026-07-01
```

### Response `200`

```json
[
  {
    "log_id": 205,
    "new_post": {
      "id": 148,
      "title": "Example headline",
      "link": "https://example.com/aggregated/example-headline/"
    },
    "matched_post": {
      "id": 130,
      "title": "Similar earlier headline",
      "link": "https://example.com/aggregated/earlier-headline/"
    },
    "algorithm": "jaccard",
    "score": 91.2,
    "threshold_at_time": 50,
    "resolution": "marked",
    "resolved_by": "Jane Admin",
    "created_at": "2026-07-06 13:02:00"
  }
]
```

`matched_post` and `resolved_by` are `null` when not applicable (no match, or not yet resolved by a human).

### Response headers

| Header            | Meaning                                     |
| ------------------ | ---------------------------------------------- |
| `X-WP-Total`       | Total rows matching the applied filters        |
| `X-WP-TotalPages`  | Total pages at the requested `per_page`         |

### curl

```bash
curl -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  "https://example.com/wp-json/smart-post-aggregator/v1/logs?resolution=queued&per_page=20"
```
