# Arquitetura de Produto

## Camadas
- **Controllers**: recebem requisição e coordenam caso de uso.
- **Services**: regra de negócio (trial, billing, decisão de automação).
- **Repositories**: persistência via PDO.
- **Integrations**: adapters de API externa.
- **Middleware**: autenticação, (futuro: tenant guard, assinatura, rate limit, CSRF).

## Multi-tenancy
Todas as tabelas operacionais possuem `tenant_id`; controle de acesso por sessão/perfil.

## Billing
Estados: `trial_active`, `trial_expired`, `active`, `past_due`, `suspended`, `cancelled`.

## WhatsApp
Provider abstraction com status de instância e sessões de pareamento.

## IA
`AIProviderInterface` + `AIManager` para plugabilidade (Gemini/OpenRouter).
