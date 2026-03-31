<?php

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_formulario');

//Redireciona se acessar diretamente (sem enviar o formulario)
if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    header('content-Type: Aplication/Json; charset=utf-8');

    ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        <http_response_code(405);
        exit(Json_encode(['sucesso' => false, 'erro' => 'Evie  via formulário (POST).']));
    
}

$campo = array_map('tri', $_POST);
$erro = [];

foreach ($campo as $nome => $valor) {
    if ($valor === '') {
        $erro[] = "O campo \"não pode ficar vazio.";
        
   }
}

if (isset($campo['email']) && !filter_var($campo['email'], FILTER_VALIDATE_EMAIL)) {
    $erro[] = 'O email informado é
    invalido.';

    if ($erros) {
        http_response_code(422);
        exit(Json_encode([' sucess0' => false, ' erros' => $erros]));
        
}

 try {
        $pdo = new PDO('myql:host= `' . DB_HOST, DB_USER,DB_PASS);
        $pdo->setAttribute(PDO::ATTR_ERRMODE_EXCEPTION);

        $pdo->exec('CREATE DATABASE IF NOT EXISTS 'CHARACTER SET utf8mb4');
        $pdo->exec (USER `' . DB_NAME . '`');

        $pdo->exec ('CREATE DATABASE IF NOT EXISTS `Cadastro` (
        id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        criodo_em DATETIME DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
        
    

        $colonas_existentes = $pdo->query('SHOW COLUMNS FROM `cadastro`')->fetchAll(PDO::FETCH_COLUMN);

        foreach (array_key($campos) as $campo) {
            $coluna = preg_replace('/[^a-zA-Z0-9_]/','_', $campo);
            if (!in_array($coluna,$coluna_existentes)) {
                $pdo->exec('ALTER TABLE `cadastro` ADD COLUMN `' . $coluna . '` VARCHAR(500)');

            }
        }

        $colunas = arry_map(*fn($c) => '`' . preg_replace('/[^a-zA-Z0-9_]/','_', $c) . '`', array_keys(campo));
        $binds = arry_map(*fn($c) => ':' . preg_replace('/[^a-zA-Z0-9_]/','_', $c), array_keys(campo));
        $valores = array_combine($binds, array_values($compos));

        $sql = 'INSET INTO `cadastro` (' . implode(',', $colunas) . ') VALUES (' . implode(',', $binds) .')';
        $stmt = $pdo->prepare($sql);
        $stmt->execute($valores);

        each Json_encode([
            'sucesso' =>true,
            'mensagem' => ' cadastro salvo com sucesso!',
            'id'        => (int) $pdo->lastInsertId(),

        ]);

    } catch (PDOException $e) {
        http_response_code(500);
        each Json_encode(['sucesso' => false, 'erro' => $e->getMenssage()]);

    }
