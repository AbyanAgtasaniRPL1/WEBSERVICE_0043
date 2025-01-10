<?php
// Koneksi ke database
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'makanan';  // Sesuaikan dengan nama database Anda

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}


// Daftar tabel dan kolomnya
$tables = [
    'pesanan'        => ['id', 'id_pelanggan', 'tanggal', 'total_harga', 'status'],
    'pembayaran'     => ['id', 'id_pesanan', 'jumlah_bayar', 'tanggal_pembayaran', 'metode_pembayaran'],
    'pelanggan'      => ['id', 'nama', 'email', 'telepon', 'alamat'],
    'menu'           => ['id', 'nama_menu', 'deskripsi', 'harga', 'stok'],
    'detail_pesanan' => ['id', 'id_pesanan', 'id_menu', 'jumlah', 'subtotal'],
];

// Pilih tabel aktif berdasarkan parameter URL
$table = $_GET['table'] ?? 'menu';
if (!isset($tables[$table])) {
    die("Tabel tidak valid.");
}
// Kolom tabel aktif
$columns = $tables[$table];

// Fungsi untuk menampilkan data tabel
function showTable($conn, $table, $columns) {
    $query = "SELECT * FROM $table";
    $result = $conn->query($query);

    echo "<h2>Data $table</h2>";
    echo "<a href='?table=$table&action=add' class='btn btn-add'>Tambah Data</a><br><br>";

    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='10' cellspacing='0' style='color: black; background-color: #F8F8FF;'>";
        echo "<tr>";
        foreach ($columns as $col) {
            echo "<th>" . ucfirst($col) . "</th>";
        }
        echo "<th>Aksi</th></tr>";

        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach ($columns as $col) {
                echo "<td>{$row[$col]}</td>";
            }
            echo "<td>
            <a href='?table=$table&action=edit&id={$row['id']}' style='color: white; background-color: green; padding: 5px 10px; border-radius: 4px; text-decoration: none;'>Edit</a> |
            <a href='?table=$table&action=delete&id={$row['id']}' onclick='return confirm(\"Yakin ingin menghapus data ini?\")' style='color: white; background-color: red; padding: 5px 10px; border-radius: 4px; text-decoration: none;'>Hapus</a>
        </td>";
echo "</tr>";

        }
        echo "</table>";
    } else {
        echo "<p>Tidak ada data.</p>";
    }
}

// Fungsi untuk menampilkan form tambah data
function addDataForm($table, $columns) {
    echo "<h2>Tambah Data $table</h2>";
    echo "<form method='post'>";
    foreach ($columns as $col) {
        if ($col == 'id') continue; // Skip kolom otomatis
        echo "<label>" . ucfirst($col) . ":</label><input type='text' name='$col' required><br>";
    }
    echo "<button type='submit' name='submit_add'>Simpan</button>";
    echo "</form>";
}

// Fungsi untuk menampilkan form edit data
function editDataForm($conn, $table, $columns, $id) {
    $query = "SELECT * FROM $table WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();

    echo "<h2>Edit Data $table</h2>";
    echo "<form method='post'>";
    foreach ($columns as $col) {
        if ($col == 'id') {
            echo "<input type='hidden' name='$col' value='{$result[$col]}'>";
        } else {
            echo "<label>" . ucfirst($col) . ":</label><input type='text' name='$col' value='{$result[$col]}' required><br>";
        }
    }
    echo "<button type='submit' name='submit_edit'>Update</button>";
    echo "</form>";
}

// Logika CRUD
if (isset($_GET['action'])) {
    $action = $_GET['action'];

    // Add new data
    if ($action == 'add') {
        addDataForm($table, $columns);
        if (isset($_POST['submit_add'])) {
            $fields = array_filter($columns, fn($col) => $col != 'id');
            $values = array_map(fn($col) => $_POST[$col], $fields);

            $query = "INSERT INTO $table (" . implode(',', $fields) . ") VALUES (" . str_repeat('?,', count($fields) - 1) . "?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param(str_repeat('s', count($fields)), ...$values);
            $stmt->execute();
            header("Location: ?table=$table");
        }
    }

    // Edit data
    elseif ($action == 'edit' && isset($_GET['id'])) {
        $id = $_GET['id'];
        editDataForm($conn, $table, $columns, $id);
        if (isset($_POST['submit_edit'])) {
            $fields = array_filter($columns, fn($col) => $col != 'id');
            $values = array_map(fn($col) => $_POST[$col], $fields);
            $values[] = $_GET['id']; // Add the ID to the values

            $query = "UPDATE $table SET " . implode(' = ?, ', $fields) . " = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param(str_repeat('s', count($fields)) . 'i', ...$values);
            $stmt->execute();
            header("Location: ?table=$table");
        }
    }

    // Delete data
    elseif ($action == 'delete' && isset($_GET['id'])) {
        $id = $_GET['id'];
        $query = "DELETE FROM $table WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        header("Location: ?table=$table");
    }
} else {
    echo '
        <!-- Navbar at the top -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Kantin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            background-color:rgb(201, 12, 198);
            color: white;
            padding: 10px 0;
            text-align: center;
        }
        nav {
            background-color: #F8F8FF;
            padding: 10px 0;
            text-align: center;
        }
        nav a {
            color: black;
            text-decoration: none;
            padding: 10px 20px;
            margin: 0 10px;
            border-radius: 5px;
            background-color: #F8F8FF;
        }
        nav a:hover {
            background-color: #E6E6FA;
        }
        .container {
            display: flex;
            flex-direction: column;
            padding: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
    </style>
</head>
<body>

<header>
    <h1>Admin - Kantin</h1>
</header>

<nav>
    <a href="?table=pesanan">Pesanan</a>
    <a href="?table=pembayaran">Pembayaran</a>
    <a href="?table=pelanggan">Pelanggan</a>
    <a href="?table=menu">Menu</a>
    <a href="?table=detail_pesanan">Detail Pesanan</a>
</nav>

<div class="container">
    <!-- Content will be displayed here -->
</div>

</body>
</html>
    ';
    showTable($conn, $table, $columns);
    echo '    </div>
    </div>';
}

$conn->close();
?>


<link rel="stylesheet" href="style.css">