<?php
require_once __DIR__ . '/auth_admin.php';
require_once __DIR__ . '/init.php';

$iniciaisUsuario = pegarIniciais($_SESSION['nome_usuario'] ?? 'Admin');

$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    try {
        if ($acao === 'nova_categoria') {
            $nome_cat = trim($_POST['nome_categoria']);
            $desc_cat = trim($_POST['desc_categoria']);
            create($pdo, 'categoria', [
                'nome_categoria' => $nome_cat,
                'desc_categoria' => $desc_cat,
                'status_ativo'   => 1,
            ]);
            $mensagem = "Nova categoria '{$nome_cat}' criada com sucesso!";
        }

        if ($acao === 'alternar_status_categoria') {
            $id_cat = (int) $_POST['id_categoria'];
            $novo_status = (int) $_POST['novo_status'];
            update($pdo, 'categoria', ['status_ativo' => $novo_status], 'id_categoria = ?', [$id_cat]);
            $mensagem = "Status da categoria #{$id_cat} atualizado!";
        }

        if ($acao === 'excluir_categoria') {
            $id_cat = (int) $_POST['id_categoria'];
            delete($pdo, 'categoria', 'id_categoria = ?', [$id_cat]);
            $mensagem = "Categoria #{$id_cat} excluída com sucesso.";
        }

        if ($acao === 'alternar_status') {
            $id_alvo = (int) $_POST['id_usuario'];
            $novo_status = (int) $_POST['novo_status'];
            update($pdo, 'usuario', ['status_ativo' => $novo_status], "id_usuario = ? AND tipo_usuario != 'ADMIN'", [$id_alvo]);
            $mensagem = "Status do usuário #{$id_alvo} atualizado!";
        }

        if ($acao === 'excluir_usuario') {
            $id_alvo = (int) $_POST['id_usuario'];
            $alvo = read($pdo, 'usuario', 'id_usuario = ?', [$id_alvo]);

            if ($alvo && strtoupper((string) $alvo['tipo_usuario']) !== 'ADMIN') {
                delete($pdo, 'avaliacao', 'id_avaliador = ? OR id_avaliado = ?', [$id_alvo, $id_alvo]);
                delete($pdo, 'agendamento', 'id_cliente = ? OR id_prestador = ?', [$id_alvo, $id_alvo]);
                delete($pdo, 'proposta', 'id_prestador = ?', [$id_alvo]);

                $solicitacoesAlvo = readAll($pdo, 'solicitacao', 'id_cliente = ? OR id_prestador = ?', [$id_alvo, $id_alvo]);
                foreach ($solicitacoesAlvo as $sol) {
                    delete($pdo, 'proposta', 'id_solicitacao = ?', [$sol['id_solicitacao']]);
                }
                delete($pdo, 'solicitacao', 'id_cliente = ? OR id_prestador = ?', [$id_alvo, $id_alvo]);

                delete($pdo, 'servico', 'id_prestador = ?', [$id_alvo]);
                delete($pdo, 'usuario', "id_usuario = ? AND tipo_usuario != 'ADMIN'", [$id_alvo]);

                $mensagem = "Usuário #{$id_alvo} e seus registros vinculados foram removidos do sistema.";
            }
        }

        if ($acao === 'excluir_servico') {
            $id_serv = (int) $_POST['id_servico'];
            delete($pdo, 'servico', 'id_servico = ?', [$id_serv]);
            $mensagem = "Serviço #{$id_serv} excluído com sucesso.";
        }
    } catch (Throwable $e) {
        $erro = "Não foi possível concluir a ação (verifique se o registro possui vínculos ativos): " . $e->getMessage();
    }
}

$clientes = readAll($pdo, 'usuario', "tipo_usuario = 'CLIENTE'");
$profissionais = readAll($pdo, 'usuario', "tipo_usuario = 'PRESTADOR'");
$categorias = readAll($pdo, 'categoria');
$servicosBrutos = readAll($pdo, 'servico');

$usuarioPorId = array_column(readAll($pdo, 'usuario'), null, 'id_usuario');
$categoriaPorId = array_column($categorias, null, 'id_categoria');

$servicos = [];
foreach ($servicosBrutos as $s) {
    $s['nome_prestador'] = $usuarioPorId[$s['id_prestador']]['nome_usuario'] ?? 'Desconhecido';
    $s['nome_categoria'] = $categoriaPorId[$s['id_categoria']]['nome_categoria'] ?? 'Sem categoria';
    $servicos[] = $s;
}

