<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';

/** @var PDO $pdo */

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login_profissional.php?erro=acesso_negado");
    exit;
}

$idUsuario = (int) $_SESSION['id_usuario'];

// Busca dados reais do usuário e da categoria (se houver serviço cadastrado)
$stmtUser = $pdo->prepare("
    SELECT u.nome_usuario, u.email, u.telefone,
           (SELECT c.nome_categoria
            FROM servico s
            INNER JOIN categoria c ON c.id_categoria = s.id_categoria
            WHERE s.id_prestador = u.id_usuario
            LIMIT 1) AS categoria_principal
    FROM usuario u
    WHERE u.id_usuario = :id
");
$stmtUser->execute([':id' => $idUsuario]);
$dadosUsuario = $stmtUser->fetch(PDO::FETCH_ASSOC);

$nomeCompleto = $dadosUsuario['nome_usuario'] ?? ($_SESSION['nome_usuario'] ?? 'Usuário');
$emailUsuario = $dadosUsuario['email'] ?? 'Não informado';
$telefoneUsuario = !empty($dadosUsuario['telefone']) ? $dadosUsuario['telefone'] : 'Não informado';
$categoriaPrincipal = !empty($dadosUsuario['categoria_principal']) ? $dadosUsuario['categoria_principal'] : 'Não definida';

$partesNome = explode(' ', trim($nomeCompleto));
$primeiroNome = $partesNome[0];
$iniciais = strtoupper(substr($primeiroNome, 0, 1) . (isset($partesNome[1]) ? substr($partesNome[1], 0, 1) : ''));

// Busca avaliações reais recebidas por este usuário
$stmtAval = $pdo->prepare("
    SELECT a.numero_nota, a.desc_comentario, a.data_avaliacao, u.nome_usuario AS nome_avaliador
    FROM avaliacao a
    INNER JOIN usuario u ON u.id_usuario = a.id_avaliador
    WHERE a.id_avaliado = :id
    ORDER BY a.data_avaliacao DESC
");
$stmtAval->execute([':id' => $idUsuario]);
$avaliacoes = $stmtAval->fetchAll(PDO::FETCH_ASSOC);

$somaNotas = 0;
foreach ($avaliacoes as $av) {
    $somaNotas += (int) $av['numero_nota'];
}
$notaMedia = count($avaliacoes) > 0 ? ($somaNotas / count($avaliacoes)) : 0;
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
        </nav>
        <div class="avatar" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <a href="perfil.php"><?= htmlspecialchars($iniciais) ?></a>
        </div>
    </header>

    <main class="perfil-main">
        <h2 class="titulo-pagina">Perfil e Avaliações</h2>

        <div class="perfil-container">

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

            <section class="avaliacoes-section">
                <h3>Avaliações recebidas · Nota média <?= number_format($notaMedia, 1, ',', '.') ?></h3>

                <div class="lista-avaliacoes">
                    <?php if (count($avaliacoes) > 0): ?>
                        <?php foreach ($avaliacoes as $av): ?>
                            <?php
                                $nota = (int) $av['numero_nota'];
                                $estrelas = str_repeat('★', $nota) . str_repeat('☆', max(0, 5 - $nota));
                                $dataAv = !empty($av['data_avaliacao']) ? date('d/m', strtotime($av['data_avaliacao'])) : '';
                            ?>
                            <div class="card-avaliacao">
                                <div class="header-avaliacao"><?= $estrelas ?> (<?= $nota ?>/5) · <?= htmlspecialchars($av['nome_avaliador']) ?> · <?= $dataAv ?></div>
                                <p class="texto-avaliacao"><?= htmlspecialchars($av['desc_comentario'] ?? '') ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <p style="color: #666; padding: 15px 0;">Nenhuma avaliação recebida ainda.</p>
                    <?php endif; ?>
                </div>
            </section>

        </div>
    </main>

</body>

</html>
