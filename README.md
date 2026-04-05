# MozConecta SaaS — FASE 2 (Schema e Multi-tenant)

Esta fase entrega a base de dados relacional completa para autenticação, subscrição, billing e operação multiempresa.

## Entregas principais
- Migrations SQL com FKs, índices, constraints e soft deletes.
- Estrutura multi-tenant com isolamento lógico por `tenant_id`.
- Seeders para planos, admin master, papéis/permissões, configurações default e status de assinatura.
- Modelos e repositórios iniciais para entidades nucleares (`Tenant`, `User`, `Plan`, `Subscription`).
- Documentação relacional de tabelas/colunas.

## Migrações incluídas
- `database/migrations/001_core_multitenant.sql`

## Seeders incluídos
1. `database/seeds/001_subscription_statuses.sql`
2. `database/seeds/002_roles_permissions.sql`
3. `database/seeds/003_plans.sql`
4. `database/seeds/004_master_admin.sql`

## Ordem de execução
```bash
mysql -u root -p mozconecta < database/migrations/001_core_multitenant.sql
mysql -u root -p mozconecta < database/seeds/001_subscription_statuses.sql
mysql -u root -p mozconecta < database/seeds/002_roles_permissions.sql
mysql -u root -p mozconecta < database/seeds/003_plans.sql
mysql -u root -p mozconecta < database/seeds/004_master_admin.sql
```

## Perfis suportados
- owner
- admin
- manager
- agent
- support

## Admin master inicial
- Email: `master@mozconecta.local`
- Password hash já incluído no seeder (alterar no primeiro acesso).

## Documentação detalhada
- `docs/DATABASE_SCHEMA.md`
