-- Adicionar campo para imagem mobile nos banners
ALTER TABLE banners ADD COLUMN imagem_mobile VARCHAR(255) NULL AFTER imagem;
