-- Adicionar campo para vídeos do YouTube/Vimeo
ALTER TABLE depoimentos 
ADD COLUMN video_url VARCHAR(500) NULL AFTER video,
ADD COLUMN tipo_depoimento ENUM('video_local', 'video_url', 'texto') DEFAULT 'video_local' AFTER video_url,
MODIFY COLUMN video VARCHAR(255) NULL,
MODIFY COLUMN depoimento TEXT NULL;

-- Comentários explicativos:
-- video: vídeos locais (MP4 no servidor)
-- video_url: URLs do YouTube ou Vimeo
-- tipo_depoimento: define qual tipo será exibido
-- depoimento: texto do depoimento (para tipo_depoimento = 'texto')
