-- FASE 7: Tarefas/follow-up e automação por fluxos

CREATE TABLE IF NOT EXISTS tasks (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  contact_id BIGINT UNSIGNED NULL,
  conversation_id BIGINT UNSIGNED NULL,
  title VARCHAR(190) NOT NULL,
  description TEXT NULL,
  assigned_user_id BIGINT UNSIGNED NULL,
  status ENUM('pending','in_progress','done','cancelled','overdue') NOT NULL DEFAULT 'pending',
  due_at DATETIME NULL,
  completed_at DATETIME NULL,
  cancelled_at DATETIME NULL,
  metadata_json JSON NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_tasks_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_tasks_contact FOREIGN KEY (contact_id) REFERENCES contacts(id),
  CONSTRAINT fk_tasks_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id),
  CONSTRAINT fk_tasks_assigned_user FOREIGN KEY (assigned_user_id) REFERENCES users(id),
  INDEX idx_tasks_tenant_status_due (tenant_id, status, due_at),
  INDEX idx_tasks_tenant_assigned (tenant_id, assigned_user_id),
  INDEX idx_tasks_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chatbot_flows (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  name VARCHAR(160) NOT NULL,
  trigger_type ENUM('keyword','all_inbound','remarketing','reentry') NOT NULL DEFAULT 'keyword',
  trigger_value VARCHAR(190) NULL,
  fallback_message TEXT NULL,
  allow_reentry TINYINT(1) NOT NULL DEFAULT 1,
  allow_remarketing TINYINT(1) NOT NULL DEFAULT 0,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_by_user_id BIGINT UNSIGNED NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  deleted_at DATETIME NULL,
  CONSTRAINT fk_chatbot_flows_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_chatbot_flows_user FOREIGN KEY (created_by_user_id) REFERENCES users(id),
  INDEX idx_chatbot_flows_tenant_active (tenant_id, is_active),
  INDEX idx_chatbot_flows_trigger (tenant_id, trigger_type, trigger_value),
  INDEX idx_chatbot_flows_deleted_at (deleted_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chatbot_nodes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  flow_id BIGINT UNSIGNED NOT NULL,
  node_key VARCHAR(120) NOT NULL,
  type ENUM('send_message','menu','keyword','condition_time','condition_tag','condition_stage','apply_tag','create_task','move_stage','webhook','handoff_human','wait_reply','end') NOT NULL,
  config_json JSON NULL,
  position_x INT NULL,
  position_y INT NULL,
  is_start TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_chatbot_nodes_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_chatbot_nodes_flow FOREIGN KEY (flow_id) REFERENCES chatbot_flows(id),
  UNIQUE KEY uq_chatbot_nodes_flow_key (flow_id, node_key),
  INDEX idx_chatbot_nodes_flow_start (flow_id, is_start),
  INDEX idx_chatbot_nodes_tenant_type (tenant_id, type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chatbot_edges (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  flow_id BIGINT UNSIGNED NOT NULL,
  from_node_id BIGINT UNSIGNED NOT NULL,
  to_node_id BIGINT UNSIGNED NOT NULL,
  condition_type ENUM('always','keyword','option','time_window','tag','stage','fallback') NOT NULL DEFAULT 'always',
  condition_value VARCHAR(190) NULL,
  priority SMALLINT UNSIGNED NOT NULL DEFAULT 100,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_chatbot_edges_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_chatbot_edges_flow FOREIGN KEY (flow_id) REFERENCES chatbot_flows(id),
  CONSTRAINT fk_chatbot_edges_from FOREIGN KEY (from_node_id) REFERENCES chatbot_nodes(id),
  CONSTRAINT fk_chatbot_edges_to FOREIGN KEY (to_node_id) REFERENCES chatbot_nodes(id),
  INDEX idx_chatbot_edges_flow_from (flow_id, from_node_id, priority)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS schedules (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  flow_id BIGINT UNSIGNED NULL,
  node_id BIGINT UNSIGNED NULL,
  conversation_id BIGINT UNSIGNED NULL,
  contact_id BIGINT UNSIGNED NULL,
  task_id BIGINT UNSIGNED NULL,
  type ENUM('follow_up','remarketing','flow_resume') NOT NULL,
  status ENUM('pending','processed','cancelled') NOT NULL DEFAULT 'pending',
  run_at DATETIME NOT NULL,
  payload_json JSON NULL,
  processed_at DATETIME NULL,
  created_at DATETIME NOT NULL,
  updated_at DATETIME NOT NULL,
  CONSTRAINT fk_schedules_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_schedules_flow FOREIGN KEY (flow_id) REFERENCES chatbot_flows(id),
  CONSTRAINT fk_schedules_node FOREIGN KEY (node_id) REFERENCES chatbot_nodes(id),
  CONSTRAINT fk_schedules_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id),
  CONSTRAINT fk_schedules_contact FOREIGN KEY (contact_id) REFERENCES contacts(id),
  CONSTRAINT fk_schedules_task FOREIGN KEY (task_id) REFERENCES tasks(id),
  INDEX idx_schedules_tenant_status_run (tenant_id, status, run_at),
  INDEX idx_schedules_type_status (type, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chatbot_execution_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NOT NULL,
  flow_id BIGINT UNSIGNED NULL,
  node_id BIGINT UNSIGNED NULL,
  conversation_id BIGINT UNSIGNED NULL,
  contact_id BIGINT UNSIGNED NULL,
  event_type VARCHAR(80) NOT NULL,
  event_payload JSON NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_chatbot_execution_logs_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_chatbot_execution_logs_flow FOREIGN KEY (flow_id) REFERENCES chatbot_flows(id),
  CONSTRAINT fk_chatbot_execution_logs_node FOREIGN KEY (node_id) REFERENCES chatbot_nodes(id),
  CONSTRAINT fk_chatbot_execution_logs_conversation FOREIGN KEY (conversation_id) REFERENCES conversations(id),
  CONSTRAINT fk_chatbot_execution_logs_contact FOREIGN KEY (contact_id) REFERENCES contacts(id),
  INDEX idx_chatbot_execution_logs_flow_created (flow_id, created_at),
  INDEX idx_chatbot_execution_logs_contact_created (contact_id, created_at),
  INDEX idx_chatbot_execution_logs_tenant_event (tenant_id, event_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
