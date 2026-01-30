<?php
session_start();

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Esta página é SOMENTE para USER
if (($_SESSION['role'] ?? 'user') !== 'user') {
    // Se for admin, manda para a página de admin
    header("Location: nada.php");
    exit;
}

require_once __DIR__ . '/model/modelo.php';

// Buscar as portas do usuário logado
$db = new MySqlConnection();
$db->connect();
$conn = $db->getConnection();

$sql = "SELECT portA, portB FROM user WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

$portA = $userData['portA'] ?? '';
$portB = $userData['portB'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Liberdade</title>
    <link href="public/css/style.css" rel="stylesheet">
</head>
<body>
<header class="header">
    <nav id="menu_index">
        <ul>
            <li><a href="index.php" class="other">HOME</a></li>
            <li><a href="index_containers2.php">CRIAR</a></li>
            <!-- Link de containers para USER -->
            <li><a href="nada2.php">MEUS CONTAINERS</a></li>
        </ul>
    </nav>
</header>
<main id="main_body">
    <h1>TROGNIZADOR</h1>
    <h3>Escolha o melhor</h3>
    <form action="criar_d5.php" method="post" target="_blank">
        Nome_container <input type="text" name="nome_ct" value="ct0"><br>

        <!-- Portas travadas do usuário -->
        Porta A <input type="text" name="host_portA" value="<?php echo $portA; ?>" readonly><br>
        Porta B <input type="text" name="host_portB" value="<?php echo $portB; ?>" readonly><br>

        <input type="radio" name="ct" value="ct1">CT1 Linux<br>
        <input type="radio" name="ct" value="ct2">CM2 Linux<br>

        <input type="submit" name="submit_button" value="OKKK">
    </form>
</main>
<footer class="footer">
    <p>Criador: Grillo Foreman | Data: 2019/09</p>
    <p>&copy; 2025. Todos os direitos reservados.</p>
</footer>
</body>
</html>
