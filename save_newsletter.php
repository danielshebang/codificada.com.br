<?php
header('Content-Type: application/json; charset=utf-8');

// Configurações
$jsonFile = 'newsletter_subscribers.json';

// Função para resposta JSON
function sendResponse($success, $message, $data = null) {
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Verifica se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendResponse(false, 'Método não permitido.');
}

// Recebe e valida os dados
$name = isset($_POST['name']) ? trim($_POST['name']) : '';
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($name) || empty($email)) {
    sendResponse(false, 'Nome e e-mail são obrigatórios.');
}

// Valida o e-mail
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    sendResponse(false, 'E-mail inválido.');
}

// Sanitiza os dados
$name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
$email = filter_var($email, FILTER_SANITIZE_EMAIL);

// Cria o arquivo se não existir
if (!file_exists($jsonFile)) {
    file_put_contents($jsonFile, '');
}

// Lê o arquivo existente
$fileContent = file_get_contents($jsonFile);
$subscribers = [];

// Parse linha por linha (cada linha é um objeto JSON)
if (!empty($fileContent)) {
    $lines = explode("\n", trim($fileContent));
    foreach ($lines as $line) {
        if (!empty($line)) {
            $subscriber = json_decode($line, true);
            if ($subscriber) {
                $subscribers[] = $subscriber;
                
                // Verifica se o e-mail já existe
                if (strtolower($subscriber['email']) === strtolower($email)) {
                    sendResponse(false, 'Este e-mail já está cadastrado em nossa lista de espera.');
                }
            }
        }
    }
}

// Cria novo registro
$newSubscriber = [
    'id' => uniqid('codificada_', true),
    'name' => $name,
    'email' => $email,
    'subscribed_at' => date('Y-m-d H:i:s'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
];

// Adiciona nova linha ao arquivo (formato: uma linha por objeto)
$jsonLine = json_encode($newSubscriber, JSON_UNESCAPED_UNICODE) . "\n";

if (file_put_contents($jsonFile, $jsonLine, FILE_APPEND | LOCK_EX) === false) {
    sendResponse(false, 'Erro ao salvar os dados. Tente novamente mais tarde.');
}

sendResponse(true, 'Cadastro realizado com sucesso! Você receberá um e-mail quando a Codificada estiver no ar.', [
    'total_subscribers' => count($subscribers) + 1
]);
?>