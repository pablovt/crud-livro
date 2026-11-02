#!/bin/sh
set -e

# Instala as dependências no primeiro start (o código é montado como volume).
if [ -f composer.json ] && [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

exec "$@"
