# MozConecta SaaS — FASE 3 (Autenticação, Onboarding e Trial 24h)

Esta fase implementa autenticação completa, gestão de sessão, controlo de acesso por perfil e onboarding automático com trial.

## Funcionalidades entregues
- Registo, login, logout.
- Recuperação e redefinição de senha.
- Alteração de senha no perfil.
- Gestão de perfil do utilizador.
- Logs de login e bloqueio por tentativas abusivas.
- Sessões seguras (`HttpOnly`, `SameSite`, `session_regenerate_id`).
- Fluxo de onboarding:
  - cria tenant
  - cria owner
  - associa tenant-user
  - cria subscrição trial de 24h (`trial_active`)
  - marca `trial_consumed`.
- Anti-abuso:
  - 1 trial por email
  - 1 trial por número
  - arquitetura preparada para validação por IP/device.
- Middleware de autenticação, perfil, tenant e assinatura/trial.

## Novas tabelas (fase 3)
- `password_resets`
- `login_logs`
- `verification_tokens` (arquitetura preparada para email/OTP)

## Rotas principais
- `GET/POST /register`
- `GET/POST /login`
- `POST /logout`
- `GET/POST /forgot-password`
- `GET/POST /reset-password`
- `GET/POST /profile`
- `POST /profile/change-password`
- `GET /dashboard`

## Ordem de execução da base
```bash
mysql -u root -p mozconecta < database/migrations/001_core_multitenant.sql
mysql -u root -p mozconecta < database/migrations/002_auth_onboarding_security.sql
mysql -u root -p mozconecta < database/seeds/001_subscription_statuses.sql
mysql -u root -p mozconecta < database/seeds/002_roles_permissions.sql
mysql -u root -p mozconecta < database/seeds/003_plans.sql
mysql -u root -p mozconecta < database/seeds/004_master_admin.sql
```

## Execução
```bash
composer install
php -S localhost:8080 -t public
```
