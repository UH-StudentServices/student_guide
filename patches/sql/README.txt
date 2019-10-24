Drupal database tables, columns etc need to be conversed to utf8mb4_swedish_ci
for finnish alphabetical ordering to work.

Generating the queries for all (currently existing tables) can be done by running:

SELECT CONCAT('ALTER TABLE `', TABLE_NAME,'` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci;') AS mySQL
FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA= "(DATABASE_NAME)" AND TABLE_TYPE="BASE TABLE"

This query can be run via drush:

drush sqlq --file=generate-queries.sql > update-collations.sq

Currently the main problem of terms not being sorted correctly should be fixable
with running just a few queries:
ALTER TABLE `taxonomy_term_field_data` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci;
ALTER TABLE `node_revision` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci;
ALTER TABLE `node_field_data` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci;


Converting columns to any utf8mb4-x format should be always lossless because they
support all characters. Converting from utf8mb4_x to latin1 for example however
could well lead to data loss as not all special chars can be supported in the more
limited charsets. In this case the relevant colums are already utf8mb4_general_ci,
and converting to utf8mb4_swedish_ci cannot lead to data loss.

There is no reason to convert to latin1 except to save index space/memory.
There are still some reasons to consider each database table separately to avoid
overlong key related issues. For example search_api enforces in:
  search_api/modules/search_api_db/src/DatabaseCompatibility/MySql.php

The collation is enforced to:
     'utf8mb4' with 'utf8mb4_bin' collation for text.
     'utf8' with 'utf8_general_ci collation for non-text


// The Drupal MySQL integration defaults to using a 4-byte-per-character
// encoding, which would make it impossible to use our normal 255 characters
// long varchar fields in a primary key (since that would exceed the key's
// maximum size). Therefore, we have to convert all tables to the "utf8"
// character set – but we only want to make fulltext tables case-sensitive.


Related reading:
https://medium.com/@jesseproudman/getting-out-of-mysql-character-set-hell-8431e75383db
https://stackoverflow.com/questions/5575491/what-will-happen-to-existing-data-if-i-change-the-collation-of-a-column-in-mysql
https://stackoverflow.com/questions/6724551/converting-mysql-database-to-support-multiple-languages
https://drupal.stackexchange.com/questions/166405/why-are-we-using-utf8mb4-general-ci-and-not-utf8mb4-unicode-ci
