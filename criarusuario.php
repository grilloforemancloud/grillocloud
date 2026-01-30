<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Usuário</title>
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <header class="header">
        <a href="#">DOCKER GUI</a>
    </header>
    <main>
        <div id="divCenter">
            <h2>Criar Usuário</h2>
            <!-- Agora o action aponta para o controller -->
            <form action="control/Usercontroller.php" method="POST">
                <label for="nome">Usuário:</label>
                <input type="text" id="nome" name="nome" required /><br>

                <label for="senha">Senha:</label>
                <input type="password" id="senha" name="senha" required /><br>


                <input type="submit" value="Criar Usuário" />
            </form>
        </div>
    </main>
</body>
</html>
