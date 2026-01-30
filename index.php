<?php
session_start();

// Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Pega o papel do usuário
$role = $_SESSION['role'] ?? 'user'; // se não tiver, assume user
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
        <nav id="menu_index">
            <ul>
                <li><a href="index.php" class="other">HOME</a></li>
                <?php if ($role === 'admin'): ?>
                <li><a href="index_containers7.php">CRIAR(ADMIN)</a></li>
                 <?php else: ?>
                    <li><a href="index_containers2.php">Criar</a></li>
                <?php endif; ?>
                <?php if ($role === 'admin'): ?>
                    <li><a href="nada.php">CONTAINERS (ADMIN)</a></li>
                <?php else: ?>
                    <li><a href="nada2.php">MEUS CONTAINERS</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>
    <main id="main_body">
        <h1>TROGNIZADOR</h1>
        <div icons8-ferramentas>
            <div class="fotos_menus2">
                <a href="index_containers2.php">
                    <img src="public/images/icons8-virtualbox.png">
                </a>
            </div>
            <div class="fotos_menus2">
                <img src="public/images/icons8-ferramentas.png" width="150px" height="150px">
            </div>
        </div>
    </main>
    <footer class="footer">
        <p>Criador: Grillo Foreman | Data: 2019/09</p>
        <p>&copy; 2025. Todos os direitos reservados.</p>
    </footer>
</body>
</html>
