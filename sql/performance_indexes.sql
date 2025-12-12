-- Índices para otimização de performance

-- Tabela noticias
ALTER TABLE noticias ADD INDEX IF NOT EXISTS idx_ativo_data (ativo, data_publicacao);
ALTER TABLE noticias ADD INDEX IF NOT EXISTS idx_destaque (destaque);

-- Tabela jogadores  
ALTER TABLE jogadores ADD INDEX IF NOT EXISTS idx_ativo_ordem (ativo, ordem, numero);

-- Tabela planos
ALTER TABLE planos ADD INDEX IF NOT EXISTS idx_ativo_ordem_planos (ativo, ordem, preco_anual);

-- Tabela depoimentos
ALTER TABLE depoimentos ADD INDEX IF NOT EXISTS idx_ativo (ativo);

-- Tabela analytics_pageviews (created_at ao invés de data)
ALTER TABLE analytics_pageviews ADD INDEX IF NOT EXISTS idx_created_sessao (created_at, session_id);
ALTER TABLE analytics_pageviews ADD INDEX IF NOT EXISTS idx_titulo (titulo);
