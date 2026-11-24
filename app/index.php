<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Verificação do Banco de Dados
$databaseStatus = 'Conectado';
try {
    require_once __DIR__ . '/init.php';
    $pdo->query('SELECT 1');
} catch (Throwable $exception) {
    $databaseStatus = 'Indisponível';
}

// 2. Redirecionamento automático se já estiver logado
if (isset($_SESSION['id_usuario'])) {
    $tipo = strtoupper($_SESSION['tipo_usuario'] ?? '');
    if ($tipo === 'CLIENTE') {
        header("Location: buscar.php");
        exit;
    } elseif ($tipo === 'PRESTADOR') {
        header("Location: mural_solicitacoes.php");
        exit;
    } elseif ($tipo === 'ADMIN') {
        header("Location: registros.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeServices - Conectando Clientes a Profissionais</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/index.css">
        
</head>
<body>

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <!-- O menu superior foi removido daqui para deixar o cabeçalho mais limpo -->
        </div>
    </header>

    <main>
        <section class="hero">
            <h2>Encontre o profissional certo ou ofereça os seus serviços.</h2>
            <p>A HomeServices liga clientes que precisam de ajuda em casa a profissionais qualificados da sua região. Rápido, prático e seguro.</p>
            
            <div class="botoes-entrada">
                <a href="login_cliente.php" class="btn-grande btn-cliente">Sou Cliente</a>
                <a href="login_profissional.php" class="btn-grande btn-profissional">Sou Profissional</a>
            </div>
        </section>
    </main>

    <!-- O link de Administração foi movido para o rodapé -->
    <footer class="footer-simples">
        <p>© <?= date('Y') ?> HomeServices. Todos os direitos reservados.</p>
        <a href="login_admin.php">Acesso Restrito (Área Administrativa)</a>
        
        <div class="status-db <?= $databaseStatus !== 'Conectado' ? 'erro' : '' ?>">
            Status do DB: <?= htmlspecialchars($databaseStatus, ENT_QUOTES, 'UTF-8') ?>
        </div>
    </footer>

</body>
</html>