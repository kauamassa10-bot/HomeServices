<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'PRESTADOR') {
    header("Location: login_profissional.php?erro=acesso_negado");
    exit;
}

$idPrestador = (int) $_SESSION['id_usuario'];
$nomeCompleto = $_SESSION['nome_usuario'] ?? 'Profissional';
$iniciais = pegarIniciais($nomeCompleto);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_agendamento'], $_POST['novo_status'])) {
    $idAgendamento = (int) $_POST['id_agendamento'];
    $novoStatus = strtoupper(trim($_POST['novo_status']));
    $permitidos = ['CONFIRMADO', 'A_CAMINHO', 'EM_EXECUCAO', 'CONCLUIDO', 'CANCELADO'];

    if (in_array($novoStatus, $permitidos, true)) {
        $agAtual = read($pdo, 'agendamento', 'id_agendamento = ? AND id_prestador = ?', [$idAgendamento, $idPrestador]);

        if ($agAtual) {
            update(
                $pdo,
                'agendamento',
                ['status_agendamento' => $novoStatus],
                'id_agendamento = ? AND id_prestador = ?',
                [$idAgendamento, $idPrestador]
            );

            $statusSol = ($novoStatus === 'CANCELADO') ? 'CANCELADA' : $novoStatus;
            update(
                $pdo,
                'solicitacao',
                ['status_solicitacao' => $statusSol],
                'id_solicitacao = ?',
                [$agAtual['id_solicitacao']]
            );
        }
    }
    $abaAtual = $_GET['aba'] ?? 'abertos';
    header("Location: agendamentos_prestador.php?aba=" . urlencode($abaAtual));
    exit;
}

$aba = $_GET['aba'] ?? 'abertos';
$todosAgendamentos = readAll($pdo, 'agendamento', 'id_prestador = ?', [$idPrestador]);

$agendamentos = [];
foreach ($todosAgendamentos as $ag) {
    $st = strtoupper($ag['status_agendamento'] ?? '');
    if ($aba === 'abertos' && in_array($st, ['AGENDADO', 'PENDENTE', 'CONFIRMADO'], true)) {
        $agendamentos[] = $ag;
    } elseif ($aba === 'caminho' && $st === 'A_CAMINHO') {
        $agendamentos[] = $ag;
    } elseif ($aba === 'execucao' && $st === 'EM_EXECUCAO') {
        $agendamentos[] = $ag;
    } elseif ($aba === 'concluidos' && in_array($st, ['CONCLUIDO', 'AVALIADO'], true)) {
        $agendamentos[] = $ag;
    } elseif ($aba === 'cancelados' && $st === 'CANCELADO') {
        $agendamentos[] = $ag;
    }
}

usort($agendamentos, static function (array $a, array $b): int {
    return strcmp(($a['data_agendamento'] ?? '') . ($a['hora_agendamento'] ?? ''), ($b['data_agendamento'] ?? '') . ($b['hora_agendamento'] ?? ''));
});
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Agendamentos - HomeServices</title>
    <link rel="stylesheet" href="css/agendamentos_prestador.css">
    <link rel="stylesheet" href="css/global.css">
</head>

