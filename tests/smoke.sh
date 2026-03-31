#!/usr/bin/env bash
set -euo pipefail
php -l public/index.php
php -l bootstrap/app.php
php -l app/Core/Application.php
php -l app/Support/Router.php
php -l app/Exceptions/ExceptionHandler.php
php -l app/Middleware/TenantMiddleware.php
php -l app/Middleware/SubscriptionMiddleware.php
php -l app/Middleware/AdminMiddleware.php
