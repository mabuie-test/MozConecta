# MozConecta SaaS — FASE 5 (Instâncias WhatsApp)

FASE 5 entrega a camada completa de instâncias WhatsApp com abstração por provider API, pareamento, sincronização de sessão, eventos técnicos e telas do painel.

## Componentes principais
Em `app/Integrations/WhatsApp`:
- `WhatsAppProviderInterface`
- `ProviderManager`
- `WhatsAppInstanceService`
- `PairingService`
- `SessionSyncService`
- `MessageInboundProcessor`
- `MessageOutboundDispatcher`
- `WebhookInboundService`
- `GenericApiProvider` (provider HTTP real)

## Funcionalidades
- Criar, editar e eliminar instância.
- Iniciar pareamento (QR/link/code).
- Reconnect e disconnect.
- Listagem e detalhe com eventos/sessões.
- Sincronização de status das instâncias.
- Persistência de logs técnicos por instância.

## Novas tabelas
- `whatsapp_instances`
- `whatsapp_pairing_sessions`
- `whatsapp_instance_events`

## Rotas WhatsApp
- `GET /whatsapp/instances`
- `POST /whatsapp/instances/create`
- `POST /whatsapp/instances/edit`
- `POST /whatsapp/instances/pair`
- `POST /whatsapp/instances/reconnect`
- `POST /whatsapp/instances/disconnect`
- `POST /whatsapp/instances/delete`
- `POST /whatsapp/instances/sync`
- `GET /whatsapp/instances/show?id={id}`

## Variáveis de ambiente WhatsApp
```env
WHATSAPP_PROVIDER_DEFAULT=generic_api
WHATSAPP_API_BASE_URL=
WHATSAPP_API_KEY=
WHATSAPP_WEBHOOK_SECRET=change-me
WHATSAPP_SYNC_ENABLED=true
WHATSAPP_SYNC_INTERVAL=120
```

## Migrações
```bash
mysql -u root -p mozconecta < database/migrations/001_core_multitenant.sql
mysql -u root -p mozconecta < database/migrations/002_auth_onboarding_security.sql
mysql -u root -p mozconecta < database/migrations/003_billing_debito.sql
mysql -u root -p mozconecta < database/migrations/004_whatsapp_instances.sql
```


## FASE 6 — Inbox, CRM e Pipeline

Esta fase adiciona `inbox` multiatendente e CRM nativo com pipeline visual:

- Migration: `database/migrations/005_inbox_crm_pipeline.sql`
- Novos módulos web:
  - `/inbox` e `/inbox/show`
  - `/crm/contacts`
  - `/crm/pipeline`
- Serviços:
  - `App\Services\InboxService`
  - `App\Services\CRMService`
- Recursos principais:
  - timeline com mensagens recebidas/enviadas/notas internas
  - filtros e busca em inbox e CRM
  - atribuição, takeover e mudança de estado de conversa
  - lead score básico por engajamento/valor/prioridade
  - movimentação de lead entre estágios com auditoria


## FASE 7 — Tarefas, Follow-up e Fluxos Automáticos

Esta fase adiciona gestão de tarefas e construtor de fluxos com execução rastreável:

- Migration: `database/migrations/006_tasks_flows_automation.sql`
- Módulo de tarefas: `/tasks`
- Módulo de fluxos: `/flows` e `/flows/show`
- Serviços:
  - `App\Services\TaskService`
  - `App\Services\FlowBuilderService`
  - `App\Services\AutomationEngineService`
- Recursos:
  - estados de tarefa (`pending`, `in_progress`, `done`, `cancelled`, `overdue`)
  - criação de follow-up e marcação automática de vencidas
  - nós e arestas de fluxo com regras por keyword/tempo/tag/fallback
  - logs de execução em `chatbot_execution_logs`
  - agendamentos em `schedules` para wait_reply/remarketing/resume


## FASE 8 — IA por API e Motor Híbrido

Esta fase adiciona providers IA desacoplados e decisão híbrida fluxo/regra/IA/humano:

- Migration: `database/migrations/007_ai_hybrid_engine.sql`
- Módulo de configuração IA por tenant: `/ai/settings`
- Serviços:
  - `App\Services\PromptBuilderService`
  - `App\Services\IntentClassifierService`
  - `App\Services\ConversationMemoryService`
  - `App\Services\AIUsageService`
  - `App\Services\FallbackBotService`
  - `App\Services\HybridDecisionService`
- Integração com inbox/fluxos:
  - inbound WhatsApp processa automação e depois motor híbrido
  - logs de prompts/respostas em `ai_prompts`
  - tracking de consumo em `ai_usage_logs` com bloqueio por limite de plano


## FASE 9 — Campanhas, Remarketing, Internet Bot e Notificações

Esta fase adiciona marketing operacional e alertas internos:

- Migration: `database/migrations/008_campaigns_internet_notifications.sql`
- Módulos:
  - `/campaigns` (campanhas/remarketing)
  - `/internet` (bot de venda de internet)
  - `/notifications` (alertas internos)
- Serviços:
  - `App\Services\CampaignService`
  - `App\Services\InternetSalesBotService`
  - `App\Services\NotificationService`
- Recursos:
  - segmentação por tags, estágio, clientes frios, perdidos e pós-venda
  - lote controlado, pausa/retoma/cancelamento e relatório de campanha
  - pacotes e pedidos de internet com tagging automático
  - notificações para nova mensagem, novo lead, pagamento, trial, tarefa vencida, instância desconectada, campanha concluída e limite próximo
