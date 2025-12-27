-- Adicionar colunas status e data_agendamento à tabela noticias
-- Execute este script para atualizar a estrutura da tabela

ALTER TABLE noticias 
ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'publicado' AFTER destaque,
ADD COLUMN IF NOT EXISTS data_agendamento DATETIME NULL AFTER status;

-- Atualizar registros existentes
UPDATE noticias SET status = 'publicado' WHERE status IS NULL OR status = '';

-- Adicionar índice para melhor performance
CREATE INDEX idx_status ON noticias(status);
CREATE INDEX idx_data_agendamento ON noticias(data_agendamento);
