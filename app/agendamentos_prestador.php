<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';

/** @var PDO $pdo */

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'PRESTADOR') {
    header("Location: login_profissional.php?erro=acesso_negado");
    exit;
}

$idPrestador = (int) $_SESSION['id_usuario'];
$nomeCompleto = $_SESSION['nome_usuario'] ?? 'Profissional';
$partesNome = explode(' ', trim($nomeCompleto));
$primeiroNome = $partesNome[0];
$iniciais = strtoupper(substr($primeiroNome, 0, 1) . (isset($partesNome[1]) ? substr($partesNome[1], 0, 1) : ''));

// Atualiza status quando o prestador clica em um dos botões
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_agendamento'], $_POST['novo_status'])) {
    $idAgendamento = (int) $_POST['id_agendamento'];
    $novoStatus = strtoupper(trim($_POST['novo_status']));
    $permitidos = ['CONFIRMADO', 'A_CAMINHO', 'EM_EXECUCAO', 'CONCLUIDO', 'CANCELADO'];

    if (in_array($novoStatus, $permitidos, true)) {
        $stmtUpd = $pdo->prepare("UPDATE agendamento SET status_agendamento = :status WHERE id_agendamento = :id AND id_prestador = :prestador");
        $stmtUpd->execute([
            ':status' => $novoStatus,
            ':id' => $idAgendamento,
            ':prestador' => $idPrestador
        ]);
    }
    $abaAtual = $_GET['aba'] ?? 'abertos';
    header("Location: agendamentos_prestador.php?aba=" . urlencode($abaAtual));
    exit;
}

$aba = $_GET['aba'] ?? 'abertos';

// Filtro SQL por aba
$condicaoStatus = "a.status_agendamento IN ('AGENDADO', 'PENDENTE', 'CONFIRMADO')";
if ($aba === 'caminho') {
    $condicaoStatus = "a.status_agendamento = 'A_CAMINHO'";
} elseif ($aba === 'execucao') {
    $condicaoStatus = "a.status_agendamento = 'EM_EXECUCAO'";
} elseif ($aba === 'concluidos') {
    $condicaoStatus = "a.status_agendamento = 'CONCLUIDO'";
} elseif ($aba === 'cancelados') {
    $condicaoStatus = "a.status_agendamento = 'CANCELADO'";
}

$sql = "
    SELECT a.*,
           u.nome_usuario AS nome_cliente,
           u.telefone AS telefone_cliente,
           s.desc_titulo,
           s.desc_endereco_atendimento,
           p.valor_proposta
    FROM agendamento a
    INNER JOIN usuario u ON u.id_usuario = a.id_cliente
    INNER JOIN solicitacao s ON s.id_solicitacao = a.id_solicitacao
    INNER JOIN proposta p ON p.id_proposta = a.id_proposta
    WHERE a.id_prestador = :id
      AND $condicaoStatus
    ORDER BY a.data_agendamento ASC, a.hora_agendamento ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $idPrestador]);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Agendamentos - HomeServices</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/agendamentos_prestador.css">
</head>

<body>

    <header class="dashboard-header">
        <div class="dashboard-logo">HomeServices</div>
        <nav class="dashboard-nav">
            <a href="dashboard_prestador.php">Visão Geral</a>
            <a href="mural_solicitacoes.php">Solicitações</a>
            <a href="agendamentos_prestador.php" class="active">Meus Agendamentos</a>
            <a href="meus_servicos_lista.php">Meus Serviços</a>
            <a href="perfil.php">Perfil</a>
        </nav>
        <div class="avatar" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <?= htmlspecialchars($iniciais) ?>
        </div>
    </header>

    <main class="agendamentos-main">
        <h2 class="titulo-pagina">Meus Agendamentos</h2>

        <div class="tabs-container">
            <a href="?aba=abertos" class="tab-btn <?= $aba === 'abertos' ? 'active' : '' ?>" style="text-decoration: none;">Em Aberto</a>
            <a href="?aba=caminho" class="tab-btn <?= $aba === 'caminho' ? 'active' : '' ?>" style="text-decoration: none;">A Caminho</a>
            <a href="?aba=execucao" class="tab-btn <?= $aba === 'execucao' ? 'active' : '' ?>" style="text-decoration: none;">Em Execução</a>
            <a href="?aba=concluidos" class="tab-btn <?= $aba === 'concluidos' ? 'active' : '' ?>" style="text-decoration: none;">Concluídos</a>
            <a href="?aba=cancelados" class="tab-btn <?= $aba === 'cancelados' ? 'active' : '' ?>" style="text-decoration: none;">Cancelados</a>
        </div>

        <div class="agendamentos-grid">
            <?php if (count($agendamentos) > 0): ?>
                <?php foreach ($agendamentos as $ag): ?>
                    <?php
                        $st = strtoupper($ag['status_agendamento']);
                        $bgCard = ($st === 'EM_EXECUCAO' || $st === 'A_CAMINHO') ? 'bg-execucao' : 'bg-pendente';
                        $badgeCor = ($st === 'EM_EXECUCAO' || $st === 'A_CAMINHO') ? 'badge-azul' : 'badge-laranja';
                        $dataFmt = date('d/m', strtotime($ag['data_agendamento']));
                        $horaFmt = substr($ag['hora_agendamento'], 0, 5);
                    ?>
                    <div class="agendamento-card <?= $bgCard ?>">
                        <div class="info-pedido">
                         <h4>Pedido #<?= (int) $ag['id_agendamento'] ?> · <?= htmlspecialchars($ag['desc_titulo']) ?></h4>
                         <p>Cliente: <?= htmlspecialchars($ag['nome_cliente']) ?> · Tel: <?= htmlspecialchars($ag['telefone_cliente'] ?? 'Não informado') ?></p>
                         <p><?= htmlspecialchars($ag['desc_endereco_atendimento']) ?></p>
                         <p>Data/Hora: <?= htmlspecialchars($dataFmt) ?> às <?= htmlspecialchars($horaFmt) ?></p>
                         <p>Valor acordado: R$ <?= number_format((float) $ag['valor_proposta'], 2, ',', '.') ?></p>
                    </div>
                        <div class="acoes-pedido">
                            <div class="badge-status-top <?= $badgeCor ?>"><?= htmlspecialchars($st) ?></div>
                            <div class="titulo-status">Status do serviço</div>
                            <form method="POST" action="?aba=<?= htmlspecialchars($aba) ?>" class="botoes-status">
                                <input type="hidden" name="id_agendamento" value="<?= (int) $ag['id_agendamento'] ?>">
                                <button type="submit" name="novo_status" value="CONFIRMADO" class="btn-passo <?= $st === 'CONFIRMADO' ? 'laranja-ativo' : 'inativo' ?>">Confirmar</button>
                                <button type="submit" name="novo_status" value="A_CAMINHO" class="btn-passo <?= $st === 'A_CAMINHO' ? 'azul-ativo' : 'inativo' ?>">A Caminho</button>
                                <button type="submit" name="novo_status" value="EM_EXECUCAO" class="btn-passo <?= $st === 'EM_EXECUCAO' ? 'azul-ativo' : 'inativo' ?>">Em Execução</button>
                                <button type="submit" name="novo_status" value="CONCLUIDO" class="btn-passo <?= $st === 'CONCLUIDO' ? 'verde-concluido' : 'inativo' ?>">Concluir</button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="color: #666; padding: 20px;">Nenhum agendamento encontrado nesta aba.</p>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>
