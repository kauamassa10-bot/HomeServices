<?php

require_once __DIR__ . '/init.php';

$sql = "
    SELECT 
        u.id_usuario, 
        u.nome_usuario, 
        c.nome_categoria,
        s.valor_base,
        COUNT(a.id_avaliacao) as total_avaliacoes,
        IFNULL(AVG(a.numero_nota), 0) as nota_media
    FROM usuario u
    INNER JOIN servico s ON u.id_usuario = s.id_prestador
    INNER JOIN categoria c ON s.id_categoria = c.id_categoria
    LEFT JOIN avaliacao a ON u.id_usuario = a.id_avaliado
    WHERE u.tipo_usuario = 'PRESTADOR' AND u.status_ativo = 1
    GROUP BY u.id_usuario, u.nome_usuario, c.nome_categoria, s.valor_base
";
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeServices | Buscar</title>
    <link rel="stylesheet" href="buscar.css">
</head>

<body>
    <header>
        <h1>HomeServices - Buscar</h1>
        <nav>
            <a href="">Início</a>
            <a href="">Serviços</a>
            <a href="">Contato</a>
        </nav>
    </header>

    <main>
        <section class="busca">
            <h2>Buscar Serviços</h2>
            <form method="GET" action="">
                <label for="pesquisa">Pesquisar:</label>
                <input type="text" id="pesquisa" name="pesquisa" placeholder="Digite o serviço desejado">
                <button type="submit">Buscar</button>
            </form>
        </section>
        <section class="botao-categorias">
            <div class="categorias">
                <?php
                $categorias = readAll($pdo, "categoria");
                foreach ($categorias as $categoria) {
                    echo '<a href="buscar.php?categoria=' . urlencode($categoria['nome_categoria']) . '">' . htmlspecialchars($categoria['nome_categoria'], ENT_QUOTES, 'UTF-8') . '</a>';
                }
                ?>
            </div>
        </section>
        <section class="conteudo-principal">
            <?php
            if (isset($_GET['pesquisa'])) {
                $pesquisa = $_GET['pesquisa'];
                $servicos = readAll($pdo, "servico", "nome_servico LIKE ?", ["%$pesquisa%"]);
            } elseif (isset($_GET['categoria'])) {
                $categoria = $_GET['categoria'];
                $servicos = readAll($pdo, "servico", "categoria_servico = ?", [$categoria]);
            } else {
                $servicos = readAll($pdo, "servico");
            }

            if (!empty($servicos)) {
                foreach ($servicos as $servico) {
                    echo '<div class="servico">';
                    echo '<h3>' . htmlspecialchars($servico['nome_servico'], ENT_QUOTES, 'UTF-8') . '</h3>';
                    echo '<p>' . htmlspecialchars($servico['desc_servico'], ENT_QUOTES, 'UTF-8') . '</p>';
                    echo '<p>Categoria: ' . htmlspecialchars($servico['id_categoria'], ENT_QUOTES, 'UTF-8') . '</p>';
                    echo '</div>';
                }
            } else {
                echo '<p>Nenhum serviço encontrado.</p>';
            }
            ?>
        </section>
    </main>


</body>

</html>