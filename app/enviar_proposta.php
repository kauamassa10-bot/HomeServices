<?php
require_once __DIR__ . '/init.php';

if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'PRESTADOR') {
    header('Location: login_profissional.php?erro=acesso_negado');
    exit;
}

$idPrestador = (int) $_SESSION['id_usuario'];
$nomeCompleto = $_SESSION['nome_usuario'] ?? '';
$iniciais = pegarIniciais($nomeCompleto);

$idSolicitacao = (int) ($_POST['id_solicitacao'] ?? $_GET['id_solicitacao'] ?? 0);
$solicitacao = $idSolicitacao > 0
    ? read($pdo, 'solicitacao', 'id_solicitacao = ? AND id_prestador = ?', [$idSolicitacao, $idPrestador])
    : false;

$mensagem = '';
$valorInformado = $_POST['valor_proposta'] ?? '';
$descricaoInformada = $_POST['descricao_proposta'] ?? '';
$podeEnviar = $solicitacao && strtoupper((string) $solicitacao['status_solicitacao']) === 'ABERTA';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$solicitacao) {
        $mensagem = 'Solicitação não encontrada para este profissional.';
    } elseif (!$podeEnviar) {
        $mensagem = 'Esta solicitação não está mais aberta para proposta.';
    } else {
        $valorBruto = trim(str_replace(['R$', ' '], '', $valorInformado));
        if (str_contains($valorBruto, ',')) {
            $valorBruto = str_replace('.', '', $valorBruto);
            $valorBruto = str_replace(',', '.', $valorBruto);
        }
        $valor = (float) $valorBruto;
        $descricao = trim($descricaoInformada);

        if ($valor <= 0 || $descricao === '') {
            $mensagem = 'Informe um valor maior que zero e a descrição da proposta.';
        } else {
            try {
                $jaExiste = read($pdo, 'proposta', 'id_solicitacao = ? AND id_prestador = ?', [$idSolicitacao, $idPrestador]);

                $dadosProposta = [
                    'id_solicitacao' => $idSolicitacao,
                    'id_prestador'   => $idPrestador,
                    'valor_proposta' => number_format($valor, 2, '.', ''),
                    'desc_proposta'  => $descricao,
                    'status_proposta' => 'ENVIADA',
                    'data_proposta'  => date('Y-m-d'),
                ];

                if ($jaExiste) {
                    update(
                        $pdo,
                        'proposta',
                        $dadosProposta,
                        'id_proposta = ?',
                        [$jaExiste['id_proposta']]
                    );
                } else {
                    create($pdo, 'proposta', $dadosProposta);
                }

                update(
                    $pdo,
                    'solicitacao',
                    [
                        'status_solicitacao' => 'EM_ORCAMENTO',
                        'id_prestador'       => $idPrestador,
                    ],
                    'id_solicitacao = ?',
                    [$idSolicitacao]
                );


                header('Location: mural_solicitacoes.php');
                exit;
            } catch (Throwable $e) {
                $mensagem = 'Não foi possível enviar a proposta. Tente novamente.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Proposta - HomeServices</title>
    <link rel="stylesheet" href="css/dashboard.css">
    <link rel="stylesheet" href="css/proposta.css">
</head>

<body>

    <header class="dashboard-header">
        <div class="dashboard-logo">HomeServices</div>
        <nav class="dashboard-nav">
            <a href="dashboard_prestador.php">Visão Geral</a>
            <a href="mural_solicitacoes.php" class="active">Solicitações</a>
            <a href="agendamentos_prestador.php">Meus Agendamentos</a>
            <a href="meus_servicos_lista.php">Meus Serviços</a>
        </nav>
        <div class="avatar" title="<?= htmlspecialchars($nomeCompleto, ENT_QUOTES, 'UTF-8') ?>">
            <a href="perfil.php"><?= htmlspecialchars($iniciais, ENT_QUOTES, 'UTF-8') ?></a>
        </div>
    </header>

    <main class="proposta-main">
        <div class="card-form-proposta">
            <h2>Enviar Proposta</h2>

            <?php if (!$solicitacao): ?>
                <div class="resumo-solicitacao">
                    <strong>Solicitação não encontrada</strong>
                    <p>Abra uma solicitação aberta pelo mural.</p>
                </div>
                <div class="botoes-acao">
                    <a href="mural_solicitacoes.php" class="btn-cancelar">Voltar ao mural</a>
                </div>
            <?php else: ?>
                <div class="resumo-solicitacao">
                    <strong><?= htmlspecialchars($solicitacao['desc_titulo'], ENT_QUOTES, 'UTF-8') ?></strong>
                    <p><?= htmlspecialchars($solicitacao['desc_solicitacao'], ENT_QUOTES, 'UTF-8') ?></p>
                    <p>Endereço: <?= htmlspecialchars($solicitacao['desc_endereco_atendimento'], ENT_QUOTES, 'UTF-8') ?></p>
                </div>

                <?php if ($mensagem !== ''): ?>
                    <div class="resumo-solicitacao">
                        <p><?= htmlspecialchars($mensagem, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($podeEnviar): ?>
                    <form action="" method="POST">
                        <input type="hidden" name="id_solicitacao" value="<?= (int) $solicitacao['id_solicitacao'] ?>">

                        <div class="form-group">
                            <label for="valor_proposta">Valor da Proposta (R$)</label>
                            <input type="text" id="valor_proposta" name="valor_proposta" class="form-control" placeholder="R$ 0,00" value="<?= htmlspecialchars($valorInformado, ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="descricao_proposta">Descrição da Proposta / O que está incluso</label>
                            <textarea id="descricao_proposta" name="descricao_proposta" class="form-control" placeholder="Ex: materiais, garantia, tempo estimado" required><?= htmlspecialchars($descricaoInformada, ENT_QUOTES, 'UTF-8') ?></textarea>
                        </div>

                        <div class="botoes-acao">
                            <a href="mural_solicitacoes.php" class="btn-cancelar">Cancelar</a>
                            <button type="submit" class="btn-enviar-proposta">Enviar Proposta ao Cliente</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="botoes-acao">
                        <a href="mural_solicitacoes.php" class="btn-cancelar">Voltar ao mural</a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>

</body>

</html>