-- Script para remover status 'pendente' do sistema de assinaturas
-- Execute este script para atualizar o banco de dados

-- 1. Primeiro, atualizar todas as assinaturas pendentes para aprovado
UPDATE assinaturas 
SET status = 'aprovado' 
WHERE status = 'pendente';

-- 2. Remover 'pendente' do ENUM e definir 'aprovado' como padrão
ALTER TABLE assinaturas 
MODIFY COLUMN status ENUM('aprovado', 'cancelado', 'expirado') 
DEFAULT 'aprovado' 
NOT NULL;

-- Verificar o resultado
SELECT status, COUNT(*) as total 
FROM assinaturas 
GROUP BY status;
