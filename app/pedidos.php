<?php
require_once __DIR__ . '/init.php';

$nomeCompleto = $dadosUsuario['nome_usuario'] ?? ($_SESSION['nome_usuario'] ?? 'Usuário');
$iniciais = pegarIniciais($nomeCompleto);
$tipo_usuario = strtoupper($_SESSION['tipo_usuario'] ?? '');
$id_usuario = (int) ($_SESSION['id_usuario'] ?? 0);


// Redireciona se não estiver logado
if (!$id_usuario) {
    header("Location: login_cliente.php?erro=acesso_negado");
    exit;
}


$pedidos = [];


try {
    if ($tipo_usuario === 'CLIENTE') {
        $solicitacoes = readAll($pdo, 'solicitacao', 'id_cliente = ?', [$id_usuario]);
        $agendamentos = readAll($pdo, 'agendamento', 'id_cliente = ?', [$id_usuario]);


        // Mapeia o agendamento mais recente por solicitação
        $agendamentoPorSolicitacao = [];
        foreach ($agendamentos as $ag) {
            $idSol = (int) $ag['id_solicitacao'];
            if (!isset($agendamentoPorSolicitacao[$idSol]) || (int) $ag['id_agendamento'] > (int) $agendamentoPorSolicitacao[$idSol]['id_agendamento']) {
                $agendamentoPorSolicitacao[$idSol] = $ag;
            }
        }


        foreach ($solicitacoes as $sol) {
            $ag = $agendamentoPorSolicitacao[(int) $sol['id_solicitacao']] ?? null;
            $sol['id_agendamento'] = $ag['id_agendamento'] ?? null;
            $pedidos[] = $sol;
        }
    } elseif ($tipo_usuario === 'PRESTADOR') {
        $agendamentos = readAll($pdo, 'agendamento', 'id_prestador = ?', [$id_usuario]);
        $todasSolicitacoes = readAll($pdo, 'solicitacao');
        $solicitacaoPorId = array_column($todasSolicitacoes, null, 'id_solicitacao');


        foreach ($agendamentos as $ag) {
            $sol = $solicitacaoPorId[$ag['id_solicitacao']] ?? null;
            if ($sol) {
                $sol['id_agendamento'] = $ag['id_agendamento'];
                $pedidos[] = $sol;
            }
        }
    } else {
        // Admin não tem painel de pedidos próprio, volta para registros
        header("Location: registros.php");
        exit;
    }


    // Ordena do ID de solicitação mais recente para o mais antigo
    usort($pedidos, static function (array $a, array $b): int {
        return (int) $b['id_solicitacao'] <=> (int) $a['id_solicitacao'];
    });
} catch (Throwable $e) {
    echo "Erro ao carregar pedidos: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Meus Pedidos - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/pedidos.css">
    <link rel="stylesheet" href="css/global.css">
</head>

<body class="fundo-claro">


    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <nav class="menu-topo">
                <a href="buscar.php">Buscar Serviços</a>
                <a href="pedidos.php">Meus Pedidos</a>
                <a href="perfil_cliente.php" class="foto"
                    title="<?= htmlspecialchars($nomeCompleto) ?>"><?= htmlspecialchars($iniciais) ?></a>

            </nav>
        </div>
    </header>


    <main class="container-pedidos">
        <h2>Meus Pedidos</h2>
        <p class="subtitulo">Acompanhe os serviços e orçamentos</p>


        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == '1'): ?>
            <div class="alerta-sucesso">
                Orçamento enviado com sucesso! Ele já está listado na tabela abaixo.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 'servico_criado'): ?>
            <div class="alerta-sucesso">
                Novo serviço adicionado ao seu perfil com sucesso!
            </div>
        <?php endif; ?>


        <?php if (empty($pedidos)): ?>
            <div class="vazio-box">
                <p>Nenhum serviço ou pedido encontrado no momento.</p>
                <?php if ($tipo_usuario === 'CLIENTE'): ?>
                    <a href="buscar.php" class="btn-solido btn-novo-pedido">Buscar profissionais agora</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <table class="tabela-pedidos">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Serviço</th>
                        <th>Descrição</th>
                        <th>Endereço / CEP</th>
                        <th>Data Desejada</th>
                        <th>Status</th>
                        <th>Ação</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?= htmlspecialchars($pedido['id_solicitacao']) ?></td>
                            <td><strong><?= htmlspecialchars($pedido['desc_titulo']) ?></strong></td>
                            <td><?= htmlspecialchars($pedido['desc_solicitacao']) ?></td>
                            <td><?= htmlspecialchars($pedido['desc_endereco_atendimento']) ?></td>
                            <td><?= !empty($pedido['data_solicitacao']) ? date('d/m/Y', strtotime($pedido['data_solicitacao'])) : '—' ?>
                            </td>
                            <td><?= htmlspecialchars(ucfirst($pedido['status_solicitacao'])) ?></td>

                            <td>
                                <?php if (!empty($pedido['id_agendamento'])): ?>
                                    <button><a href="acompanhar_pedido.php?id=<?= (int) $pedido['id_agendamento'] ?>"
                                            class="btn-solido">Acompanhar</a></button>
                                <?php else: ?>
                                    <span class="texto-aguardando">Aguardando Profissional</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>


</body>

</html>