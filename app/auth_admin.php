<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Bloqueia acesso se não estiver logado ou se não for ADMIN
if (!isset($_SESSION['id_usuario']) || ($_SESSION['tipo_usuario'] ?? '') !== 'ADMIN') {
    header("Location: login.php?erro=acesso_negado");
    exit;
}