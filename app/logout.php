<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Identifica para qual tela de login redirecionar antes de apagar a sessão
$tipo = strtoupper($_SESSION['tipo_usuario'] ?? '');
$destino = 'login_cliente.php'; // Padrão

if ($tipo === 'ADMIN') {
    $destino = 'login_cliente.php';
} elseif ($tipo === 'PRESTADOR') {
    $destino = 'login_profissional.php';
}

// Limpa todas as variáveis da sessão
$_SESSION = [];

// Remove o cookie de sessão do navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destrói a sessão no servidor
session_destroy();

// Garante que o redirecionamento funcione mesmo se algum output já tiver sido enviado
if (!headers_sent()) {
    header("Location: " . $destino);
    exit;
} else {
    echo "<script>window.location.href='" . htmlspecialchars($destino, ENT_QUOTES, 'UTF-8') . "';</script>";
    exit;
}