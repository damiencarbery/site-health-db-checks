=== Site Health - add database checks ===
Contributors: daymobrew
Tags: site health
Requires at least: 6.7
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.1.20251126
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Add some database checks to Site Health - look for orphaned postmeta and large options values.

== Description ==
At the end of Rodolfo Melogli's Woo Masterclass on [WooCommerce database tables](https://www.businessbloomer.com/class/woocommerce-database-walkthrough-tables-explained/)
he had a few slides about database maintenance. He listed two queries that I found interesting:
- counting the number of orphaned postmeta rows
- list options with large option values.

I decided to write Site Heath tests to report on these queries.

= Orphaned Postmeta =
Rodolfo's query to count the number of orphaned postmeta rows is:
`SELECT COUNT(*) FROM wp_postmeta pm LEFT JOIN wp_posts p ON pm.post_id = p.ID WHERE p.ID IS NULL;`
and then using
`DELETE pm FROM wp_postmeta pm LEFT JOIN wp_posts p ON pm.post_id = p.ID WHERE p.ID IS NULL;`
to delete the orphaned rows.

In this plugin it suggests using the [Advanced Database Cleaner plugin](https://wordpress.org/plugins/advanced-database-cleaner/) to delete the orphaned postmeta.

= Large options values =
Rodolfo's query lists the options with large values:
`SELECT option_name, LENGTH(option_value)/1024 AS size_kb FROM wp_options WHERE autoload='yes' ORDER BY size_kb DESC LIMIT 20;`

For my Site Health test I changed this to count the number of rows with large values.
`SELECT COUNT(option_name) FROM wp_options WHERE autoload='yes' AND LENGTH(option_value) > 1024`
This change was inspired by [a reply in the Fixing WordPress forum](https://wordpress.org/support/topic/autoload-wp_installer_settings/#post-18624756).

= Future ideas =
Maybe this plugin could be expanded to offer to show the large options and their values.
