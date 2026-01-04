<?php
// Limpa OpCache do PHP
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✓ OpCache limpo com sucesso!";
} else {
    echo "OpCache não está ativo";
}
?>
