#!/bin/sh
# Recria o banco de teste a partir dos scripts de database/ (sem o seed).
set -e

DB="mariadb -h db -uroot -proot --skip-ssl"

$DB -e "DROP DATABASE IF EXISTS livros_test; CREATE DATABASE livros_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
cat database/01_schema.sql database/02_view.sql | $DB livros_test
