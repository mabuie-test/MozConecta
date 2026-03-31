#!/usr/bin/env bash
set -euo pipefail
php -l public/index.php
php -l app/Controllers/AuthController.php
php -l app/Services/AuthService.php