<body>

    <header class="cabecalho">
        <div class="logo">HomeServices</div>
        <nav class="links">
            <a href="dashboard_prestador.php">Visão Geral</a>
            <a href="mural_solicitacoes.php">Solicitações</a>
            <a href="agendamentos_prestador.php" class="active">Meus Agendamentos</a>
            <a href="meus_servicos_lista.php">Meus Serviços</a>
            <a href="perfil.php" class="foto" title="<?= htmlspecialchars($nomeCompleto) ?>"><?= htmlspecialchars($iniciais) ?></a>
        </nav>
    </header>

    <main class="agendamentos-main">
        <h2 class="titulo-pagina">Meus Agendamentos</h2>

        <div class="tabs-container">
            <a href="?aba=abertos" class="tab-btn sem-sublinhado <?= $aba === 'abertos' ? 'active' : '' ?>">Em Aberto</a>
            <a href="?aba=caminho" class="tab-btn sem-sublinhado <?= $aba === 'caminho' ? 'active' : '' ?>">A Caminho</a>
            <a href="?aba=execucao" class="tab-btn sem-sublinhado <?= $aba === 'execucao' ? 'active' : '' ?>">Em Execução</a>
            <a href="?aba=concluidos" class="tab-btn sem-sublinhado <?= $aba === 'concluidos' ? 'active' : '' ?>">Concluídos</a>
            <a href="?aba=cancelados" class="tab-btn sem-sublinhado <?= $aba === 'cancelados' ? 'active' : '' ?>">Cancelados</a>
        </div>

        <div class="agendamentos-grid">
            <?php if (count($agendamentos) > 0): ?>
                <?php foreach ($agendamentos as $ag): ?>
                    <?php
                        $cliente = read($pdo, 'usuario', 'id_usuario = ?', [$ag['id_cliente']]) ?: [];
                        $solicitacao = read($pdo, 'solicitacao', 'id_solicitacao = ?', [$ag['id_solicitacao']]) ?: [];
                        $proposta = read($pdo, 'proposta', 'id_proposta = ?', [$ag['id_proposta']]) ?: [];

                        $nomeCliente = !empty($cliente['nome_usuario']) ? $cliente['nome_usuario'] : 'Cliente #' . (int) $ag['id_cliente'];
                        $telCliente = !empty($cliente['telefone']) ? $cliente['telefone'] : 'Não informado';

                        $st = strtoupper($ag['status_agendamento']);
                        $bgCard = ($st === 'EM_EXECUCAO' || $st === 'A_CAMINHO') ? 'bg-execucao' : 'bg-pendente';
                        $badgeCor = ($st === 'EM_EXECUCAO' || $st === 'A_CAMINHO') ? 'badge-azul' : 'badge-laranja';
                        $dataFmt = date('d/m', strtotime($ag['data_agendamento']));
                        $horaFmt = substr($ag['hora_agendamento'], 0, 5);
                    ?>
                    <div class="agendamento-card <?= $bgCard ?>">
                        <div class="info-pedido">
                            <h4>Pedido #<?= (int) $ag['id_agendamento'] ?> · <?= htmlspecialchars($solicitacao['desc_titulo'] ?? '') ?></h4>
                            <p>Cliente: <?= htmlspecialchars($nomeCliente) ?> · Tel: <?= htmlspecialchars($telCliente) ?></p>
                            <p><?= htmlspecialchars($solicitacao['desc_endereco_atendimento'] ?? '') ?></p>
                            <p>Data/Hora: <?= htmlspecialchars($dataFmt) ?> às <?= htmlspecialchars($horaFmt) ?></p>
                            <p>Valor acordado: R$ <?= number_format((float) ($proposta['valor_proposta'] ?? 0), 2, ',', '.') ?></p>
                            <?php if (!empty($ag['observacoes'])): ?>
                                <p class="recado-cliente">
                                    <strong>Recado do cliente:</strong> <?= nl2br(htmlspecialchars($ag['observacoes'])) ?>
                                </p>
                            <?php endif; ?>
                        </div>
                        <div class="acoes-pedido">
                            <div class="badge-status-top <?= $badgeCor ?>"><?= htmlspecialchars($st) ?></div>
                            <div class="titulo-status">Status do serviço</div>
                            <form method="POST" action="?aba=<?= htmlspecialchars($aba) ?>" class="botoes-status">
                                <input type="hidden" name="id_agendamento" value="<?= (int) $ag['id_agendamento'] ?>">
                                <button type="submit" name="novo_status" value="CONFIRMADO" class="btn-passo <?= $st === 'CONFIRMADO' ? 'laranja-ativo' : 'inativo' ?>">Confirmar</button>
                                <button type="submit" name="novo_status" value="A_CAMINHO" class="btn-passo <?= $st === 'A_CAMINHO' ? 'azul-ativo' : 'inativo' ?>">A Caminho</button>
                                <button type="submit" name="novo_status" value="EM_EXECUCAO" class="btn-passo <?= $st === 'EM_EXECUCAO' ? 'azul-ativo' : 'inativo' ?>">Em Execução</button>
                                <button type="submit" name="novo_status" value="CONCLUIDO" class="btn-passo <?= ($st === 'CONCLUIDO' || $st === 'AVALIADO') ? 'verde-concluido' : 'inativo' ?>">Concluir</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="mensagem-vazia">Nenhum agendamento encontrado nesta aba.</p>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>
