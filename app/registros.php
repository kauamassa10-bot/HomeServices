<?php
require_once __DIR__ . '/auth_admin.php'; // Protege a página: só ADMIN acessa
require_once __DIR__ . '/init.php';

$cliente = read($pdo, "usuario");
$iniciaisUsuario = pegarIniciais($cliente['nome_usuario']);

$mensagem = '';
$erro = '';

/**
 * Remove recursivamente todos os registros vinculados a um usuário
 * em tabelas filhas antes de excluir o próprio usuário.
 */
function excluirDependenciasUsuario(PDO $pdo, string $tabela, string $colunaPk, int $id, array &$visitados = []): void
{
    $chave = "{$tabela}:{$colunaPk}:{$id}";
    if (isset($visitados[$chave])) {
        return;
    }
    $visitados[$chave] = true;

    $stmtFks = $pdo->prepare("
        SELECT TABLE_NAME, COLUMN_NAME
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
        WHERE TABLE_SCHEMA = DATABASE()
          AND REFERENCED_TABLE_NAME = :tabela
          AND REFERENCED_COLUMN_NAME = :coluna
    ");
    $stmtFks->execute([':tabela' => $tabela, ':coluna' => $colunaPk]);

    foreach ($stmtFks->fetchAll(PDO::FETCH_ASSOC) as $fk) {
        $tabFilha = $fk['TABLE_NAME'];
        $colFilha = $fk['COLUMN_NAME'];

        $stmtPk = $pdo->prepare("
            SELECT COLUMN_NAME
            FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = :tabela
              AND CONSTRAINT_NAME = 'PRIMARY'
            LIMIT 1
        ");
        $stmtPk->execute([':tabela' => $tabFilha]);
        $pkFilha = $stmtPk->fetchColumn();

        if ($pkFilha) {
            $stmtIds = $pdo->prepare("SELECT `{$pkFilha}` FROM `{$tabFilha}` WHERE `{$colFilha}` = :id");
            $stmtIds->execute([':id' => $id]);
            foreach ($stmtIds->fetchAll(PDO::FETCH_COLUMN) as $idFilho) {
                excluirDependenciasUsuario($pdo, $tabFilha, $pkFilha, (int) $idFilho, $visitados);
            }
        }

        $stmtDel = $pdo->prepare("DELETE FROM `{$tabFilha}` WHERE `{$colFilha}` = :id");
        $stmtDel->execute([':id' => $id]);
    }
}

// --- FUNÇÕES EXCLUSIVAS DO ADMINISTRADOR ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    try {
        // 1. Criar nova categoria de serviço
        if ($acao === 'nova_categoria') {
            $nome_cat = trim($_POST['nome_categoria']);
            $desc_cat = trim($_POST['desc_categoria']);
            $stmt = $pdo->prepare("INSERT INTO categoria (nome_categoria, desc_categoria, status_ativo) VALUES (:nome, :desc, 1)");
            $stmt->execute([':nome' => $nome_cat, ':desc' => $desc_cat]);
            $mensagem = "Nova categoria '{$nome_cat}' criada com sucesso!";
        }

        // 2. Ativar ou Desativar Categoria
        if ($acao === 'alternar_status_categoria') {
            $id_cat = (int) $_POST['id_categoria'];
            $novo_status = (int) $_POST['novo_status'];
            $stmt = $pdo->prepare("UPDATE categoria SET status_ativo = :status WHERE id_categoria = :id");
            $stmt->execute([':status' => $novo_status, ':id' => $id_cat]);
            $mensagem = "Status da categoria #{$id_cat} atualizado!";
        }

        // 3. Excluir Categoria
        if ($acao === 'excluir_categoria') {
            $id_cat = (int) $_POST['id_categoria'];
            $stmt = $pdo->prepare("DELETE FROM categoria WHERE id_categoria = :id");
            $stmt->execute([':id' => $id_cat]);
            $mensagem = "Categoria #{$id_cat} excluída com sucesso.";
        }

        // 4. Ativar ou Desativar (Bloquear) Usuário
        if ($acao === 'alternar_status') {
            $id_alvo = (int) $_POST['id_usuario'];
            $novo_status = (int) $_POST['novo_status'];
            $stmt = $pdo->prepare("UPDATE usuario SET status_ativo = :status WHERE id_usuario = :id AND tipo_usuario != 'ADMIN'");
            $stmt->execute([':status' => $novo_status, ':id' => $id_alvo]);
            $mensagem = "Status do usuário #{$id_alvo} atualizado!";
        }

        // 5. Excluir Usuário e todos os seus vínculos
        if ($acao === 'excluir_usuario') {
            $id_alvo = (int) $_POST['id_usuario'];

            $stmtCheck = $pdo->prepare("SELECT tipo_usuario FROM usuario WHERE id_usuario = :id");
            $stmtCheck->execute([':id' => $id_alvo]);
            $tipoAlvo = strtoupper((string) $stmtCheck->fetchColumn());

            if ($tipoAlvo !== '' && $tipoAlvo !== 'ADMIN') {
                $pdo->beginTransaction();
                try {
                    excluirDependenciasUsuario($pdo, 'usuario', 'id_usuario', $id_alvo);

                    $stmt = $pdo->prepare("DELETE FROM usuario WHERE id_usuario = :id AND tipo_usuario != 'ADMIN'");
                    $stmt->execute([':id' => $id_alvo]);

                    $pdo->commit();
                    $mensagem = "Usuário #{$id_alvo} e seus registros vinculados foram removidos do sistema.";
                } catch (PDOException $e) {
                    if ($pdo->inTransaction()) {
                        $pdo->rollBack();
                    }
                    throw $e;
                }
            }
        }

        // 6. Excluir Serviço
        if ($acao === 'excluir_servico') {
            $id_serv = (int) $_POST['id_servico'];
            $stmt = $pdo->prepare("DELETE FROM servico WHERE id_servico = :id");
            $stmt->execute([':id' => $id_serv]);
            $mensagem = "Serviço #{$id_serv} excluído com sucesso.";
        }
    } catch (PDOException $e) {
        $erro = "Não foi possível concluir a ação (verifique se o registro possui vínculos ativos): " . $e->getMessage();
    }
}

