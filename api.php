<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require __DIR__ . '/vendor/autoload.php';

use Tqdev\PhpCrudApi\Api;
use Tqdev\PhpCrudApi\Config;
use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\ServerRequest;
use Nyholm\Psr7\Response;

// Buat objek konfigurasi
$config = new Config([
    'driver' => 'mysql',
    'address' => 'localhost',
    'port' => '3306',
    'username' => 'root',
    'password' => '',  // Ganti dengan password MySQL Anda jika ada
    'database' => 'makanan',
]);

// Buat objek API
$api = new Api($config);

// Buat objek permintaan PSR-7
$factory = new Psr17Factory();

// Membuat objek permintaan dari variabel global PHP
$request = new ServerRequest(
    $_SERVER['REQUEST_METHOD'], 
    $_SERVER['REQUEST_URI'],
    [], 
    null, 
    '1.1',
    $_SERVER
);

// Tambahkan parameter dari query string
if (isset($_GET)) {
    $request = $request->withQueryParams($_GET);
}

// Tambahkan parameter dari body (jika ada)
if ($_SERVER['REQUEST_METHOD'] === 'POST' || $_SERVER['REQUEST_METHOD'] === 'PUT') {
    $body = file_get_contents('php://input');
    $parsedBody = json_decode($body, true);
    $request = $request->withParsedBody($parsedBody);
}

// Tangani permintaan dan keluarkan respons
$response = $api->handle($request);
http_response_code($response->getStatusCode());
foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header("$name: $value");
    }
}
echo $response->getBody();
