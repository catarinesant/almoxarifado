<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "conexao.php";

$metodo = $_SERVER["REQUEST_METHOD"];

$dados = json_decode(file_get_contents("php://input"), true);

function responder($mensagem, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode(["mensagem" => $mensagem],
        JSON_UNESCAPED_UNICODE);
    exit;
}

function validarPeca($dados) {
    $categorias = ["eletrica", "mecanica", "hidraulica"];

    if (
        !is_array($dados) ||
        !isset(
            $dados["nome"],
            $dados["categoria"],
            $dados["fornecedor"],
            $dados["quantidade"],
            $dados["preco_unitario"]
        ) ||
        trim((string)$dados["nome"]) === "" ||
        trim((string)$dados["fornecedor"]) === "" ||
        !is_numeric($dados["quantidade"]) ||
        !is_numeric($dados["preco_unitario"]) ||
        !is_int($dados["quantidade"]) ||
        $dados["quantidade"] < 0 ||
        $dados["preco_unitario"] <= 0 ||
        !in_array($dados["categoria"], $categorias, true)
    ) {
        responder("Dados inválidos ou obrigatórios não informados.", 400);
    }
}

try {

    // CADASTRAR UMA PEÇA
    if ($metodo == "POST") {

        validarPeca($dados);

        $sql = "INSERT INTO pecas
        (nome, categoria, fornecedor, quantidade, preco_unitario)
        VALUES (:nome, :categoria, :fornecedor, :quantidade, :preco)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":nome" => $dados["nome"],
            ":categoria" => $dados["categoria"],
            ":fornecedor" => $dados["fornecedor"],
            ":quantidade" => $dados["quantidade"],
            ":preco" => $dados["preco_unitario"]
        ]);

        responder("Peça cadastrada com sucesso!", 201);
    }

    // LISTAR TODAS AS PEÇAS
    elseif ($metodo == "GET") {

        $sql = "SELECT * FROM pecas ORDER BY id";
        $stmt = $pdo->query($sql);

        echo json_encode(
            $stmt->fetchAll(PDO::FETCH_ASSOC),
            JSON_UNESCAPED_UNICODE
        );
    }

    // ATUALIZAR UMA PEÇA
    elseif ($metodo == "PUT") {

        if (!is_array($dados) || !isset($dados["id"]) ||
            !filter_var($dados["id"], FILTER_VALIDATE_INT) ||
            $dados["id"] <= 0) {
            responder("Informe um id válido.", 400);
        }

        validarPeca($dados);

        $sql = "UPDATE pecas SET
        nome = :nome,
        categoria = :categoria,
        fornecedor = :fornecedor,
        quantidade = :quantidade,
        preco_unitario = :preco
        WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ":nome" => $dados["nome"],
            ":categoria" => $dados["categoria"],
            ":fornecedor" => $dados["fornecedor"],
            ":quantidade" => $dados["quantidade"],
            ":preco" => $dados["preco_unitario"],
            ":id" => $dados["id"]
        ]);

        if ($stmt->rowCount() == 0) {
            $verificar = $pdo->prepare("SELECT id FROM pecas WHERE id = ?");
            $verificar->execute([$dados["id"]]);

            if (!$verificar->fetch()) {
                responder("Peça não encontrada.", 404);
            }
        }

        responder("Peça atualizada com sucesso!");
    }

    // EXCLUIR UMA PEÇA
    elseif ($metodo == "DELETE") {

        if (!is_array($dados) || !isset($dados["id"]) ||
            !filter_var($dados["id"], FILTER_VALIDATE_INT) ||
            $dados["id"] <= 0) {
            responder("Informe um id válido.", 400);
        }

        $stmt = $pdo->prepare("DELETE FROM pecas WHERE id = ?");
        $stmt->execute([$dados["id"]]);

        if ($stmt->rowCount() == 0) {
            responder("Peça não encontrada.", 404);
        }

        responder("Peça excluída com sucesso!");
    }

    else {
        responder("Método não permitido.", 405);
    }

} catch (PDOException $e) {
    responder("Erro ao executar a operação.", 500);
}
?>