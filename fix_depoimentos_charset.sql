-- Corrigir charset dos depoimentos
SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Atualizar nome com acento correto
UPDATE depoimentos SET nome = 'João Silva' WHERE id = 1;

-- Verificar
SELECT id, nome, descricao FROM depoimentos;
