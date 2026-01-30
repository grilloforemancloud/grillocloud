<?php


require_once __DIR__ . '/../model/modelo.php'; 

class UserController {
    public function criar($nome, $senha) {
        // Gera hash seguro da senha
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Conecta ao banco
        $db = new MySqlConnection();
        $db->connect();
        $conn = $db->getConnection();

        // Descobre a última porta usada
        $sql = "SELECT MAX(portA) AS ultimaPorta FROM user";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();

        $ultimaPorta = $row['ultimaPorta'] ?? null;

        if ($ultimaPorta === null || $ultimaPorta < 500) {
            $portA = 500;
        } else {
            $portA = $ultimaPorta + 1;
        }

        // Se quiser também portB como a mesma porta ou +1
        $portB = $portA; // ou $portA + 1 se precisar diferente

        // Cria objeto usuário
        $usuario = new Usera(null, $nome, $senhaHash, $portA, $portB);

        // Insere usuário
        $db->insertUser($usuario);

        // Mensagem de sucesso
        $_SESSION['success_message'] = "Usuário '$nome' criado com sucesso! Porta atribuída: $portA";
        header("Location: ../login.php"); 
        exit;
    }
}

// Se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST["nome"] ?? '';
    $senha = $_POST["senha"] ?? '';

    $controller = new UserController();
    $controller->criar($nome, $senha);
}
?>
