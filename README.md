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
