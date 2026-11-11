# Models

This folder contains the database logic.

A model file is strictly for SQL and shouldn't contain HTML, `$_POST handling` and `$_SESSION checks`

Functions must accept `$pdo` as the first parameter, execute queries, and return results.

## Files