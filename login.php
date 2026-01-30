<?php
// Deve ser a PRIMEIRA linha de código PHP
session_start();

//include __DIR__ . "/../model/modelo.php";
include "model/modelo.php";

// Verifica e exibe a mensagem de erro (se o controlador a definiu)
if (isset($_SESSION['error_message'])) {
    echo '<p style="color:red; font-weight: bold;">' . htmlspecialchars($_SESSION['error_message']) . '</p>';
    unset($_SESSION['error_message']); 
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liberdade</title>
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <a href="#"> DOCKER GUI</a>
        <a href="https://www.youtube.com/watch?v=nJ046CijyYE&t=1844s"> Executable </a>
        <a href="https://www.youtube.com/watch?v=95Uh9Ab_QWQ"> Assembler</a>
        <a href="https://www.youtube.com/watch?v=32XsfeIX_rM"> boa vinda</a>
        <a href="https://www.youtube.com/watch?v=bZRemXbO7kU"> boa vindas</a>
    </header>
    <main>
        <div id="divCenter">
            <form action="control/controler.php" method="POST">
                <label>Usuário</label><br>
                <input type="text" name="nome" required><br>
                <label>Senha</label><br>
                <input type="password" name="senha" required><br><br>
                <input type="submit" value="Entrar">
            </form>
            <br>
            <img src="public/images/pombo1.png" width="400px" height="300px">
              <a href="criarusuario.php">Criar_USUARIOS</a>
            <p>Cloud Grillo</p>
</br></br>

        </div>
    </main>
    <footer class="footer">
        <p>Criador: Grillo Foreman </p>
        <p>&copy; 2025. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
