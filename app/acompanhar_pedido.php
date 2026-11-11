<?php
require_once __DIR__ . '/init.php';

$id_agendamento = $_GET['id'] ?? null;

if ($id_agendamento) {
    $agendamento = read($pdo, 'agendamento', 'id_agendamento = ?', [$id_agendamento]);
} else {
    $agendamento = read($pdo, 'agendamento', '1=1 ORDER BY id_agendamento DESC LIMIT 1');
}

if (!$agendamento) {
    echo "Nenhum agendamento encontrado no banco de dados.";
    exit;
}

$solicitacao = read($pdo, 'solicitacao', 'id_solicitacao = ?', [$agendamento['id_solicitacao']]);
$prestador   = read($pdo, 'usuario', 'id_usuario = ?', [$agendamento['id_prestador']]);
$cliente     = read($pdo, 'usuario', 'id_usuario = ?', [$agendamento['id_cliente']]);
$proposta    = read($pdo, 'proposta', 'id_proposta = ?', [$agendamento['id_proposta']]);

$iniciaisAvatar = pegarIniciais($cliente['nome_usuario']);
$dataHora = date('d/m/Y \à\s H:i', strtotime($agendamento['data_agendamento']));

$statusMap = [
    'SOLICITADO'  => 1,
    'CONFIRMADO'  => 2,
    'AGENDADO'    => 2,
    'A_CAMINHO'   => 3,
    'EM_EXECUCAO' => 4,
    'CONCLUIDO'   => 5
];
$passoAtual = $statusMap[$agendamento['status_agendamento']] ?? 1;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acompanhar Pedido</title>
    <link rel="stylesheet" href="css/styleacompanhar-pedido.css">
</head>

<body>

    <header class="cabecalho">
        <div class="logo">HomeServices</div>
        <nav class="links">
            <a href="#">Categorias</a>
            <a href="#">Meus pedidos</a>
            <a href="#">Ajuda</a>
            <div class="foto"><?= $iniciaisAvatar ?></div>
        </nav>
    </header>

    <main class="conteudo">
        <div class="titulo">
            <h1>Pedido #<?= $agendamento['id_agendamento'] ?></h1>
            <p><?= htmlspecialchars($solicitacao['desc_titulo']) ?> com <?= htmlspecialchars($prestador['nome_usuario']) ?> · <?= $dataHora ?></p>
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
                <div class="itemhistorico"><strong>Serviço:</strong> <?= htmlspecialchars($solicitacao['desc_titulo']) ?></div>
                <div class="itemhistorico"><strong>Descrição:</strong> <?= htmlspecialchars($solicitacao['desc_solicitacao']) ?></div>
                <div class="itemhistorico"><strong>Endereço:</strong> <?= htmlspecialchars($solicitacao['desc_endereco_atendimento']) ?></div>
                <div class="itemhistorico"><strong>Valor Aceito:</strong> R$ <?= number_format((float)$proposta['valor_proposta'], 2, ',', '.') ?></div>
                <div class="itemhistorico"><strong>Prestador:</strong> <?= htmlspecialchars($prestador['nome_usuario']) ?> (<?= htmlspecialchars($prestador['telefone']) ?>)</div>
            </div>

            <div class="caixa acoes">
                <h3>Status do Serviço</h3>
                <p class="tempo">Situação atual: <strong><?= htmlspecialchars($agendamento['status_agendamento']) ?></strong></p>
                <button class="botao">Enviar mensagem</button>
                <button class="botaosecundario">Reagendar ou cancelar</button>
            </div>
        </div>
    </main>

</body>

</html>