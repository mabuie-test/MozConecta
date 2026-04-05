# Base de Dados — FASE 2 (Multi-tenant)

## Separação global vs tenant

### Entidades globais (`tenant_id` nulo ou inexistente)
- `users` (identidade global)
- `roles`, `permissions`, `role_permissions`
- `plans`
- `subscription_statuses`
- `settings` (quando `tenant_id IS NULL`)

### Entidades por tenant
- `tenants`
- `tenant_users`
- `subscriptions`
- `invoices`
- `payments`
- `integrations` (pode ser global ou tenant-scoped)
- `audit_logs`
- `notifications`
- `support_tickets`
- `settings` (quando `tenant_id` preenchido)

## Tabelas principais

### tenants
- PK: `id`
- Chaves únicas: `uuid`, `slug`
- Soft delete: `deleted_at`
- Índices: `status`, `deleted_at`

### users
- PK: `id`
- Chaves únicas: `uuid`, `email`
- Campo de segurança: `failed_attempts`, `locked_until`
- Admin global: `is_master_admin`
- Soft delete: `deleted_at`

### tenant_users
- Relaciona utilizador ao tenant e papel (`role_id`)
- FK: `tenant_id`, `user_id`, `role_id`, `invited_by`
- Controle de owner: `is_owner`
- Soft delete: `deleted_at`

### plans
- Catálogo comercial global
- Limites: mensagens, IA, instâncias, utilizadores
- Recursos em `feature_flags_json`
- Soft delete: `deleted_at`

### subscription_statuses
- Estados normalizados: trial_active, trial_expired, active, past_due, suspended, cancelled

### subscriptions
- FK para `tenants`, `plans`, `subscription_statuses`
- Janelas: trial, período atual, cancelamento/suspensão
- Soft delete: `deleted_at`

### invoices / payments / payment_transactions
- Cadeia financeira completa com idempotência em transações (`idempotency_key`)
- Soft delete em invoices/payments

### settings
- Configuração global e por tenant (`tenant_id` nullable)
- Chave única por escopo: `(tenant_id, key_name)`

### audit_logs
- Auditoria técnica e funcional por entidade/ação
- Índices por tenant e entidade

### notifications
- Notificações para utilizador/tenant com marcação de leitura `read_at`

### support_tickets
- Tickets de suporte multi-tenant
- Status e prioridade com índices de operação
- Soft delete: `deleted_at`

## Índices e isolamento
- Quase todas as consultas operacionais usam `tenant_id` + estado/tempo.
- Todas as entidades tenant-aware possuem FK para `tenants`.
- Repositórios devem obrigatoriamente filtrar por `tenant_id` nas fases seguintes.

## Extensões FASE 3 (Auth/Security)

### password_resets
- Token com hash + expiração + `used_at`.
- Permite recuperação segura de senha.

### login_logs
- Histórico de tentativas de login (sucesso/falha), IP e user-agent.
- Base para auditoria e anti-abuso.

### verification_tokens
- Estrutura de verificação por email/OTP.
- Preparado para integração de providers externos de envio.

## Extensões FASE 5 (WhatsApp)

### whatsapp_instances
- Guarda estado operacional de cada instância por tenant.
- Inclui status de conexão, QR, session token, erro técnico e metadata.

### whatsapp_pairing_sessions
- Histórico de tentativas/sessões de pareamento.
- Guarda QR e payload retornado pelo provider.

### whatsapp_instance_events
- Trilhas técnicas de eventos (create, pair, sync, reconnect, inbound/outbound).
- Base para observabilidade e troubleshooting.


## FASE 6 — Inbox, CRM e Pipeline

Tabelas adicionadas:
- `contacts`: base de contactos/leads por tenant, com prioridade, origem, valor potencial e estágio do funil.
- `tags` e `contact_tags`: etiquetagem de contactos.
- `conversations`: thread de inbox por contacto com estado, responsável e takeover.
- `conversation_messages`: timeline de mensagens inbound/outbound/system e payload técnico.
- `funnels` e `funnel_stages`: pipeline comercial visual por tenant.
- `lead_scores`: score agregado básico por contacto.

Índices operacionais cobrem: busca por tenant/status, timeline por conversa, score por tenant e filtros de CRM.


## FASE 7 — Tarefas, Schedules e Fluxos

Tabelas adicionadas:
- `tasks`: gestão de tarefas/follow-up com estados operacionais e vencimento.
- `schedules`: agendamento de eventos (follow-up, remarketing, resume de fluxo).
- `chatbot_flows`: definição do fluxo, trigger, fallback e flags de reentrada/remarketing.
- `chatbot_nodes`: nós configuráveis do construtor.
- `chatbot_edges`: ligações condicionais entre nós.
- `chatbot_execution_logs`: rastreio técnico da execução do motor.


## FASE 8 — IA por API e Motor Híbrido

Tabelas adicionadas:
- `assistant_profiles`: configuração do assistente por tenant (persona, idioma, tom, FAQ, políticas, objetivos, provider principal/fallback).
- `ai_prompts`: log de prompts, respostas, provider/model e status.
- `ai_usage_logs`: tracking de consumo de IA para controlo de limites e auditoria.


## FASE 9 — Campanhas, Internet Bot e Notificações

Tabelas adicionadas:
- `campaigns`: configuração da campanha, segmentação, status, lote e métricas.
- `campaign_contacts`: fila/rastreio de envio por contacto.
- `internet_packages`: catálogo de pacotes de internet por tenant.
- `internet_orders`: pedidos comerciais do bot de internet com status de acompanhamento.

A tabela `notifications` (fase anterior) passa a ser usada ativamente para alertas operacionais internos.
