<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/init.php';

// Verifica se o usuário está logado (ele será o avaliador)
$id_avaliador = $_SESSION['id_usuario'] ?? null;

if (!$id_avaliador) {
    header("Location: login.php?erro=acesso_negado");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recebe os dados do formulário
    $id_agendamento = $_POST['id_agendamento'] ?? null;
    $id_avaliado = $_POST['id_prestador'] ?? null; // ID do prestador recebendo a nota
    $nota = $_POST['nota'] ?? null;
    $comentario = trim($_POST['comentario'] ?? '');

    // Validação básica
    if ($id_agendamento && $id_avaliado && $nota) {
        try {
            /* 
             * NOTA: Verifique se os nomes das colunas correspondem exatamente à sua tabela 'avaliacao'.
             * Baseado no seu arquivo buscar.php, sabemos que existe 'id_avaliado' e 'numero_nota'.
             * Assumimos os nomes comuns para o restante.
             */
           $sql = "INSERT INTO avaliacao (id_avaliador, id_avaliado, id_agendamento, numero_nota, desc_comentario, data_avaliacao) 
        VALUES (:id_avaliador, :id_avaliado, :id_agendamento, :numero_nota, :comentario, NOW())";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id_avaliador' => $id_avaliador,
                ':id_avaliado' => $id_avaliado,
                ':id_agendamento' => $id_agendamento,
                ':numero_nota' => $nota,
                ':comentario' => $comentario
            ]);

            // Redireciona de volta para o acompanhamento com mensagem de sucesso
            header("Location: acompanhar_pedido.php?id=" . $id_agendamento . "&sucesso=avaliacao_enviada");
            exit;

        } catch (PDOException $e) {
            echo "<div style='font-family: sans-serif; padding: 20px;'>";
            echo "<h2>Erro no banco de dados</h2>";
            echo "<p>Verifique se os nomes das colunas na tabela <b>avaliacao</b> estão corretos.</p>";
            echo "<p>Erro técnico: " . htmlspecialchars($e->getMessage()) . "</p>";
            echo "<a href='acompanhar_pedido.php?id=" . htmlspecialchars($id_agendamento) . "'>Voltar</a>";
            echo "</div>";
            exit;
        }
    } else {
        echo "Dados incompletos para a avaliação. Por favor, escolha uma nota.";
        exit;
    }
} else {
    // Se tentarem acessar a página diretamente sem enviar o formulário, redireciona
    header("Location: pedidos.php");
    exit;
}
?>