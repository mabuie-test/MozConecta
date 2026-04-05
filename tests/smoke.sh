#!/usr/bin/env bash
set -euo pipefail
php -l public/index.php
php -l bootstrap/app.php
php -l app/Core/Application.php
php -l app/Support/Router.php
php -l app/Controllers/AuthController.php
php -l app/Controllers/ProfileController.php
php -l app/Services/AuthService.php
php -l app/Middleware/ProfileMiddleware.php
php -l app/Repositories/PasswordResetRepository.php
