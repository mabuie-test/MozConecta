#!/usr/bin/env bash
set -euo pipefail
php -l public/index.php
php -l bootstrap/app.php
php -l app/Core/Application.php
php -l app/Support/Router.php
php -l app/Controllers/WhatsAppInstanceController.php
php -l app/Integrations/WhatsApp/ProviderManager.php
php -l app/Integrations/WhatsApp/WhatsAppInstanceService.php
php -l app/Integrations/WhatsApp/PairingService.php
php -l app/Integrations/WhatsApp/SessionSyncService.php
php -l app/Repositories/WhatsAppInstanceRepository.php
php -l app/Repositories/WhatsAppPairingSessionRepository.php
php -l app/Repositories/WhatsAppInstanceEventRepository.php

php -l app/Controllers/InboxController.php
php -l app/Controllers/CRMController.php
php -l app/Services/InboxService.php
php -l app/Services/CRMService.php
php -l app/Repositories/ConversationRepository.php
php -l app/Repositories/ConversationMessageRepository.php
php -l app/Repositories/ContactRepository.php
php -l app/Repositories/FunnelRepository.php
