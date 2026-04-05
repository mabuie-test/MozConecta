# Camada WhatsApp — FASE 5

## Arquitetura
- `ProviderManager` resolve provider ativo por instância.
- `GenericApiProvider` faz chamadas HTTP reais para API externa.
- Serviços de aplicação desacoplados dos controllers.

## Fluxo de pareamento
1. Instância é criada no provider.
2. `PairingService` solicita pareamento.
3. QR/metadata são guardados em `whatsapp_pairing_sessions` e `whatsapp_instances`.
4. Eventos técnicos são guardados em `whatsapp_instance_events`.

## Sincronização
- `SessionSyncService` consulta status da instância no provider.
- Atualiza `status`, `last_seen_at`, `connected_at`, `disconnected_at`, `last_error`.
- Regista eventos de sync e falha.

## Webhook inbound
- `WebhookInboundService` valida `webhook_secret` da instância.
- `MessageInboundProcessor` guarda evento inbound para acoplamento futuro com Inbox/CRM.

## Campos críticos por instância
- tenant_id
- name
- phone_number
- provider_name
- provider_instance_id
- status
- pairing_mode
- qr_code
- qr_expires_at
- session_token
- webhook_secret
- last_seen_at
- connected_at
- disconnected_at
- last_error
- metadata_json
