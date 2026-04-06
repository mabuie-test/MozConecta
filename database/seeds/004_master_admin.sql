INSERT INTO users (uuid, first_name, last_name, email, phone, password_hash, email_verified_at, status, is_master_admin, created_at, updated_at)
VALUES (UUID(), 'Master', 'Admin', 'master@mozconecta.local', NULL, '$2y$10$8Z8YxNWMVf56PfS4dl8xlOfvFv4Y2Q9hM4VAcQn95P97f9fBofYxK', NOW(), 'active', 1, NOW(), NOW());

INSERT INTO settings (tenant_id, key_name, value_type, value_text, is_public, created_at, updated_at)
VALUES
(NULL, 'platform.name', 'string', 'MozConecta', 1, NOW(), NOW()),
(NULL, 'platform.trial_hours', 'int', '24', 0, NOW(), NOW()),
(NULL, 'billing.default_currency', 'string', 'MZN', 0, NOW(), NOW()),
(NULL, 'security.max_login_attempts', 'int', '5', 0, NOW(), NOW());
