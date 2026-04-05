<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\LoginLogRepository;
use App\Repositories\PasswordResetRepository;
use App\Repositories\PlanRepository;
use App\Repositories\RoleRepository;
use App\Repositories\SubscriptionRepository;
use App\Repositories\SubscriptionStatusRepository;
use App\Repositories\TenantRepository;
use App\Repositories\UserRepository;
use App\Support\Logger;
use Exception;
use PDO;

final class AuthService extends BaseService
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly UserRepository $users,
        private readonly TenantRepository $tenants,
        private readonly RoleRepository $roles,
        private readonly PlanRepository $plans,
        private readonly SubscriptionRepository $subscriptions,
        private readonly SubscriptionStatusRepository $subscriptionStatuses,
        private readonly PasswordResetRepository $passwordResets,
        private readonly LoginLogRepository $loginLogs,
        private readonly Logger $logger,
    ) {
    }

    public function register(array $payload): array
    {
        $email = strtolower(trim((string)($payload['email'] ?? '')));
        $phone = trim((string)($payload['phone'] ?? ''));

        if ($this->users->findByEmail($email)) {
            throw new Exception('Este email já está registado.');
        }
        if ($this->users->emailConsumedTrial($email)) {
            throw new Exception('Este email já consumiu trial anteriormente.');
        }
        if ($phone !== '' && $this->users->phoneConsumedTrial($phone)) {
            throw new Exception('Este número já consumiu trial anteriormente.');
        }

        $trialPlan = $this->plans->findBySlug('trial-24h');
        $trialStatus = $this->subscriptionStatuses->findByCode('trial_active');
        $ownerRole = $this->roles->findTenantRoleByCode('owner');
        if (!$trialPlan || !$trialStatus || !$ownerRole) {
            throw new Exception('Configuração de onboarding incompleta (plan/status/role).');
        }

        $this->pdo->beginTransaction();
        try {
            $userId = $this->users->create([
                'first_name' => trim((string)($payload['first_name'] ?? '')),
                'last_name' => trim((string)($payload['last_name'] ?? '')) ?: null,
                'email' => $email,
                'phone' => $phone ?: null,
                'password_hash' => password_hash((string)($payload['password'] ?? ''), PASSWORD_DEFAULT),
                'email_verified_at' => null,
            ]);

            $company = trim((string)($payload['company'] ?? ''));
            $tenantId = $this->tenants->create([
                'name' => $company,
                'slug' => $this->tenants->generateUniqueSlug($company),
                'email' => $email,
                'phone' => $phone ?: null,
                'status' => 'active',
                'trial_consumed' => 1,
            ]);

            $this->tenants->attachUser($tenantId, $userId, (int)$ownerRole['id'], true);
            $this->subscriptions->createTrial($tenantId, (int)$trialPlan['id'], (int)$trialStatus['id']);

            $this->pdo->commit();

            return [
                'user_id' => $userId,
                'tenant_id' => $tenantId,
                'role' => 'owner',
                'subscription_status' => 'trial_active',
            ];
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function attemptLogin(string $email, string $password, ?string $ip = null, ?string $ua = null): array
    {
        $user = $this->users->findByEmail(strtolower(trim($email)));
        if (!$user) {
            $this->loginLogs->add(null, $email, $ip, $ua, false, 'email_not_found');
            throw new Exception('Credenciais inválidas.');
        }

        if (!empty($user['locked_until']) && strtotime((string)$user['locked_until']) > time()) {
            $this->loginLogs->add((int)$user['id'], $email, $ip, $ua, false, 'locked');
            throw new Exception('Conta temporariamente bloqueada por tentativas abusivas.');
        }

        if (!password_verify($password, (string)$user['password_hash'])) {
            $this->users->incrementFailedAttempts((int)$user['id']);
            if ((int)$user['failed_attempts'] + 1 >= 5) {
                $this->users->lockForMinutes((int)$user['id'], 15);
            }
            $this->loginLogs->add((int)$user['id'], $email, $ip, $ua, false, 'invalid_password');
            throw new Exception('Credenciais inválidas.');
        }

        $this->users->clearFailedAttempts((int)$user['id']);
        $membership = $this->users->getTenantMemberships((int)$user['id'])[0] ?? null;
        if (!$membership) {
            throw new Exception('Utilizador sem vínculo a tenant.');
        }
        $subscription = $this->subscriptions->latestByTenant((int)$membership['tenant_id']);

        $this->loginLogs->add((int)$user['id'], $email, $ip, $ua, true, null);

        return [
            'user_id' => (int)$user['id'],
            'tenant_id' => (int)$membership['tenant_id'],
            'role' => $membership['role_code'],
            'subscription_status' => $subscription['status_code'] ?? 'trial_expired',
            'name' => trim(($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')),
            'email_verified' => !empty($user['email_verified_at']),
        ];
    }

    public function requestPasswordReset(string $email): ?string
    {
        $user = $this->users->findByEmail(strtolower(trim($email)));
        if (!$user) {
            return null;
        }

        $token = bin2hex(random_bytes(32));
        $this->passwordResets->create((int)$user['id'], password_hash($token, PASSWORD_DEFAULT));

        $this->logger->info('Password reset requested', ['user_id' => $user['id']]);
        return $token; // arquitetura pronta para envio por email/otp provider
    }

    public function resetPassword(string $email, string $token, string $newPassword): bool
    {
        $user = $this->users->findByEmail(strtolower(trim($email)));
        if (!$user) {
            return false;
        }
        $reset = $this->passwordResets->findValidByUser((int)$user['id']);
        if (!$reset || !password_verify($token, (string)$reset['token_hash'])) {
            return false;
        }

        $this->users->updatePassword((int)$user['id'], password_hash($newPassword, PASSWORD_DEFAULT));
        $this->passwordResets->markUsed((int)$reset['id']);
        return true;
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): bool
    {
        $user = $this->users->findById($userId);
        if (!$user || !password_verify($currentPassword, (string)$user['password_hash'])) {
            return false;
        }
        $this->users->updatePassword($userId, password_hash($newPassword, PASSWORD_DEFAULT));
        return true;
    }

    public function updateProfile(int $userId, string $firstName, ?string $lastName, ?string $phone): void
    {
        $this->users->updateProfile($userId, $firstName, $lastName, $phone);
    }

    public function userById(int $userId): ?array
    {
        return $this->users->findById($userId);
    }
}
