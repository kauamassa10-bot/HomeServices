<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'CLIENTE') {
    header('Location: login_cliente.php?erro=acesso_negado');
    exit;
}

$idCliente = (int) $_SESSION['id_usuario'];
$idProposta = (int) ($_POST['id_proposta'] ?? $_GET['id_proposta'] ?? 0);
$proposta = $idProposta > 0 ? read($pdo, 'proposta', 'id_proposta = ?', [$idProposta]) : false;
$solicitacao = $proposta ? read($pdo, 'solicitacao', 'id_solicitacao = ?', [$proposta['id_solicitacao']]) : false;
$prestador = $proposta ? read($pdo, 'usuario', 'id_usuario = ?', [$proposta['id_prestador']]) : false;
$cliente = read($pdo, 'usuario', 'id_usuario = ?', [$idCliente]);

$propostaDoCliente = $proposta
    && $solicitacao
    && $prestador
    && (int) $solicitacao['id_cliente'] === $idCliente
    && (int) $proposta['id_solicitacao'] === (int) $solicitacao['id_solicitacao']
    && (
        empty($solicitacao['id_prestador'])
        || (int) $solicitacao['id_prestador'] === (int) $proposta['id_prestador']
    );

if (!$propostaDoCliente) {
    header('Location: buscar.php');
    exit;
}

$iniciaisAvatar = pegarIniciais($cliente['nome_usuario'] ?? ($_SESSION['nome_usuario'] ?? ''));
$mensagem = '';
$jaAgendado = read($pdo, 'agendamento', 'id_proposta = ?', [$idProposta]);
$podeAgendar = strtoupper((string) $proposta['status_proposta']) === 'ENVIADA' && !$jaAgendado;

$dataForm = trim((string) ($_POST['data_agendamento'] ?? $solicitacao['data_solicitacao']));
$horaForm = trim((string) ($_POST['hora_agendamento'] ?? ''));
$observacoesForm = $_POST['observacoes'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $podeAgendar) {
    $dataValida = DateTime::createFromFormat('Y-m-d', (string) $dataForm);
    $horaValida = DateTime::createFromFormat('H:i', substr((string) $horaForm, 0, 5));

    if (!$dataValida || $dataValida->format('Y-m-d') !== $dataForm || !$horaValida) {
        $mensagem = 'Escolha a data e o horário do atendimento.';
    } else {
        try {
            $pdo->beginTransaction();
            $idAgendamento = create($pdo, 'agendamento', [
                'id_solicitacao' => $solicitacao['id_solicitacao'],
                'id_proposta' => $proposta['id_proposta'],
                'id_cliente' => $idCliente,
                'id_prestador' => $proposta['id_prestador'],
                'data_agendamento' => $dataValida->format('Y-m-d'),
                'hora_agendamento' => $horaValida->format('H:i'),
                'observacoes' => trim($observacoesForm),
                'status_agendamento' => 'AGENDADO',
                'data_criacao' => date('Y-m-d'),
            ]);
            update(
                $pdo,
                'proposta',
                ['status_proposta' => 'ACEITA'],
                'id_proposta = ?',
                [$proposta['id_proposta']]
            );
            $pdo->commit();

            header('Location: acompanhar_pedido.php?id=' . urlencode((string) $idAgendamento));
            exit;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $mensagem = 'Não foi possível confirmar o agendamento. Tente novamente.';
        }
    }
}

$dataDesejada = $solicitacao['data_solicitacao']
    ? date('d/m/Y', strtotime($solicitacao['data_solicitacao']))
    : '';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamento - HomeServices</title>
    <link rel="stylesheet" href="css/global.css">
    <link rel="stylesheet" href="css/agendamento.css">
</head>

<body>
    <header class="cabecalho">
        <div class="logo">HomeServices</div>
        <nav class="links">
            <a href="buscar.php">Buscar Serviços</a>
            <a href="pedidos.php">Meus Pedidos</a>
            <a href="acompanhar_pedido.php">Acompanhar Pedido</a>
            <a href="logout.php">Sair</a>
            <div class="foto"><?= htmlspecialchars($iniciaisAvatar, ENT_QUOTES, 'UTF-8') ?></div>
        </nav>
    </header>

    <main class="conteudo">
        <div class="titulo">
            <h1>Agende o serviço</h1>
            <p>Escolha a melhor data e horário para receber o <?= htmlspecialchars($prestador['nome_usuario'], ENT_QUOTES, 'UTF-8') ?>.</p>
        </div>

        <?php if ($mensagem !== ''): ?>
            <p class="titulo"><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>
        <?php endif; ?>

        <?php if ($jaAgendado): ?>
            <p class="titulo">Este serviço já foi agendado.</p>
            <a href="acompanhar_pedido.php?id=<?= (int) $jaAgendado['id_agendamento'] ?>" class="botao">Acompanhar pedido</a>
        <?php elseif (!$podeAgendar): ?>
            <p class="titulo">Esta proposta não está disponível para agendamento. Status: <?= htmlspecialchars($proposta['status_proposta'], ENT_QUOTES, 'UTF-8') ?></p>
            <a href="buscar.php" class="botao">Voltar para a busca</a>
        <?php else: ?>
            <form action="" method="POST">
                <input type="hidden" name="id_proposta" value="<?= (int) $proposta['id_proposta'] ?>">

                <div class="bloco bloco-agendamento">
                    <div class="caixa selecao-tempo">
                        <h3>1. Escolha a Data</h3>
                        <input type="date" name="data_agendamento" class="campo-texto" required value="<?= htmlspecialchars((string) $dataForm, ENT_QUOTES, 'UTF-8') ?>">

                        <h3 class="mt-40">2. Escolha o Horário</h3>
                        <input type="time" name="hora_agendamento" class="campo-texto" required value="<?= htmlspecialchars(substr((string) $horaForm, 0, 5), ENT_QUOTES, 'UTF-8') ?>">

                        <h3 class="mt-40">3. Observações</h3>
                        <textarea name="observacoes" class="campo-texto" placeholder="Instruções para o profissional..."><?= htmlspecialchars($observacoesForm, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="caixa resumo-agendamento">
                        <h3>Resumo do Serviço</h3>
                        <div class="detalhes-resumo">
                            <div class="item-resumo">
                                <span>Serviço</span>
                                <strong><?= htmlspecialchars($solicitacao['desc_titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <div class="item-resumo">
                                <span>Profissional</span>
                                <strong><?= htmlspecialchars($prestador['nome_usuario'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <div class="item-resumo">
                                <span>Endereço</span>
                                <strong><?= htmlspecialchars($solicitacao['desc_endereco_atendimento'], ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                            <div class="item-resumo">
                                <span>Data desejada</span>
                                <strong class="destaque-verde"><?= htmlspecialchars($dataDesejada, ENT_QUOTES, 'UTF-8') ?></strong>
                            </div>
                        </div>

                        <div class="divisor-resumo"></div>

                        <div class="valor-total">
                            <span>Total Estimado</span>
                            <strong>R$ <?= number_format((float) $proposta['valor_proposta'], 2, ',', '.') ?></strong>
                        </div>

                        <button type="submit" class="botao">Confirmar agendamento</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </main>

</body>

</html>
