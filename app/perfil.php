<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';

// Verifica se está logado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login_profissional.php?erro=acesso_negado");
    exit;
}

// Dados do utilizador (a puxar da sessão ou base de dados no futuro)
$nomeCompleto = $_SESSION['nome_usuario'] ?? 'João da Silva';
$emailUsuario = $_SESSION['email_usuario'] ?? 'joao@email.com';
$telefoneUsuario = '(11) 96666-0000'; // Exemplo
$categoriaPrincipal = 'Elétrica'; // Exemplo

// Iniciais para o Avatar
$partesNome = explode(' ', trim($nomeCompleto));
$primeiroNome = $partesNome[0];
$iniciais = strtoupper(substr($primeiroNome, 0, 1) . (isset($partesNome[1]) ? substr($partesNome[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil e Avaliações - HomeServices</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/perfil.css">
</head>

<body>

    <header class="dashboard-header">
        <div class="dashboard-logo">HomeServices</div>
        <nav class="dashboard-nav">
            <a href="dashboard_prestador.php">Visão Geral</a>
            <a href="mural_solicitacoes.php">Solicitações</a>
            <a href="agendamentos_prestador.php">Meus Agendamentos</a>
            <a href="meus_servicos_lista.php">Meus Serviços</a>
            <a href="perfil.php" class="active">Perfil</a>
        </nav>
        <div class="avatar" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <?= htmlspecialchars($iniciais) ?>
        </div>
    </header>

    <main class="perfil-main">
        <h2 class="titulo-pagina">Perfil e Avaliações</h2>

        <div class="perfil-container">

            <!-- Coluna Esquerda: Dados do Perfil -->
            <aside class="card-perfil">
                <div class="avatar-grande">
                    <?= htmlspecialchars($iniciais) ?>
                </div>

                <div class="info-grupo">
                    <strong>Nome</strong>
                    <p><?= htmlspecialchars($nomeCompleto) ?></p>
                </div>

                <div class="info-grupo">
                    <strong>E-mail</strong>
                    <p><?= htmlspecialchars($emailUsuario) ?></p>
                </div>

                <div class="info-grupo">
                    <strong>Telefone</strong>
                    <p><?= htmlspecialchars($telefoneUsuario) ?></p>
                </div>

                <div class="info-grupo">
                    <strong>Categoria principal</strong>
                    <p><?= htmlspecialchars($categoriaPrincipal) ?></p>
                </div>

                <a href="#" class="btn-editar-perfil">Editar Perfil</a>
            </aside>

            <!-- Coluna Direita: Avaliações -->
            <section class="avaliacoes-section">
                <h3>Avaliações recebidas · Nota média 4,8</h3>

                <div class="lista-avaliacoes">

                    <div class="card-avaliacao">
                        <div class="header-avaliacao">★★★★★ (5/5) · Maria Souza · 05/11</div>
                        <p class="texto-avaliacao">Profissional pontual e muito cuidadoso. Recomendo.</p>
                    </div>

                    <div class="card-avaliacao">
                        <div class="header-avaliacao">★★★★☆ (4/5) · Carlos Lima · 28/10</div>
                        <p class="texto-avaliacao">Bom serviço, terminou um pouco depois do combinado.</p>
                    </div>

                    <div class="card-avaliacao">
                        <div class="header-avaliacao">★★★★★ (5/5) · Ana Paula · 20/10</div>
                        <p class="texto-avaliacao">Resolveu o problema rápido e explicou tudo.</p>
                    </div>

                </div>
            </section>

        </div>
    </main>

</body>

</html>