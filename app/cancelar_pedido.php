<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'CLIENTE') {
    header("Location: login_cliente.php?erro=acesso_negado");
    exit;
}

$idCliente = (int) $_SESSION['id_usuario'];
$idAgendamento = (int) ($_POST['id_agendamento'] ?? $_GET['id'] ?? 0);

$agendamento = $idAgendamento > 0
    ? read($pdo, 'agendamento', 'id_agendamento = ? AND id_cliente = ?', [$idAgendamento, $idCliente])
    : false;

if (!$agendamento) {
    header("Location: pedidos.php");
    exit;
}

$solicitacao = read($pdo, 'solicitacao', 'id_solicitacao = ?', [$agendamento['id_solicitacao']]) ?: [];
$prestador   = read($pdo, 'usuario', 'id_usuario = ?', [$agendamento['id_prestador']]) ?: [];
$iniciaisAvatar = pegarIniciais($_SESSION['nome_usuario'] ?? '');
$hoje = date('Y-m-d');
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    if ($acao === 'reagendar') {
        $novaData = trim($_POST['data_agendamento'] ?? '');
        $novaHora = trim($_POST['hora_agendamento'] ?? '');

        if ($novaData < $hoje) {
            $erro = 'Escolha uma data a partir de hoje.';
        } elseif ($novaData !== '' && $novaHora !== '') {
            $conflitos = readAll(
                $pdo,
                'agendamento',
                "data_agendamento = ? AND hora_agendamento = ? AND id_prestador = ? AND id_agendamento != ? AND status_agendamento != 'CANCELADO'",
                [$novaData, $novaHora, $agendamento['id_prestador'], $idAgendamento]
            );

            if (count($conflitos) > 0) {
                $erro = 'Este horário já está ocupado para este profissional. Escolha outro.';
            } else {
                update(
                    $pdo,
                    'agendamento',
                    [
                        'data_agendamento'   => $novaData,
                        'hora_agendamento'   => $novaHora,
                        'status_agendamento' => 'CONFIRMADO'
                    ],
                    'id_agendamento = ? AND id_cliente = ?',
                    [$idAgendamento, $idCliente]
                );
                header("Location: acompanhar_pedido.php?id=" . $idAgendamento);
                exit;
            }
        }
    } elseif ($acao === 'cancelar') {
        update(
            $pdo,
            'agendamento',
            ['status_agendamento' => 'CANCELADO'],
            'id_agendamento = ? AND id_cliente = ?',
            [$idAgendamento, $idCliente]
        );
        update(
            $pdo,
            'solicitacao',
            ['status_solicitacao' => 'CANCELADA'],
            'id_solicitacao = ?',
            [$agendamento['id_solicitacao']]
        );
        header("Location: pedidos.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reagendar ou Cancelar Pedido - HomeServices</title>
    <link rel="stylesheet" href="css/acompanhar_pedido.css">
    <link rel="stylesheet" href="css/global.css">
</head>

<body>

    <header class="cabecalho">
        <div class="logo">
            <p>HomeServices</p>
        </div>
        <nav class="links">
            <a href="buscar.php">Buscar Serviços</a>
            <a href="pedidos.php">Meus Pedidos</a>
            <a href="acompanhar_pedido.php">Acompanhar Pedido</a>
            <a href="logout.php">Sair</a>
            <div class="foto"><?= $iniciaisAvatar ?></div>
        </nav>
    </header>

    <main class="conteudo">
        <div class="titulo">
            <h1>Reagendar ou Cancelar Pedido #<?= (int) $agendamento['id_agendamento'] ?></h1>
            <p><?= htmlspecialchars($solicitacao['desc_titulo'] ?? '') ?> com <?= htmlspecialchars($prestador['nome_usuario'] ?? '') ?></p>
        </div>

        <?php if ($erro !== ''): ?>
            <p style="color: #b91c1c; font-weight: bold; margin-bottom: 15px;"><?= htmlspecialchars($erro) ?></p>
        <?php endif; ?>

        <div class="bloco">
            <div class="caixa historico">
                <h3>Escolha uma nova data e horário</h3>
                <form method="POST" action="" class="form-mensagem">
                    <input type="hidden" name="id_agendamento" value="<?= (int) $agendamento['id_agendamento'] ?>">
                    <input type="hidden" name="acao" value="reagendar">

                    <div class="itemhistorico">
                        <label for="data_agendamento"><strong>Nova Data:</strong></label><br>
                        <input type="date" id="data_agendamento" name="data_agendamento" min="<?= $hoje ?>" value="<?= htmlspecialchars(max($hoje, $agendamento['data_agendamento'] ?? $hoje)) ?>" required>
                    </div>

                    <div class="itemhistorico">
                        <label for="hora_agendamento"><strong>Novo Horário:</strong></label><br>
                        <input type="time" id="hora_agendamento" name="hora_agendamento" value="<?= htmlspecialchars(substr($agendamento['hora_agendamento'] ?? '09:00', 0, 5)) ?>" required>
                    </div>

                    <button type="submit" class="botao">Confirmar Reagendamento</button>
                </form>
            </div>

            <div class="caixa acoes">
                <h3>Cancelar este agendamento</h3>
                <p class="tempo">Se não precisar mais do serviço, você pode cancelar o pedido abaixo.</p>

                <form method="POST" action="">
                    <input type="hidden" name="id_agendamento" value="<?= (int) $agendamento['id_agendamento'] ?>">
                    <input type="hidden" name="acao" value="cancelar">
                    <button type="submit" class="botaosecundario" style="width: 100%; cursor: pointer;">Cancelar Pedido</button>
                </form>

                <br>
                <a href="acompanhar_pedido.php?id=<?= (int) $agendamento['id_agendamento'] ?>" class="botaosecundario">Voltar</a>
            </div>
        </div>
    </main>

</body>

</html>
