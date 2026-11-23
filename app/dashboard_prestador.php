<?php
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
$iniciais = pegarIniciais($nomeCompleto);

// Busca dados pelas funções do CRUD
$agendamentosPrestador = readAll($pdo, 'agendamento', 'id_prestador = ?', [$idPrestador]);
$propostasPrestador = readAll($pdo, 'proposta', 'id_prestador = ?', [$idPrestador]);
$avaliacoesPrestador = readAll($pdo, 'avaliacao', 'id_avaliado = ?', [$idPrestador]);

$propostaPorId = array_column($propostasPrestador, null, 'id_proposta');
$usuarios = readAll($pdo, 'usuario');
$usuarioPorId = array_column($usuarios, null, 'id_usuario');
$solicitacoes = readAll($pdo, 'solicitacao');
$solicitacaoPorId = array_column($solicitacoes, null, 'id_solicitacao');

// 1. Total de serviços concluídos e 3. Faturamento do mês
$totalConcluidos = 0;
$faturamentoMes = 0.0;
$mesAtual = date('Y-m');
$proximosAgendamentos = [];

foreach ($agendamentosPrestador as $ag) {
    $status = strtoupper($ag['status_agendamento'] ?? '');

    if ($status === 'CONCLUIDO') {
        $totalConcluidos++;
        if (!empty($ag['data_agendamento']) && strpos($ag['data_agendamento'], $mesAtual) === 0) {
            $prop = $propostaPorId[$ag['id_proposta']] ?? null;
            if ($prop) {
                $faturamentoMes += (float) $prop['valor_proposta'];
            }
        }
    } elseif ($status !== 'CANCELADO') {
        $proximosAgendamentos[] = $ag;
    }
}

usort($proximosAgendamentos, static function (array $a, array $b): int {
    return strcmp(($a['data_agendamento'] ?? '') . ($a['hora_agendamento'] ?? ''), ($b['data_agendamento'] ?? '') . ($b['hora_agendamento'] ?? ''));
});

// 2. Total de propostas enviadas
$totalPropostas = count($propostasPrestador);

// 4. Nota média
$somaNotas = 0;
foreach ($avaliacoesPrestador as $av) {
    $somaNotas += (int) $av['numero_nota'];
}
$notaMedia = count($avaliacoesPrestador) > 0 ? ($somaNotas / count($avaliacoesPrestador)) : 0;
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

    <header class="dashboard-header">
        <div class="dashboard-logo">HomeServices</div>
        <nav class="dashboard-nav">
            <a href="dashboard_prestador.php" class="active">Visão Geral</a>
            <a href="mural_solicitacoes.php">Solicitações</a>
            <a href="agendamentos_prestador.php">Meus Agendamentos</a>
            <a href="meus_servicos_lista.php">Meus Serviços</a>
        </nav>
        <div class="avatar" title="<?= htmlspecialchars($nomeCompleto) ?>">
            <a href="perfil.php"><?= htmlspecialchars($iniciais) ?></a>
        </div>
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
                            $cliente = $usuarioPorId[$ag['id_cliente']] ?? [];
                            $solicitacao = $solicitacaoPorId[$ag['id_solicitacao']] ?? [];
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
                                <span>Cliente: <?= htmlspecialchars($cliente['nome_usuario'] ?? 'Cliente') ?></span>
                                <span><?= htmlspecialchars($solicitacao['desc_endereco_atendimento'] ?? '') ?></span>
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
