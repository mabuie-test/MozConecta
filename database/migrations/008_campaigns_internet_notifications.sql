-- FASE 9: Campanhas, remarketing, bot de internet e notificações

CREATE TABLE IF NOT EXISTS campaigns (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  type ENUM('broadcast','remarketing','post_sale','cold_leads','lost_leads') NOT NULL DEFAULT 'broadcast',
  channel ENUM('whatsapp') NOT NULL DEFAULT 'whatsapp',
  message_template TEXT NOT NULL,
  segment_type ENUM('all','tags','stage','cold','lost','post_sale') NOT NULL DEFAULT 'all',
  segment_value VARCHAR(190) NULL,
  status ENUM('draft','scheduled','running','paused','completed','cancelled','failed') NOT NULL DEFAULT 'draft',
  batch_size INT UNSIGNED NOT NULL DEFAULT 50,
  total_recipients INT UNSIGNED NOT NULL DEFAULT 0,
  sent_count INT UNSIGNED NOT NULL DEFAULT 0,
  delivered_count INT UNSIGNED NOT NULL DEFAULT 0,
  failed_count INT UNSIGNED NOT NULL DEFAULT 0,
  scheduled_at DATETIME NULL,
  started_at DATETIME NULL,
  completed_at DATETIME NULL,
  metadata_json JSON NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_campaigns_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  INDEX idx_campaigns_tenant_status (tenant_id, status),
  INDEX idx_campaigns_schedule (tenant_id, scheduled_at),
  INDEX idx_campaigns_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS campaign_contacts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  campaign_id BIGINT UNSIGNED NOT NULL,
  contact_id BIGINT UNSIGNED NOT NULL,
  status ENUM('pending','sent','delivered','failed','cancelled') NOT NULL DEFAULT 'pending',
  error_message VARCHAR(255) NULL,
  sent_at DATETIME NULL,
  delivered_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_campaign_contacts_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_campaign_contacts_campaign FOREIGN KEY (campaign_id) REFERENCES campaigns(id),
  CONSTRAINT fk_campaign_contacts_contact FOREIGN KEY (contact_id) REFERENCES contacts(id),
  UNIQUE KEY uq_campaign_contact (campaign_id, contact_id),
  INDEX idx_campaign_contacts_tenant_status (tenant_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS internet_packages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(140) NOT NULL,
  description TEXT NULL,
  price DECIMAL(12,2) NOT NULL,
  validity_days INT UNSIGNED NOT NULL DEFAULT 30,
  sales_message TEXT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_internet_packages_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  INDEX idx_internet_packages_tenant_active (tenant_id, is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS internet_orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  contact_id BIGINT UNSIGNED NOT NULL,
  package_id BIGINT UNSIGNED NOT NULL,
  conversation_id BIGINT UNSIGNED NULL,
  customer_name VARCHAR(160) NULL,
  customer_phone VARCHAR(40) NOT NULL,
  installation_address VARCHAR(255) NULL,
  operator_notes TEXT NULL,
  status ENUM('new','qualified','sent_to_operator','approved','installed','cancelled') NOT NULL DEFAULT 'new',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_internet_orders_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_internet_orders_contact FOREIGN KEY (contact_id) REFERENCES contacts(id),
  CONSTRAINT fk_internet_orders_package FOREIGN KEY (package_id) REFERENCES internet_packages(id),
  CONSTRAINT fk_internet_orders_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id),
  INDEX idx_internet_orders_tenant_status (tenant_id, status),
  INDEX idx_internet_orders_package (package_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
