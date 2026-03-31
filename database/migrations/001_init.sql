CREATE TABLE tenants (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(150) NOT NULL,
  slug VARCHAR(180) NOT NULL UNIQUE,
  status VARCHAR(30) NOT NULL DEFAULT 'active',
  trial_consumed TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE users (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  status VARCHAR(30) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE tenant_users (
  tenant_id BIGINT UNSIGNED NOT NULL,
  user_id BIGINT UNSIGNED NOT NULL,
  role VARCHAR(30) NOT NULL,
  created_at DATETIME NOT NULL,
  PRIMARY KEY (tenant_id, user_id),
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE plans (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(120) NOT NULL,
  slug VARCHAR(120) NOT NULL UNIQUE,
  price_mt DECIMAL(12,2) NOT NULL,
  period_unit VARCHAR(20) NOT NULL,
  message_limit INT NULL,
  ai_limit INT NULL,
  instance_limit INT NOT NULL,
  user_limit INT NOT NULL,
  features_json JSON NOT NULL,
  highlight_label VARCHAR(120) NULL,
  display_order INT NOT NULL,
  status VARCHAR(20) NOT NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL
);

CREATE TABLE subscriptions (
  id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
  tenant_id BIGINT UNSIGNED NOT NULL,
  plan_id BIGINT UNSIGNED NOT NULL,
  status VARCHAR(30) NOT NULL,
  trial_starts_at DATETIME NULL,
  trial_ends_at DATETIME NULL,
  current_period_starts_at DATETIME NULL,
  current_period_ends_at DATETIME NULL,
  cancelled_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  FOREIGN KEY (plan_id) REFERENCES plans(id),
  INDEX idx_subscriptions_tenant_status (tenant_id,status)
);

CREATE TABLE invoices (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, subscription_id BIGINT UNSIGNED NOT NULL, amount DECIMAL(12,2) NOT NULL, status VARCHAR(30) NOT NULL, due_at DATETIME NOT NULL, paid_at DATETIME NULL, metadata_json JSON NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id), FOREIGN KEY (subscription_id) REFERENCES subscriptions(id));
CREATE TABLE payments (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, invoice_id BIGINT UNSIGNED NOT NULL, provider VARCHAR(30) NOT NULL, status VARCHAR(30) NOT NULL, amount DECIMAL(12,2) NOT NULL, paid_at DATETIME NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id), FOREIGN KEY (invoice_id) REFERENCES invoices(id));
CREATE TABLE payment_transactions (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, payment_id BIGINT UNSIGNED NOT NULL, provider_txn_id VARCHAR(190) NOT NULL, idempotency_key VARCHAR(190) NOT NULL, raw_payload_json JSON NOT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, UNIQUE KEY uniq_pay_txn(provider_txn_id), UNIQUE KEY uniq_idempo(idempotency_key), FOREIGN KEY (payment_id) REFERENCES payments(id));
CREATE TABLE whatsapp_instances (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, name VARCHAR(120) NOT NULL, phone_number VARCHAR(40) NOT NULL, provider_name VARCHAR(40) NOT NULL, provider_instance_id VARCHAR(190) NULL, status VARCHAR(30) NOT NULL, pairing_mode VARCHAR(40) NULL, qr_code TEXT NULL, qr_expires_at DATETIME NULL, session_token TEXT NULL, webhook_secret VARCHAR(190) NULL, last_seen_at DATETIME NULL, connected_at DATETIME NULL, disconnected_at DATETIME NULL, last_error TEXT NULL, metadata_json JSON NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id), INDEX idx_wi_tenant_status(tenant_id,status));
CREATE TABLE whatsapp_pairing_sessions (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, instance_id BIGINT UNSIGNED NOT NULL, status VARCHAR(30) NOT NULL, qr_code TEXT NULL, expires_at DATETIME NULL, created_at DATETIME NOT NULL, FOREIGN KEY (instance_id) REFERENCES whatsapp_instances(id));
CREATE TABLE whatsapp_instance_events (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, instance_id BIGINT UNSIGNED NOT NULL, event_type VARCHAR(60) NOT NULL, payload_json JSON NOT NULL, created_at DATETIME NOT NULL, FOREIGN KEY (instance_id) REFERENCES whatsapp_instances(id));
CREATE TABLE contacts (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, name VARCHAR(120) NOT NULL, phone VARCHAR(40) NOT NULL, email VARCHAR(190) NULL, source VARCHAR(80) NULL, lead_stage VARCHAR(80) NULL, owner_user_id BIGINT UNSIGNED NULL, priority VARCHAR(20) NULL, potential_value DECIMAL(12,2) NULL, notes TEXT NULL, last_interaction_at DATETIME NULL, score INT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id), INDEX idx_contacts_tenant_phone(tenant_id,phone));
CREATE TABLE tags (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, name VARCHAR(80) NOT NULL, color VARCHAR(20) NULL, created_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id));
CREATE TABLE contact_tags (contact_id BIGINT UNSIGNED NOT NULL, tag_id BIGINT UNSIGNED NOT NULL, PRIMARY KEY (contact_id,tag_id), FOREIGN KEY (contact_id) REFERENCES contacts(id), FOREIGN KEY (tag_id) REFERENCES tags(id));
CREATE TABLE conversations (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, contact_id BIGINT UNSIGNED NOT NULL, instance_id BIGINT UNSIGNED NOT NULL, assignee_user_id BIGINT UNSIGNED NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id), FOREIGN KEY (contact_id) REFERENCES contacts(id), FOREIGN KEY (instance_id) REFERENCES whatsapp_instances(id));
CREATE TABLE conversation_messages (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, conversation_id BIGINT UNSIGNED NOT NULL, direction VARCHAR(10) NOT NULL, provider_message_id VARCHAR(190) NULL, content TEXT NULL, media_url TEXT NULL, metadata_json JSON NULL, created_at DATETIME NOT NULL, UNIQUE KEY uniq_provider_msg(provider_message_id), FOREIGN KEY (conversation_id) REFERENCES conversations(id));
CREATE TABLE chatbot_flows (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, name VARCHAR(120) NOT NULL, status VARCHAR(30) NOT NULL, config_json JSON NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id));
CREATE TABLE chatbot_nodes (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, flow_id BIGINT UNSIGNED NOT NULL, node_type VARCHAR(40) NOT NULL, config_json JSON NOT NULL, created_at DATETIME NOT NULL, FOREIGN KEY (flow_id) REFERENCES chatbot_flows(id));
CREATE TABLE chatbot_edges (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, flow_id BIGINT UNSIGNED NOT NULL, from_node_id BIGINT UNSIGNED NOT NULL, to_node_id BIGINT UNSIGNED NOT NULL, condition_json JSON NULL, created_at DATETIME NOT NULL, FOREIGN KEY (flow_id) REFERENCES chatbot_flows(id));
CREATE TABLE funnels (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, name VARCHAR(120) NOT NULL, created_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id));
CREATE TABLE funnel_stages (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, funnel_id BIGINT UNSIGNED NOT NULL, name VARCHAR(80) NOT NULL, stage_order INT NOT NULL, created_at DATETIME NOT NULL, FOREIGN KEY (funnel_id) REFERENCES funnels(id));
CREATE TABLE campaigns (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, name VARCHAR(120) NOT NULL, status VARCHAR(30) NOT NULL, segment_json JSON NOT NULL, scheduled_at DATETIME NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id));
CREATE TABLE campaign_contacts (campaign_id BIGINT UNSIGNED NOT NULL, contact_id BIGINT UNSIGNED NOT NULL, status VARCHAR(30) NOT NULL, PRIMARY KEY (campaign_id,contact_id), FOREIGN KEY (campaign_id) REFERENCES campaigns(id), FOREIGN KEY (contact_id) REFERENCES contacts(id));
CREATE TABLE schedules (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, type VARCHAR(40) NOT NULL, payload_json JSON NOT NULL, run_at DATETIME NOT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id));
CREATE TABLE tasks (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, contact_id BIGINT UNSIGNED NULL, title VARCHAR(190) NOT NULL, description TEXT NULL, assignee_user_id BIGINT UNSIGNED NULL, due_at DATETIME NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id));
CREATE TABLE lead_scores (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, contact_id BIGINT UNSIGNED NOT NULL, score INT NOT NULL, reason VARCHAR(190) NULL, created_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id), FOREIGN KEY (contact_id) REFERENCES contacts(id));
CREATE TABLE ai_usage_logs (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, user_id BIGINT UNSIGNED NULL, provider VARCHAR(40) NOT NULL, tokens_in INT NOT NULL, tokens_out INT NOT NULL, feature VARCHAR(80) NOT NULL, created_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id));
CREATE TABLE ai_prompts (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, name VARCHAR(120) NOT NULL, prompt TEXT NOT NULL, metadata_json JSON NULL, created_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id));
CREATE TABLE media_assets (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, type VARCHAR(40) NOT NULL, path TEXT NOT NULL, metadata_json JSON NULL, created_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id));
CREATE TABLE webhooks_inbound (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NULL, source VARCHAR(40) NOT NULL, idempotency_key VARCHAR(190) NOT NULL, payload_json JSON NOT NULL, status VARCHAR(30) NOT NULL, created_at DATETIME NOT NULL, UNIQUE KEY uniq_wh_in(idempotency_key));
CREATE TABLE webhooks_outbound (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NULL, target_url TEXT NOT NULL, payload_json JSON NOT NULL, status VARCHAR(30) NOT NULL, attempts INT NOT NULL DEFAULT 0, created_at DATETIME NOT NULL);
CREATE TABLE notifications (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, user_id BIGINT UNSIGNED NULL, type VARCHAR(60) NOT NULL, title VARCHAR(190) NOT NULL, body TEXT NOT NULL, read_at DATETIME NULL, created_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id));
CREATE TABLE audit_logs (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NULL, user_id BIGINT UNSIGNED NULL, action VARCHAR(120) NOT NULL, entity VARCHAR(120) NOT NULL, entity_id BIGINT NULL, changes_json JSON NULL, ip_address VARCHAR(64) NULL, created_at DATETIME NOT NULL);
CREATE TABLE integrations (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NULL, type VARCHAR(40) NOT NULL, provider VARCHAR(60) NOT NULL, credentials_encrypted TEXT NOT NULL, status VARCHAR(30) NOT NULL, metadata_json JSON NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL);
CREATE TABLE settings (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NULL, setting_key VARCHAR(190) NOT NULL, setting_value TEXT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, UNIQUE KEY uniq_setting(tenant_id,setting_key));
CREATE TABLE support_tickets (id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT, tenant_id BIGINT UNSIGNED NOT NULL, subject VARCHAR(190) NOT NULL, status VARCHAR(30) NOT NULL, priority VARCHAR(20) NOT NULL, created_by BIGINT UNSIGNED NOT NULL, assigned_to BIGINT UNSIGNED NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, FOREIGN KEY (tenant_id) REFERENCES tenants(id));
