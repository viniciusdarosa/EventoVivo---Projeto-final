<?php
    $servidor = "localhost";
    $usuario = "root";
    $senha = "";
    $banco = "eventovivo";

    $conexao = new mysqli($servidor, $usuario, $senha, $banco);

    if ($conexao->connect_error) {
        die("Erro na conexão: " . $conexao->connect_error);
    }

    // Garante UTF-8 em toda a comunicação com o banco
    $conexao->set_charset('utf8mb4');
?>