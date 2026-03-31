<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\PlanRepository;
use App\Repositories\SubscriptionRepository;
use App\Repositories\TenantRepository;
use App\Repositories\UserRepository;

final class AuthService
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly TenantRepository $tenants,
        private readonly PlanRepository $plans,
        private readonly SubscriptionRepository $subscriptions,
    ) {
    }

    public function registerTenantOwner(array $payload): void
    {
        $password = password_hash((string)$payload['password'], PASSWORD_DEFAULT);
        $userId = $this->users->create((string)$payload['name'], (string)$payload['email'], $password);
        $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', (string)$payload['company']));
        $tenantId = $this->tenants->create((string)$payload['company'], trim($slug, '-'));
        $this->tenants->attachUser($tenantId, $userId, 'owner');

        $trial = $this->plans->findBySlug('trial-24h');
        if ($trial) {
            $this->subscriptions->createTrial($tenantId, (int)$trial['id']);
        }
    }

    public function attemptLogin(string $email, string $password): ?array
    {
        $user = $this->users->findByEmail($email);
        if (!$user || !password_verify($password, (string)$user['password_hash'])) {
            return null;
        }
        return $user;
    }
}
