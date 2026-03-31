# MozConecta SaaS — FASE 1 (Fundação Arquitetural)

Base arquitetural completa em **PHP 8.2+** com **MySQL/MariaDB**, preparada para evolução de produto SaaS multi-tenant.

## Stack
- PHP 8.2+
- PDO (MySQL/MariaDB)
- MVC clássico
- Frontend HTML/CSS/JS
- Composer autoload PSR-4

## Entregas da Fase 1
- Estrutura de diretórios profissional (`app`, `bootstrap`, `config`, `routes`, `database`, `public`, `storage`, `tests`).
- `composer.json` com autoload `App\\`.
- Bootstrap central (`bootstrap/app.php`) + front controller (`public/index.php`).
- Configuração por `.env` e arquivos de config centralizada.
- Router com suporte a `GET/POST/PUT/PATCH/DELETE`.
- Dispatcher de controllers + middleware pipeline.
- Classes base: `BaseController`, `BaseModel`, `BaseRepository`, `BaseService`, `Request`, `Response`.
- Middlewares iniciais: `AuthMiddleware`, `TenantMiddleware`, `SubscriptionMiddleware`, `AdminMiddleware`.
- Tratamento global de erros e exceções + logger técnico em `storage/logs/app.log`.
- Layout base da landing e painel.
- Estrutura inicial para jobs e integrações (`WhatsApp`, `AI`, `Payments`, `Media`).

## Instalação
1. Copie `.env.example` para `.env`.
2. Configure credenciais de base de dados.
3. Instale autoload:
   ```bash
   composer install
   ```
4. Execute localmente:
   ```bash
   php -S localhost:8080 -t public
   ```

> Para shared hosting: defina **document root** para `public/`.

## Testes rápidos
```bash
bash tests/smoke.sh
```

## Próxima fase
Implementação dos módulos de negócio (autenticação completa, assinatura, billing, WhatsApp, inbox, CRM, funil, IA, campanhas e admin operacional).
