<?php

$host = "192.168.10.14";
$port = "5432";
$dbname = "almoxarifado";
$user = "postgres";
$senha = "SUA_SENHA";

try {
    $pdo = new PDO(
        "pgsql:host=$host;port=$port;dbname=$dbname",
        $user,
        $senha
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    header("Content-Type: application/json; charset=UTF-8");
    http_response_code(500);
    echo json_encode(["mensagem" => "Erro na conexão com o banco."]);
    exit;
}
?>