<?php
require_once __DIR__ . '/init.php';

$mensagem = '';

// Carrega prestadores e categorias para os campos de seleção
$prestadores = $pdo->query("SELECT id_usuario, nome_usuario FROM usuario WHERE tipo_usuario = 'PRESTADOR' AND status_ativo = 1 ORDER BY nome_usuario ASC")->fetchAll(PDO::FETCH_ASSOC);
$categorias = $pdo->query("SELECT id_categoria, nome_categoria FROM categoria WHERE status_ativo = 1 ORDER BY nome_categoria ASC")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_prestador = (int) $_POST['id_prestador'];
    $id_categoria = (int) $_POST['id_categoria'];
    $nome_servico = trim($_POST['nome_servico']);
    $desc_servico = trim($_POST['desc_servico']);
    $valor_base = str_replace(',', '.', $_POST['valor_base']);

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

        header("Location: registros.php?sucesso=servico");
        exit;
    } catch (PDOException $e) {
        $mensagem = "<div class='erro'>Erro ao cadastrar serviço: " . $e->getMessage() . "</div>";
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
</head>
<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <nav class="menu-topo">
                <a href="orcamento.php">Orçamento</a>
                <a href="pedidos.php">Meus pedidos</a>
                <a href="cadastro_cliente.php">Novo Cliente</a>
                <a href="cadastro_profissional.php">Novo Profissional</a>
                <a href="cadastro_servicos.php">Novo Serviço</a>
                <a href="registros.php">Registros</a>
            </nav>
        </div>
    </header>

    <main class="layout-duas-colunas">
        <section class="coluna-esquerda">
            <h2>Cadastrar Serviço</h2>
            <p class="subtitulo">Vincule um novo serviço a um profissional e categoria</p>

            <?= $mensagem ?>

            <form method="POST" action="" class="form-pedido-leitura">
                <div class="linha-dupla">
                    <div class="campo-simples">
                        <select name="id_prestador" class="campo-select" required>
                            <option value="">Selecione o Profissional...</option>
                            <?php foreach ($prestadores as $p): ?>
                                <option value="<?= $p['id_usuario'] ?>"><?= htmlspecialchars($p['nome_usuario']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="campo-simples">
                        <select name="id_categoria" class="campo-select" required>
                            <option value="">Selecione a Categoria...</option>
                            <?php foreach ($categorias as $c): ?>
                                <option value="<?= $c['id_categoria'] ?>"><?= htmlspecialchars($c['nome_categoria']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="campo-simples">
                    <input type="text" name="nome_servico" placeholder="Nome do serviço (ex: Troca de Fiação Residencial)" required>
                </div>

                <div class="campo-simples">
                    <textarea name="desc_servico" placeholder="Descrição detalhada do que está incluso no serviço..." required></textarea>
                </div>

                <div class="campo-simples">
                    <input type="number" step="0.01" min="0" name="valor_base" placeholder="Valor base em R$ (ex: 180.00)" required>
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
                    <p>Caso o profissional ainda não apareça na lista, cadastre-o primeiro na aba <strong>Novo Profissional</strong>.</p>
                </div>
            </div>
        </aside>
    </main>

</body>
</html>