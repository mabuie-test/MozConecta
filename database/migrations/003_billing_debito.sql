-- FASE 4: Billing com integração Débito (M-Pesa/eMola)

ALTER TABLE invoices
  ADD COLUMN plan_id BIGINT UNSIGNED NULL AFTER subscription_id,
  ADD COLUMN provider_name VARCHAR(50) NULL AFTER status,
  ADD COLUMN payment_status VARCHAR(40) NULL AFTER provider_name,
  ADD COLUMN status_checked_at DATETIME NULL AFTER payment_status,
  ADD CONSTRAINT fk_invoices_plan FOREIGN KEY (plan_id) REFERENCES plans(id),
  ADD INDEX idx_invoices_plan (plan_id),
  ADD INDEX idx_invoices_payment_status (payment_status);

ALTER TABLE payments
  MODIFY provider VARCHAR(50) NOT NULL,
  ADD COLUMN provider_name VARCHAR(50) NULL AFTER provider,
  ADD COLUMN provider_reference VARCHAR(120) NULL AFTER provider_name,
  ADD COLUMN debito_reference VARCHAR(120) NULL AFTER provider_reference,
  ADD COLUMN external_transaction_id VARCHAR(120) NULL AFTER debito_reference,
  ADD COLUMN request_payload JSON NULL AFTER metadata_json,
  ADD COLUMN response_payload JSON NULL AFTER request_payload,
  ADD COLUMN payment_status VARCHAR(40) NULL AFTER response_payload,
  ADD COLUMN status_checked_at DATETIME NULL AFTER payment_status,
  ADD COLUMN failure_reason VARCHAR(255) NULL AFTER status_checked_at,
  ADD UNIQUE KEY uq_payments_debito_reference (debito_reference),
  ADD INDEX idx_payments_provider_reference (provider_reference),
  ADD INDEX idx_payments_payment_status (payment_status);

ALTER TABLE payment_transactions
  ADD COLUMN provider_name VARCHAR(50) NULL AFTER payment_id,
  ADD COLUMN provider_reference VARCHAR(120) NULL AFTER provider_name,
  ADD COLUMN debito_reference VARCHAR(120) NULL AFTER provider_reference,
  ADD COLUMN external_transaction_id VARCHAR(120) NULL AFTER debito_reference,
  ADD COLUMN request_payload JSON NULL AFTER payload_json,
  ADD COLUMN response_payload JSON NULL AFTER request_payload,
  ADD COLUMN payment_status VARCHAR(40) NULL AFTER response_payload,
  ADD COLUMN status_checked_at DATETIME NULL AFTER payment_status,
  ADD COLUMN failure_reason VARCHAR(255) NULL AFTER status_checked_at,
  ADD UNIQUE KEY uq_payment_transactions_debito_reference (debito_reference),
  ADD INDEX idx_payment_transactions_status_checked (payment_status, status_checked_at);

CREATE TABLE IF NOT EXISTS payment_provider_logs (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT UNSIGNED NULL,
  payment_id BIGINT UNSIGNED NULL,
  provider_name VARCHAR(50) NOT NULL,
  endpoint VARCHAR(255) NOT NULL,
  method VARCHAR(10) NOT NULL,
  request_payload JSON NULL,
  response_payload JSON NULL,
  response_status INT NULL,
  success TINYINT(1) NOT NULL DEFAULT 0,
  error_message TEXT NULL,
  created_at DATETIME NOT NULL,
  CONSTRAINT fk_payment_provider_logs_tenant FOREIGN KEY (tenant_id) REFERENCES tenants(id),
  CONSTRAINT fk_payment_provider_logs_payment FOREIGN KEY (payment_id) REFERENCES payments(id),
  INDEX idx_payment_provider_logs_tenant_created (tenant_id, created_at),
  INDEX idx_payment_provider_logs_payment (payment_id),
  INDEX idx_payment_provider_logs_provider (provider_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
