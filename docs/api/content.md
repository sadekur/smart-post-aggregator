# Content API

Mounted at `/content`. Requires an administrator (see [System → Authentication](system.md#authentication)).

| Method | Path       | Auth  | Body | Notes                                   |
| ------ | ---------- | ----- | ---- | ---------------------------------------- |
| GET    | `/content` | Admin | —    | Paginated list of aggregated content     |

---

## `GET /content`

Lists published `spa_content` posts — the items the aggregation pipeline has ingested — for the admin Dashboard/Content screens.

### Query parameters

| Param      | Type    | Required | Default | Description                  |
| ---------- | ------- | :------: | :-----: | ----------------------------- |
| `page`     | integer | No       | `1`     | Page number (1-based)         |
| `per_page` | integer | No       | `8`     | Items per page                |

Example:

```
GET /wp-json/smart-post-aggregator/v1/content?page=2&per_page=8
```

### Response `200`

```json
[
  {
    "id": 142,
    "title": "Example headline pulled from a source",
    "link": "https://example.com/aggregated/example-headline/",
    "thumbnail": "https://example.com/wp-content/uploads/2026/07/thumb.jpg"
  },
  {
    "id": 141,
    "title": "Another aggregated item",
    "link": "https://example.com/aggregated/another-item/",
    "thumbnail": null
  }
]
```

`thumbnail` is `null` when the post has no featured image.

### Response headers

| Header            | Meaning                                  |
| ------------------ | ------------------------------------------ |
| `X-WP-Total`       | Total published `spa_content` posts        |
| `X-WP-TotalPages`  | Total pages at the requested `per_page`     |

### Errors

| Status | Condition                                    |
| ------ | --------------------------------------------- |
| `401`/`403` | Not authenticated, or not an administrator |

### curl

```bash
curl -u "admin:xxxx xxxx xxxx xxxx xxxx xxxx" \
  "https://example.com/wp-json/smart-post-aggregator/v1/content?page=1&per_page=8"
```
