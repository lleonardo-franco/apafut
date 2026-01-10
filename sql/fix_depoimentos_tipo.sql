-- Adicionar tipo 'video_com_texto' ao ENUM de tipo_depoimento
ALTER TABLE depoimentos 
MODIFY COLUMN tipo_depoimento ENUM('video_local', 'video_url', 'texto', 'video_com_texto') DEFAULT 'video_local';

-- Comentário: Permite que depoimentos tenham vídeo com texto descritivo adicional
