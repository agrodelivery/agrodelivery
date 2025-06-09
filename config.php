<?php
// config.php

// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'agrodelivery');

// Conexão com o banco de dados
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Erro de conexão: " . $conn->connect_error);
    }
    
    $conn->set_charset("utf8");
    return $conn;
}

// Iniciar sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Função para redirecionar
function redirect($url) {
    header("Location: $url");
    exit();
}

// Função para sanitizar inputs
function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

// Função para formatar preço
function formatPrice($price) {
    return 'R$ ' . number_format($price, 2, ',', '.');
}
?>