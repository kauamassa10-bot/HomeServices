<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'PRESTADOR') {
    header('Location: login_profissional.php?erro=acesso_negado');
    exit;
}

$idPrestador = (int) $_SESSION['id_usuario'];
$nomeCompleto = $_SESSION['nome_usuario'] ?? '';
$iniciais = pegarIniciais($nomeCompleto);

$filtroCategoria = trim($_GET['categoria'] ?? '');
$filtroLocal = trim($_GET['local'] ?? '');
$filtroData = trim($_GET['data'] ?? '');

$solicitacoes = readAll(
    $pdo,
    'solicitacao',
    'id_prestador = ? AND status_solicitacao = ?',
    [$idPrestador, 'ABERTA']
);
$usuarios = readAll($pdo, 'usuario');
$categorias = readAll($pdo, 'categoria');
$usuarioPorId = array_column($usuarios, null, 'id_usuario');
$categoriaPorId = array_column($categorias, null, 'id_categoria');

$solicitacoesVisiveis = [];
foreach ($solicitacoes as $solicitacao) {
    $cliente = $usuarioPorId[$solicitacao['id_cliente']] ?? null;
    $categoria = $categoriaPorId[$solicitacao['id_categoria']] ?? null;
    if ($cliente === null || $categoria === null) {
        continue;
    }

    $nomeCategoria = $categoria['nome_categoria'];
    $endereco = (string) $solicitacao['desc_endereco_atendimento'];
    $dataBruta = (string) $solicitacao['data_solicitacao'];
    $dataFormatada = $dataBruta !== '' ? date('d/m/Y', strtotime($dataBruta)) : '';

    if ($filtroCategoria !== '' && stripos($nomeCategoria, $filtroCategoria) === false) {
        continue;
    }
    if ($filtroLocal !== '' && stripos($endereco, $filtroLocal) === false) {
        continue;
    }
    if ($filtroData !== '' && stripos($dataFormatada, $filtroData) === false && stripos($dataBruta, $filtroData) === false) {
        continue;
    }

    $solicitacao['nome_cliente'] = $cliente['nome_usuario'];
    $solicitacao['nome_categoria'] = $nomeCategoria;
    $solicitacao['data_formatada'] = $dataFormatada;
    $solicitacoesVisiveis[] = $solicitacao;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mural de Solicitações - HomeServices</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/solicitacoes.css">
</head>

<body>

    <header class="cabecalho">
        <div class="logo">HomeServices</div>
        <nav class="links">
            <a href="dashboard_prestador.php">Visão Geral</a>
            <a href="mural_solicitacoes.php" class="active">Solicitações</a>
            <a href="agendamentos_prestador.php">Meus Agendamentos</a>
            <a href="meus_servicos_lista.php">Meus Serviços</a>
            <div class="foto" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <a href="perfil.php"><?= htmlspecialchars($iniciais) ?></a>
        </div>
        </nav>
        
    </header>

    <main class="solicitacoes-main">
        <h2 class="titulo-pagina">Mural de Solicitações</h2>

        <form method="GET" action="" class="filtros-container">
            <input type="text" name="categoria" class="input-filtro" placeholder="Categoria" value="<?= htmlspecialchars($filtroCategoria, ENT_QUOTES, 'UTF-8') ?>">
            <input type="text" name="local" class="input-filtro" placeholder="Cidade / Bairro" value="<?= htmlspecialchars($filtroLocal, ENT_QUOTES, 'UTF-8') ?>">
            <input type="text" name="data" class="input-filtro" placeholder="Data de publicação" value="<?= htmlspecialchars($filtroData, ENT_QUOTES, 'UTF-8') ?>">
            <button type="submit" class="btn-filtrar">Filtrar</button>
        </form>

        <div class="solicitacoes-grid">
            <?php if ($solicitacoesVisiveis === []): ?>
                <div class="card-solicitacao">
                    <div class="conteudo-solicitacao">
                        <h4>Nenhuma solicitação aberta</h4>
                        <div class="detalhe-solicitacao descricao">Quando um cliente pedir orçamento para você, o pedido aparece aqui.</div>
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($solicitacoesVisiveis as $solicitacao): ?>
                    <div class="card-solicitacao">
                        <div class="conteudo-solicitacao">
                            <h4><?= htmlspecialchars($solicitacao['desc_titulo'], ENT_QUOTES, 'UTF-8') ?></h4>
                            <div class="detalhe-solicitacao categoria">
                                <?= htmlspecialchars($solicitacao['nome_categoria'], ENT_QUOTES, 'UTF-8') ?>
                                · Cliente: <?= htmlspecialchars($solicitacao['nome_cliente'], ENT_QUOTES, 'UTF-8') ?>
                            </div>
                            <div class="detalhe-solicitacao local"><?= htmlspecialchars($solicitacao['desc_endereco_atendimento'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="detalhe-solicitacao descricao"><?= htmlspecialchars($solicitacao['desc_solicitacao'], ENT_QUOTES, 'UTF-8') ?></div>
                            <div class="detalhe-solicitacao data">Publicado em <?= htmlspecialchars($solicitacao['data_formatada'], ENT_QUOTES, 'UTF-8') ?></div>
                        </div>
                        <form method="GET" action="enviar_proposta.php">
                            <input type="hidden" name="id_solicitacao" value="<?= (int) $solicitacao['id_solicitacao'] ?>">
                            <button type="submit" class="btn-proposta">Fazer Proposta</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>

</body>
</html>
