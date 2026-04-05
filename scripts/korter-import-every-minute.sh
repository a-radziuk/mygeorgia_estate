#!/usr/bin/env bash
# Runs `php artisan korter:import` once per minute until stopped (Ctrl+C).
# Usage: from repo root: ./scripts/korter-import-every-minute.sh
# Or:    bash scripts/korter-import-every-minute.sh

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT" || exit 1

while true; do
  echo ""
  echo "======== $(date '+%Y-%m-%d %H:%M:%S') — korter:import ========"
  php artisan korter:import 2>&1
  exit_code=$?
  echo "-------- finished with exit code ${exit_code} --------"
  sleep 60
done
