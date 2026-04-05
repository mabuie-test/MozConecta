# Integração Débito (M-Pesa/eMola)

## Componentes
- `DebitoAuthService`: autenticação em `/api/v1/login` e cache de token.
- `DebitoClient`: cliente HTTP com timeout e Bearer token.
- `DebitoMpesaProvider` e `DebitoEmolaProvider`: criação de cobrança C2B.
- `PaymentStatusPollingService`: consulta status de pendentes.
- `WebhookPaymentService`: pronto para processamento por callback.

## Endpoints usados
- `POST /api/v1/login`
- `POST /api/v1/wallets/{wallet_id}/c2b/mpesa`
- `POST /api/v1/wallets/{wallet_id}/c2b/emola`
- `GET /api/v1/transactions/{debito_reference}/status`

## Campos persistidos
- `provider_name`
- `provider_reference`
- `debito_reference`
- `external_transaction_id`
- `request_payload`
- `response_payload`
- `payment_status`
- `status_checked_at`
- `paid_at`
- `failure_reason`

## Fluxo
1. Cliente escolhe plano.
2. Sistema cria invoice.
3. Cliente escolhe método (M-Pesa/eMola).
4. `PaymentService` chama provider Débito.
5. Payment fica `pending` e status é consultado periodicamente.
6. Em sucesso: assinatura activa + invoice/payment pagos + audit log.
7. Em falha: invoice/payment marcados como failed/cancelled e audit log.
