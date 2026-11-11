<?php

require_once __DIR__ . '/init.php';

$sql = "
    SELECT 
        s.id_servico,
        s.nome_servico,
        s.desc_servico,
        s.valor_base,
        c.nome_categoria,
        u.nome_usuario AS nome_prestador,
        COUNT(a.id_avaliacao) as total_avaliacoes,
        IFNULL(AVG(a.numero_nota), 0) as nota_media
    FROM servico s
    INNER JOIN usuario u ON s.id_prestador = u.id_usuario
    INNER JOIN categoria c ON s.id_categoria = c.id_categoria
    LEFT JOIN avaliacao a ON u.id_usuario = a.id_avaliado
    WHERE s.status_ativo = 1
    GROUP BY 
        s.id_servico, 
        s.nome_servico, 
        s.desc_servico, 
        s.valor_base, 
        c.nome_categoria, 
        u.nome_usuario;        
";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HomeServices | Buscar</title>
    <link rel="stylesheet" href="css/buscar.css">
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
        <section class="categorias">
            <div class="botao-categorias">
                <?php
                $categorias = readAll($pdo, "categoria");
                echo '<a href="buscar.php">Todos</a>';
                foreach ($categorias as $categoria) {
                    echo '<a href="buscar.php?categoria=' . urlencode($categoria['nome_categoria']) . '">' . htmlspecialchars($categoria['nome_categoria'], ENT_QUOTES, 'UTF-8') . '</a>';
                }
                ?>
            </div>
        </section>
        <section class="conteudo-principal">
            <?php
            // Mantendo a query original base com JOINs, para não perder a busca após o ciclo de filtros
            $sqlBusca = "
        SELECT 
            s.id_servico,
            s.nome_servico,
            s.desc_servico,
            s.valor_base,
            c.nome_categoria,
            u.nome_usuario AS nome_prestador,
            COUNT(a.id_avaliacao) as total_avaliacoes,
            IFNULL(AVG(a.numero_nota), 0) as nota_media
        FROM servico s
        INNER JOIN usuario u ON s.id_prestador = u.id_usuario
        INNER JOIN categoria c ON s.id_categoria = c.id_categoria
        LEFT JOIN avaliacao a ON u.id_usuario = a.id_avaliado
        WHERE s.status_ativo = 1
    ";
            $params = [];

            // Aplicando filtros de pesquisa dinâmica
            if (isset($_GET['pesquisa']) && !empty($_GET['pesquisa'])) {
                $sqlBusca .= " AND s.nome_servico LIKE ?";
                $params[] = "%" . $_GET['pesquisa'] . "%";
            } elseif (isset($_GET['categoria']) && !empty($_GET['categoria'])) {
                $sqlBusca .= " AND c.nome_categoria = ?";
                $params[] = $_GET['categoria'];
            }

            // Adicionar o GROUP BY no final
            $sqlBusca .= " GROUP BY s.id_servico, s.nome_servico, s.desc_servico, s.valor_base, c.nome_categoria, u.nome_usuario";

            $stmtBusca = $pdo->prepare($sqlBusca);
            $stmtBusca->execute($params);
            $servicos = $stmtBusca->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div class="servicos">

                <?php
                if (!empty($servicos)) {
                    foreach ($servicos as $servico) {

                        echo '<h3>' . htmlspecialchars($servico['nome_servico'], ENT_QUOTES, 'UTF-8') . '</h3>';
                        echo '<p>Prestador: ' . htmlspecialchars($servico['nome_prestador'], ENT_QUOTES, 'UTF-8') . '</p>';
                        echo '<p>' . htmlspecialchars($servico['desc_servico'], ENT_QUOTES, 'UTF-8') . '</p>';
                        echo '<p>Categoria: ' . htmlspecialchars($servico['nome_categoria'], ENT_QUOTES, 'UTF-8') . '</p>';
                        $sqlAvaliacoes = "SELECT COUNT(*) as total_avaliacoes, IFNULL(AVG(numero_nota), 0) as nota_media FROM avaliacao WHERE id_avaliado = ?";
                        $stmtAvaliacoes = $pdo->prepare($sqlAvaliacoes);
                        $stmtAvaliacoes->execute([$servico["id_prestador"]]);
                        $avaliacoes = $stmtAvaliacoes->fetch(PDO::FETCH_ASSOC);
                        echo '<p>Nota: ' . number_format((float) $avaliacoes['nota_media'], 1, ',', '.') . ' (' . $avaliacoes['total_avaliacoes'] . ' avaliações)</p>';
                        echo '<button><a href="orcamento.php?id=' . $servico['id_servico'] . '">Solicitar Serviço</a></button>';
                    }
                } else {
                    echo '<p>Nenhum serviço encontrado.</p>';
                }
                ?>
            </div>
        </section>
        </section>
    </main>


</body>

</html>