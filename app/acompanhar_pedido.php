<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'CLIENTE') {
    header("Location: login_cliente.php?erro=acesso_negado");
    exit;
}

$idCliente = (int) $_SESSION['id_usuario'];
$id_agendamento = (int) ($_GET['id'] ?? 0);

if ($id_agendamento > 0) {
    $agendamento = read($pdo, 'agendamento', 'id_agendamento = ? AND id_cliente = ?', [$id_agendamento, $idCliente]);
} else {
    $meusAgendamentos = readAll($pdo, 'agendamento', 'id_cliente = ?', [$idCliente]);
    usort($meusAgendamentos, static fn($a, $b) => (int) $b['id_agendamento'] <=> (int) $a['id_agendamento']);
    $agendamento = $meusAgendamentos ?: false;
}

if (!$agendamento) {
    header("Location: pedidos.php");
    exit;
}

if (strtoupper($agendamento['status_agendamento'] ?? '') === 'AVALIADO') {
    header("Location: pedidos.php");
    exit;
}

$solicitacao = read($pdo, 'solicitacao', 'id_solicitacao = ?', [$agendamento['id_solicitacao']]) ?: [];
$prestador   = read($pdo, 'usuario', 'id_usuario = ?', [$agendamento['id_prestador']]) ?: [];
$cliente     = read($pdo, 'usuario', 'id_usuario = ?', [$agendamento['id_cliente']]) ?: [];
$proposta    = read($pdo, 'proposta', 'id_proposta = ?', [$agendamento['id_proposta']]) ?: [];

$iniciaisAvatar = pegarIniciais($cliente['nome_usuario'] ?? ($_SESSION['nome_usuario'] ?? 'Cliente'));
$dataFormatada = date('d/m/Y', strtotime($agendamento['data_agendamento']));
$horaFormatada = substr($agendamento['hora_agendamento'] ?? '00:00', 0, 5);
$dataHora = $dataFormatada . ' às ' . $horaFormatada;

$statusMap = [
    'SOLICITADO'  => 1,
    'CONFIRMADO'  => 2,
    'AGENDADO'    => 2,
    'A_CAMINHO'   => 3,
    'EM_EXECUCAO' => 4,
    'CONCLUIDO'   => 5,
];
$passoAtual = $statusMap[strtoupper($agendamento['status_agendamento'] ?? '')] ?? 1;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acompanhar Pedido - HomeServices</title>
    <link rel="stylesheet" href="css/acompanhar_pedido.css">
    <link rel="stylesheet" href="css/global.css">
</head>

<body>

    <header class="cabecalho">
        <div class="logo">HomeServices</div>
        <nav class="links">
            <a href="buscar.php">Buscar Serviços</a>
            <a href="pedidos.php">Meus Pedidos</a>
            <a href="perfil_cliente.php" class="foto" title="<?= htmlspecialchars($cliente['nome_usuario'] ?? 'Perfil') ?>"><?= htmlspecialchars($iniciaisAvatar) ?></a>
        </nav>
    </header>

    <main class="conteudo">
        <div class="titulo">
            <h1>Pedido #<?= (int) $agendamento['id_agendamento'] ?></h1>
            <p><?= htmlspecialchars($solicitacao['desc_titulo'] ?? '') ?> com <?= htmlspecialchars($prestador['nome_usuario'] ?? '') ?> · <?= $dataHora ?></p>
        </div>

        <div class="passos">
            <div class="passo <?= $passoAtual >= 1 ? 'ativo' : '' ?>">
                <div class="bolinha">1</div>
                <span>Solicitado</span>
            </div>
            <div class="passo <?= $passoAtual >= 2 ? 'ativo' : '' ?>">
                <div class="bolinha">2</div>
                <span>Confirmado</span>
            </div>
            <div class="passo <?= $passoAtual >= 3 ? 'ativo' : '' ?>">
                <div class="bolinha">3</div>
                <span>A caminho</span>
            </div>
            <div class="passo <?= $passoAtual >= 4 ? 'ativo' : '' ?>">
                <div class="bolinha">4</div>
                <span>Em execução</span>
            </div>
            <div class="passo <?= $passoAtual >= 5 ? 'ativo' : '' ?>">
                <div class="bolinha">5</div>
                <span>Concluído</span>
            </div>
        </div>

        <div class="bloco">
            <div class="caixa historico">
                <h3>Detalhes do Pedido</h3>
                <div class="itemhistorico"><strong>Serviço:</strong> <?= htmlspecialchars($solicitacao['desc_titulo'] ?? '') ?></div>
                <div class="itemhistorico"><strong>Descrição:</strong> <?= htmlspecialchars($solicitacao['desc_solicitacao'] ?? '') ?></div>
                <div class="itemhistorico"><strong>Endereço:</strong> <?= htmlspecialchars($solicitacao['desc_endereco_atendimento'] ?? '') ?></div>
                <div class="itemhistorico"><strong>Valor Aceito:</strong> R$ <?= number_format((float) ($proposta['valor_proposta'] ?? 0), 2, ',', '.') ?></div>
                <div class="itemhistorico"><strong>Prestador:</strong> <?= htmlspecialchars($prestador['nome_usuario'] ?? '') ?> (<?= htmlspecialchars($prestador['telefone'] ?? 'Não informado') ?>)</div>
                <?php if (!empty($agendamento['observacoes'])): ?>
                    <div class="itemhistorico">
                        <strong>Mensagens / Observações:</strong><br>
                        <?= nl2br(htmlspecialchars($agendamento['observacoes'])) ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="caixa acoes">
                <h3>Status do Serviço</h3>
                <p class="tempo">Situação atual: <strong><?= htmlspecialchars($agendamento['status_agendamento']) ?></strong></p>

                <?php if ($passoAtual < 5): ?>
                    <div class="botao caixa-details">
                        <details>
                            <summary>Enviar mensagem</summary>
                            <form action="processar_mensagem.php" method="POST" class="form-mensagem">
                                <input type="hidden" name="id_agendamento" value="<?= (int) $agendamento['id_agendamento'] ?>">
                                <input type="hidden" name="id_prestador" value="<?= (int) $agendamento['id_prestador'] ?>">
                                
                                <textarea name="mensagem" required placeholder="Escreva a sua mensagem..."></textarea>
                                <button type="submit" class="botao">Enviar</button>
                            </form>
                        </details>
                    </div>

                    <a href="cancelar_pedido.php?id=<?= (int) $agendamento['id_agendamento'] ?>" class="botaosecundario">Reagendar ou cancelar</a>
                
                <?php else: ?>
                    <div class="caixa-avaliacao">
                        <h4>Avalie o serviço</h4>
                        <form action="processar_avaliacao.php" method="POST" class="form-avaliacao">
                            <input type="hidden" name="id_agendamento" value="<?= (int) $agendamento['id_agendamento'] ?>">
                            <input type="hidden" name="id_prestador" value="<?= (int) $agendamento['id_prestador'] ?>">
                            
                            <select name="nota" required class="input-avaliacao">
                                <option value="" disabled selected>Escolha uma nota...</option>
                                <option value="5"> (5/5) ⭐⭐⭐⭐⭐</option>
                                <option value="4"> (4/5) ⭐⭐⭐⭐ </option>
                                <option value="3"> (3/5) ⭐⭐⭐</option>
                                <option value="2"> (2/5) ⭐⭐</option>
                                <option value="1"> (1/5) ⭐</option>
                            </select>
                            
                            <textarea name="comentario" placeholder="Deixe um comentário (opcional)..." class="input-avaliacao"></textarea>
                            <button type="submit" class="botao">Enviar Avaliação</button>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

</body>

</html>
