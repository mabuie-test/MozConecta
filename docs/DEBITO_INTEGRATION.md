# Integração Débito (M-Pesa + eMola)

## Configuração
- `DEBITO_BASE_URL=https://my.debito.co.mz`
- `DEBITO_TOKEN=` (token comum opcional)
- `DEBITO_EMAIL=` / `DEBITO_PASSWORD=` (fallback para login dinâmico em `/api/v1/login`)
- `DEBITO_WALLET_ID_MPESA=`
- `DEBITO_WALLET_ID_EMOLA=`
- `DEBITO_CALLBACK_URL=` (opcional)
- `DEBITO_TIMEOUT=30`
- `DEBITO_RETRY_COUNT=3`
- `DEBITO_STATUS_POLLING_ENABLED=true`
- `DEBITO_STATUS_POLLING_INTERVAL=60`

## Arquitetura
- `DebitoClient`: HTTP robusto (GET/POST), timeout, retries, logs técnicos.
- `DebitoAuthService`: token por `.env` ou login dinâmico com cache seguro.
- `DebitoMpesaProvider`: `/api/v1/wallets/{wallet_id}/c2b/mpesa`.
- `DebitoEmolaProvider`: `/api/v1/wallets/{wallet_id}/c2b/emola`.
- `PaymentService`: checkout e persistência completa de request/response.
- `PaymentStatusPollingService`: polling automático + atualização idempotente de negócio.
- `WebhookPaymentService`: callback opcional, sem depender exclusivamente dele.

## Endpoints Débito usados
- `POST /api/v1/login`
- `POST /api/v1/wallets/{wallet_id}/c2b/mpesa`
- `POST /api/v1/wallets/{wallet_id}/c2b/emola`
- `GET /api/v1/transactions/{debito_reference}/status`

## Fluxo de negócio
1. Cliente escolhe plano, sistema cria invoice e payment pendente.
2. Checkout chama provider correto (M-Pesa/eMola) com wallet dedicado.
3. Resposta é normalizada e persistida com logs técnicos.
4. Estado é acompanhado por polling e/ou callback.
5. Em sucesso: invoice paga + assinatura ativa + notificação + auditoria.
6. Em falha/cancelamento: marca estado final + razão de falha + notificação + histórico.