// Consultas das tabelas
$clientes = $pdo->query("SELECT * FROM usuario WHERE tipo_usuario = 'CLIENTE' ORDER BY id_usuario DESC")->fetchAll(PDO::FETCH_ASSOC);
$profissionais = $pdo->query("SELECT * FROM usuario WHERE tipo_usuario = 'PRESTADOR' ORDER BY id_usuario DESC")->fetchAll(PDO::FETCH_ASSOC);
$categorias = $pdo->query("SELECT * FROM categoria ORDER BY id_categoria DESC")->fetchAll(PDO::FETCH_ASSOC);

$sqlServicos = "SELECT s.*, u.nome_usuario AS nome_prestador, c.nome_categoria 
                FROM servico s
                INNER JOIN usuario u ON s.id_prestador = u.id_usuario
                INNER JOIN categoria c ON s.id_categoria = c.id_categoria
                ORDER BY s.id_servico DESC";
$servicos = $pdo->query($sqlServicos)->fetchAll(PDO::FETCH_ASSOC);
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
            <h1 class="logo-texto">HomeServices</h1>
            <nav class="menu-topo">
                <a href="cadastro_servicos.php">Novo Serviço</a>
                <a href="registros.php">Painel Admin</a>
            </nav>
            <div class='foto'>
                <?= $iniciaisUsuario ?>
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

        <!-- FUNÇÃO EXCLUSIVA ADMIN: CADASTRAR NOVA CATEGORIA -->
        <section class="secao-tabela">
            <h3>Nova Categoria de Serviço (Exclusivo Admin)</h3>
            <form method="POST" action="" class="form-inline-admin">
                <input type="hidden" name="acao" value="nova_categoria">
                <input type="text" name="nome_categoria" placeholder="Nome da categoria (ex: Jardinagem)" required>
                <input type="text" name="desc_categoria" placeholder="Descrição breve da categoria" required>
                <button type="submit" class="btn-solido btn-add-categoria">Adicionar Categoria</button>
            </form>
        </section>

        <!-- TABELA 1: CATEGORIAS -->
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

        <!-- TABELA 2: CLIENTES -->
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
                        <tr><td colspan="6" class="vazio-linha">Nenhum cliente cadastrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($clientes as $cli): ?>
                            <tr>
                                <td><?= $cli['id_usuario'] ?></td>
                                <td><strong><?= htmlspecialchars($cli['nome_usuario']) ?></strong></td>
                                <td><?= htmlspecialchars($cli['email']) ?></td>
                                <td><?= htmlspecialchars($cli['telefone'] ?? '-') ?></td>
                                <td>
                                    <?php if ($cli['status_ativo']): ?>
                                        <span class="badge-ativo">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge-inativo">Bloqueado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="acoes-cell">
                                    <form method="POST" class="form-inline-acao">
                                        <input type="hidden" name="acao" value="alternar_status">
                                        <input type="hidden" name="id_usuario" value="<?= $cli['id_usuario'] ?>">
                                        <input type="hidden" name="novo_status" value="<?= $cli['status_ativo'] ? 0 : 1 ?>">
                                        <button type="submit" class="btn-acao <?= $cli['status_ativo'] ? 'btn-bloquear' : 'btn-ativar' ?>">
                                            <?= $cli['status_ativo'] ? 'Bloquear' : 'Ativar' ?>
                                        </button>
                                    </form>
                                    <form method="POST" class="form-inline-acao" onsubmit="return confirm('Tem certeza que deseja excluir este cliente e todos os seus registros?');">
                                        <input type="hidden" name="acao" value="excluir_usuario">
                                        <input type="hidden" name="id_usuario" value="<?= $cli['id_usuario'] ?>">
                                        <button type="submit" class="btn-acao btn-excluir">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- TABELA 3: PROFISSIONAIS -->
        <section class="secao-tabela">
            <h3>Profissionais Cadastrados (<?= count($profissionais) ?>)</h3>
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
                    <?php if (empty($profissionais)): ?>
                        <tr><td colspan="6" class="vazio-linha">Nenhum profissional cadastrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($profissionais as $prof): ?>
                            <tr>
                                <td><?= $prof['id_usuario'] ?></td>
                                <td><strong><?= htmlspecialchars($prof['nome_usuario']) ?></strong></td>
                                <td><?= htmlspecialchars($prof['email']) ?></td>
                                <td><?= htmlspecialchars($prof['telefone'] ?? '-') ?></td>
                                <td>
                                    <?php if ($prof['status_ativo']): ?>
                                        <span class="badge-ativo">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge-inativo">Bloqueado</span>
                                    <?php endif; ?>
                                </td>
                                <td class="acoes-cell">
                                    <form method="POST" class="form-inline-acao">
                                        <input type="hidden" name="acao" value="alternar_status">
                                        <input type="hidden" name="id_usuario" value="<?= $prof['id_usuario'] ?>">
                                        <input type="hidden" name="novo_status" value="<?= $prof['status_ativo'] ? 0 : 1 ?>">
                                        <button type="submit" class="btn-acao <?= $prof['status_ativo'] ? 'btn-bloquear' : 'btn-ativar' ?>">
                                            <?= $prof['status_ativo'] ? 'Bloquear' : 'Ativar' ?>
                                        </button>
                                    </form>
                                    <form method="POST" class="form-inline-acao" onsubmit="return confirm('Tem certeza que deseja excluir este profissional e todos os seus registros?');">
                                        <input type="hidden" name="acao" value="excluir_usuario">
                                        <input type="hidden" name="id_usuario" value="<?= $prof['id_usuario'] ?>">
                                        <button type="submit" class="btn-acao btn-excluir">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>

        <!-- TABELA 4: SERVIÇOS -->
        <section class="secao-tabela">
            <h3>Serviços Cadastrados (<?= count($servicos) ?>)</h3>
            <table class="tabela-dados">
                <thead>
                    <tr>
                        <th>#ID</th>
                        <th>Serviço</th>
                        <th>Categoria</th>
                        <th>Profissional</th>
                        <th>Valor Base</th>
                        <th>Ações Admin</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($servicos)): ?>
                        <tr><td colspan="6" class="vazio-linha">Nenhum serviço cadastrado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($servicos as $serv): ?>
                            <tr>
                                <td><?= $serv['id_servico'] ?></td>
                                <td><strong><?= htmlspecialchars($serv['nome_servico']) ?></strong></td>
                                <td><?= htmlspecialchars($serv['nome_categoria']) ?></td>
                                <td><?= htmlspecialchars($serv['nome_prestador']) ?></td>
                                <td>R$ <?= number_format((float)$serv['valor_base'], 2, ',', '.') ?></td>
                                <td class="acoes-cell">
                                    <form method="POST" class="form-inline-acao" onsubmit="return confirm('Deseja remover este serviço?');">
                                        <input type="hidden" name="acao" value="excluir_servico">
                                        <input type="hidden" name="id_servico" value="<?= $serv['id_servico'] ?>">
                                        <button type="submit" class="btn-acao btn-excluir">Excluir</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </section>
    </main>

</body>
</html>