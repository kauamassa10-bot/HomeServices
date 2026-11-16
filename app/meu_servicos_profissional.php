<?php
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/banco/database.php';

/** @var PDO $pdo */

// Proteção: Apenas profissionais podem acessar
if (!isset($_SESSION['id_usuario']) || strtoupper($_SESSION['tipo_usuario'] ?? '') !== 'PRESTADOR') {
    header("Location: login_profissional.php?erro=acesso_negado");
    exit;
}

$id_profissional = $_SESSION['id_usuario'];
$mensagem = '';

// Se o formulário foi enviado, salva o serviço
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = trim($_POST['preco'] ?? '');

    if (!empty($titulo) && !empty($descricao)) {
        try {
            // Supondo que sua tabela se chame 'servico' ou 'servicos'. 
            // Ajuste os nomes das colunas conforme o seu banco de dados.
            $sql = "INSERT INTO servico (id_usuario, nome_servico, descricao, valor, status_ativo) 
                    VALUES (:id_usuario, :titulo, :descricao, :preco, 1)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_usuario' => $id_profissional,
                ':titulo' => $titulo,
                ':descricao' => $descricao,
                ':preco' => $preco
            ]);
            $mensagem = "<div class='alerta-sucesso' style='color: green; margin-bottom: 15px;'>Serviço cadastrado com sucesso!</div>";
        } catch (PDOException $e) {
            $mensagem = "<div class='alerta-erro' style='color: red; margin-bottom: 15px;'>Erro ao cadastrar serviço: " . htmlspecialchars($e->getMessage()) . "</div>";
        }
    }
}

// Busca os serviços já cadastrados por este profissional
$sqlBusca = "SELECT * FROM servico WHERE id_usuario = :id_usuario ORDER BY id_servico DESC";
$stmtBusca = $pdo->prepare($sqlBusca);
$stmtBusca->execute([':id_usuario' => $id_profissional]);
$meusServicos = $stmtBusca->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Serviços - HomeServices</title>
    <link rel="stylesheet" href="css/orcamento.css">
    <link rel="stylesheet" href="css/cadastros.css">
    <link rel="stylesheet" href="css/global.css">
</head>
<body class="fundo-claro">

    <header class="header-simples">
        <div class="header-container">
            <h1 class="logo-texto">HomeServices <span style="font-size: 0.9rem; color: #0284c7;">| Portal do Profissional</span></h1>
            <nav class="menu-topo">
                <a href="meus_servicos.php" style="font-weight: bold; text-decoration: underline;">Meus Serviços</a>
                <a href="pedidos.php">Meus Pedidos (Orçamentos)</a>
                <a href="logout.php">Sair</a>
            </nav>
        </div>
    </header>

    <main class="layout-duas-colunas" style="margin-top: 30px;">
        <!-- Coluna da Esquerda: Formulário de Cadastro -->
        <section class="coluna-esquerda">
            <h2>Cadastrar Novo Serviço</h2>
            <p class="subtitulo">Adicione os serviços que você oferece na plataforma</p>

            <?= $mensagem ?>

            <form method="POST" action="" class="form-pedido-leitura">
                <div class="campo-simples">
                    <label>Título do Serviço</label>
                    <input type="text" name="titulo" placeholder="Ex: Manutenção Elétrica Residencial" required>
                </div>

                <div class="campo-simples">
                    <label>Descrição Detalhada</label>
                    <textarea name="descricao" rows="4" placeholder="Descreva o que está incluso no seu serviço..." required></textarea>
                </div>

                <div class="campo-simples">
                    <label>Valor Base / Hora (Opcional)</label>
                    <input type="number" step="0.01" name="preco" placeholder="Ex: 150.00">
                </div>

                <div class="botoes-rodape">
                    <button type="submit" class="btn-solido" style="width: 100%;">Adicionar Serviço</button>
                </div>
            </form>
        </section>

        <!-- Coluna da Direita: Lista de Serviços Cadastrados -->
        <aside class="coluna-direita">
            <h2>Meus Serviços Ativos</h2>
            <div style="margin-top: 15px; display: flex; flex-direction: column; gap: 15px;">
                
                <?php if (count($meusServicos) > 0): ?>
                    <?php foreach ($meusServicos as $servico): ?>
                        <div class="card-resumo" style="border-left: 4px solid #0284c7; padding: 15px; background: #fff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);">
                            <h3 style="margin: 0 0 5px 0; font-size: 1.1rem;"><?= htmlspecialchars($servico['nome_servico'] ?? 'Sem título') ?></h3>
                            <p style="margin: 0 0 10px 0; font-size: 0.9rem; color: #555;">
                                <?= htmlspecialchars($servico['descricao'] ?? '') ?>
                            </p>
                            <span style="font-weight: bold; color: #16a34a;">
                                R$ <?= number_format($servico['valor'] ?? 0, 2, ',', '.') ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="color: #666; font-style: italic;">Nenhum serviço cadastrado ainda.</p>
                <?php endif; ?>

            </div>
        </aside>
    </main>

</body>
</html>