usort($clientes, static fn($a, $b) => (int) $b['id_usuario'] <=> (int) $a['id_usuario']);
usort($profissionais, static fn($a, $b) => (int) $b['id_usuario'] <=> (int) $a['id_usuario']);
usort($categorias, static fn($a, $b) => (int) $b['id_categoria'] <=> (int) $a['id_categoria']);
usort($servicos, static fn($a, $b) => (int) $b['id_servico'] <=> (int) $a['id_servico']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Painel Administrativo - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/cadastros.css">
    <link rel="stylesheet" href="css/registros.css">
    <link rel="stylesheet" href="css/global.css">
</head>
<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto"><a href="index.php">HomeServices</a></h1>
            <div class="foto">
                <?= htmlspecialchars($iniciaisUsuario) ?>
            </div>
        </div>
    </header>

    <main class="container-geral">
        <div class="barra-admin">
            <span>Logado como Administrador: <strong><?= htmlspecialchars($_SESSION['nome_usuario']) ?></strong></span>
            <a href="logout.php" class="btn-sair">Sair da Conta</a>
        </div>

        <h2>Painel de Controle Administrativo</h2>
        <p class="subtitulo">Gerencie categorias, usuários e serviços cadastrados na plataforma</p>

        <?php if (!empty($mensagem) || isset($_GET['sucesso'])): ?>
            <div class="alerta-sucesso">
                <?= htmlspecialchars($mensagem ?: 'Operação realizada com sucesso!') ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($erro)): ?>
            <div class="erro"><?= htmlspecialchars($erro) ?></div>
        <?php endif; ?>

        <section class="secao-tabela">
            <h3>Nova Categoria de Serviço (Exclusivo Admin)</h3>
            <form method="POST" action="" class="form-inline-admin">
                <input type="hidden" name="acao" value="nova_categoria">
                <input type="text" name="nome_categoria" placeholder="Nome da categoria (ex: Jardinagem)" required>
                <input type="text" name="desc_categoria" placeholder="Descrição breve da categoria" required>
                <button type="submit" class="btn-solido btn-add-categoria">Adicionar Categoria</button>
            </form>
        </section>

        <section class="secao-tabela">
            <h3>Categorias Cadastradas (<?= count($categorias) ?>)</h3>
            <table class="tabela-dados">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Categoria</th>
                        <th>Descrição</th>
                        <th>Status</th>
                        <th>Ações Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($categorias)): ?>
                        <tr><td colspan="5" class="vazio-linha">Nenhuma categoria cadastrada.</td></tr>
                    <?php else: ?>
                        <?php foreach ($categorias as $cat): ?>
                            <tr>
                                <td><?= $cat['id_categoria'] ?></td>
                                <td><strong><?= htmlspecialchars($cat['nome_categoria']) ?></strong></td>
                                <td><?= htmlspecialchars($cat['desc_categoria'] ?? '-') ?></td>
                                <td>
                                    <?php if ($cat['status_ativo']): ?>
                                        <span class="badge-ativo">Ativa</span>
                                    <?php else: ?>
                                        <span class="badge-inativo">Inativa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="acoes-cell">
                                    <form method="POST" class="form-inline-acao">
                                        <input type="hidden" name="acao" value="alternar_status_categoria">
                                        <input type="hidden" name="id_categoria" value="<?= $cat['id_categoria'] ?>">
                                        <input type="hidden" name="novo_status" value="<?= $cat['status_ativo'] ? 0 : 1 ?>">
                                        <button type="submit" class="btn-acao <?= $cat['status_ativo'] ? 'btn-bloquear' : 'btn-ativar' ?>">
                                            <?= $cat['status_ativo'] ? 'Desativar' : 'Ativar' ?>
                                        </button>
                                    </form>
                                    <form method="POST" class="form-inline-acao" onsubmit="return confirm('Deseja remover esta categoria?');">
                                        <input type="hidden" name="acao" value="excluir_categoria">
                                        <input type="hidden" name="id_categoria" value="<?= $cat['id_categoria'] ?>">
                                        <button type="submit" class="btn-acao btn-excluir">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <section class="secao-tabela">
            <h3>Clientes Cadastrados (<?= count($clientes) ?>)</h3>
            <table class="tabela-dados">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Telefone</th>
                        <th>Status</th>
                        <th>Ações Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
