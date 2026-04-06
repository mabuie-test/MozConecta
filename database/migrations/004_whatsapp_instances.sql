-- FASE 5: Instâncias WhatsApp, pareamento e sessão

CREATE TABLE IF NOT EXISTS whatsapp_instances (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  phone_number VARCHAR(40) NOT NULL,
  provider_name VARCHAR(60) NOT NULL,
  provider_instance_id VARCHAR(190) NULL,
  status ENUM('created','pending_pair','qr_ready','pairing','connected','disconnected','expired','blocked','error','reconnecting') NOT NULL DEFAULT 'created',
  pairing_mode ENUM('qr','link','code') NOT NULL DEFAULT 'qr',
  qr_code LONGTEXT NULL,
  qr_expires_at DATETIME NULL,
  session_token TEXT NULL,
  webhook_secret VARCHAR(190) NULL,
  last_seen_at DATETIME NULL,
  connected_at DATETIME NULL,
  disconnected_at DATETIME NULL,
  last_error TEXT NULL,
  metadata_json JSON NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_whatsapp_instances_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  UNIQUE KEY uq_whatsapp_instances_provider_instance (provider_name, provider_instance_id),
  INDEX idx_whatsapp_instances_tenant_status (tenant_id, status),
  INDEX idx_whatsapp_instances_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS whatsapp_pairing_sessions (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  instance_id BIGINT UNSIGNED NOT NULL,
  provider_reference VARCHAR(190) NULL,
  status ENUM('created','pending','qr_ready','paired','expired','failed','cancelled') NOT NULL DEFAULT 'created',
  qr_code LONGTEXT NULL,
  qr_expires_at DATETIME NULL,
  pairing_payload JSON NULL,
  last_error TEXT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_whatsapp_pairing_sessions_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_whatsapp_pairing_sessions_instance FOREIGN KEY (instance_id) REFERENCES whatsapp_instances(id),
  INDEX idx_whatsapp_pairing_sessions_instance_status (instance_id, status),
  INDEX idx_whatsapp_pairing_sessions_tenant_created (tenant_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS whatsapp_instance_events (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  instance_id BIGINT UNSIGNED NOT NULL,
  event_type VARCHAR(80) NOT NULL,
  event_status VARCHAR(40) NULL,
  event_payload JSON NULL,
  technical_message TEXT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_whatsapp_instance_events_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_whatsapp_instance_events_instance FOREIGN KEY (instance_id) REFERENCES whatsapp_instances(id),
  INDEX idx_whatsapp_instance_events_instance_created (instance_id, created_at),
  INDEX idx_whatsapp_instance_events_tenant_type (tenant_id, event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
