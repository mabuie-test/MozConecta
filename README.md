# MozConecta SaaS — FASE 4 (Billing + Débito API)

FASE 4 implementa módulo completo de billing com checkout de planos, cobrança C2B (M-Pesa/eMola) via API Débito, consulta de status e activação de assinatura.

## O que foi implementado
- Planos e checkout com criação de invoice.
- Pagamentos via Débito:
  - M-Pesa: `POST /api/v1/wallets/{wallet_id}/c2b/mpesa`
  - eMola: `POST /api/v1/wallets/{wallet_id}/c2b/emola`
- Autenticação Débito via `POST /api/v1/login` com cache de token.
- Consulta de status via `GET /api/v1/transactions/{debito_reference}/status`.
- Polling de pagamentos pendentes e activação automática de assinatura quando sucesso.
- Histórico financeiro, assinatura atual e páginas de billing.
- Logs técnicos de provider e auditoria de eventos de pagamento.

## Variáveis de ambiente Débito
```env
DEBITO_BASE_URL=
DEBITO_EMAIL=
DEBITO_PASSWORD=
DEBITO_WALLET_ID=
DEBITO_TIMEOUT=20
DEBITO_STATUS_POLLING_ENABLED=true
DEBITO_STATUS_POLLING_INTERVAL=60
```

## Rotas de billing
- `GET /billing/plans`
- `GET /billing/checkout?plan={slug}`
- `POST /billing/checkout`
- `GET /billing/payment-status?payment_id={id}`
- `GET /billing/history`
- `GET /billing/subscription`
- `POST /billing/change-plan`

## Migrações
```bash
mysql -u root -p mozconecta < database/migrations/001_core_multitenant.sql
mysql -u root -p mozconecta < database/migrations/002_auth_onboarding_security.sql
mysql -u root -p mozconecta < database/migrations/003_billing_debito.sql
```

## Seeders
```bash
mysql -u root -p mozconecta < database/seeds/001_subscription_statuses.sql
mysql -u root -p mozconecta < database/seeds/002_roles_permissions.sql
mysql -u root -p mozconecta < database/seeds/003_plans.sql
mysql -u root -p mozconecta < database/seeds/004_master_admin.sql
```

## Nota de produção
As classes Débito usam chamadas HTTP reais via cURL. Para produção:
- configurar credenciais reais;
- habilitar TLS válido no servidor;
- ligar polling em cron/worker.
