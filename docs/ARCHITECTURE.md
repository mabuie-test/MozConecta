# Arquitetura — FASE 3

## Camadas
- Controllers: fluxo HTTP (auth, perfil, dashboard).
- Services: regras de onboarding, login seguro, trial e password reset.
- Repositories: acesso a dados com PDO e prepared statements.
- Middleware: auth, tenant, perfil e assinatura/trial.

## Fluxo de onboarding
1. Utilizador submete registo.
2. `AuthService` valida anti-abuso (email/telefone).
3. Cria `users` + `tenants` + `tenant_users` (owner).
4. Cria `subscriptions` com `trial_active`, início/fim de 24h.
5. Sessão autenticada é iniciada.

## Segurança de autenticação
- Hash de senhas com `password_hash`.
- Registo de tentativas em `login_logs`.
- Bloqueio temporário após falhas consecutivas.
- Password reset com token hash + expiração.
- Sessão com cookie seguro e regeneração de ID.

## Preparação para verificação email/OTP
- tabela `verification_tokens` criada.
- arquitetura pronta para provider de envio externo.
