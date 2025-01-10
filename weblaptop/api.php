<?php
// Koneksi ke database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "laptop_sales"; // Ganti dengan nama database Anda

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fungsi untuk mendapatkan data dari input JSON
function getInputData() {
    return json_decode(file_get_contents('php://input'), true);
}

// Menentukan aksi berdasarkan metode request
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        // Untuk mengambil data laptop (dengan atau tanpa ID)
        $table = isset($_GET['table']) ? $_GET['table'] : ''; // Nama tabel dari query string
        if ($table == 'laptops') {
            if (isset($_GET['id'])) {
                // Mengambil laptop berdasarkan ID
                $id = $_GET['id'];
                $stmt = $conn->prepare("SELECT * FROM laptops WHERE laptop_id = ?");
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $result = $stmt->get_result();
                $data = $result->fetch_assoc();
                echo json_encode($data);
            } else {
                // Mengambil semua laptop
                $result = $conn->query("SELECT * FROM laptops");
                $data = [];
                while ($row = $result->fetch_assoc()) {
                    $data[] = $row;
                }
                echo json_encode($data);
            }
        } else {
            echo json_encode(["error" => "No valid table specified"]);
        }
        break;

    case 'POST':
        // Menambahkan data laptop baru
        $table = isset($_GET['table']) ? $_GET['table'] : '';
        $data = getInputData();
        
        if ($table == 'laptops' && !empty($data)) {
            $brand = $data['brand'];
            $model = $data['model'];
            $specifications = $data['specifications'];
            $price = $data['price'];
            $stock = $data['stock'];

            $stmt = $conn->prepare("INSERT INTO laptops (brand, model, specifications, price, stock) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssdi", $brand, $model, $specifications, $price, $stock);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Data added successfully"]);
            } else {
                echo json_encode(["error" => "Failed to add data"]);
            }
        } else {
            echo json_encode(["error" => "Invalid input data or table"]);
        }
        break;

    case 'PUT':
        // Memperbarui data laptop berdasarkan ID
        $table = isset($_GET['table']) ? $_GET['table'] : '';
        $data = getInputData();

        if ($table == 'laptops' && !empty($data) && isset($data['laptop_id'])) {
            $id = $data['laptop_id'];
            $brand = $data['brand'];
            $model = $data['model'];
            $specifications = $data['specifications'];
            $price = $data['price'];
            $stock = $data['stock'];

            $stmt = $conn->prepare("UPDATE laptops SET brand = ?, model = ?, specifications = ?, price = ?, stock = ? WHERE laptop_id = ?");
            $stmt->bind_param("ssssii", $brand, $model, $specifications, $price, $stock, $id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Data updated successfully"]);
            } else {
                echo json_encode(["error" => "Failed to update data"]);
            }
        } else {
            echo json_encode(["error" => "Invalid input data or table"]);
        }
        break;

    case 'DELETE':
        // Menghapus data laptop berdasarkan ID
        $table = isset($_GET['table']) ? $_GET['table'] : '';
        $data = getInputData();

        if ($table == 'laptops' && !empty($data) && isset($data['laptop_id'])) {
            $id = $data['laptop_id'];

            $stmt = $conn->prepare("DELETE FROM laptops WHERE laptop_id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                echo json_encode(["message" => "Data deleted successfully"]);
            } else {
                echo json_encode(["error" => "Failed to delete data"]);
            }
        } else {
            echo json_encode(["error" => "Invalid input data or table"]);
        }
        break;

    default:
        echo json_encode(["error" => "Invalid request method"]);
        break;
}

$conn->close();
?>
