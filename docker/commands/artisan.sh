#!/bin/bash
# Allocate a TTY only when stdin is one, so this works both interactively and
# when called from non-interactive contexts like setup.sh.
if [ -t 0 ]; then TTY=-it; else TTY=-i; fi
docker exec $TTY php_canalizador php artisan "$@"
