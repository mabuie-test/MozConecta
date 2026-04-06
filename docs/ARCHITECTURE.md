# Arquitetura — FASE 5

## Módulo WhatsApp desacoplado
- Interface de provider: `WhatsAppProviderInterface`.
- Gestão de provider: `ProviderManager`.
- Serviços de domínio:
  - `WhatsAppInstanceService`
  - `PairingService`
  - `SessionSyncService`
  - `MessageInboundProcessor`
  - `MessageOutboundDispatcher`
  - `WebhookInboundService`

## Persistência
- `whatsapp_instances`
- `whatsapp_pairing_sessions`
- `whatsapp_instance_events`

## Operações
- CRUD de instâncias no painel.
- Pareamento e exibição de QR.
- Reconexão/desconexão e sync periódico.
- Trilhas técnicas de eventos para observabilidade.
