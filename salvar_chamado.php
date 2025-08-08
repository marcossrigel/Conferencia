<?php
header('Content-Type: application/json');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "abertura_chamado";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["ok" => false, "erro" => "Falha na conexão com o banco"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (empty($data['titulo']) || empty($data['descricao']) || empty($data['quem_abriu'])) {
    echo json_encode(["ok" => false, "erro" => "Todos os campos são obrigatórios"]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO chamados (titulo, descricao, quem_abriu) VALUES (?, ?, ?)");
$stmt->bind_param("sss", $data['titulo'], $data['descricao'], $data['quem_abriu']);

if ($stmt->execute()) {
    echo json_encode(["ok" => true, "id" => $stmt->insert_id]);
} else {
    echo json_encode(["ok" => false, "erro" => "Erro ao inserir chamado"]);
}

$stmt->close();
$conn->close();
?>
