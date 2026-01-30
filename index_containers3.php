

<?php
session_start();
require_once __DIR__ . '/control/controler.php'; 

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Verifica se é admin
if ($_SESSION['role'] !== 'user') {
    // Se não for admin, manda para a página do usuário
    header("Location: index_containers3.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liberdade</title>
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
<header class="header">
    <nav id="menu_index">
        <ul>
            <li> <a href="index.php" class="other">HOME</a> </li>
            <li> <a href="index_containers.php"> CRIAR</a> </li>
            <li> <a href="nada4.php"> CONTAINERS PS -A</a> </li>
        </ul>
</nav>
</header>
    <main id="main_body">
      <h1> TROGNIZADOR</h1>
      <h3> Escolha o melhor</h3>
     <form action="criar_d5.php" method="post" target="_blank">
  Nome_container <input type="text" name="nome_ct" value="ct0"><br>
  Porta do Host (ex: 502) <input type="text" name="host_port" value="502"><br>
  <input type="radio" name="ct" value="ct1">CT1 Linux
  <br>
  <input type="radio" name="ct" value="ct2"> CM2 Linux
  <br>
  <input type="submit" name="submit_button" value="OKKK">
</form>
    </main>

 <footer class="footer">
        <p>Criador: Grillo Foreman | Data: 2019/09</p>
        <p>&copy; 2025. Todos os direitos reservados.</p>
    </footer>

</body>
</html>
