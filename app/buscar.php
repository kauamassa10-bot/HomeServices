<?php

require_once __DIR__ . '/init.php';

$propostas = readAll($pdo, "propostas");
$users = readAll($pdo, "users");
foreach($propostas as $p){
    echo "id: {$p['id']}, nome: {$p['nome']}" . PHP_EOL;
    echo "telefone: {$p['telefone']}, quantidade: {$p['quantidade']}" . PHP_EOL;
    }

?>