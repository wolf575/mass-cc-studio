#!/bin/sh
set -e

# The persistent data directory may be a bind-mounted host path owned by root.
# Ensure Apache (www-data) can write to it before starting, so credentials,
# the session secret, and rate-limit state persist correctly.
mkdir -p /data
chown -R www-data:www-data /data 2>/dev/null || true

exec "$@"