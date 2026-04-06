# Rotas principais da plataforma

## Autenticação
- `GET /login`
- `POST /login`
- `GET /register`
- `POST /register`
- `POST /logout`

## Billing
- `GET /billing/plans`
- `POST /billing/checkout`
- `GET /billing/history`

## WhatsApp
- `GET /whatsapp/instances`
- `POST /whatsapp/instances/create`
- `POST /webhooks/whatsapp`

## Inbox / CRM
- `GET /inbox`
- `GET /crm/contacts`
- `GET /crm/pipeline`

## Fluxos / Tarefas
- `GET /flows`
- `POST /flows/nodes/add`
- `GET /tasks`

## IA
- `GET /ai/settings`
- `POST /ai/settings/save`
- `POST /ai/test-hybrid`

## Campanhas / Internet / Notificações
- `GET /campaigns`
- `POST /campaigns/run`
- `GET /internet`
- `GET /notifications`

## Admin global
- `GET /admin`
- `POST /admin/cms/save`

- `GET /billing/payment-detail`
- `POST /billing/payment-status/recheck`
- `POST /billing/payment/retry`
- `POST /webhooks/debito` (callback opcional)
