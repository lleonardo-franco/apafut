-- Adicionar campos de agendamento e status nas notícias
ALTER TABLE noticias 
ADD COLUMN status ENUM('rascunho', 'agendado', 'publicado') DEFAULT 'publicado' AFTER ativo,
ADD COLUMN data_agendamento DATETIME NULL AFTER status;

-- Atualizar notícias existentes para status 'publicado'
UPDATE noticias SET status = 'publicado' WHERE ativo = 1;

-- Índice para otimizar queries de agendamento
CREATE INDEX idx_status_agendamento ON noticias(status, data_agendamento);

-- Adicionar campo status_pagamento nas assinaturas
ALTER TABLE assinaturas 
MODIFY COLUMN status ENUM('pendente', 'aprovado', 'cancelado', 'expirado') DEFAULT 'pendente';
