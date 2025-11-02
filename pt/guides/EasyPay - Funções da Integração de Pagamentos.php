config.php
<?php
require __DIR__ . '/../vendor/autoload.php'; 

use Dotenv\Dotenv;

// A função createImmutable() define onde está o ficheiro .env que queres carregar
// __DIR__ no PHP retorna o diretório onde está o ficheiro PHP que contém __DIR__, no caso, config.php   <- aqui está o __DIR__
$dotenv = Dotenv::createImmutable(__DIR__); // Fica na pasta acima, caso esteja na mesma, basta __DIR__ . '/..'
$dotenv->load();

// Função para obter API Key    
function getEasypayApiKey() {
    return getenv('EASYPAY_ENV') === 'prod'
        ? getenv('EASYPAY_API_KEY_PROD')
        : getenv('EASYPAY_API_KEY_SANDBOX');
}

// Função para obter URL do endpoint
function getEasypayUrl($endpoint = 'payment') {
    $base = getenv('EASYPAY_ENV') === 'prod'
        ? 'https://api.easypay.pt/2.0/'
        : 'https://api.test.easypay.pt/2.0/';
    return $base . $endpoint; // . serve para concatenar, no caso, o endpoint desejado
}

// Função para conexão PDO usando .env
// PDO (PHP Data Objects) é uma interface do PHP para trabalhar com bases de dados de forma segura e consistente
function getDbConnection() {
    try {
        $pdo = new PDO("mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4", getenv('DB_USER'), getenv('DB_PASS'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
            // Em new PDO($dsn, $user, $pass, $options); - dsn 
            // O quarto argumento ($options) é um array associativo com opções de configuração que controlam o comportamento da conexão como tratar erros, como devolver resultados, etc...
            // PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION - Define o modo de tratamento de erros do PDO, neste caso, sempre que ocorrer um erro (por exemplo, ligação falhada, query inválida), o PDO lança uma exceção (PDOException)
            // PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC - Define o formato padrão dos resultados das queries, neste caso, passa a devolver apenas arrays associativos, ou seja, cada valor é acessível apenas pelo nome da coluna, em vez de também ter um índice numérico
        );
        return $pdo;
    } catch (PDOException $e) {
        error_log("EASYPAY database connection failed: " . $e->getMessage()); // mostra o erro capturado
        http_response_code(500);
        exit("Erro de base de dados");
    }
}



// Função para criar pagamento
function createPayment($name, $email, $amount, $method) {
    $methodMap = [
        "card" => "cc",
        "mbway" => "mbw",
        "multibanco" => "mb"
    ];
    $method = $methodMap[$method] ?? "cc";
    $orderId = uniqid("order_");
    
    $payload = [
        "amount" => $amount,
        "currency" => "EUR",
        "orderId" => $orderId,
        "customer" => [
            "name" => $name,
            "email" => $email
        ],
        "paymentMethod" => $method,
        "redirectUrl" => "https://url_do_website/checkout_success.php"
    ];

    $ch = curl_init(); // inicializa uma nova sessão cURL e retorna um manipulador ($ch) que será usado nas próximas configurações
    curl_setopt($ch, CURLOPT_URL, getEasypayUrl('payment')); // define a URL para a qual a requisição será enviada
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // o resultado da requisição passa a ser retornado como string pelo curl_exec(), em vez de ser impresso diretamente no browser
    curl_setopt($ch, CURLOPT_POST, true); // define que a requisição será do tipo POST, portanto, mais segura, invisivel
    curl_setopt($ch, CURLOPT_HTTPHEADER, [ // define os headers HTTP da requisição
        "Content-Type: application/json", // indica que o corpo da requisição está em formato JSON
        "Authorization: Bearer " . getEasypayApiKey() // envia a chave da API como token Bearer para autenticar a requisição
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload)); // define o corpo da requisição POST, transformando o array $payload em JSON, visto que o endpoint da EasyPay espera receber os dados do pagamento em JSON

    // Tendo em conta que a função curl_exec() pode falhar silenciosamente precisamos de validar os seguintes pontos:
    // Se o cURL executou corretamente
    // Qual o HTTP status code da resposta (200, 400, 401, 500, ...)
    // https://developer.mozilla.org/en-US/docs/Web/HTTP/Guides/Network_Error_Logging
    // Se a resposta JSON é válida

    $response = curl_exec($ch); // executa a requisição HTTP que configuraste anteriormente com curl_setopt()
    curl_close($ch); // fecha a sessão para evitar "memory leaks" (consumo de memória desnecessário)

    $data = json_decode($response, true); // $response contém a resposta da API que recebeste com curl_exec($ch) e json_decode() é uma unção nativa do PHP que converte uma string JSON em dados PHP, com true, o JSON é convertido em array associativo em vez de objeto
    // Sem o true → objeto
    // $data->method->entity // "12345"

    // Com o true → array associativo
    $entity = $data['method']['entity'] ?? null; // "12345" 
    $reference = $data['method']['reference'] ?? null; // repara que senão houver entidade ou referência, retorna null

    // Guardar pagamento na base de dados
    savePayment($orderId, $name, $email, $amount, $method, 'pending', $entity, $reference);

    return $data;
}

// Função para salvar pagamento na base de dados - dependendo do método de pagamento
function savePayment($orderId, $name, $email, $amount, $method, $status, $entity = null, $reference = null) {
    $pdo = getDbConnection();
    $stmt = $pdo->prepare("INSERT INTO pagamentos (orderId, customerName, customerEmail, amount, paymentMethod, status, entity, reference) VALUES (?, ?, ?, ?, ?, ?, ?, ?)"); // $pdo->prepare() cria uma query preparada e os ? são placeholders, que serão substituídos pelos valores reais de forma segura
    $stmt->execute([$orderId, $name, $email, $amount, $method, $status, $entity, $reference]); // execute() associa os valores reais aos placeholders ? na ordem que aparecem
}

// Função para obter status de pagamento
function getPaymentStatus($paymentId) {
    $ch = curl_init(getEasypayUrl('payment/') . $paymentId);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer " . getEasypayApiKey()
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

// Função para atualizar pagamento via webhook
function updatePaymentStatus($orderId, $status) {
    $pdo = new PDO(
            "mysql:host=" . getenv('DB_HOST') . ";dbname=" . getenv('DB_NAME') . ";charset=utf8mb4", getenv('DB_USER'), getenv('DB_PASS'),
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]
        );
    $stmt = $pdo->prepare("UPDATE pagamentos SET status = :status, dataConfirmacao = NOW() WHERE orderId = :orderId");
    $stmt->execute([':status' => $status, ':orderId' => $orderId]);
}
?>
