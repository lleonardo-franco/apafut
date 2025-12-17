-- Adicionar campos para informações detalhadas dos jogadores
ALTER TABLE jogadores 
ADD COLUMN IF NOT EXISTS nome_completo VARCHAR(255) AFTER nome,
ADD COLUMN IF NOT EXISTS cidade VARCHAR(100) AFTER nome_completo,
ADD COLUMN IF NOT EXISTS altura VARCHAR(20) AFTER cidade,
ADD COLUMN IF NOT EXISTS peso VARCHAR(20) AFTER altura,
ADD COLUMN IF NOT EXISTS data_nascimento VARCHAR(50) AFTER peso,
ADD COLUMN IF NOT EXISTS carreira TEXT AFTER data_nascimento;

-- Atualizar dados dos jogadores com informações
UPDATE jogadores SET
    altura = '1.85m',
    peso = '78kg',
    nome_completo = nome,
    cidade = 'Caxias do Sul (RS)',
    data_nascimento = '15/03/2000',
    carreira = 'Em formação pela Apafut'
WHERE altura IS NULL OR altura = '';
