#!/usr/bin/env bash
set -euo pipefail
php -l public/index.php
php -l bootstrap/app.php
php -l app/Core/Application.php
php -l app/Support/Router.php
php -l app/Controllers/AuthController.php
php -l app/Controllers/BillingController.php
php -l app/Integrations/Payments/DebitoClient.php
php -l app/Integrations/Payments/PaymentService.php
php -l app/Integrations/Payments/PaymentStatusPollingService.php
php -l app/Repositories/PaymentRepository.php
