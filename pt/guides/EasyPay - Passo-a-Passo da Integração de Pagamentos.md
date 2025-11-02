# Guia de Integração Easypay (Sandbox) até Produção
## 1. Preparar ambiente
### A. Preparar a Estrutura de Pastas 
3. Criar os ficheiros:
    /payments
    ├─ index.php                    # Formulário de checkout
    ├─ checkout.php                 # Cria pagamento via API
    ├─ checkout_success.php         # Página de sucesso
    ├─ checkout_fail.php            # Página de falha
    ├─ webhook.php                  # Endpoint para receber notificações de pagamentos
    ├─ config.php                   # Configurações seguras
    ├─ .env                         # Credenciais privadas, se possivel colocar fora de payments, por segurança
    ├─ .htaccess                    # Protege o .env e outras pastas
    
 
> **O que é a raiz pública** 
> É a pasta do servidor onde ficam os ficheiros acessíveis via browser
> Tudo que estiver lá pode ser acedido por URL, por exemplo: https://website.pt/index.php
 
/public_html                    <- raiz pública do site
 ├─ /payments         
 │   ├─ index.php               # Formulário de checkout       
 │   ├─ checkout.php            # Cria pagamento via API
 │   ├─ checkout_success.php    # Página de sucesso
 │   ├─ checkout_fail.php       # Endpoint para receber notificações de pagamentos
 │   ├─ webhook.php             # Configurações seguras
 │   ├─ config.php              # Credenciais privadas, se possivel colocar fora de payments, por segurança
 │   └─ .htaccess               # Protege o .env e outras pastas                
 ├─ .env

MAIS TARDE
 ├─ composer.json
 ├─ composer.lock
 └─ /vendor   ← criado pelo Composer (local) 
 Tens que copiar para o servidor sempre que mudares dependências, estes ficheiros     

