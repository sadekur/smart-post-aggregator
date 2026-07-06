# Duplicates API

Mounted at `/duplicates`. Requires an administrator (see [System → Authentication](system.md#authentication)). Backs the Review inbox — see `Services\DuplicateDetector` and `Services\DuplicateResolver`.

| Method | Path                       | Auth  | Body      | Notes                                    |
| ------ | -------------------------- | ----- | --------- | ------------------------------------------ |
| GET    | `/duplicates`              | Admin | —         | List items still queued for manual review |
| POST   | `/duplicates/{id}/resolve` | Admin | `action`  | Apply a reviewer decision to a log row    |

---

## `GET /duplicates`

Lists `spa_duplicate_log` rows still awaiting a decision (`resolution = 'queued'`), oldest first, capped at 50.

### Response `200`

```json
[
  {
    "log_id": 12,
    "score": 62.5,
    "algorithm": "jaccard",
    "created_at": "2026-07-06 10:15:00",
    "new": {
      "id": 148,
      "title": "Breaking: example story",
      "excerpt": "First 30 words of the new post's content...",
      "link": "https://example.com/aggregated/breaking-example-story/",
      "thumbnail": null
    },
    "matched": {
      "id": 130,
      "title": "Breaking — example story (earlier source)",
      "excerpt": "First 30 words of the matched post's content...",
      "link": "https://example.com/aggregated/earlier-source/",
      "thumbnail": "https://example.com/wp-content/uploads/2026/07/thumb.jpg"
    }
  }
]
```

`matched` is `null` when the log row has no matched post (shouldn't normally happen for a queued row, but the field is nullable).

Rows whose `new_post_id` no longer resolves to an existing post (e.g. hard-deleted outside the plugin) are silently dropped from the response.

### curl

```bash
curl -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  https://example.com/wp-json/smart-post-aggregator/v1/duplicates
```

---

## `POST /duplicates/{id}/resolve`

Applies a reviewer's decision to a `spa_duplicate_log` row. Delegates to `Services\DuplicateResolver::resolve()`, which also stamps `resolved_by` with the current user ID.

### Request body

| Field    | Type   | Required | Validation                                                             | Description        |
| -------- | ------ | :------: | ------------------------------------------------------------------------ | -------------------- |
| `action` | string | Yes      | one of `approve_unique`, `confirm_duplicate`, `merge`, `ignore`         | Resolution to apply |

| Action               | Effect                                                                                                  |
| ---------------------- | ---------------------------------------------------------------------------------------------------------- |
| `approve_unique`      | Marks the new post `_spa_duplicate_status = unique`, clears `_spa_duplicate_of`. Post is kept as-is.       |
| `confirm_duplicate`   | Marks the new post `_spa_duplicate_status = duplicate`. Post is kept (for audit), just flagged.            |
| `merge`               | Backfills a missing thumbnail on the matched original from the new post, then trashes the new post (`wp_trash_post`, recoverable). |
| `ignore`              | Trashes the new post (`wp_trash_post`, recoverable) without touching the matched original.                 |

Example:

```json
{ "action": "confirm_duplicate" }
```

### Response `200`

```json
{ "resolution": "marked" }
```

`resolution` is one of `approved`, `marked`, `merged`, `ignored` — see `Services\DuplicateResolver` for the mapping from action → resolution string.

### Errors

| Status | Condition                                    |
| ------ | ----------------------------------------------- |
| `400`  | `action` is not one of the four allowed values  |
| `404`  | No `spa_duplicate_log` row exists for `{id}`    |

### curl

```bash
curl -X POST https://example.com/wp-json/smart-post-aggregator/v1/duplicates/12/resolve \
  -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  -H "Content-Type: application/json" \
  -d '{"action":"confirm_duplicate"}'
```
