<?php
// Script para criar um favicon.ico válido a partir do logo.png

$sourceImage = 'assets/logo.png';
$outputIco = 'assets/favicon.ico';

if (!file_exists($sourceImage)) {
    die("Erro: logo.png não encontrado!\n");
}

// Criar imagem a partir do PNG
$img = imagecreatefrompng($sourceImage);
if (!$img) {
    die("Erro ao carregar logo.png\n");
}

// Obter dimensões originais
$width = imagesx($img);
$height = imagesy($img);

// Criar favicon em múltiplos tamanhos (16x16, 32x32, 48x48)
$sizes = [16, 32, 48];
$images = [];

foreach ($sizes as $size) {
    // Criar nova imagem do tamanho desejado
    $resized = imagecreatetruecolor($size, $size);
    
    // Preservar transparência
    imagealphablending($resized, false);
    imagesavealpha($resized, true);
    $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
    imagefill($resized, 0, 0, $transparent);
    imagealphablending($resized, true);
    
    // Redimensionar mantendo proporção
    $aspectRatio = $width / $height;
    if ($aspectRatio > 1) {
        $newWidth = $size;
        $newHeight = $size / $aspectRatio;
    } else {
        $newHeight = $size;
        $newWidth = $size * $aspectRatio;
    }
    
    $offsetX = ($size - $newWidth) / 2;
    $offsetY = ($size - $newHeight) / 2;
    
    imagecopyresampled($resized, $img, $offsetX, $offsetY, 0, 0, $newWidth, $newHeight, $width, $height);
    $images[$size] = $resized;
}

// Salvar como PNG temporários e depois converter para ICO
// Como PHP não tem suporte nativo para ICO, vamos criar PNGs de diferentes tamanhos

// Criar o favicon.ico usando apenas 16x16 (mais comum)
$favicon16 = $images[16];

// Salvar como PNG temporário
imagepng($favicon16, $outputIco . '.png');

// Criar arquivo ICO manualmente (formato básico)
// Header ICO: 6 bytes
$icoHeader = pack('vvv', 0, 1, 1); // Reserved, Type (1=ICO), Count

// Image directory: 16 bytes por imagem
$size = 16;
ob_start();
imagepng($images[16]);
$pngData = ob_get_clean();

$imageDir = pack('CCCCvvVV', 
    $size,           // Width
    $size,           // Height
    0,               // Color palette
    0,               // Reserved
    1,               // Color planes
    32,              // Bits per pixel
    strlen($pngData), // Size of image data
    22               // Offset (6 header + 16 directory)
);

// Escrever arquivo ICO
file_put_contents($outputIco, $icoHeader . $imageDir . $pngData);

// Também salvar versões PNG para diferentes tamanhos
imagepng($images[16], 'assets/favicon-16x16.png');
imagepng($images[32], 'assets/favicon-32x32.png');
imagepng($images[48], 'assets/favicon-48x48.png');

// Limpar
foreach ($images as $img) {
    imagedestroy($img);
}
imagedestroy($img);

// Copiar também para logo.ico
copy($outputIco, 'assets/logo.ico');

echo "✅ Favicon criado com sucesso!\n";
echo "   - favicon.ico (formato ICO)\n";
echo "   - logo.ico (cópia do favicon)\n";
echo "   - favicon-16x16.png\n";
echo "   - favicon-32x32.png\n";
echo "   - favicon-48x48.png\n";
echo "\nTamanho do arquivo ICO: " . filesize($outputIco) . " bytes\n";
