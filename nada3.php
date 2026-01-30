<?php
session_start();
require_once __DIR__ . '/model/modelo.php';

// 1. Verifica se está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// 2. Verifica se a role está definida
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

// 3. Esta página é SOMENTE para USER
if ($_SESSION['role'] !== 'user') {
    header("Location: admin.php");
    exit;
}

$dockerApiUrl = 'http://localhost:2375/v1.45/containers';

// Conectar ao banco
$db = new MySqlConnection();
$db->connect();
$conn = $db->getConnection();

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // STOP
    if (isset($_POST['stop_container_name'])) {
        $containerName = $_POST['stop_container_name'];

        $stopUrl = $dockerApiUrl . '/' . $containerName . '/stop';
        $ch = curl_init($stopUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_exec($ch);
        curl_close($ch);

        $timeEnd = date('Y-m-d H:i:s');
        $update = $conn->prepare("UPDATE container SET timeEnd=? WHERE nome=? AND user_id=?");
        $update->bind_param("ssi", $timeEnd, $containerName, $_SESSION['user_id']);
        $update->execute();
        $update->close();

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // START
    if (isset($_POST['start_container_name'])) {
        $containerName = $_POST['start_container_name'];

        $startUrl = $dockerApiUrl . '/' . $containerName . '/start';
        $ch = curl_init($startUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_exec($ch);
        curl_close($ch);

        $reset = $conn->prepare("UPDATE container SET timeEnd='0000-00-00' WHERE nome=? AND user_id=?");
        $reset->bind_param("si", $containerName, $_SESSION['user_id']);
        $reset->execute();
        $reset->close();

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

    // DELETE de verdade
    if (isset($_POST['delete_container_name'])) {
        $containerName = $_POST['delete_container_name'];

        // Primeiro para o container
        $stopUrl = $dockerApiUrl . '/' . $containerName . '/stop';
        $ch = curl_init($stopUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_exec($ch);
        curl_close($ch);

        // Agora remove do Docker
        $deleteUrl = $dockerApiUrl . '/' . $containerName . '?force=true';
        $ch = curl_init($deleteUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code == 204) {
            $del = $conn->prepare("DELETE FROM container WHERE nome=? AND user_id=?");
            $del->bind_param("si", $containerName, $_SESSION['user_id']);
            $del->execute();
            $del->close();
        } else {
            $mensagem = "Erro ao deletar container: $response (HTTP $code)";
        }

        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Buscar containers do usuário (ativos e parados)
$sql = "SELECT id, nome, portA, portB, timeInput, timeEnd
        FROM container
        WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$containers = [];
while ($row = $result->fetch_assoc()) {
    $containers[] = $row;
}
$stmt->close();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciador de Containers</title>
    <link href="css/style.css" rel="stylesheet">
    <style>
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; }
        thead tr { background-color: #f2f2f2; }
        .btn-action { padding: 6px 10px; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: bold; }
        .btn-stop { background-color: #ffc107; }
        .btn-start { background-color: #28a745; }
        .btn-delete { background-color: #dc3545; }
    </style>
</head>
<body>
<header class="header">
    <nav id="menu_index">
        <ul>
            <li><a href="index.php" class="other">HOME</a></li>
            <li><a href="index_containers2.php">CRIAR</a></li>
        </ul>
    </nav>
</header>

<main id="main_body">
    <h1>Meus Containers</h1>

    <?php if (!empty($containers)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>PortA</th>
                    <th>PortB</th>
                    <th>TimeInput</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($containers as $c): ?>
                    <tr>
                        <td><?php echo $c['id']; ?></td>
                        <td><?php echo htmlspecialchars($c['nome']); ?></td>
                        <td><?php echo $c['portA']; ?></td>
                        <td><?php echo $c['portB']; ?></td>
                        <td><?php echo $c['timeInput']; ?></td>
                        <td>
                            <?php
                            if ($c['timeEnd'] === '0000-00-00' || $c['timeEnd'] === null) {
                                echo "Ativo";
                            } else {
                                echo "Parado";
                            }
                            ?>
                        </td>
                        <td>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="stop_container_name" value="<?php echo $c['nome']; ?>">
                                <button type="submit" class="btn-action btn-stop">Parar</button>
                            </form>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="start_container_name" value="<?php echo $c['nome']; ?>">
                                <button type="submit" class="btn-action btn-start">Startar</button>
                            </form>
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="delete_container_name" value="<?php echo $c['nome']; ?>">
                                <button type="submit" class="btn-action btn-delete">Deletar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Nenhum container encontrado para este usuário.</p>
    <?php endif; ?>
</main>
</body>
</html>
