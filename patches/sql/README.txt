Some Drupal database tables, columns etc need to be converted to utf8mb4_swedish_ci
for finnish alphabetical sorting to work as expected.

Generating the queries for all (currently existing tables) can be done by running:

SELECT CONCAT('ALTER TABLE `', TABLE_NAME,'` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci;') AS mySQL
FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA= "(DATABASE_NAME)" AND TABLE_TYPE="BASE TABLE"

This query can be run via drush:

drush sqlq --file=generate-queries.sql > update-collations.sq

Currently the main problem of terms not being sorted correctly should be fixable
with running just a few queries instead of the whole file above:

ALTER TABLE `taxonomy_term_field_data` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci;
ALTER TABLE `taxonomy_term_field_revision` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci;
ALTER TABLE `node_revision` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci;
ALTER TABLE `node_field_data` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci;

Running collation conversions for these 4 tables has been implemented in the following
update hook:
 uhsg_search_update_8001()

Converting columns to any utf8mb4-x format should be always lossless because they
support all characters. Converting from utf8mb4_x to latin1 for example however
could well lead to data loss as not all special chars can be supported in the more
limited charsets. In this case the relevant colums are already utf8mb4_general_ci,
and converting to utf8mb4_swedish_ci cannot lead to data loss.

There is usually no reason to convert to latin1 except to save index space/memory.
There are still some reasons to consider each database table separately to avoid
overlong key related issues. This issue depends on database version, config
and resources. Testing this locally is problematic for example because we have
mariadb 10.4 instead of 10.1 (currently in production).

For comparison, search_api enforces a specific collation to be utf8 instead of
utf8mb4 in:
  search_api/modules/search_api_db/src/DatabaseCompatibility/MySql.php

The collation is enforced to:
     'utf8mb4' with 'utf8mb4_bin' collation for text.
     'utf8' with 'utf8_general_ci collation for non-text

Search API dev comments:
// The Drupal MySQL integration defaults to using a 4-byte-per-character
// encoding, which would make it impossible to use our normal 255 characters
// long varchar fields in a primary key (since that would exceed the key's
// maximum size). Therefore, we have to convert all tables to the "utf8"
// character set – but we only want to make fulltext tables case-sensitive.

We should obviously not re-convert the collation to utf8mb4 (_swedish_ci) globally.

Related reading:
https://medium.com/@jesseproudman/getting-out-of-mysql-character-set-hell-8431e75383db
https://stackoverflow.com/questions/5575491/what-will-happen-to-existing-data-if-i-change-the-collation-of-a-column-in-mysql
https://stackoverflow.com/questions/6724551/converting-mysql-database-to-support-multiple-languages
https://drupal.stackexchange.com/questions/166405/why-are-we-using-utf8mb4-general-ci-and-not-utf8mb4-unicode-ci
