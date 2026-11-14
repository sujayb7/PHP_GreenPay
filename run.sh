#!/usr/bin/env bash
# Starts GreenPay with PHP's built-in web server on 127.0.0.1 only.
#   ./run.sh                 http://127.0.0.1:8080, logs in ./logs/app.log
#   PORT=9090 ./run.sh       another port
#   GREENPAY_LOG_DIR=/path/to/folder ./run.sh   write app.log somewhere else (for example ResolveAI's demo-systems/live/logs/greenpay)
set -euo pipefail
cd "$(dirname "$0")"
export XDEBUG_MODE=off
[ -f data/greenpay.sqlite ] || ./install.sh
PORT="${PORT:-8080}"
echo "GreenPay on http://127.0.0.1:${PORT}  (logs: ${GREENPAY_LOG_DIR:-./logs}/app.log)  Ctrl+C to stop"
exec php -S "127.0.0.1:${PORT}" -t public
