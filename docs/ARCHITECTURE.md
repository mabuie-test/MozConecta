# Arquitetura — Fase 1

## Bootstrap
- `public/index.php` recebe requests.
- `bootstrap/app.php` inicializa autoload e aplicação.
- `App\Core\Application` sobe container, config, PDO, logger e error handler.

## MVC
- `Controllers` para coordenação HTTP.
- `Services` para regras de negócio (fases seguintes).
- `Repositories/Models` para persistência.
- `Views/layouts` para renderização.

## Pipeline HTTP
1. Router resolve método + path.
2. Middlewares executam (auth/tenant/subscription/admin).
3. Dispatcher chama controller/método.
4. `Response` retorna view/json/redirect.

## Robustez inicial
- `ExceptionHandler` captura falhas globais.
- `Logger` grava logs técnicos em `storage/logs`.
- Estrutura pronta para cron/jobs e integrações externas.