**Nota:** Precisas de ter o Composer (um gestor de dependências do PHP) instalado no teu sistema (local)
- Verificar com `composer --version`
- Caso não possuas, [instala](https://getcomposer.org/download/)


2. No terminal, dentro de payments, pasta raiz, instalar o **Dotenv**, para poder usar o `.env`
```bash
composer require vlucas/phpdotenv
```
Vai criar a pasta vendor/ e o ficheiro composer.lock e composer.json
No vendor/ estará a biblioteca phpdotenv que o teu config.php vai usar

> Dotenv serve para guardar variáveis de ambiente em ficheiros .env e carregá-las automaticamente no PHP
> Variáveis de ambiente são dados sensíveis ou de configuração que não queremos deixar hardcoded no código
> Hardcoded significa que um valor está escrito diretamente no código, em vez de ser carregado de uma configuração externa (como um .env, base de dados, ou variável de ambiente)


3. Em Windows:
- Abrir o Bloco de Notas ou uma nova janela de documento de texto no VS
- Inserir:
<Files .env>
  Order allow,deny
    Deny from all
</Files>
- Ao guardar, escolher `Todos os ficheiros` no tipo e escreve .htacess

---
### B. Preparar o servidor
**Se for local:**
- XAMPP → iniciar **Apache** e **MySQL**
**Se for hospedado (Netlify, Namecheap, etc.):**  
- Aceder via browser: link_do_servidor


**Enviar ficheiros para o servidor (através de FTP)**
- Instalar no tpc local, o cliente FTP [filezilla](https://filezilla-project.org/)
- O teu fornecedor (ou do cliente) de alojamento (ex: Hostinger, Namecheap, OVH, etc...) dá-te as credenciais

| Parâmetro       | Exemplo                     | Descrição                                |
|------------------|-----------------------------|-------------------------------------------|
| **Host**         | `ftp.teusite.pt` ou `185.123.45.67` | Endereço do servidor                     |
| **Porta**        | `21 (FTP)` ou `22 (SFTP)`   | Protocolo de comunicação (FTP ou seguro SFTP) |
| **Utilizador**   | `user@teusite.pt`           | Nome de utilizador da conta FTP           |
| **Password**     | `********`                  | Palavra-passe da conta FTP                |
| **Caminho remoto** | `/public_html/`           | Pasta principal onde o site “vive”        |
> Podes encontrar isto no painel do teu alojamento, normalmente em: "Contas FTP" → "Gerir" → "Detalhes de acesso"

- Abrir o FileZilla, colocas as credenciais e clicas em "Quickconnect"
- Arrastar os ficheiros para o servidor, na estrutura disposta acima

> FTP = trabalhas nos ficheiros
> URL = acedes ao site no navegador

---
### C. Preparar a base de dados
**Verificar compatibilidade para trabalhar:**
- PHP 7.4+ (recomendo PHP 8.1) no servidor local/remoto
- MySQL ou MariaDB a correr
- cURL habilitado no PHP (ou seja, phpinfo() deve mostrar curl)

> O código abaixo assume uma base de dados MySQL chamada pagamentos e uma tabela pagamentos com campos para armazenar as informações do pedido e do estado do pagamento
> Lembra-te de [instalar o mySQL](https://dev.mysql.com/downloads/installer)


**Iniciar conexão**
1. Conectar ao servidor MySQL do cliente
```bash
mysql -u utilizador -p -h host_do_cliente
```
> -u → nome do utilizador
> -p → pede a password
> -h → endereço do servidor (localhost se estiveres no próprio servidor)

2. Criar a base de dados (se ainda não existir)
```bash
CREATE DATABASE IF NOT EXISTS pagamentos
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE pagamentos;
``` 

3. Criar a tabela pagamentos
```bash
-- Usa a base de dados
USE pagamentos;

-- Cria a tabela
CREATE TABLE IF NOT EXISTS pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orderId VARCHAR(50) NOT NULL UNIQUE,       -- identificador único do pedido
    customerName VARCHAR(100) DEFAULT NULL,    -- nome do cliente
    customerEmail VARCHAR(100) DEFAULT NULL,   -- email do cliente
    amount DECIMAL(10,2) NOT NULL,            -- valor a pagar
    paymentMethod VARCHAR(10) NOT NULL,       -- 'mb', 'mbw', 'cc'
    status VARCHAR(20) DEFAULT 'pending',     -- 'pending', 'paid', 'failed'
    entity VARCHAR(10) DEFAULT NULL,          -- entidade MB (apenas para MB)
    reference VARCHAR(20) DEFAULT NULL,       -- referência MB (apenas para  MB)
    dataCriacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    dataConfirmacao TIMESTAMP NULL
);
```

4. Verificar se a tabela foi criada
```bash
SHOW TABLES;
DESCRIBE pagamentos;
```

> Uma conexão **PDO** (PHP Data Objects) é, essencialmente, a **forma de o PHP comunicar de maneira segura e eficiente com uma base de dados** (geralmente MySQL, mas também suporta PostgreSQL, SQLite, etc.)

---
## 2. Criar conta sandbox Easypay - Em nome do cliente
1. Ir à [easypay](https://www.easypay.pt)  
2. Clicar **“Área de Cliente” → “Criar Conta” → “Sandbox” (ou “Conta de Teste”)**  
3. Preencher os dados básicos (empresa, email, NIF)  
4. Depois de entrar no **Dashboard Sandbox**:
   - Ir a **Integrações → API Keys**
   - Criar uma **nova API Key** (tipo: *test / sandbox*)
   - Copiar a chave (exemplo: `sk_test_xxxxxxxxxx`)

---
## 3. Atualizar os ficheiros
**Formulário de Checkout** - página onde o utilizador insere os dados necessários para pagar que são enviados via POST para o checkout.php
index.php
```php
// Action de um <form> define para onde (para que URL ou ficheiro) os dados do formulário serão enviados quando o utilizador clicar em submeter (no caso submit), sendo action o destino e method a forma como os dados são enviados (no caso POST, para serem invisiveis, devido a serem dados sensiveis)
<form action="checkout.php" method="POST"> 

    <label>Nome:</label> 
    // <label> → texto descritivo para o campo do formulário, permite leitores de ecrã anunciar o <label> quando o utilizador foca no input

    <input type="text" name="name" required> // required → torna o campo obrigatório antes de submeter

    <label>Email:</label>
    <input type="email" name="email" required>
    // Podiamos fazer <label for="email">Email:</label> - Aqui, o for="email" liga o label ao input com id="email"
    // Clicar no texto “Email:” coloca automaticamente o cursor no input

    <label>Valor (€):</label>
    <input type="number" name="amount" step="0.01" required>

    <label>Método de Pagamento:</label>
    <select name="method"> // <select name="method"> → cria uma lista de opções de pagamento
        <option value="card">Cartão</option>
        <option value="mbway">MB WAY</option>
        <option value="multibanco">Multibanco</option>
    </select>

    <button type="submit">Pagar</button> // envia os dados para o action definido (checkout.php)
</form>
```


**Formulário de Checkout** - recebe os dados enviados pelo index.php, valida-os e cria o pagamento real na API EasyPay
checkout.php
```php
<?php
require 'config.php'; // estás a incluir o ficheiro config.php no teu script atual, ou seja, tdas as funções, constantes, variáveis e configurações ficam disponíveis no script onde fizeste o require

// Obtém os valores enviados pelo formulário index.php via método POST
$name = $_POST['name'];
$email = $_POST['email'];
$amount = $_POST['amount'];
$method = $_POST['method'];

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { // filter_var() com FILTER_VALIDATE_EMAIL é uma função nativa do PHP para validar endereços de e-mail
    exit("Email inválido");
}

if ($amount < 0) {
    exit("Valor inválido");
}

$paymentData = createPayment($name, $email, $amount, $method);

// Redireciona para a página do pagamento EasyPay
if (isset($paymentData['paymentUrl'])) { // isset() verifica se a chave paymentUrl existe no array $paymentData (é o array que recebeste da API EasyPay depois de chamar createPayment(), ou seja, estás a perguntar se recebeste um URL de pagamento válido)
    header("Location: " . $paymentData['paymentUrl']); // header("Location: ...") envia um cabeçalho HTTP de redirecionamento para o browser
    exit;
} else {
    header("Location: checkout_fail.php?error=" . urlencode($paymentData['error'] ?? 'Erro desconhecido'));
    // Redireciona para checkout_fail.php
    // ?error=... passa uma mensagem de erro como query string como parâmetro GET para a página de destino, ou seja, fica disponível na variável global $_GET do PHP nessa página e visivel no URL
    // urlencode() garante que a mensagem de erro seja segura para colocar na URL
    // $paymentData['error'] ?? 'Erro desconhecido' significa: Se $paymentData['error'] existir, usa-o, caso contrário, usa a string 'Erro desconhecido'
    exit;
}
?>
```
> Aqui assumimos que a resposta da API EasyPay tem um `paymentUrl` para onde o utilizador vai pagar
> Verificar na documentação oficial da API para ver o nome exato do campo, se este existe (no caso da Easy Pay existe)


**Página de Sucesso** - checkout_sucess.php
```php
<?php
echo "<h1>Pagamento concluído!</h1>";
echo "<p>Obrigado pelo seu pagamento.</p>";
?>
```
Esta página será usada como `redirectUrl` na tua API, ou seja, na função createPayment() passas o link para esta página, para onde o utilizador volta após pagar


**Página de Falha** - checkout_fail.php
```php
<?php
$error = isset($_GET['error']) ? urldecode($_GET['error']) : 'Ocorreu um erro no pagamento'; // pega o valor do parâmetro error da URL, caso o parâmetro não existe, retorna a mensagem padrão
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Erro no Pagamento</title>
</head>
<body>
    <h1>Pagamento não concluído</h1>
    <p><?= htmlspecialchars($error) ?></p>
    // Transforma caracteres especiais em HTML seguro, evitando ataques XSS (por exemplo, se alguém tentar enviar <script> na query string, será mostrado como texto e não executado)
    <a href="index.php">Voltar</a> // podemos voltar ao checkout
</body>
</html>
```


**Receber notificações via webhook**
**Endpoint** recebe Easypay sempre que um pagamento Multibanco é confirmado e permite mostrar o estado do pagamento em tempo real (requer base de dados, onde vai ser alterado de `pending` para `paid`). Com o webhook o estado muda no servidor mas o cliente só verá a alteração se recarregar a página. Para feedback em tempo real no navegador tens de adicionar polling AJAX ou SSE
> Repara que o teu `webhook.php` precisa estar acessível pela EasyPay, se estiveres em local, podes usar ngrok para gerar um URL HTTPS temporário

**Webhook** - webhook.php
```php
<?php
require 'config.php';

// EasyPay envia JSON no body e depois...
$input = file_get_contents("php://input"); // lê o conteúdo do JSON
$data = json_decode($input, true); // transforma num array associativo de PHP 

if (isset($data['orderId']) && isset($data['status'])) { // verifica se a easypay enviou "orderId" e "status"
    updatePaymentStatus($data['orderId'], $data['status']); // chamo a função "updatePaymentStatus" com as informações passadas
    http_response_code(200);
    echo "OK";
} else {
    http_response_code(400);
    echo "Dados inválidos"; // ou algum outro erro
}
```
Depois, no painel EasyPay, definir o URL do webhook para https://url_do_website/webhook.php

---
## 4. Fazer o teste real (sandbox)
1. Abrir no browser https://url_do_website/checkout.php 
2. Preencher o formulário (nome, email, método de pagamento)
3. Clicar "Pagar"
4. (Serás redirecionado para a página da Easypay sandbox (interface de teste)). Concluir o pagamento (ou cancelar, se quiseres testar falha). Esperar ser redirecionado para:
   - checkout_success.php → se pago
   - checkout_fail.php → se falhou/cancelado

### Antes de ir para produção:
- Testar pagamento Multibanco → pendente → confirmado
- Testar MB Way → aprovado e rejeitado
- Testar Cartão → sucesso e falha
- Testar checkout_success.php e checkout_fail.php
- Testar webhook ou SSE/AJAX para atualização de estado
- Verificar dados guardados na base de dados (status, orderId, entity, reference)
- Testar acesso não autorizado (alteração de paymentId)
- Validar redirecionamentos HTTPS

---
## 5. Subir para produção
Trocar:
**URL:**
```bash
https://api.test.easypay.pt/... → https://api.easypay.pt/... ou https://api.prod.easypay.pt/...
```
**API Key:**
```bash
sk_test_... → sk_live_...
```
E passas de sandbox → produção real