# API Documentation

Welcome to the developer documentation for the **Smart Post Aggregator** REST API — the backend the plugin's own React admin app talks to, and that any other client can call the same way.

## General

- [System](api/system.md) — Base URL, namespace, authentication scheme, permission model, error format, response headers/pagination, the background cron sweep, and duplicate-detection defaults.

## Admin — Protected

Every endpoint below requires the caller to be logged in as a WordPress user with the `administrator` role (or `manage_options`-equivalent), or for the site to have `spa_SANDBOX` enabled for local testing.

- [Content](api/content.md) — Paginated listing of aggregated content (the `spa_content` posts created by the ingestion pipeline).
- [Sources](api/sources.md) — Manage the RSS/API sources the Cron sweep aggregates from: list, create (with SSRF-guarded URL validation), delete.
- [Duplicates](api/duplicates.md) — The Review inbox: list items still awaiting a manual decision, and resolve one (approve as unique, confirm as a duplicate, merge, or ignore).
- [Settings](api/settings.md) — Read/write the duplicate-detection configuration (algorithm, threshold, review margin, default resolution).
- [Reports](api/reports.md) — Dashboard stat tiles + recent activity feed, and the full filterable `spa_duplicate_log` audit trail.
