# Script para restaurar imagens originais dos backups

$backups = Get-ChildItem "assets\backup_*.jpg"

if ($backups.Count -eq 0) {
    Write-Host "Nenhum backup encontrado!" -ForegroundColor Red
    exit
}

Write-Host "Restaurando imagens originais..." -ForegroundColor Cyan

foreach ($backup in $backups) {
    $nomeOriginal = $backup.Name -replace "^backup_", ""
    $caminhoOriginal = "assets\$nomeOriginal"
    
    try {
        if (Test-Path $caminhoOriginal) {
            Remove-Item $caminhoOriginal -Force
        }
        
        Copy-Item $backup.FullName $caminhoOriginal -Force
        Write-Host "  OK - $nomeOriginal restaurada" -ForegroundColor Green
        
    } catch {
        Write-Host "  ERRO ao restaurar $nomeOriginal : $_" -ForegroundColor Red
    }
}

Write-Host "`nImagens restauradas com sucesso!" -ForegroundColor Cyan
Write-Host "Os backups foram mantidos na pasta assets" -ForegroundColor Yellow
