#!/bin/sh

# =========================================================
# Laravel Queue Worker - Hostinger Cron
# =========================================================
# Script ini dijalankan oleh Cron setiap 1 menit.
# Worker akan memproses job yang tersedia lalu berhenti
# ketika queue kosong, sehingga cocok untuk shared hosting.
# =========================================================

set -eu

# Lokasi root project Laravel.
PROJECT_DIR="$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)"

cd "$PROJECT_DIR"

# Jalankan queue worker dan berhenti ketika queue kosong.
# --tries dan --timeout bisa disesuaikan dengan kebutuhan job.
php artisan queue:work \
    --stop-when-empty \
    --sleep=3 \
    --tries=3 \
    --timeout=120
