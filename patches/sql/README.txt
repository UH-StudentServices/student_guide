Drupal database tables, columns etc need to be conversed to utf8mb4_swedish_ci
for finnish alphabetical ordering to work.

Generating the queries for all (currently existing tables) can be done by running:

SELECT CONCAT('ALTER TABLE `', TABLE_NAME,'` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci;') AS mySQL
FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA= "(DATABASE_NAME)" AND TABLE_TYPE="BASE TABLE"

This query can be run via drush:

drush sqlq --file=generate-queries.sql > update-collations.sq

Currently the main problem of terms not being sorted correctly should be fixable
with running just one query:
ALTER TABLE `taxonomy_term_field_data` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_swedish_ci;


Converting columns to any utf8mb4-x format should be always lossless because they
support all characters. Converting from utf8mb4_x to latin1 for example however
could well lead to data loss as not all special chars can be supported in the more
limited charsets. But there is no reason to convert to latin1 except to safe
index space/memory.

Related reading:
https://medium.com/@jesseproudman/getting-out-of-mysql-character-set-hell-8431e75383db
https://stackoverflow.com/questions/5575491/what-will-happen-to-existing-data-if-i-change-the-collation-of-a-column-in-mysql
https://stackoverflow.com/questions/6724551/converting-mysql-database-to-support-multiple-languages
https://drupal.stackexchange.com/questions/166405/why-are-we-using-utf8mb4-general-ci-and-not-utf8mb4-unicode-ci
