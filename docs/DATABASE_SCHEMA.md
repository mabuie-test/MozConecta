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
