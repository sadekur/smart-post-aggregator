# Sources API

Mounted at `/sources`. Requires an administrator (see [System → Authentication](system.md#authentication)). Backs the Sources screen and is the table the Cron sweep reads from — see `Services\ContentAggregator` and the [background cron sweep](system.md#background-cron-sweep).

| Method | Path                | Auth  | Body                                                 | Notes                       |
| ------ | ------------------- | ----- | ----------------------------------------------------- | ---------------------------- |
| GET    | `/sources`          | Admin | —                                                     | List sources (most recent first, up to 100) |
| POST   | `/sources`          | Admin | `name`, `type`, `url`, `fetch_interval?`, `config?`   | Create a source              |
| DELETE | `/sources/{id}`     | Admin | —                                                     | Delete a source              |

---

## `GET /sources`

Lists every row from `spa_sources`, newest first, capped at 100.

### Response `200`

```json
[
  {
    "id": "3",
    "name": "Example News RSS",
    "type": "rss",
    "url": "https://example.com/feed",
    "config": null,
    "fetch_interval": "900",
    "last_fetched_at": "2026-07-06 14:30:00",
    "status": "active",
    "retry_count": "0",
    "next_retry_at": null,
    "created_at": "2026-07-01 09:00:00"
  }
]
```

> Numeric fields come back as strings — this is a raw `$wpdb` row, not a REST-schema-typed response.

### curl

```bash
curl -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  https://example.com/wp-json/smart-post-aggregator/v1/sources
```

---

## `POST /sources`

Creates a new source. `url` is fetched server-side on every cron sweep, so it goes through core's `wp_http_validate_url()` **plus** an explicit link-local (`169.254.0.0/16`) check — this closes the gap in core's own SSRF guard for the AWS/GCP/Azure cloud-metadata IP (`169.254.169.254`).

### Request body

| Field            | Type    | Required | Validation                    | Description                                                  | Example                          |
| ---------------- | ------- | :------: | ------------------------------ | -------------------------------------------------------------- | --------------------------------- |
| `name`           | string  | Yes      | non-empty                     | Display name                                                   | `"Example News RSS"`             |
| `type`           | string  | Yes      | `rss` or `api`                 | Which `Services\Fetchers\*` implementation handles this source | `"rss"`                           |
| `url`            | string  | Yes      | valid URL, not link-local      | Feed or API endpoint                                            | `"https://example.com/feed"`     |
| `fetch_interval` | integer | No       | > 0 (default `900`)            | Seconds between fetches for this source                        | `900`                             |
| `config`         | object  | No       | required shape for `type: api` | Field-mapping for JSON API sources (see below)                 | see below                          |

For `type: "api"`, `config` maps arbitrary JSON response shapes onto the normalized item shape `Services\Fetchers\ApiFetcher` expects:

| Key                  | Default        | Meaning                                                              |
| ---------------------- | ---------------- | ----------------------------------------------------------------------- |
| `items_path`          | `""` (root)     | Dot-notation path to the array of items, e.g. `"data.results"`         |
| `id_field`            | `"id"`          | Key holding each item's unique/external ID                             |
| `title_field`         | `"title"`       | Key holding the item's title                                           |
| `content_field`       | `"content"`     | Key holding the item's body content                                    |
| `link_field`          | `"link"`        | Key holding the item's URL                                             |
| `published_at_field`  | `"published_at"`| Key holding the item's publish date                                    |

Example (RSS source):

```json
{
  "name": "Example News RSS",
  "type": "rss",
  "url": "https://example.com/feed",
  "fetch_interval": 900
}
```

Example (JSON API source):

```json
{
  "name": "Example JSON API",
  "type": "api",
  "url": "https://api.example.com/v1/articles",
  "fetch_interval": 1800,
  "config": {
    "items_path": "data.articles",
    "id_field": "uuid",
    "title_field": "headline",
    "content_field": "body",
    "link_field": "url",
    "published_at_field": "published_at"
  }
}
```

### Response `200`

Returns the newly created row (same shape as a `GET /sources` item).

### Errors

| Status | Condition                                                        |
| ------ | ------------------------------------------------------------------ |
| `400`  | `type` is not `rss` or `api`                                      |
| `400`  | `name` or `url` missing                                            |
| `400`  | `url` fails `wp_http_validate_url()`, or resolves to a link-local address |
| `500`  | Insert into `spa_sources` failed                                   |

### curl

```bash
curl -X POST https://example.com/wp-json/smart-post-aggregator/v1/sources \
  -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  -H "Content-Type: application/json" \
  -d '{"name":"Example News RSS","type":"rss","url":"https://example.com/feed"}'
```

---

## `DELETE /sources/{id}`

Deletes a source by ID. Does not affect any `spa_content` already ingested from it.

### Response `200`

```json
{ "deleted": 3 }
```

### curl

```bash
curl -X DELETE https://example.com/wp-json/smart-post-aggregator/v1/sources/3 \
  -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx"
```
