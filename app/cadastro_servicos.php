<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/banco/database.php';

/** @var PDO $pdo */

// Verifica qual é o tipo de usuário logado
$tipo_usuario = strtoupper($_SESSION['tipo_usuario'] ?? '');
$id_usuario_logado = (int) ($_SESSION['id_usuario'] ?? 0);

// Restringe o acesso apenas para ADMIN ou PRESTADOR
if (!in_array($tipo_usuario, ['ADMIN', 'PRESTADOR'])) {
    header("Location: login_cliente.php?erro=acesso_negado");
    exit;
}

$mensagem = '';
$isAdmin = ($tipo_usuario === 'ADMIN');

// Carrega categorias para o select (ambos precisam)
$categorias =$pdo->query("SELECT id_categoria, nome_categoria FROM categoria WHERE status_ativo = 1 ORDER BY nome_categoria ASC")->fetchAll(PDO::FETCH_ASSOC);

// Carrega prestadores SOMENTE se for ADMIN
$prestadores = [];
if ($isAdmin) {
    $prestadores =$pdo->query("SELECT id_usuario, nome_usuario FROM usuario WHERE tipo_usuario = 'PRESTADOR' AND status_ativo = 1 ORDER BY nome_usuario ASC")->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Se for ADMIN, pega o id_prestador do formulário; se for PRESTADOR, usa o próprio ID da sessão
    $id_prestador =$isAdmin ? (int) $_POST['id_prestador'] :$id_usuario_logado;
    
    $id_categoria = (int)$_POST['id_categoria'];
    $nome_servico = trim($_POST['nome_servico']);
    $desc_servico = trim($_POST['desc_servico']);
    $valor_base = str_replace(',', '.',$_POST['valor_base']);

    $sql = "INSERT INTO servico (id_prestador, id_categoria, nome_servico, desc_servico, valor_base, status_ativo) 
            VALUES (:id_prestador, :id_categoria, :nome_servico, :desc_servico, :valor_base, 1)";

    $stmt = $pdo->prepare($sql);

    try {
        $stmt->execute([
            ':id_prestador' => $id_prestador,
            ':id_categoria' => $id_categoria,
            ':nome_servico' => $nome_servico,
            ':desc_servico' => $desc_servico,
            ':valor_base' => $valor_base
        ]);

        // Redirecionamento baseado no perfil
        if ($isAdmin) {
            header("Location: registros.php?sucesso=servico");
        } else {
            header("Location: pedidos.php?sucesso=servico_criado");
        }
        exit;
    } catch (PDOException $e) {$mensagem = "<div class='erro'>Erro ao cadastrar serviço: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar Serviço - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/cadastros.css">
    <link rel="stylesheet" href="css/global.css">
</head>
<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            
            <nav class="menu-topo">
                <?php if ($isAdmin): ?>
                    <a href="registros.php">Registros</a>
                    <a href="cadastro_cliente.php">Novo Cliente</a>
                    <a href="cadastro_profissional.php">Novo Profissional</a>
                    <a href="cadastro_servicos.php">Novo Serviço</a>
                <?php else: ?>
                    <a href="pedidos.php">Meus Serviços e Pedidos</a>
                    <a href="cadastro_servicos.php">Adicionar Serviço</a>
                <?php endif; ?>
                <a href="logout.php">Sair</a>
            </nav>

        </div>
    </header>

    <main class="layout-duas-colunas">
        <section class="coluna-esquerda">
            <h2><?= $isAdmin ? 'Cadastrar Serviço' : 'Oferecer Novo Serviço' ?></h2>
            <p class="subtitulo">
                <?= $isAdmin ? 'Vincule um novo serviço a um profissional e categoria' : 'Preencha os dados do serviço que você deseja oferecer aos clientes.' ?>
            </p>

            <?= $mensagem ?>

            <form method="POST" action="" class="form-pedido-leitura">
                
                <?php if ($isAdmin): ?>
                    <!-- Campo de selecionar profissional aparece apenas para o ADMIN -->
                    <div class="campo-simples">
                        <select name="id_prestador" class="campo-select" required>
                            <option value="">Selecione o Profissional...</option>
                            <?php foreach ($prestadores as$p): ?>
                                <option value="<?= $p['id_usuario'] ?>"><?= htmlspecialchars($p['nome_usuario']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; 
                ?>

                <div class="campo-simples">
                    <select name="id_categoria" class="campo-select" required>
                        <option value="">Selecione a Categoria...</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nome_categoria']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo-simples">
                    <input type="text" name="nome_servico" placeholder="Nome do serviço (ex: Troca de Fiação Residencial)" required>
                </div>

                <div class="campo-simples">
                    <textarea name="desc_servico" placeholder="Descrição detalhada do que está incluso no serviço..." required></textarea>
                </div>

                <div class="campo-simples">
                    <input type="number" step="0.01" min="0" name="valor_base" placeholder="Valor base estimado em R$ (ex: 180.00)" required>
                </div>

                <div class="botoes-rodape">
                    <button type="submit" class="btn-solido" style="width: 100%;">Salvar Serviço</button>
                </div>
            </form>
        </section>

        <aside class="coluna-direita">
            <div class="card-resumo">
                <div class="detalhes-resumo">
                    <strong>Dica</strong>
                    <?php if ($isAdmin): ?>
                        <p>Caso o profissional ainda não apareça na lista, cadastre-o primeiro na aba <strong>Novo Profissional</strong>.</p>
                    <?php else: ?>
                        <p>Descreva bem o seu serviço! Uma boa descrição ajuda os clientes a entenderem exatamente o que está incluso no valor base.</p>
                    <?php endif; ?>
                </div>
            </div>
        </aside>
    </main>

</body>
</html>