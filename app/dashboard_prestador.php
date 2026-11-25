<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'PRESTADOR') {
    header("Location: login_profissional.php?erro=acesso_negado");
    exit;
}

$idPrestador = (int) $_SESSION['id_usuario'];

// ---------- Nome e iniciais ----------
$usuarioPrestador = read($pdo, 'usuario', 'id_usuario = ?', [$idPrestador]);

$nomeCompleto = 'Profissional';
if ($usuarioPrestador) {
    $nomeCompleto = $usuarioPrestador['nome_usuario'];
}

$partesDoNome = explode(' ', $nomeCompleto);
$primeiroNome = $partesDoNome[0];
$iniciais     = pegarIniciais($nomeCompleto);

// ---------- Busca dos dados ----------
$listaAgendamentos = readAll($pdo, 'agendamento', 'id_prestador = ?', [$idPrestador]);
$listaPropostas    = readAll($pdo, 'proposta', 'id_prestador = ?', [$idPrestador]);
$listaAvaliacoes   = readAll($pdo, 'avaliacao', 'id_avaliado = ?', [$idPrestador]);

// ---------- Total de propostas ----------
$totalPropostas = count($listaPropostas);

// ---------- Nota média ----------
$somaDasNotas = 0;
foreach ($listaAvaliacoes as $avaliacao) {
    $somaDasNotas = $somaDasNotas + $avaliacao['numero_nota'];
}

$notaMedia = 0;
if (count($listaAvaliacoes) > 0) {
    $notaMedia = $somaDasNotas / count($listaAvaliacoes);
}

// ---------- Concluídos e faturamento do mês ----------
$totalConcluidos = 0;
$faturamentoMes  = 0;
$mesAtual        = date('Y-m');

foreach ($listaAgendamentos as $agendamento) {
    if ($agendamento['status_agendamento'] !== 'CONCLUIDO') {
        continue;
    }

    $totalConcluidos = $totalConcluidos + 1;

    $mesDoAgendamento = substr($agendamento['data_agendamento'], 0, 7);
    if ($mesDoAgendamento === $mesAtual) {
        $propostaDoAgendamento = read($pdo, 'proposta', 'id_proposta = ?', [$agendamento['id_proposta']]);

        if ($propostaDoAgendamento) {
            $faturamentoMes = $faturamentoMes + $propostaDoAgendamento['valor_proposta'];
        }
    }
}

// ---------- Próximos agendamentos ----------
$proximosAgendamentos = [];

foreach ($listaAgendamentos as $agendamento) {
    $status = $agendamento['status_agendamento'];

    if ($status === 'CONCLUIDO' || $status === 'CANCELADO') {
        continue;
    }

    $cliente     = read($pdo, 'usuario', 'id_usuario = ?', [$agendamento['id_cliente']]);
    $solicitacao = read($pdo, 'solicitacao', 'id_solicitacao = ?', [$agendamento['id_solicitacao']]);

    $agendamento['nome_cliente'] = '';
    if ($cliente) {
        $agendamento['nome_cliente'] = $cliente['nome_usuario'];
    }

    $agendamento['desc_endereco_atendimento'] = '';
    if ($solicitacao) {
        $agendamento['desc_endereco_atendimento'] = $solicitacao['desc_endereco_atendimento'];
    }

    $proximosAgendamentos[] = $agendamento;
}

// Ordena por data e depois por hora
usort($proximosAgendamentos, function ($a, $b) {
    return strcmp($a['data_agendamento'] . $a['hora_agendamento'], $b['data_agendamento'] . $b['hora_agendamento']);
});
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard do Prestador - HomeServices</title>
    <link rel="stylesheet" href="css/global.css"> 
    <link rel="stylesheet" href="css/dashboard.css"> 
</head>
<body>

    <header class="cabecalho">
        <div class="logo">HomeServices</div>
        <nav class="links">
            <a href="dashboard_prestador.php" class="active">Visão Geral</a>
            <a href="mural_solicitacoes.php">Solicitações</a>
            <a href="agendamentos_prestador.php">Meus Agendamentos</a>
            <a href="meus_servicos_lista.php">Meus Serviços</a>
            <div class="foto" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <a href="perfil.php"><?= htmlspecialchars($iniciais) ?></a>
        </div>
        </nav>
        
    </header>

    <main class="dashboard-main">
        <h2 class="saudacao">Olá, <?= htmlspecialchars($primeiroNome) ?>. Visão geral do mês</h2>

        <div class="cards-grid">
            <div class="card-resumo card-verde">
                <span class="titulo-card">Serviços Concluídos</span>
                <span class="valor-card"><?= $totalConcluidos ?></span>
            </div>
            <div class="card-resumo card-azul">
                <span class="titulo-card">Propostas Enviadas</span>
                <span class="valor-card"><?= $totalPropostas ?></span>
            </div>
            <div class="card-resumo card-laranja">
                <span class="titulo-card">Faturamento do Mês</span>
                <span class="valor-card">R$ <?= number_format($faturamentoMes, 2, ',', '.') ?></span>
            </div>
            <div class="card-resumo card-amarelo">
                <span class="titulo-card">Nota Média</span>
                <span class="valor-card"><?= number_format($notaMedia, 1, ',', '.') ?> de 5 (estrelas)</span>
            </div>
        </div>

        <div class="agendamentos-container">
            <h3>Próximos Agendamentos</h3>

            <div class="lista-agendamentos">
                <?php if (count($proximosAgendamentos) > 0): ?>
                    <?php foreach ($proximosAgendamentos as $ag): ?>
                        <?php
                            $dataFormatada = date('d/m', strtotime($ag['data_agendamento']));
                            $horaFormatada = substr($ag['hora_agendamento'], 0, 5);
                            $status = strtoupper($ag['status_agendamento']);
                            $classeBadge = 'badge-pendente';
                            $textoBadge = 'Pendente';

                            if ($status === 'CONFIRMADO' || $status === 'AGENDADO') {
                                $classeBadge = 'badge-confirmado';
                                $textoBadge = 'Confirmado';
                            } elseif ($status === 'EM_EXECUCAO' || $status === 'A_CAMINHO') {
                                $classeBadge = 'badge-execucao';
                                $textoBadge = ($status === 'A_CAMINHO') ? 'A Caminho' : 'Em Execução';
                            }
                        ?>
                        <div class="agendamento-item">
                            <div class="agendamento-info">
                                <strong><?= htmlspecialchars($dataFormatada) ?> · <?= htmlspecialchars($horaFormatada) ?></strong>
                                <span>Cliente: <?= htmlspecialchars($ag['nome_cliente'] ?? 'Cliente') ?></span>
                                <span><?= htmlspecialchars($ag['desc_endereco_atendimento'] ?? '') ?></span>
                            </div>
                            <div class="agendamento-acoes">
                                <span class="badge <?= $classeBadge ?>"><?= $textoBadge ?></span>
                                <a href="agendamentos_prestador.php" class="btn-acao" style="text-decoration: none;">Ver Detalhes / Atualizar Status</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #666; padding: 15px 0;">Nenhum agendamento próximo no momento.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>
</html>
