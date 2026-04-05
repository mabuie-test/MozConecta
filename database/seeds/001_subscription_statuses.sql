INSERT INTO subscription_statuses (code, label, is_paid_state, created_at, updated_at) VALUES
('trial_active', 'Trial Ativo', 0, NOW(), NOW()),
('trial_expired', 'Trial Expirado', 0, NOW(), NOW()),
('active', 'Ativa', 1, NOW(), NOW()),
('past_due', 'Pagamento em atraso', 1, NOW(), NOW()),
('suspended', 'Suspensa', 0, NOW(), NOW()),
('cancelled', 'Cancelada', 0, NOW(), NOW());
