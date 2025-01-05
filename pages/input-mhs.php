<?php
// Query untuk ambil data inputmhs
$sql = "select  id, namaMhs, nim, ipk, sks, matakuliah from inputmhs";
$result = $mysqli->query($sql);

// Proses penyimpanan atau update data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari POST
    $namaMhs = $_POST['nama-mhs'] ?? null;
    $nim = $_POST['nim-mhs'] ?? null;
    $ipk = $_POST['ipk-mhs'] ?? null;

    // Validasi data
    if (empty($namaMhs) || empty($ipk) || empty($nim)) {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Harap isi semua kolom!'];
    } else {
        // Tentukan nilai SKS berdasarkan IPK
        $sks = (float)$ipk < 3 ? 20 : 24;

        // Pastikan NIM unik
        $query = "SELECT COUNT(*) as count FROM inputmhs WHERE nim = ?";
        $stmt = $mysqli->prepare($query);
        if ($stmt) {
            $stmt->bind_param("s", $nim);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();

            if ($row['count'] > 0) {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => "Mahasiswa dengan NIM: " . htmlspecialchars($nim) . " sudah diinput!"];
            } else {
                // Proses insert data ke database
                $sql = "INSERT INTO inputmhs (namaMhs, nim, ipk, sks) VALUES (?, ?, ?, ?)";
                $stmt = $mysqli->prepare($sql);
                if ($stmt) {
                    // Mengikat parameter termasuk SKS
                    $stmt->bind_param("sssi", $namaMhs, $nim, $ipk, $sks);
                    $result = $stmt->execute();
                    if ($result) {
                        $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Data mahasiswa berhasil diinput'];
                    } else {
                        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal menginput data mahasiswa: ' . $mysqli->error];
                    }
                } else {
                    $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal menyiapkan query untuk insert'];
                }
            }
            $stmt->close(); // Tutup statement
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal menyiapkan query untuk cek NIM'];
        }
    }

    // Redirect setelah proses
    header('Location: index.php'); // Ganti dengan halaman yang sesuai
    exit;
}

// Hapus data mahasiswa
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    // Hapus data dari tabel jwl_mhs yang terkait dengan mhs_id
    $delete_jwl_query = "DELETE FROM jwl_mhs WHERE mhs_id = ?";
    $delete_jwl_stmt = $mysqli->prepare($delete_jwl_query);

    if ($delete_jwl_stmt) {
        $delete_jwl_stmt->bind_param("i", $id);
        $delete_jwl_stmt->execute();
        $delete_jwl_stmt->close();
    }

    // Hapus data dari tabel inputmhs
    $delete_query = "DELETE FROM inputmhs WHERE id = ?";
    $delete_stmt = $mysqli->prepare($delete_query);

    if ($delete_stmt) {
        $delete_stmt->bind_param("i", $id);

        if ($delete_stmt->execute()) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Data berhasil dihapus'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal menghapus data: ' . $delete_stmt->error];
        }

        $delete_stmt->close();
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal menyiapkan query: ' . $mysqli->error];
    }

    // Redirect ke halaman input-mhs.php
    header('Location: index.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Input Kartu Rencana Studi (KRS)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="../assets/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body>
    <div class="main-container container-lg shadow rounded">
<div class="container-judul d-flex justify-content-center flex-column align-items-center m-3">
    <h2>Sistem Input Kartu Rencana Studi (KRS)</h2>
    <p>Input Data Mahasiswa disini!</p>
</div>
<div class="input-container">
<form class="row g-3" method="POST" action="">
  <div class="col-md-4">
    <label for="nama-mhs" class="form-label">Nama Mahasiswa</label>
    <input type="text" class="form-control" id="nama-mhs" name="nama-mhs" placeholder="Masukkan Nama Mahasiswa" required>
  </div>
  <div class="col-md-4">
    <label for="nim-mhs" class="form-label">NIM</label>
    <input type="text" class="form-control" id="nim-mhs" name="nim-mhs" placeholder="Masukkan NIM" required>
  </div>
  <div class="col-md-4">
    <label for="ipk-mhs" class="form-label">IPK</label>
    <input type="text" class="form-control" id="ipk-mhs" name="ipk-mhs" placeholder="Masukkan IPK" required>
  </div>

  <div class="col-12">
    <button type="submit" class="btn btn-primary w-100">Input Mahasiswa</button>
  </div>
</form>
<table class="table table-striped table-bordered mt-4">
  <thead class="text-center">
    <tr>
      <th scope="col">No</th>
      <th scope="col">Nama Mahasiswa</th>
      <th scope="col">IPK</th>
      <th scope="col">SKS Maksimal</th>
      <th scope="col">Matkul yang Diambil</th> <!-- Batasi lebar kolom -->
      <th scope="col">Aksi</th>
    </tr>
  </thead>
  <tbody class="text-center">
    <?php
    $no = 1;
    // Fetch and display each row
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . htmlspecialchars($row['namaMhs']) . "</td>";
        echo "<td>" . htmlspecialchars($row['ipk']) . "</td>";
        echo "<td>" . htmlspecialchars($row['sks'])  . "</td>";
        echo "<td style='max-width: 400px;'>" . htmlspecialchars($row['matakuliah'] ?? "-") . "</td>"; 
        echo "<td>
            <button class='btn btn-sm btn-success' onclick=\"location.href='pages/input-krs.php?action=edit&idMhs=" . $row['id'] . "'\"><i class='bi bi-pencil-square'></i> Edit</button>
            <button class='btn btn-sm btn-danger' onclick=\"confirmDelete('" . $row['id'] . "')\"><i class='bi bi-trash-fill'></i> Hapus</button>
            <button class='btn btn-sm btn-secondary' onclick=\"location.href='pages/cetak-krs.php?action=view&idMhs=" . $row['id'] . "'\"><i class='bi bi-eye'></i> Lihat</button>
        </td>";
        echo "</tr>";
    }
    ?>
  </tbody>
</table>
</div>
    </div>

<script>
    //delete confirm
    function confirmDelete(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Anda tidak akan dapat mengembalikan ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Jika pengguna mengonfirmasi, arahkan ke URL hapus
                location.href = 'index.php?action=delete&id=' + id;
            }
        });
    }

    <?php
if (isset($_SESSION['flash_message'])) {
    $type = $_SESSION['flash_message']['type'];
    $message = $_SESSION['flash_message']['message'];
    
    $buttonColor = '#3085d6';
    if ($type === 'success') {
        $buttonColor = '#28a745';
    } elseif ($type === 'error') {
        $buttonColor = '#dc3545'; 
    } elseif ($type === 'warning') {
        $buttonColor = '#ffc107'; 
    }
    echo "
    Swal.fire({
        title: '" . ucfirst($type) . "',
        text: '$message',
        icon: '$type',
        confirmButtonColor: '$buttonColor'
    });
    ";

    unset($_SESSION['flash_message']);
}
?>
</script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>