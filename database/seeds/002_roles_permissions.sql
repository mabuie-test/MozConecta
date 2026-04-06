INSERT INTO roles (code, name, scope, created_at, updated_at) VALUES
('master_admin', 'Master Admin', 'global', NOW(), NOW()),
('owner', 'Owner', 'tenant', NOW(), NOW()),
('admin', 'Admin', 'tenant', NOW(), NOW()),
('manager', 'Manager', 'tenant', NOW(), NOW()),
('agent', 'Agent', 'tenant', NOW(), NOW()),
('support', 'Support', 'tenant', NOW(), NOW());

INSERT INTO permissions (code, name, module, created_at, updated_at) VALUES
('tenant.manage', 'Gerir tenant', 'tenant', NOW(), NOW()),
('user.manage', 'Gerir utilizadores', 'users', NOW(), NOW()),
('billing.manage', 'Gerir billing', 'billing', NOW(), NOW()),
('subscription.manage', 'Gerir subscrições', 'subscriptions', NOW(), NOW()),
('support.manage', 'Gerir suporte', 'support', NOW(), NOW()),
('dashboard.view', 'Ver dashboard', 'dashboard', NOW(), NOW());

INSERT INTO role_permissions (role_id, permission_id, created_at)
SELECT r.id, p.id, NOW()
FROM roles r
JOIN permissions p
WHERE (r.code = 'master_admin')
   OR (r.code = 'owner' AND p.code IN ('tenant.manage','user.manage','billing.manage','subscription.manage','dashboard.view'))
   OR (r.code = 'admin' AND p.code IN ('user.manage','billing.manage','subscription.manage','dashboard.view'))
   OR (r.code = 'manager' AND p.code IN ('user.manage','dashboard.view','support.manage'))
   OR (r.code = 'agent' AND p.code IN ('dashboard.view'))
   OR (r.code = 'support' AND p.code IN ('support.manage','dashboard.view'));
