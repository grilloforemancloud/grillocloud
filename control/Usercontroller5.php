<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../model/modelo.php'; 

class UserController {
    public function criar($nome, $senha) {
        // Gera hash seguro da senha
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

        // Cria objeto usuário (id pode ser auto_increment no banco, então passamos null)
        $usuario = new Usera(null, $nome, $senhaHash, $portA, $portB);

        // Conecta ao banco
        $db = new MySqlConnection();
        $db->connect();

        // Insere usuário
        $db->insertUser($usuario);

        // Mensagem de sucesso
        $_SESSION['success_message'] = "Usuário '$nome' criado com sucesso!";
        header("Location: ../criarlogin.html"); 
        exit;
    }
}



// Se o formulário foi enviado
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome = $_POST["nome"] ?? '';
    $senha = $_POST["senha"] ?? '';
    $portA = $_POST["portA"] ?? null;
    $portB = $_POST["portB"] ?? null;

    $controller = new UserController();
    $controller->criar($nome, $senha, $portA, $portB);
}

?>