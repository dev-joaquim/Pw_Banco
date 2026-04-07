<?php 

// CONFIGURAÇÃO
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_cadastro');

// PASSO 1 - Responde sempre em JSON
header('Content-Type: application/json; charset=utf-8');

// PASSO 2 - Garante que veio de um formulário
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['sucesso' => false, 'erro' => 'Envie os dados via formulario (POST).']));
}

// PASSO 3 - Lê os campos e valida
$campos = array_map('trim', $_POST); 
$erros = [];

foreach ($campos as $nome => $valor) {
    if ($valor === '') {
        $erros[] = "O campo \"$nome\" nao pode ficar vazio.";
    }
}

// Validação específica de e-mail (se o campo existir)
if (isset($campos['email']) && !filter_var($campos['email'], FILTER_VALIDATE_EMAIL)){
    $erros[] = 'Email informado é invalido.';
}

if (!empty($erros)){
    http_response_code(422);
    exit(json_encode(['sucesso' => false, 'erros' => $erros]));
}

// PASSO 4 - Conecta ao MYSQL e cria o banco
try {
    $pdo = new PDO('mysql:host=' . DB_HOST, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ERRMODE_EXCEPTION);
    
    // Cria o banco se não existir
    $pdo->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4');
    $pdo->exec('USE `' . DB_NAME . '`');
    
    // PASSO 5 - Cria a tabela se não existir 
    $pdo->exec('CREATE TABLE IF NOT EXISTS `cadastros` (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
        criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');

    // PASSO 6 - Adiciona colunas novas automaticamente
    $colunas_query = $pdo->query('SHOW COLUMNS FROM `cadastros`');
    $colunas_existentes = $colunas_query->fetchAll(PDO::FETCH_COLUMN);

    foreach ($campos as $campo => $valor) {
        $coluna = preg_replace('/[^a-zA-Z0-9_]/', '_', $campo);
        if (!in_array($coluna, $colunas_existentes)){
            // Corrigido: falta de espaço e erro de digitação 'COLUMMN'
            $pdo->exec('ALTER TABLE `cadastros` ADD COLUMN `' . $coluna . '` VARCHAR(500)');
        }
    }

    // PASSO 7 — Salva os dados no banco
    $colunas = array_map(fn($c) => '`' . preg_replace('/[^a-zA-Z0-9_]/', '_', $c) . '`', array_keys($campos));
    $binds   = array_map(fn($c) => ':' . preg_replace('/[^a-zA-Z0-9_]/', '_', $c), array_keys($campos));
    
    // Prepara os valores para o bindParam/execute
    $valores_finais = [];
    foreach ($campos as $chave => $valor) {
        $nome_bind = preg_replace('/[^a-zA-Z0-9_]/', '_', $chave);
        $valores_finais[':' . $nome_bind] = $valor;
    }

    $sql  = 'INSERT INTO `cadastros` (' . implode(', ', $colunas) . ') VALUES (' . implode(', ', $binds) . ')';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores_finais);

    // PASSO 8 — Retorna sucesso
    echo json_encode([
        'sucesso'  => true,
        'mensagem' => 'Cadastro salvo com sucesso!',
        'id'       => (int) $pdo->lastInsertId(),
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['sucesso' => false, 'erro' => $e->getMessage()]);
