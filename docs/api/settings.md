# Settings API

Mounted at `/settings`. Requires an administrator (see [System → Authentication](system.md#authentication)). Scoped strictly to the plugin's own `smart-post-aggregator_settings` option — see [System → Duplicate-detection defaults](system.md#duplicate-detection-defaults) and `Services\DuplicateDetector`.

| Method | Path         | Auth  | Body                                                              | Notes                          |
| ------ | ------------ | ----- | -------------------------------------------------------------------- | -------------------------------- |
| GET    | `/settings` | Admin | —                                                                     | Current (or default) settings   |
| POST   | `/settings` | Admin | `algorithm`, `threshold`, `review_margin`, `default_resolution`      | Replace all settings             |

---

## `GET /settings`

Returns the saved `smart-post-aggregator_settings` option, falling back to defaults for any missing key.

### Response `200`

```json
{
  "algorithm": "jaccard",
  "threshold": 50,
  "review_margin": 10,
  "default_resolution": "mark"
}
```

### curl

```bash
curl -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  https://example.com/wp-json/smart-post-aggregator/v1/settings
```

---

## `POST /settings`

Replaces the entire settings option — this is not a partial update; send all four fields.

### Request body

| Field                 | Type   | Required | Validation                          | Description                                                        | Example    |
| ----------------------- | ------ | :------: | -------------------------------------- | ---------------------------------------------------------------------- | ------------ |
| `algorithm`            | string | Yes      | one of: `jaccard`                     | Similarity algorithm (`Services\Similarity\SimilarityFactory`)         | `"jaccard"` |
| `threshold`            | number | Yes      | clamped to `0`–`100`                  | Similarity % at/above which content is queued for review or flagged   | `50`        |
| `review_margin`        | number | Yes      | clamped to `0`–`100`                  | % above `threshold` that's still "queue for review" rather than an automatic `duplicate` | `10`  |
| `default_resolution`   | string | Yes      | one of: `mark`, `trash`               | What happens to a confirmed high-confidence duplicate at ingest time  | `"mark"`    |

`threshold`/`review_margin` are clamped server-side (`min(100, max(0, …))`) rather than rejected out of range.

Score-to-status mapping at ingest time (`Services\DuplicateDetector::detect()`):

| Similarity score              | Status            |
| -------------------------------- | ------------------- |
| `< threshold`                    | `unique`            |
| `>= threshold` and `< threshold + review_margin` | `pending_review` |
| `>= threshold + review_margin`   | `duplicate`         |

`default_resolution` only applies once a row is `duplicate`: `mark` keeps the post but flags it; `trash` additionally calls `wp_trash_post()` on it immediately (still recoverable from the WordPress trash).

Example:

```json
{
  "algorithm": "jaccard",
  "threshold": 60,
  "review_margin": 15,
  "default_resolution": "trash"
}
```

### Response `200`

Echoes back the saved settings.

```json
{
  "algorithm": "jaccard",
  "threshold": 60,
  "review_margin": 15,
  "default_resolution": "trash"
}
```

### Errors

| Status | Condition                                                |
| ------ | ----------------------------------------------------------- |
| `400`  | `algorithm` is not `jaccard`                               |
| `400`  | `default_resolution` is not `mark` or `trash`               |

### curl

```bash
curl -X POST https://example.com/wp-json/smart-post-aggregator/v1/settings \
  -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  -H "Content-Type: application/json" \
  -d '{"algorithm":"jaccard","threshold":60,"review_margin":15,"default_resolution":"trash"}'
```
