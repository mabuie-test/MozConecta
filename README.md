# MozConecta SaaS (PHP 8.2 + MySQL 8)

Plataforma SaaS multi-tenant de automação comercial via WhatsApp com CRM, funil, automações, IA e billing local.

## Arquitetura
- MVC clássico com separação: Controllers, Services, Repositories, Integrations e Middleware.
- Multi-tenant com isolamento por `tenant_id`.
- PDO + prepared statements.
- Camadas de integração desacopladas para WhatsApp, IA e pagamentos.

## Estrutura
Consulte pastas `app/`, `config/`, `routes/`, `database/`, `public/` e `storage/`.

## Requisitos
- PHP 8.2+
- MySQL 8+ ou MariaDB compatível
- Extensão PDO MySQL

## Instalação
1. Copiar `.env.example` para `.env` e preencher variáveis.
2. Criar base de dados.
3. Executar SQL:
   - `database/migrations/001_init.sql`
   - `database/seeds/001_plans.sql`
4. Servir pasta `public/` como document root.

Exemplo local:
```bash
php -S localhost:8080 -t public
```

## Fluxo de trial 24h
No registo, cria-se tenant + owner + assinatura `trial_active` com fim em +24h.

## Módulos incluídos (base de produto)
- Landing comercial
- Auth/registro/login
- Dashboard cliente
- Dashboard admin global
- Billing e assinaturas (estrutura + serviços)
- Pagamentos M-Pesa/eMola (interfaces e stubs)
- Instâncias WhatsApp (serviços base)
- Engine de automação híbrida (decisor)
- Banco com schema completo para CRM, inbox, campanhas, funis, tasks, IA, webhooks e auditoria.

## Segurança
- `password_hash/password_verify`
- Prepared statements
- Base para RBAC e middleware de autenticação
- Tabelas de auditoria e webhooks idempotentes

## Próximas evoluções
- CSRF middleware e rate limiting persistente
- workers/cron para jobs assíncronos
- interfaces completas de inbox/funil/campanhas
- provedores reais de API
