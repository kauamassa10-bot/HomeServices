<?php
require_once __DIR__ . '/init.php';

/** @var PDO $pdo */

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'PRESTADOR') {
    header("Location: login_profissional.php?erro=acesso_negado");
    exit;
}

$idPrestador = (int) $_SESSION['id_usuario'];
$nomeCompleto = $_SESSION['nome_usuario'] ?? 'Profissional';
$iniciais = pegarIniciais($nomeCompleto);

// Alternar status Ativo / Inativo usando read() e update() do CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $idServico = (int) $_POST['toggle_id'];
    $servicoAtual = read($pdo, 'servico', 'id_servico = ? AND id_prestador = ?', [$idServico, $idPrestador]);

    if ($servicoAtual) {
        $novoStatus = ((int) $servicoAtual['status_ativo'] === 1) ? 0 : 1;
        update(
            $pdo,
            'servico',
            ['status_ativo' => $novoStatus],
            'id_servico = ? AND id_prestador = ?',
            [$idServico, $idPrestador]
        );
    }
    header("Location: meus_servicos_lista.php");
    exit;
}

$servicos = readAll($pdo, 'servico', 'id_prestador = ?', [$idPrestador]);
$categorias = readAll($pdo, 'categoria');
$categoriaPorId = array_column($categorias, null, 'id_categoria');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Serviços - HomeServices</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/lista_servicos.css">
</head>

<body>

    <header class="dashboard-header">
        <div class="dashboard-logo">HomeServices</div>
        <nav class="dashboard-nav">
            <a href="dashboard_prestador.php">Visão Geral</a>
            <a href="mural_solicitacoes.php">Solicitações</a>
            <a href="agendamentos_prestador.php">Meus Agendamentos</a>
            <a href="meus_servicos_lista.php" class="active">Meus Serviços</a>
        </nav>
        <div class="avatar" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <a href="perfil.php"><?= htmlspecialchars($iniciais) ?></a>
        </div>
    </header>

    <main class="meus-servicos-main">

        <div class="header-acoes">
            <h2 class="titulo-pagina">Meus Serviços</h2>
            <a href="cadastro_servicos.php" class="btn-novo-servico">+ Cadastrar Novo Serviço</a>
        </div>

        <div class="tabela-container">

            <div class="toolbar-tabela">
                <span title="Atualizar">↻</span>
                <span title="Layout">◫</span>
                <span title="Imprimir">⎙</span>
                <span title="Filtrar">▽</span>
                <span title="Ordenar">⇅</span>
                <span title="Lista">≣</span>
                <span title="Configurações">⊶</span>
                <span title="Pesquisar">🔍</span>
            </div>

            <table class="tabela-servicos">
                <thead>
                    <tr>
                        <th class="col-id"></th>
                        <th>⚑ Nome do Serviço</th>
                        <th>≡ Categoria</th>
                        <th>≡ Descrição</th>
                        <th>≡ Valor Base (R$)</th>
                        <th>⚙ Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($servicos) > 0): ?>
                        <?php foreach ($servicos as $index => $s): ?>
                            <?php
                                $ativo = ((int) $s['status_ativo'] === 1);
                                $cat = $categoriaPorId[$s['id_categoria']] ?? [];
                            ?>
                            <tr>
                                <td class="col-id"><?= $index + 1 ?></td>
                                <td><?= htmlspecialchars($s['nome_servico']) ?></td>
                                <td><?= htmlspecialchars($cat['nome_categoria'] ?? '') ?></td>
                                <td><?= htmlspecialchars($s['desc_servico'] ?? '') ?></td>
                                <td>R$ <?= number_format((float) $s['valor_base'], 2, ',', '.') ?></td>
                                <td>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="toggle_id" value="<?= (int) $s['id_servico'] ?>">
                                        <button type="submit" class="badge-status <?= $ativo ? 'status-ativo' : 'status-inativo' ?>" style="border: none; cursor: pointer;" title="Clique para alternar o status">
                                            <?= $ativo ? 'Ativo' : 'Inativo' ?>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px; color: #666;">
                                Nenhum serviço cadastrado ainda. Clique em <strong>+ Cadastrar Novo Serviço</strong> para começar.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <a href="cadastro_servicos.php" class="linha-adicionar" title="Adicionar nova linha" style="display: block; text-decoration: none;">
                +
            </a>

        </div>
    </main>

</body>

</html>
