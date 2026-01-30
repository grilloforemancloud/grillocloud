<?php
// Verificador de sessão

session_start();
  require_once __DIR__ . '/control/controler.php'; 




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
if ($_SESSION['role'] !== 'admin') {
    header("Location: admin.php");
    exit;
}






// URL da API do Docker para listar todos os containers
$dockerApiUrl = 'http://localhost:2375/v1.41/containers/json?all=true';

// Processar ações de Stop e Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['stop_container_id'])) {
        $containerId = $_POST['stop_container_id'];
        $stopUrl = 'http://localhost:2375/v1.41/containers/' . $containerId . '/stop';
        $ch = curl_init($stopUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_exec($ch);
        curl_close($ch);
        // Redireciona para a mesma página para atualizar a lista
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }

   if (isset($_POST['delete_container_id'])) {
    $containerId = $_POST['delete_container_id'];

    // Para o container antes de deletar
    $stopUrl = 'http://localhost:2375/containers/' . $containerId . '/stop';
    $ch = curl_init($stopUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
    curl_exec($ch);
    curl_close($ch);

    // Remove do Docker
    $deleteUrl = 'http://localhost:2375/containers/' . $containerId . '?force=true';
    $ch = curl_init($deleteUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($code == 204) {
        // Marca como deletado no banco
        $db = new MySqlConnection();
        $db->connect();
        $conn = $db->getConnection();

        $deletedAt = date('Y-m-d H:i:s');
        $upd = $conn->prepare("UPDATE container SET deleted_at=? WHERE container_id=?");
        $upd->bind_param("ss", $deletedAt, $containerId);
        $upd->execute();
        $upd->close();
    }

    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
}

// Obter a lista de containers da API do Docker
$ch = curl_init($dockerApiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Verifica se a requisição foi bem-sucedida
if ($httpCode == 200) {
    $containers = json_decode($response, true);
} else {
    $containers = [];
    $error = "Erro ao conectar à API do Docker. Código HTTP: " . $httpCode;
}

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciador de Containers</title>
    <link href="css/style.css" rel="stylesheet">
    <style>
        /* Estilos da tabela */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        thead tr {
            background-color: #f2f2f2;
        }
        /* Estilos para as cores das linhas da tabela */
        .status-running {
            border-left: 5px solid #28a745; /* Verde */
        }
        .status-exited {
            border-left: 5px solid #dc3545; /* Vermelho */
        }
        .status-other {
            border-left: 5px solid #ffc107; /* Amarelo */
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
        /* Estilos dos botões */
        .btn-action {
            padding: 8px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            color: white;
            font-weight: bold;
        }
        .btn-stop {
            background-color: #ffc107;
        }
        .btn-delete {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav id="menu_index">
            <ul>
                <li> <a href="index.php" class="other">HOME</a> </li>
                <li> <a href="index_containers2.php"> CRIAR</a> </li>
            </ul>
        </nav>
    </header>

    <main id="main_body">
        <h1>Containers Docker</h1>

        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php elseif (!empty($containers)): ?>
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
                    <?php foreach ($containers as $container): ?>
                        <?php
                            $status = strtolower($container['State']);
                            $statusClass = '';
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
                            <td><?php echo substr($container['Id'], 0, 12); ?></td>
                            <td><?php echo htmlspecialchars($container['Image']); ?></td>
                            <td><span class="container-status"><?php echo htmlspecialchars($container['Status']); ?></span></td>
                            <td>
                                <?php if (strpos($status, 'running') !== false): ?>
                                    <form method="POST" style="display:inline-block;">
                                        <input type="hidden" name="stop_container_id" value="<?php echo $container['Id']; ?>">
                                        <button type="submit" class="btn-action btn-stop">Parar</button>
                                    </form>
                                <?php endif; ?>
                                <form method="POST" style="display:inline-block;">
                                    <input type="hidden" name="delete_container_id" value="<?php echo $container['Id']; ?>">
                                    <button type="submit" class="btn-action btn-delete">Deletar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>Nenhum container encontrado.</p>
        <?php endif; ?>
    </main>
</body>
</html>
