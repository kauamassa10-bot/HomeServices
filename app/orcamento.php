<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/banco/database.php';


// Apenas CLIENTES podem solicitar orçamentos
if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'CLIENTE') {
    header("Location: login_cliente.php?erro=acesso_negado");
    exit;
}




$mensagem = '';
$id_cliente = (int) $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_categoria = 1; // Ajuste para pegar do formulário caso o cliente precise selecionar
    $tipo_servico = trim($_POST['tipo_servico']);
    $descricao = trim($_POST['descricao']);
    $endereco = trim($_POST['endereco']);
    $data_desejada = $_POST['data_desejada'];

    $sql = "INSERT INTO solicitacao 
            (id_cliente, id_categoria, desc_titulo, desc_solicitacao, desc_endereco_atendimento, status_solicitacao, data_solicitacao) 
            VALUES 
            (:id_cliente, :id_categoria, :desc_titulo, :desc_solicitacao, :desc_endereco_atendimento, 'aberto', :data_solicitacao)";
    
    $stmt = $pdo->prepare($sql);
    
    try {
        $stmt->execute([
            ':id_cliente' => $id_cliente,
            ':id_categoria' => $id_categoria,
            ':desc_titulo' => $tipo_servico,
            ':desc_solicitacao' => $descricao,
            ':desc_endereco_atendimento' => $endereco,
            ':data_solicitacao' => $data_desejada
        ]);
        
        // Redireciona direto para Meus Pedidos após salvar
        header("Location: pedidos.php?sucesso=1");
        exit;
    } catch (PDOException $e) {
        $mensagem = "<div class='erro'>Erro ao enviar pedido: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Novo Pedido - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/global.css">
</head>
<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <nav class="menu-topo">
                <a href="buscar.php">Buscar Serviços</a>
                <a href="pedidos.php">Meus Pedidos</a>
                <a href="acompanhar_pedido.php">Acompanhar Pedido</a>
                <a href="logout.php">Sair</a>
            </nav>
        </div>
    </header>

    <main class="layout-duas-colunas">
        <section class="coluna-esquerda">
            <h2>Solicitar Orçamento</h2>
            <p class="subtitulo">Preencha os detalhes do que você precisa</p>

            <?= $mensagem ?>

            <form method="POST" action="" class="form-pedido-leitura">
                
                <div class="campo-simples">
                    <input type="text" name="" value="<?= $_GET['nome_servico'] ?>" required>
                </div>

                <div class="campo-simples">
                    <textarea name="descricao" placeholder="Descreva o serviço detalhadamente..." required></textarea>
                </div>

                <div class="linha-dupla">
                    <div class="campo-simples">
                        <input type="text" name="endereco" placeholder="Endereço / CEP" required>
                    </div>
                    <div class="campo-simples">
                        <input type="date" name="data_desejada" required>
                    </div>
                </div>

                <div class="botoes-rodape">
                    <button type="submit" class="btn-solido" style="width: 100%;">Enviar Pedido para Profissional</button>
                </div>
            </form>
        </section>

        <aside class="coluna-direita">
            <div class="card-resumo">
                <div class="detalhes-resumo">
                    <strong>Informações</strong>
                    <p>O seu pedido será enviado para profissionais da sua região.</p>
                    <p>Orçamento gratuito e sem compromisso.</p>
                </div>
            </div>
        </aside>
    </main>

</body>
</html>