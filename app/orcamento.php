<?php
require_once __DIR__ . '/init.php';

$mensagem = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_cliente = 1; // ID fixo simulando cliente logado
    $id_categoria = 1; // Categoria padrão obrigatória pela chave estrangeira do banco
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
        $mensagem = "<div class='erro'>Erro ao enviar pedido: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Novo Pedido - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
</head>
<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices</h1>
            <nav class="menu-topo">
                <a href="orcamento.php">Novo Pedido</a>
                <a href="pedidos.php">Meus pedidos</a>
                <a href="cadastro_cliente.php">Cadastrar novos clientes</a>
                <a href="cadastro_profissional.php">Cadastrar novos profissionais</a>
                <a href="cadastro_servicos.php">Cadastrar novos servicos</a>
                <a href="registros.php">Registros</a>
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
                    <input type="text" name="tipo_servico" placeholder="Ex: Eletricista, Limpeza residencial..." required>
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
                    <button type="submit" class="btn-solido" style="width: 100%;">Enviar Pedido para Profissionais</button>
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