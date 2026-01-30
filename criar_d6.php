<?php
session_start();
require_once __DIR__ . '/model/modelo.php';

// Verifica login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$dockerApiUrl = 'http://localhost:2375';
$apiVersion = 'v1.41';

// Função para verificar se a porta está em uso
function is_port_in_use($port) {
    $escaped_port = escapeshellarg($port);
    $command = "netstat -tuln | grep ':" . $escaped_port . "'";
    $output = shell_exec($command);
    return !empty($output);
}

$mensagem = "";

if (isset($_POST["submit_button"])) {
    $nomeBase   = $_POST["nome_ct"] ?? null;
    $ct         = $_POST["ct"] ?? null;
    $userId     = $_SESSION['user_id'];

    // Se já existe container, buscar portas do banco
    $host_portA = 0;
    $host_portB = 0;

    if (!empty($_POST["container_id"])) {
        $db = new MySqlConnection();
        $db->connect();
        $conn = $db->getConnection();

        $stmt = $conn->prepare("SELECT portA, portB FROM container WHERE id=? AND user_id=?");
        $stmt->bind_param("ii", $_POST["container_id"], $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $host_portA = (int)$row['portA'];
            $host_portB = (int)$row['portB'];
        }
        $stmt->close();
    } else {
        // Se for novo container, pegar do formulário
        $host_portA = isset($_POST["host_portA"]) ? (int)$_POST["host_portA"] : 0;
        $host_portB = isset($_POST["host_portB"]) ? (int)$_POST["host_portB"] : 0;
    }

    if (empty($nomeBase) || $host_portA <= 0 || $host_portB <= 0) {
        $mensagem .= "Erro: Nome ou portas inválidas.<br>";
    } elseif (is_port_in_use($host_portA) || is_port_in_use($host_portB)) {
        $mensagem .= "Erro: Uma das portas ($host_portA ou $host_portB) já está em uso.<br>";
    } else {
        // Nome único com base no usuário
        $userName = $_SESSION['nome_usuario'] ?? 'user';
        $uniqueSuffix = substr(md5(uniqid()), 0, 6);
        $containerName = strtolower($userName . "_" . $nomeBase . "_" . $uniqueSuffix);

        // Escolhe imagem
        $imageName = ($ct === 'ct1') ? 'exemplo/oi' : 'exemplo/oi';
        $memoryInBytes = 350 * 1024 * 1024;

        // Configuração do container
        $createUrl = $dockerApiUrl . '/containers/create?name=' . urlencode($containerName);
        $containerConfig = json_encode([
            'Image' => $imageName,
            'ExposedPorts' => [
                '22/tcp' => new stdClass()   
            ],
            'HostConfig' => [
                'PortBindings' => [
                    '22/tcp' => [['HostPort' => (string) $host_portA]],
                ],
                'Memory' => $memoryInBytes
            ],
            'Cmd' => ['/usr/sbin/sshd', '-D']
        ]);

        // Cria container
        $ch = curl_init($createUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $containerConfig);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode == 201) {
            $result = json_decode($response);
            $containerId = $result->Id;

            $mensagem .= "Container '$containerName' criado com sucesso!<br>";
            $mensagem .= "ID: " . substr($containerId, 0, 12) . "<br>";

            // Inicia container
            $startUrl = $dockerApiUrl . '/containers/' . $containerId . '/start';
            $ch_start = curl_init($startUrl);
            curl_setopt($ch_start, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_start, CURLOPT_POST, true);
            curl_setopt($ch_start, CURLOPT_POSTFIELDS, "{}");
            $responseStart = curl_exec($ch_start);
            $httpCodeStart = curl_getinfo($ch_start, CURLINFO_HTTP_CODE);
            curl_close($ch_start);

            if ($httpCodeStart == 204) {
                $mensagem .= "Container iniciado com sucesso!<br>";
            } else {
                $mensagem .= "Erro ao iniciar container: $responseStart (HTTP $httpCodeStart)<br>";
            }

            // Salvar no banco (apenas se for novo container)
            if (empty($_POST["container_id"])) {
                $db = new MySqlConnection();
                $db->connect();
                $conn = $db->getConnection();

                $timeInput = date('Y-m-d H:i:s');
                $timeEnd   = '0000-00-00';

                $sql = "INSERT INTO container (nome, portA, portB, timeInput, timeEnd, user_id) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("siissi", $containerName, $host_portA, $host_portB, $timeInput, $timeEnd, $userId);
                $stmt->execute();
                $stmt->close();


            }
        } else {
            $mensagem .= "Erro ao criar container: $response<br>";
        }
    }
} else {
    $mensagem = "Formulário não submetido.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Resultado</title>
</head>
<body>
    <h2>Resultado da Criação do Container</h2>
    <p><?php echo $mensagem; ?></p>
</body>
</html>
