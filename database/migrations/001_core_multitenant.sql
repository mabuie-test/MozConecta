-- FASE 2: Core multi-tenant + billing foundation

CREATE TABLE IF NOT EXISTS tenants (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid CHAR(36) NOT NULL,
  name VARCHAR(160) NOT NULL,
  slug VARCHAR(190) NOT NULL,
  legal_name VARCHAR(190) NULL,
  tax_id VARCHAR(60) NULL,
  email VARCHAR(190) NULL,
  phone VARCHAR(40) NULL,
  country_code CHAR(2) NOT NULL DEFAULT 'MZ',
  timezone VARCHAR(80) NOT NULL DEFAULT 'Africa/Maputo',
  status ENUM('active','inactive','blocked') NOT NULL DEFAULT 'active',
  trial_consumed TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_tenants_uuid (uuid),
  UNIQUE KEY uq_tenants_slug (slug),
  INDEX idx_tenants_status (status),
  INDEX idx_tenants_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uuid CHAR(36) NOT NULL,
  first_name VARCHAR(80) NOT NULL,
  last_name VARCHAR(80) NULL,
  name VARCHAR(160) GENERATED ALWAYS AS (CONCAT(first_name, IFNULL(CONCAT(' ', last_name), ''))) STORED,
  email VARCHAR(190) NOT NULL,
  phone VARCHAR(40) NULL,
  password_hash VARCHAR(255) NOT NULL,
  email_verified_at DATETIME NULL,
  last_login_at DATETIME NULL,
  status ENUM('active','invited','suspended','blocked') NOT NULL DEFAULT 'active',
  is_master_admin TINYINT(1) NOT NULL DEFAULT 0,
  failed_attempts SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  locked_until DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_users_uuid (uuid),
  UNIQUE KEY uq_users_email (email),
  INDEX idx_users_status (status),
  INDEX idx_users_master (is_master_admin),
  INDEX idx_users_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS roles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL,
  name VARCHAR(80) NOT NULL,
  scope ENUM('global','tenant') NOT NULL DEFAULT 'tenant',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_roles_code_scope (code, scope)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS permissions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(80) NOT NULL,
  name VARCHAR(120) NOT NULL,
  module VARCHAR(60) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_permissions_code (code),
  INDEX idx_permissions_module (module)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id BIGINT UNSIGNED NOT NULL,
  permission_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (role_id, permission_id),
  CONSTRAINT fk_role_permissions_role FOREIGN KEY (role_id) REFERENCES roles(id),
  CONSTRAINT fk_role_permissions_permission FOREIGN KEY (permission_id) REFERENCES permissions(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tenant_users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  role_id BIGINT UNSIGNED NOT NULL,
  is_owner TINYINT(1) NOT NULL DEFAULT 0,
  invited_by BIGINT UNSIGNED NULL,
  joined_at DATETIME NULL,
  status ENUM('active','invited','disabled') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_tenant_users_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_tenant_users_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_tenant_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
  CONSTRAINT fk_tenant_users_invited_by FOREIGN KEY (invited_by) REFERENCES users(id),
  UNIQUE KEY uq_tenant_user_active (tenant_id, user_id, deleted_at),
  INDEX idx_tenant_users_tenant_role (tenant_id, role_id),
  INDEX idx_tenant_users_status (status),
  INDEX idx_tenant_users_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS plans (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  description TEXT NULL,
  price_mt DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'MZN',
  billing_period ENUM('24h','monthly','yearly','custom') NOT NULL DEFAULT 'monthly',
  message_limit INT UNSIGNED NULL,
  ai_limit INT UNSIGNED NULL,
  instance_limit INT UNSIGNED NOT NULL DEFAULT 1,
  user_limit INT UNSIGNED NOT NULL DEFAULT 1,
  feature_flags_json JSON NOT NULL,
  is_featured TINYINT(1) NOT NULL DEFAULT 0,
  display_order SMALLINT UNSIGNED NOT NULL DEFAULT 100,
  status ENUM('active','inactive','archived') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  UNIQUE KEY uq_plans_slug (slug),
  INDEX idx_plans_status_order (status, display_order),
  INDEX idx_plans_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscription_statuses (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  code VARCHAR(40) NOT NULL,
  label VARCHAR(80) NOT NULL,
  is_paid_state TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  UNIQUE KEY uq_subscription_statuses_code (code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscriptions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  plan_id BIGINT UNSIGNED NOT NULL,
  status_id BIGINT UNSIGNED NOT NULL,
  starts_at DATETIME NOT NULL,
  trial_starts_at DATETIME NULL,
  trial_ends_at DATETIME NULL,
  current_period_starts_at DATETIME NULL,
  current_period_ends_at DATETIME NULL,
  cancelled_at DATETIME NULL,
  suspended_at DATETIME NULL,
  metadata_json JSON NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_subscriptions_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_subscriptions_plan FOREIGN KEY (plan_id) REFERENCES plans(id),
  CONSTRAINT fk_subscriptions_status FOREIGN KEY (status_id) REFERENCES subscription_statuses(id),
  INDEX idx_subscriptions_tenant_status (tenant_id, status_id),
  INDEX idx_subscriptions_period_end (current_period_ends_at),
  INDEX idx_subscriptions_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoices (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  subscription_id BIGINT UNSIGNED NOT NULL,
  invoice_no VARCHAR(80) NOT NULL,
  amount_subtotal DECIMAL(12,2) NOT NULL,
  amount_discount DECIMAL(12,2) NOT NULL DEFAULT 0,
  amount_total DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'MZN',
  status ENUM('draft','pending','paid','void','overdue','cancelled') NOT NULL DEFAULT 'pending',
  due_at DATETIME NOT NULL,
  paid_at DATETIME NULL,
  metadata_json JSON NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_invoices_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_invoices_subscription FOREIGN KEY (subscription_id) REFERENCES subscriptions(id),
  UNIQUE KEY uq_invoices_invoice_no (invoice_no),
  INDEX idx_invoices_tenant_status_due (tenant_id, status, due_at),
  INDEX idx_invoices_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  invoice_id BIGINT UNSIGNED NOT NULL,
  provider ENUM('mpesa','emola','manual','other') NOT NULL,
  status ENUM('pending','authorized','paid','failed','refunded','cancelled') NOT NULL DEFAULT 'pending',
  amount DECIMAL(12,2) NOT NULL,
  currency CHAR(3) NOT NULL DEFAULT 'MZN',
  payer_phone VARCHAR(40) NULL,
  paid_at DATETIME NULL,
  metadata_json JSON NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_payments_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_payments_invoice FOREIGN KEY (invoice_id) REFERENCES invoices(id),
  INDEX idx_payments_tenant_provider_status (tenant_id, provider, status),
  INDEX idx_payments_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payment_transactions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  payment_id BIGINT UNSIGNED NOT NULL,
  external_txn_id VARCHAR(120) NOT NULL,
  idempotency_key VARCHAR(120) NOT NULL,
  event_type VARCHAR(60) NOT NULL,
  payload_json JSON NOT NULL,
  processed_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_payment_transactions_payment FOREIGN KEY (payment_id) REFERENCES payments(id),
  UNIQUE KEY uq_payment_transactions_external (external_txn_id),
  UNIQUE KEY uq_payment_transactions_idempotency (idempotency_key),
  INDEX idx_payment_transactions_event (event_type, processed_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS integrations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NULL,
  category ENUM('payment','whatsapp','ai','media','other') NOT NULL,
  provider VARCHAR(80) NOT NULL,
  display_name VARCHAR(120) NULL,
  credentials_encrypted TEXT NOT NULL,
  webhook_secret VARCHAR(190) NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  last_error TEXT NULL,
  metadata_json JSON NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_integrations_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  INDEX idx_integrations_tenant_category (tenant_id, category),
  INDEX idx_integrations_active (is_active),
  INDEX idx_integrations_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS settings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NULL,
  key_name VARCHAR(190) NOT NULL,
  value_type ENUM('string','int','bool','json') NOT NULL DEFAULT 'string',
  value_text LONGTEXT NULL,
  is_public TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_settings_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  UNIQUE KEY uq_settings_scope_key (tenant_id, key_name),
  INDEX idx_settings_key_name (key_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  action VARCHAR(120) NOT NULL,
  entity_type VARCHAR(120) NOT NULL,
  entity_id BIGINT UNSIGNED NULL,
  old_values_json JSON NULL,
  new_values_json JSON NULL,
  ip_address VARCHAR(64) NULL,
  user_agent VARCHAR(255) NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_audit_logs_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_audit_logs_user FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_audit_logs_tenant_created (tenant_id, created_at),
  INDEX idx_audit_logs_entity (entity_type, entity_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS notifications (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NULL,
  user_id BIGINT UNSIGNED NULL,
  type VARCHAR(60) NOT NULL,
  title VARCHAR(190) NOT NULL,
  body TEXT NOT NULL,
  channels_json JSON NULL,
  read_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_notifications_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users(id),
  INDEX idx_notifications_user_read (user_id, read_at),
  INDEX idx_notifications_tenant_created (tenant_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS support_tickets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  requester_user_id BIGINT UNSIGNED NOT NULL,
  assigned_user_id BIGINT UNSIGNED NULL,
  subject VARCHAR(190) NOT NULL,
  description TEXT NULL,
  status ENUM('open','in_progress','waiting_customer','resolved','closed') NOT NULL DEFAULT 'open',
  priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_support_tickets_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_support_tickets_requester FOREIGN KEY (requester_user_id) REFERENCES users(id),
  CONSTRAINT fk_support_tickets_assignee FOREIGN KEY (assigned_user_id) REFERENCES users(id),
  INDEX idx_support_tickets_tenant_status (tenant_id, status),
  INDEX idx_support_tickets_priority (priority),
  INDEX idx_support_tickets_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
