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

php -l app/Controllers/TaskController.php
php -l app/Controllers/FlowController.php
php -l app/Services/TaskService.php
php -l app/Services/FlowBuilderService.php
php -l app/Services/AutomationEngineService.php
php -l app/Repositories/TaskRepository.php
php -l app/Repositories/ScheduleRepository.php
php -l app/Repositories/ChatbotFlowRepository.php
php -l app/Repositories/ChatbotNodeRepository.php
php -l app/Repositories/ChatbotEdgeRepository.php
php -l app/Repositories/ChatbotExecutionLogRepository.php

php -l app/Controllers/AIController.php
php -l app/Services/HybridDecisionService.php
php -l app/Services/PromptBuilderService.php
php -l app/Services/IntentClassifierService.php
php -l app/Services/ConversationMemoryService.php
php -l app/Services/AIUsageService.php
php -l app/Services/FallbackBotService.php
php -l app/Repositories/AssistantProfileRepository.php
php -l app/Repositories/AIPromptRepository.php
php -l app/Repositories/AIUsageLogRepository.php
php -l app/Integrations/AI/OpenRouterProvider.php
php -l app/Integrations/AI/GeminiProvider.php
