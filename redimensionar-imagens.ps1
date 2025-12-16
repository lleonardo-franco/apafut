# Script para redimensionar imagens do carrossel
Add-Type -AssemblyName System.Drawing

$targetWidth = 1200
$targetHeight = 800

$imagens = @("goleiros.jpg", "meninas.jpg", "noticia2.jpg", "noticia3.jpg")

foreach ($imagemNome in $imagens) {
    $caminhoOriginal = "assets\$imagemNome"
    $caminhoTemp = "assets\temp_$imagemNome"
    
    if (Test-Path $caminhoOriginal) {
        Write-Host "Redimensionando $imagemNome..." -ForegroundColor Yellow
        
        try {
            $imagemOriginal = [System.Drawing.Image]::FromFile((Resolve-Path $caminhoOriginal))
            $novaImagem = New-Object System.Drawing.Bitmap($targetWidth, $targetHeight)
            $graphics = [System.Drawing.Graphics]::FromImage($novaImagem)
            
            $graphics.InterpolationMode = [System.Drawing.Drawing2D.InterpolationMode]::HighQualityBicubic
            $graphics.SmoothingMode = [System.Drawing.Drawing2D.SmoothingMode]::HighQuality
            $graphics.PixelOffsetMode = [System.Drawing.Drawing2D.PixelOffsetMode]::HighQuality
            $graphics.CompositingQuality = [System.Drawing.Drawing2D.CompositingQuality]::HighQuality
            
            $graphics.DrawImage($imagemOriginal, 0, 0, $targetWidth, $targetHeight)
            
            $graphics.Dispose()
            $imagemOriginal.Dispose()
            
            $novaImagem.Save($caminhoTemp, [System.Drawing.Imaging.ImageFormat]::Jpeg)
            $novaImagem.Dispose()
            
            $backupPath = "assets\backup_$imagemNome"
            if (Test-Path $backupPath) {
                Remove-Item $backupPath -Force
            }
            Move-Item $caminhoOriginal $backupPath -Force
            Move-Item $caminhoTemp $caminhoOriginal -Force
            
            Write-Host "  OK - Redimensionada para ${targetWidth}x${targetHeight}px" -ForegroundColor Green
            
        } catch {
            Write-Host "  ERRO: $_" -ForegroundColor Red
            if (Test-Path $caminhoTemp) {
                Remove-Item $caminhoTemp -Force
            }
        }
    } else {
        Write-Host "$imagemNome nao encontrada" -ForegroundColor Red
    }
}

Write-Host "`nConcluido!" -ForegroundColor Cyan
