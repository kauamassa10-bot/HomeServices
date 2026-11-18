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
    <style>
        /* Define a altura mínima da página para o rodapé ficar em baixo */
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            margin: 0;
        }
        
        main {
            flex: 1; /* Faz a área principal expandir e empurrar o rodapé */
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .hero {
            text-align: center;
            padding: 40px 20px;
            background-color: transparent; /* Removido o fundo cinza para ficar mais limpo */
        }
        
        .hero h2 {
            font-size: 32px;
            color: #2b5cbf;
            margin-bottom: 15px;
        }
        
        .hero p {
            font-size: 18px;
            color: #555;
            max-width: 600px;
            margin: 0 auto 30px auto;
            line-height: 1.6;
        }
        
        .botoes-entrada {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .btn-grande {
            padding: 12px 30px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }
        
        .btn-cliente {
            background-color: #2b5cbf;
            color: white;
            border: 2px solid #2b5cbf;
        }
        
        .btn-profissional {
            background-color: transparent;
            color: #2b5cbf;
            border: 2px solid #2b5cbf;
        }
        
        .btn-grande:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(43, 92, 191, 0.15);
        }

        /* Estilo do Rodapé */
        .footer-simples {
            text-align: center;
            padding: 20px;
            border-top: 1px solid #eaeaea;
            background-color: #f9f9f9;
            font-size: 13px;
        }

        .footer-simples a {
            color: #888;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-simples a:hover {
            color: #2b5cbf;
        }

        /* Estilo do status do banco */
        .status-db {
            font-size: 12px;
            color: #777;
            margin-top: 10px;
        }
        .status-db.erro {
            color: #e53e3e;
            font-weight: bold;
        }
    </style>
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