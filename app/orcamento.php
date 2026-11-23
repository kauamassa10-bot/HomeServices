<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'CLIENTE') {
    header('Location: login_cliente.php?erro=acesso_negado');
    exit;
}

$idPrestador = (int) ($_POST['id_prestador'] ?? $_GET['id_prestador'] ?? 0);
$idServico = (int) ($_POST['id_servico'] ?? $_GET['id_servico'] ?? 0);
$idCliente = (int) $_SESSION['id_usuario'];

$servico = read($pdo, 'servico', 'id_servico = ? AND id_prestador = ? AND status_ativo = 1', [$idServico, $idPrestador]);
$prestador = $servico ? read($pdo, 'usuario', 'id_usuario = ?', [$idPrestador]) : false;
$categoria = $servico ? read($pdo, 'categoria', 'id_categoria = ?', [$servico['id_categoria']]) : false;
$prestadorValido = $prestador && strtoupper((string) $prestador['tipo_usuario']) === 'PRESTADOR';

if (!$servico || !$prestadorValido || !$categoria) {
    header('Location: buscar.php');
    exit;
}

$mensagem = '';
$titulo = $_POST['desc_titulo'] ?? $servico['nome_servico'];
$descricao = $_POST['desc_solicitacao'] ?? '';
$endereco = $_POST['desc_endereco_atendimento'] ?? '';
$dataSolicitacao = $_POST['data_solicitacao'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($titulo);
    $descricao = trim($descricao);
    $endereco = trim($endereco);
    $dataValida = DateTime::createFromFormat('!Y-m-d', $dataSolicitacao);
    $dataConfere = $dataValida && $dataValida->format('Y-m-d') === $dataSolicitacao;

    if ($titulo === '' || $descricao === '' || $endereco === '' || !$dataConfere) {
        $mensagem = 'Preencha o título, a descrição, o endereço e a data do atendimento.';
    } else {
        try {
            create($pdo, 'solicitacao', [
                'id_cliente' => $idCliente,
                'id_prestador' => $idPrestador,
                'id_categoria' => $categoria['id_categoria'],
                'desc_titulo' => $titulo,
                'desc_solicitacao' => $descricao,
                'desc_endereco_atendimento' => $endereco,
                'data_solicitacao' => $dataValida->format('Y-m-d'),
                'status_solicitacao' => 'ABERTA',
            ]);

            header('Location: pedidos.php?sucesso=1');
            exit;
        } catch (PDOException $e) {
            $mensagem = 'Não foi possível enviar a solicitação. Tente novamente.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Novo Pedido - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/global.css">
</head>

<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <nav class="menu-topo">
                <a href="buscar.php">Buscar Serviços</a>
                <a href="pedidos.php">Meus Pedidos</a>
                <a href="acompanhar_pedido.php">Acompanhar Pedido</a>
                <a href="logout.php">Sair</a>
            </nav>
        </div>
    </header>

    <main class="layout-duas-colunas">
        <section class="coluna-esquerda">
            <h2>Solicitar Orçamento</h2>
            <p class="subtitulo">Preencha os detalhes do que você precisa</p>

            <?php if ($mensagem !== ''): ?>
                <p class="subtitulo"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>

            <form method="POST" action="" class="form-pedido-leitura">
                <input type="hidden" name="id_prestador" value="<?= $idPrestador ?>">
                <input type="hidden" name="id_servico" value="<?= $idServico ?>">

                <div class="campo-simples">
                    <input type="text" name="desc_titulo" value="<?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8') ?>" required>
                </div>

                <div class="campo-simples">
                    <textarea name="desc_solicitacao" placeholder="Descreva o serviço desejado detalhadamente..." required><?= htmlspecialchars($descricao, ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="linha-dupla">
                    <div class="campo-simples">
                        <input type="text" name="desc_endereco_atendimento" placeholder="Endereço / CEP" value="<?= htmlspecialchars($endereco, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="campo-simples">
                        <input type="date" name="data_solicitacao" value="<?= htmlspecialchars($dataSolicitacao, ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                </div>

                <div class="botoes-rodape">
                    <button type="submit" class="btn-solido" style="width: 100%;">Enviar para 1 profissional</button>
                </div>
            </form>
        </section>

        <aside class="coluna-direita">
            <div class="card-resumo">
                <div class="detalhes-resumo">
                    <strong><?= htmlspecialchars($prestador['nome_usuario'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <p><?= htmlspecialchars($servico['nome_servico'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><?= htmlspecialchars($categoria['nome_categoria'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p><?= htmlspecialchars($servico['desc_servico'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p>A partir de R$ <?= number_format((float) $servico['valor_base'], 2, ',', '.') ?></p>
                    <p><?= htmlspecialchars($prestador['telefone'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>
            </div>
        </aside>
    </main>

</body>

</html>
