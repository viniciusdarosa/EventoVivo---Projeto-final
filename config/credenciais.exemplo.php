<?php
/* ==========================================================
 * CONFIGURAÇÃO DE CREDENCIAIS — EXEMPLO
 *
 * 1. Copie este arquivo para: config/credenciais.local.php
 * 2. Ajuste os valores ao seu servidor.
 * 3. credenciais.local.php está no .gitignore (não vai para o repositório).
 *
 * Alternativa recomendada em produção: variáveis de ambiente
 * EVENTOVIVO_DB_HOST / EVENTOVIVO_DB_USER / EVENTOVIVO_DB_PASS /
 * EVENTOVIVO_DB_NAME (têm prioridade sobre este arquivo).
 * ========================================================== */

return array(
    'host' => 'localhost',
    'usuario' => 'root',
    'senha' => '',
    'banco' => 'eventovivo',
);
