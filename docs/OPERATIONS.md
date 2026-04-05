# Operação e produção

## Instalação rápida
1. Copiar `.env.example` para `.env`.
2. Configurar credenciais MySQL/MariaDB e chaves de API.
3. Executar migrations na ordem de `database/schema.sql`.
4. Executar seeds em `database/seeds`.
5. Rodar `composer test` para smoke checks.

## Segurança
- Habilitar `CSRF_ENABLED=true`.
- Em produção usar `SESSION_SECURE_COOKIE=true` e HTTPS.
- Definir `WHATSAPP_VALIDATE_SIGNATURE=true` para validar assinatura HMAC dos webhooks.
- Definir `APP_KEY` forte e única para criptografia de segredos.

## Monitoramento
- Logs técnicos em `storage/logs/app.log`.
- Auditoria funcional em `audit_logs`.
- Logs de integrações de pagamento em `payment_provider_logs`.
- Logs de execução de fluxo em `chatbot_execution_logs`.
- Logs de IA em `ai_prompts` e consumo em `ai_usage_logs`.

## Rotinas recomendadas
- Polling de pagamentos pendentes.
- Processamento de schedules (remarketing/flow_resume).
- Expiração de trial e suspensão de assinaturas vencidas.
- Revisão diária de notificações e tickets.
