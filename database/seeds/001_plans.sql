INSERT INTO plans (name, slug, price_mt, period_unit, message_limit, ai_limit, instance_limit, user_limit, features_json, highlight_label, display_order, status, created_at, updated_at) VALUES
('TRIAL 24H', 'trial-24h', 0, '24h', 150, 80, 1, 1, JSON_ARRAY('fluxos_basicos','inbox_basico','crm_basico','trial_24h'), 'Teste Real', 1, 'active', NOW(), NOW()),
('INICIAL', 'inicial', 199, 'month', 500, 500, 1, 1, JSON_ARRAY('ia_500','fluxos_basicos','dashboard_essencial','bot_internet_30d'), 'Entrada', 2, 'active', NOW(), NOW()),
('ESSENCIAL', 'essencial', 499, 'month', 1200, 1200, 1, 2, JSON_ARRAY('fluxos_completos','crm_basico','midia','agendamento','ia_imagem'), 'Mais Popular', 3, 'active', NOW(), NOW()),
('CRESCIMENTO', 'crescimento', 1000, 'month', 2500, 2500, 2, 5, JSON_ARRAY('crm_completo','funil','remarketing','massa','relatorios'), 'Escala', 4, 'active', NOW(), NOW()),
('PROFISSIONAL', 'profissional', 1800, 'month', 10000, 10000, 5, 15, JSON_ARRAY('assistente_avancado','notificacoes_tempo_real','tarefas_followup','score_lead'), 'Performance', 5, 'active', NOW(), NOW()),
('ELITE / ENTERPRISE', 'elite-enterprise', 2475, 'month', NULL, NULL, 20, 100, JSON_ARRAY('uso_justo_configuravel','integracoes','permissoes_perfil','suporte_dedicado'), 'Enterprise', 6, 'active', NOW(), NOW());
