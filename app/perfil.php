<?php
require_once __DIR__ . '/init.php';

/** @var PDO $pdo */

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login_profissional.php?erro=acesso_negado");
    exit;
}

$idUsuario = (int) $_SESSION['id_usuario'];

// Busca dados do usuário com read() do CRUD
$dadosUsuario = read($pdo, 'usuario', 'id_usuario = ?', [$idUsuario]) ?: [];

// Busca categoria principal a partir do primeiro serviço cadastrado pelo usuário
$servicosUsuario = readAll($pdo, 'servico', 'id_prestador = ?', [$idUsuario]);
$categoriaPrincipal = 'Não definida';
if (count($servicosUsuario) > 0) {
    $cat = read($pdo, 'categoria', 'id_categoria = ?', [$servicosUsuario[0]['id_categoria']]);
    if ($cat) {
        $categoriaPrincipal = $cat['nome_categoria'];
    }
}

$nomeCompleto = $dadosUsuario['nome_usuario'] ?? ($_SESSION['nome_usuario'] ?? 'Usuário');
$emailUsuario = !empty($dadosUsuario['email']) ? $dadosUsuario['email'] : 'Não informado';
$telefoneUsuario = !empty($dadosUsuario['telefone']) ? $dadosUsuario['telefone'] : 'Não informado';
$iniciais = pegarIniciais($nomeCompleto);

// Busca avaliações recebidas usando readAll() do CRUD
$avaliacoes = readAll($pdo, 'avaliacao', 'id_avaliado = ?', [$idUsuario]);
$usuarios = readAll($pdo, 'usuario');
$usuarioPorId = array_column($usuarios, null, 'id_usuario');

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

                <button class="btn-editar" onclick="window.location.href='editar_perfil.php'">Editar Perfil</button>
                <button class="btn-logout" onclick="window.location.href='logout.php'">Sair</button>
            </aside>

            <section class="avaliacoes-section">
                <h3>Avaliações recebidas · Nota média <?= number_format($notaMedia, 1, ',', '.') ?></h3>

                <div class="lista-avaliacoes">
                    <?php if (count($avaliacoes) > 0): ?>
                        <?php foreach ($avaliacoes as $av): ?>
                            <?php
                                $avaliador = $usuarioPorId[$av['id_avaliador']] ?? [];
                                $nota = (int) $av['numero_nota'];
                                $estrelas = str_repeat('★', $nota) . str_repeat('☆', max(0, 5 - $nota));
                                $dataAv = !empty($av['data_avaliacao']) ? date('d/m', strtotime($av['data_avaliacao'])) : '';
                            ?>
                            <div class="card-avaliacao">
                                <div class="header-avaliacao"><?= $estrelas ?> (<?= $nota ?>/5) · <?= htmlspecialchars($avaliador['nome_usuario'] ?? 'Cliente') ?> · <?= $dataAv ?></div>
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
