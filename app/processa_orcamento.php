<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'CLIENTE') {
    header('Location: login_cliente.php?erro=acesso_negado');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: buscar.php');
    exit;
}

$idCliente = (int) $_SESSION['id_usuario'];
$idServico = (int) ($_POST['id_servico'] ?? 0);

// Dados do serviço sempre vêm do banco
$servico = $idServico > 0 ? read($pdo, 'servico', 'id_servico = ? AND status_ativo = 1', [$idServico]) : false;

if (!$servico) {
    header('Location: buscar.php');
    exit;
}

$descricao = trim((string) ($_POST['desc_solicitacao'] ?? ''));
$endereco  = trim((string) ($_POST['desc_endereco_atendimento'] ?? ''));

if ($descricao === '' || $endereco === '') {
    $_SESSION['orcamento_erro'] = 'Preencha a descrição e o endereço.';
    $_SESSION['orcamento_old'] = [
        'desc_solicitacao'          => $descricao,
        'desc_endereco_atendimento' => $endereco,
    ];
    header('Location: orcamento.php?id_servico=' . $idServico);
    exit;
}

try {
    create($pdo, 'solicitacao', [
        'id_cliente'                => $idCliente,
        'id_prestador'              => $servico['id_prestador'],
        'id_categoria'              => $servico['id_categoria'],
        'desc_titulo'               => $servico['nome_servico'],
        'desc_solicitacao'          => $descricao,
        'desc_endereco_atendimento' => $endereco,
        'status_solicitacao'        => 'ABERTA',
    ]);
} catch (Throwable $e) {
    error_log('Erro ao criar solicitação: ' . $e->getMessage());
    $_SESSION['orcamento_erro'] = 'Não foi possível enviar a solicitação. Tente novamente.';
    $_SESSION['orcamento_old'] = [
        'desc_solicitacao'          => $descricao,
        'desc_endereco_atendimento' => $endereco,
    ];
    header('Location: orcamento.php?id_servico=' . $idServico);
    exit;
}

header('Location: buscar.php');
exit;
