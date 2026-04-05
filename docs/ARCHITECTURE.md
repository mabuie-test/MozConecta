# Arquitetura — FASE 4

## Billing desacoplado
A integração de pagamentos está em `app/Integrations/Payments` com providers separados por método de cobrança:
- `DebitoMpesaProvider`
- `DebitoEmolaProvider`

`PaymentService` coordena checkout, persistência e logs.

## Serviços de pagamento
- `BillingService`: planos, invoice e histórico financeiro.
- `PaymentService`: inicia cobrança e grava referências do gateway.
- `PaymentStatusPollingService`: consulta estado e actualiza subscrição.
- `SubscriptionService`: activa/falha assinatura conforme pagamento.
- `WebhookPaymentService`: preparado para callbacks.

## Persistência e auditoria
- `invoices`, `payments`, `payment_transactions` com campos de provider.
- `payment_provider_logs` para logs técnicos de request/response.
- `audit_logs` para rastreabilidade de eventos de cobrança.

## Segurança operacional
- autenticação Débito com token cacheado e renovação automática.
- timeout configurável por env.
- payloads e respostas guardadas para troubleshooting.
