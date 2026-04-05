INSERT INTO plans (name, slug, description, price_mt, currency, billing_period, message_limit, ai_limit, instance_limit, user_limit, feature_flags_json, is_featured, display_order, status, created_at, updated_at)
VALUES
('TRIAL 24H', 'trial-24h', 'Plano de avaliação por 24 horas', 0.00, 'MZN', '24h', 150, 80, 1, 1,
 JSON_ARRAY('trial_24h','fluxos_basicos','crm_basico','inbox_basico'), 0, 1, 'active', NOW(), NOW()),
('INICIAL', 'inicial', 'Plano inicial para pequenos negócios', 199.00, 'MZN', 'monthly', 500, 500, 1, 1,
 JSON_ARRAY('ia_500','funil_prompt_ia','imagem_personalizada','dashboard_essencial'), 0, 2, 'active', NOW(), NOW()),
('ESSENCIAL', 'essencial', 'Plano essencial com CRM básico', 499.00, 'MZN', 'monthly', 1200, 1200, 1, 2,
 JSON_ARRAY('fluxos_completos','midia','agendamento','crm_basico'), 1, 3, 'active', NOW(), NOW()),
('CRESCIMENTO', 'crescimento', 'Plano de escala comercial', 1000.00, 'MZN', 'monthly', 2500, 2500, 2, 5,
 JSON_ARRAY('crm_completo','funil_comercial','remarketing','mensagens_massa','relatorios_avancados'), 0, 4, 'active', NOW(), NOW()),
('PROFISSIONAL', 'profissional', 'Plano profissional com múltiplos atendentes', 1800.00, 'MZN', 'monthly', 10000, 10000, 5, 15,
 JSON_ARRAY('assistente_ia_avancado','notificacoes_tempo_real','tarefas_followup','score_lead'), 0, 5, 'active', NOW(), NOW()),
('ELITE / ENTERPRISE', 'elite-enterprise', 'Plano enterprise com uso justo configurável', 2475.00, 'MZN', 'monthly', NULL, NULL, 20, 100,
 JSON_ARRAY('assistente_ia_premium','integracoes_api','suporte_dedicado','permissoes_avancadas'), 0, 6, 'active', NOW(), NOW());
