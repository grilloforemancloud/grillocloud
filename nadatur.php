<?php
session_start();
require_once __DIR__ . '/model/modelo.php';

/* ==========================
   VERIFICA LOGIN
========================== */
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] !== 'admin') {
    header("Location: admin.php");
    exit;
}

/* ==========================
   BANCO
========================== */
$db = new MySqlConnection();
$db->connect();
$conn = $db->getConnection();

/* ==========================
   FUNÇÃO CURL
========================== */
function dockerRequest($url, $method = 'GET')
{
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return [
        'code' => $code,
        'response' => $response
    ];
}

/* ==========================
   PROCESSAR AÇÕES
========================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /* REMOVER DOCKER + BANCO */
    if (isset($_POST['remove_all_id'])) {

        $containerId = $_POST['remove_all_id'];

        // tenta parar
        dockerRequest("http://localhost:2375/v1.41/containers/$containerId/stop", 'POST');

        // remove docker
        dockerRequest("http://localhost:2375/v1.41/containers/$containerId?force=true", 'DELETE');

        // remove banco
        $del = $conn->prepare("DELETE FROM container WHERE container_id=?");
        $del->bind_param("s", $containerId);
        $del->execute();
        $del->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    /* REMOVER SÓ DO BANCO */
    if (isset($_POST['remove_db_id'])) {

        $containerId = $_POST['remove_db_id'];

        $del = $conn->prepare("DELETE FROM container WHERE container_id=?");
        $del->bind_param("s", $containerId);
        $del->execute();
        $del->close();

        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

/* ==========================
   LISTA DOCKER
========================== */
$dockerApiUrl = 'http://localhost:2375/v1.41/containers/json?all=true';

$dockerResult = dockerRequest($dockerApiUrl);

if ($dockerResult['code'] == 200) {
    $dockerContainers = json_decode($dockerResult['response'], true);
} else {
    $dockerContainers = [];
    $error = "Erro ao conectar à API Docker.";
}

/* ==========================
   LISTA BANCO
========================== */
$sql = "SELECT * FROM container ORDER BY id DESC";
$result = $conn->query($sql);
$dbContainers = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $dbContainers[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Containers</title>

    <!-- SEU CSS -->
    <link href="public/css/style.css" rel="stylesheet">

    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            margin-bottom: 40px;
            table-layout: fixed;
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }

        thead tr {
            background-color: #f2f2f2;
        }

        .status-running {
            border-left: 5px solid #28a745;
        }

        .status-exited {
            border-left: 5px solid #dc3545;
        }

        .status-other {
            border-left: 5px solid #ffc107;
        }

        .container-status {
            font-weight: bold;
        }

        .status-running .container-status {
            color: #28a745;
        }

        .status-exited .container-status {
            color: #dc3545;
        }

        .status-other .container-status {
            color: #ffc107;
        }

        .btn-action {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-weight: bold;
            font-size: 12px;
            white-space: nowrap;
        }

        .btn-stop {
            background-color: #dc3545;
        }

        .btn-delete {
            background-color: #007bff;
        }

        .section-title {
            margin-top: 40px;
        }

        .col-id {
            max-width: 220px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .col-actions {
            width: 150px;
            text-align: center;
        }

        #main_body {
            max-width: 1000px;
            margin: auto;
        }
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

    <h1>Containers do Docker</h1>

    <?php if (isset($error)): ?>
        <p class="error"><?php echo $error; ?></p>
    <?php elseif (!empty($dockerContainers)): ?>

        <table>
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>ID</th>
                    <th>Imagem</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($dockerContainers as $container): ?>

                    <?php
                    $status = strtolower($container['State']);

                    if (strpos($status, 'running') !== false) {
                        $statusClass = 'status-running';
                    } elseif (strpos($status, 'exited') !== false) {
                        $statusClass = 'status-exited';
                    } else {
                        $statusClass = 'status-other';
                    }
                    ?>

                    <tr class="<?php echo $statusClass; ?>">
                        <td><?php echo htmlspecialchars(ltrim($container['Names'][0], '/')); ?></td>
                        <td class="col-id"><?php echo substr($container['Id'], 0, 12); ?></td>
                        <td><?php echo htmlspecialchars($container['Image']); ?></td>
                        <td>
                            <span class="container-status">
                                <?php echo htmlspecialchars($container['Status']); ?>
                            </span>
                        </td>
                        <td class="col-actions">
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="remove_all_id" value="<?php echo $container['Id']; ?>">
                                <button type="submit" class="btn-action btn-stop">
                                    Remover Docker + BD
                                </button>
                            </form>
                        </td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>
        <p>Nenhum container encontrado no Docker.</p>
    <?php endif; ?>


    <h1 class="section-title">Containers do Banco de Dados</h1>

    <?php if (!empty($dbContainers)): ?>

        <table>
            <thead>
                <tr>
                    <th>ID Banco</th>
                    <th>Container ID</th>
                    <th>Nome</th>
                    <th>Criado em</th>
                    <th>Ações</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($dbContainers as $row): ?>

                    <tr>
                        <td><?php echo $row['id']; ?></td>

                        <td class="col-id" title="<?php echo htmlspecialchars($row['container_id']); ?>">
                            <?php echo substr($row['container_id'], 0, 18); ?>...
                        </td>

                        <td><?php echo htmlspecialchars($row['name'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at'] ?? '-'); ?></td>

                        <td class="col-actions">
                            <form method="POST" style="display:inline-block;">
                                <input type="hidden" name="remove_db_id" value="<?php echo $row['container_id']; ?>">
                                <button type="submit" class="btn-action btn-delete">
                                    Remover só BD
                                </button>
                            </form>
                        </td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>

    <?php else: ?>
        <p>Nenhum registro encontrado no banco.</p>
    <?php endif; ?>

</main>

</body>
</html>
