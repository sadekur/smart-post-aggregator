=== Smart Post Aggregator ===
Contributors: codexpert
Donate link: https://github.com/sadekur/smart-post-aggregator
Tags: content aggregator, rss feed, duplicate content, feed importer, cron
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 0.9
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Aggregate content from multiple RSS feeds and JSON APIs into your site automatically, with built-in near-duplicate detection.

== Description ==

Smart Post Aggregator pulls content in from external RSS feeds and JSON APIs on a recurring schedule and turns each new item into an "Aggregated Content" entry on your site — without you lifting a finger after the initial setup. Because the same story often gets pulled in from more than one source, the plugin also checks every new item against your recently aggregated content and flags anything that looks like a near-duplicate, so you can review it before it's treated as fresh content.

= Features =

* **Multiple source types** — add unlimited RSS feeds, or JSON API endpoints with a configurable field mapping (which JSON key holds the title, content, link, publish date, etc.).
* **Automatic background sync** — a recurring sweep checks every active source and fetches only what's due, based on each source's own fetch interval, so you don't need to configure a cron job yourself.
* **Resilient fetching** — a source that fails to respond is retried with exponential backoff; after repeated failures it's automatically flagged so you can fix or remove it, instead of being retried forever.
* **Duplicate detection** — every newly aggregated item is compared against recent content using an exact-match check plus a configurable similarity algorithm (word-overlap scoring). Items are automatically marked unique, queued for manual review, or flagged as a duplicate depending on how closely they match.
* **Review inbox** — anything that isn't a clear-cut case is queued for you to resolve by hand: approve it as unique after all, confirm it as a duplicate, merge it into the original, or discard it.
* **Dashboard** — at-a-glance counts of content aggregated today/this week/in total, how many items are pending review, and which sources are currently erroring out.
* **Audit log** — a filterable history of every duplicate check the plugin has ever run, including the score, the algorithm used, and who resolved it (if anyone).
* **Tunable sensitivity** — adjust the similarity threshold and the review margin around it from Settings, and choose whether confirmed duplicates are simply flagged or moved straight to the trash.

= How it works =

1. You register one or more sources (RSS feed URLs, or JSON API endpoints) under **Sources**.
2. A background sweep runs automatically every 15 minutes and fetches any source that is due, based on the fetch interval you set for it.
3. Each new item from a source becomes an "Aggregated Content" entry.
4. The item is immediately checked against your recently aggregated content. An exact match is flagged right away; anything else is scored for similarity.
5. Depending on the score, the item is left alone, queued in **Review**, or marked as a duplicate.
6. You resolve anything sitting in Review, and everything that happened along the way is recorded in **Logs**.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/smart-post-aggregator` directory, or install the plugin directly through the WordPress Plugins screen.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to the **Post Aggregator** menu in your WordPress admin.
4. Open **Sources** and add at least one RSS feed or API endpoint to start aggregating content.
5. Content aggregation then runs automatically in the background — there's nothing further to configure unless you want to adjust the duplicate-detection sensitivity under **Settings**.

== Frequently Asked Questions ==

= What kinds of sources can I aggregate from? =

RSS/Atom feeds, and JSON API endpoints. For a JSON API source you provide a small field mapping (which key in the response holds the item list, its ID, title, content, link, and publish date), so the plugin can normalize responses from arbitrary APIs.

= How often is content fetched? =

A single background sweep runs every 15 minutes, but each source is only actually re-fetched once its own configured fetch interval has elapsed (900 seconds / 15 minutes by default, adjustable per source). This keeps the load on any one external source low regardless of how many sources you add.

= What happens if a source goes down or starts erroring? =

It's retried automatically with exponential backoff. If it keeps failing past a handful of attempts, the source is marked as erroring and stops being retried automatically until you check on it — you'll see it called out on the Dashboard.

= How does duplicate detection decide what's a duplicate? =

Every new item is first checked for an exact content match against existing aggregated content. If there's no exact match, it's compared for similarity (word overlap) against your recently aggregated content. If the similarity score is below your configured threshold it's treated as unique; if it's within the "review margin" above the threshold it's queued for you to review by hand; well above the threshold, it's flagged as a duplicate automatically.

= Can I control how sensitive duplicate detection is? =

Yes — under **Settings** you can adjust the similarity threshold (%) and the review margin around it, and choose whether confirmed duplicates should simply be flagged (kept, but marked) or moved to the trash automatically.

= What can I do with an item sitting in the Review inbox? =

Approve it as unique after all, confirm it as a duplicate, merge it into the matching original (carrying over a thumbnail if the original doesn't have one), or ignore/discard it. None of these permanently delete anything outright — discarded and merged items are moved to the trash, so they can still be recovered from there.

= Where does aggregated content show up on my site? =

Each aggregated item is stored as its own content type with its own archive, separate from your regular posts, so your existing post listings aren't affected by aggregation.

== Screenshots ==

1. Dashboard — aggregation stats, pending review count, and recent activity at a glance.
2. Sources — add and manage the RSS feeds and API endpoints being aggregated.
3. Review — resolve items flagged as possible duplicates.
4. Logs — a full, filterable audit trail of every duplicate check.
5. Settings — tune the duplicate-detection algorithm, threshold, and review margin.

== Changelog ==

= 0.9 =
* Initial release: RSS/API source aggregation with a background sync sweep, exact-match + similarity-based duplicate detection, a review workflow for flagged items, and an admin dashboard for stats, sources, review, and logs.

== Upgrade Notice ==

= 0.9 =
Initial release.
