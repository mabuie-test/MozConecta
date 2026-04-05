-- FASE 6: Inbox, CRM, pipeline visual e lead score

CREATE TABLE IF NOT EXISTS contacts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  first_name VARCHAR(80) NULL,
  last_name VARCHAR(80) NULL,
  display_name VARCHAR(160) NOT NULL,
  phone VARCHAR(40) NOT NULL,
  email VARCHAR(190) NULL,
  lead_origin VARCHAR(80) NULL,
  funnel_stage_id BIGINT UNSIGNED NULL,
  assigned_user_id BIGINT UNSIGNED NULL,
  priority ENUM('low','medium','high','urgent') NOT NULL DEFAULT 'medium',
  potential_value DECIMAL(12,2) NULL,
  notes TEXT NULL,
  last_interaction_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_contacts_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_contacts_assigned_user FOREIGN KEY (assigned_user_id) REFERENCES users(id),
  UNIQUE KEY uq_contacts_tenant_phone (tenant_id, phone, deleted_at),
  INDEX idx_contacts_tenant_stage (tenant_id, funnel_stage_id),
  INDEX idx_contacts_tenant_assigned (tenant_id, assigned_user_id),
  INDEX idx_contacts_priority (priority),
  INDEX idx_contacts_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tags (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(80) NOT NULL,
  color VARCHAR(20) NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_tags_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  UNIQUE KEY uq_tags_tenant_name (tenant_id, name),
  INDEX idx_tags_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS contact_tags (
  contact_id BIGINT UNSIGNED NOT NULL,
  tag_id BIGINT UNSIGNED NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (contact_id, tag_id),
  CONSTRAINT fk_contact_tags_contact FOREIGN KEY (contact_id) REFERENCES contacts(id),
  CONSTRAINT fk_contact_tags_tag FOREIGN KEY (tag_id) REFERENCES tags(id),
  INDEX idx_contact_tags_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS funnels (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  is_default TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_funnels_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  INDEX idx_funnels_tenant_default (tenant_id, is_default)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS funnel_stages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  funnel_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL,
  position SMALLINT UNSIGNED NOT NULL,
  is_won_stage TINYINT(1) NOT NULL DEFAULT 0,
  is_lost_stage TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_funnel_stages_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_funnel_stages_funnel FOREIGN KEY (funnel_id) REFERENCES funnels(id),
  UNIQUE KEY uq_funnel_stages_slug (funnel_id, slug),
  UNIQUE KEY uq_funnel_stages_position (funnel_id, position),
  INDEX idx_funnel_stages_tenant_funnel (tenant_id, funnel_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE contacts
  ADD CONSTRAINT fk_contacts_funnel_stage FOREIGN KEY (funnel_stage_id) REFERENCES funnel_stages(id);

CREATE TABLE IF NOT EXISTS conversations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  contact_id BIGINT UNSIGNED NOT NULL,
  whatsapp_instance_id BIGINT UNSIGNED NULL,
  assigned_user_id BIGINT UNSIGNED NULL,
  status ENUM('open','pending','resolved','closed') NOT NULL DEFAULT 'open',
  takeover_by_user_id BIGINT UNSIGNED NULL,
  internal_notes TEXT NULL,
  last_message_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_conversations_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_conversations_contact FOREIGN KEY (contact_id) REFERENCES contacts(id),
  CONSTRAINT fk_conversations_instance FOREIGN KEY (whatsapp_instance_id) REFERENCES whatsapp_instances(id),
  CONSTRAINT fk_conversations_assigned_user FOREIGN KEY (assigned_user_id) REFERENCES users(id),
  CONSTRAINT fk_conversations_takeover_user FOREIGN KEY (takeover_by_user_id) REFERENCES users(id),
  INDEX idx_conversations_tenant_status (tenant_id, status),
  INDEX idx_conversations_tenant_assigned (tenant_id, assigned_user_id),
  INDEX idx_conversations_last_message (tenant_id, last_message_at),
  INDEX idx_conversations_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS conversation_messages (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  conversation_id BIGINT UNSIGNED NOT NULL,
  contact_id BIGINT UNSIGNED NOT NULL,
  direction ENUM('inbound','outbound','system') NOT NULL,
  message_type ENUM('text','media','internal_note') NOT NULL DEFAULT 'text',
  body TEXT NULL,
  media_url VARCHAR(500) NULL,
  payload_json JSON NULL,
  external_message_id VARCHAR(190) NULL,
  sent_by_user_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_conversation_messages_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_conversation_messages_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id),
  CONSTRAINT fk_conversation_messages_contact FOREIGN KEY (contact_id) REFERENCES contacts(id),
  CONSTRAINT fk_conversation_messages_user FOREIGN KEY (sent_by_user_id) REFERENCES users(id),
  UNIQUE KEY uq_conversation_messages_external (external_message_id),
  INDEX idx_conversation_messages_timeline (conversation_id, created_at),
  INDEX idx_conversation_messages_tenant_direction (tenant_id, direction)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS lead_scores (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  contact_id BIGINT UNSIGNED NOT NULL,
  score SMALLINT UNSIGNED NOT NULL DEFAULT 0,
  reason VARCHAR(255) NULL,
  last_calculated_at DATETIME NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_lead_scores_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_lead_scores_contact FOREIGN KEY (contact_id) REFERENCES contacts(id),
  UNIQUE KEY uq_lead_scores_contact (contact_id),
  INDEX idx_lead_scores_tenant_score (tenant_id, score)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
