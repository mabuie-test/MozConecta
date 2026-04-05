-- FASE 11: hardening integração Débito (C2B M-Pesa/eMola)

ALTER TABLE payments
  ADD COLUMN provider_method VARCHAR(40) NULL AFTER provider_name,
  ADD COLUMN wallet_id_used VARCHAR(120) NULL AFTER provider_method,
  ADD COLUMN provider_response_code VARCHAR(60) NULL AFTER external_transaction_id,
  ADD COLUMN callback_payload JSON NULL AFTER response_payload,
  ADD COLUMN callback_received_at DATETIME NULL AFTER callback_payload,
  ADD COLUMN raw_provider_status VARCHAR(80) NULL AFTER payment_status,
  ADD COLUMN poll_attempts INT UNSIGNED NOT NULL DEFAULT 0 AFTER raw_provider_status,
  ADD COLUMN last_poll_at DATETIME NULL AFTER poll_attempts,
  ADD INDEX idx_payments_provider_method (provider_method),
  ADD INDEX idx_payments_last_poll_at (last_poll_at);

ALTER TABLE payment_provider_logs
  CHANGE COLUMN method http_method VARCHAR(10) NOT NULL,
  CHANGE COLUMN response_status response_status_code INT NULL,
  ADD COLUMN request_headers JSON NULL AFTER http_method,
  ADD COLUMN latency_ms INT NULL AFTER error_message,
  ADD INDEX idx_payment_provider_logs_status_code (response_status_code);
