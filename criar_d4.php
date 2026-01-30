<?php

//////////////////////////////////////////////////////////////////////
$dockerApiUrl = 'http://localhost:2375';
$apiVersion = 'v1.41';

// --- NOVO: Função para verificar se uma porta está em uso via linha de comando ---
function is_port_in_use($port) {
    // Escapa a porta para evitar injeção de comando
    $escaped_port = escapeshellarg($port);

    // Usa 'netstat' para verificar a porta em sistemas Unix-like
    $command = "netstat -tuln | grep ':" . $escaped_port . "'";

    // Executa o comando e captura a saída
    $output = shell_exec($command);

    // Se a saída não for vazia, significa que a porta está em uso
    if (!empty($output)) {
        return true; // Porta em uso
    }
    return false; // Porta não está em uso
}

// Check if the form was submitted
if (isset($_POST["submit_button"])) {

    $nombre = $_POST["nome_ct"];
    $host_port = $_POST["host_port"];

    // Validate that a container type was selected
    if (!isset($_POST["ct"])) {
        echo "Por favor, selecione um tipo de container (CT1 ou CT2).";
        exit;
    }

    $ct = $_POST["ct"];
    $mensagem = ""; // Variável para armazenar a mensagem de sucesso ou erro

    // --- Passo 1.5: Verificar a porta antes de criar o contêiner ---
    if (is_port_in_use($host_port)) {
        $mensagem .= "Erro: A porta " . htmlspecialchars($host_port) . " já está em uso. Por favor, escolha outra.<br>";
        // Exibe a mensagem de erro e interrompe a execução
        echo '<h2>Resultado da Criação do Container</h2><p>' . $mensagem . '</p>';
        exit;
    }

    // Example: choose image based on selection
    $imageName = ($ct === 'ct1') ? 'exemplo/oi' : 'exemplo/oi'; 
    $memoryInBytes = 350 * 1024 * 1024;

    // --- Passo 2: Criar o contêiner ---
    $createUrl = $dockerApiUrl . '/' . $apiVersion . '/containers/create?name=' . urlencode($nombre);

    $containerConfig = json_encode([
        'Image' => $imageName,
        'ExposedPorts' => [
            '22/tcp' => new stdClass()
        ],
        'HostConfig' => [
            'PortBindings' => [
                '22/tcp' => [
                    [
                        'HostPort' => (string) $host_port
                    ]
                ]
            ],
            'Memory' => $memoryInBytes
        ],
        'Cmd' => ['/usr/sbin/sshd', '-D']
    ]);

    $ch = curl_init($createUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $containerConfig);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // O resto do código permanece o mesmo...

    if ($httpCode == 201) {
        $result = json_decode($response);
        $containerId = $result->Id;
    
        $mensagem .= "Container '" . htmlspecialchars($nombre) . "' criado com sucesso!<br>";
        $mensagem .= "ID do Container: " . substr($containerId, 0, 12) . "<br>";
    
        // --- Passo 3: Iniciar o contêiner criado ---
        $startUrl = $dockerApiUrl . '/' . $apiVersion . '/containers/' . $containerId . '/start';
        $ch_start = curl_init($startUrl);
        curl_setopt($ch_start, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_start, CURLOPT_CUSTOMREQUEST, 'POST');

        $startResponse = curl_exec($ch_start);
        $startHttpCode = curl_getinfo($ch_start, CURLINFO_HTTP_CODE);
        curl_close($ch_start);

        if ($startHttpCode == 204) {
            $mensagem .= "Container '" . htmlspecialchars($nombre) . "' iniciado com sucesso!<br>";
            $mensagem .= "Status: 'Running'<br>";

            // --- Passo 4: Inspecionar o contêiner para obter detalhes ---
            $inspectUrl = $dockerApiUrl . '/' . $apiVersion . '/containers/' . $containerId . '/json';
            $ch_inspect = curl_init($inspectUrl);
            curl_setopt($ch_inspect, CURLOPT_RETURNTRANSFER, true);
        
            $inspectResponse = curl_exec($ch_inspect);
            curl_close($ch_inspect);

            $containerDetails = json_decode($inspectResponse, true);
            $portBindings = $containerDetails['NetworkSettings']['Ports']['22/tcp'][0];
            $hostPort = $portBindings['HostPort'];

            $mensagem .= "A porta do host para a porta 22 do container é: " . $hostPort . "<br>";

        } else {
            $mensagem .= "Erro ao iniciar o container: " . $startResponse . "<br>";
            $mensagem .= "Código HTTP: " . $startHttpCode . "<br>";
        }

    } else {
        $mensagem .= "Erro ao criar o container: " . $response . "<br>";
        $mensagem .= "Código HTTP: " . $httpCode . "<br>";
    }
} else {
    $mensagem = "Formulário não submetido.";
}

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Processando Criação</title>
</head>
<body>
    <h2>Resultado da Criação do Container</h2>
    <p><?php echo $mensagem; ?></p>
</body>
</html>


