-- FASE 8: IA por API + motor híbrido

CREATE TABLE IF NOT EXISTS assistant_profiles (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  assistant_name VARCHAR(120) NOT NULL DEFAULT 'Assistente MozConecta',
  persona TEXT NULL,
  language_code VARCHAR(12) NOT NULL DEFAULT 'pt-PT',
  tone VARCHAR(80) NOT NULL DEFAULT 'profissional',
  business_rules TEXT NULL,
  faq_json JSON NULL,
  products_services_json JSON NULL,
  policies_json JSON NULL,
  business_goals_json JSON NULL,
  primary_provider VARCHAR(40) NOT NULL DEFAULT 'openrouter',
  fallback_provider VARCHAR(40) NOT NULL DEFAULT 'gemini',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_assistant_profiles_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  UNIQUE KEY uq_assistant_profiles_tenant (tenant_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_prompts (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  conversation_id BIGINT UNSIGNED NULL,
  contact_id BIGINT UNSIGNED NULL,
  provider_name VARCHAR(40) NOT NULL,
  model_name VARCHAR(120) NULL,
  prompt_text LONGTEXT NOT NULL,
  response_text LONGTEXT NULL,
  status ENUM('success','fallback','failed') NOT NULL DEFAULT 'success',
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_ai_prompts_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_ai_prompts_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id),
  CONSTRAINT fk_ai_prompts_contact FOREIGN KEY (contact_id) REFERENCES contacts(id),
  INDEX idx_ai_prompts_tenant_created (tenant_id, created_at),
  INDEX idx_ai_prompts_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ai_usage_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  conversation_id BIGINT UNSIGNED NULL,
  contact_id BIGINT UNSIGNED NULL,
  provider_name VARCHAR(40) NOT NULL,
  usage_type ENUM('message','classification','summary','image') NOT NULL DEFAULT 'message',
  units_used INT UNSIGNED NOT NULL DEFAULT 1,
  metadata_json JSON NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_ai_usage_logs_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_ai_usage_logs_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id),
  CONSTRAINT fk_ai_usage_logs_contact FOREIGN KEY (contact_id) REFERENCES contacts(id),
  INDEX idx_ai_usage_logs_tenant_created (tenant_id, created_at),
  INDEX idx_ai_usage_logs_type (usage_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
