<?php 
session_start();
require_once '../config/koneksi.php'; 
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<?php
// Mengambil data dari URL menggunakan parameter GET
$mhs_id = isset($_GET['idMhs']) ? htmlspecialchars($_GET['idMhs']) : '';
// Query untuk mengambil data mahasiswa dari tabel inputmhs
if ($mhs_id > 0) {
    $sql_mhs = "SELECT namaMhs, nim, ipk FROM inputmhs WHERE id = ?";
    if ($stmt = $mysqli->prepare($sql_mhs)) {
        $stmt->bind_param("i", $mhs_id);
        $stmt->execute();
        $stmt->bind_result($nama_mhs, $nim_mhs, $ipk_mhs);
        $stmt->fetch();
        $stmt->close();
    } else {
        // Jika query gagal
        $nama_mhs = 'Tidak ada nama';
        $nim_mhs = 'Tidak ada NIM';
        $ipk_mhs = 'Tidak ada IPK';
    }
} else {
    // Jika ID tidak valid
    $nama_mhs = 'Tidak ada nama';
    $nim_mhs = 'Tidak ada NIM';
    $ipk_mhs = 'Tidak ada IPK';
}

// Query untuk ambil data matakuliah
$sql = "SELECT id, matakuliah, sks, kelp, ruangan FROM jwl_matakuliah";
$result = $mysqli->query($sql);

// Query untuk ambil data jwl_mhs
$sql_jwl_mhs = "SELECT id, mhs_id, matakuliah, sks, kelp, ruangan FROM jwl_mhs WHERE mhs_id = $mhs_id";
$jwl_mhs = $mysqli->query($sql_jwl_mhs);

// Ambil kuota SKS dari tabel inputmhs
$kuota_sks = 0;
if ($mhs_id > 0) {
    $kuota_sql = "SELECT sks FROM inputmhs WHERE id = ?";
    if ($kuota_stmt = $mysqli->prepare($kuota_sql)) {
        $kuota_stmt->bind_param("i", $mhs_id);
        $kuota_stmt->execute();
        $kuota_stmt->bind_result($kuota_sks);
        $kuota_stmt->fetch();
        $kuota_stmt->close();
    }
}

// Hitung total SKS yang sudah diambil
$total_sks = 0;
$total_sks_sql = "SELECT SUM(sks) FROM jwl_mhs WHERE mhs_id = ?";
if ($total_sks_stmt = $mysqli->prepare($total_sks_sql)) {
    $total_sks_stmt->bind_param("i", $mhs_id);
    $total_sks_stmt->execute();
    $total_sks_stmt->bind_result($total_sks);
    $total_sks_stmt->fetch();
    $total_sks_stmt->close();
}

// Cek apakah form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $mhs_id = isset($_POST['mhs_id']) ? $_POST['mhs_id'] : '';
    $matakuliah_id = isset($_POST['matakuliah']) ? $_POST['matakuliah'] : '';

    // Pastikan data tidak kosong
    if (!empty($mhs_id) && !empty($matakuliah_id)) {
        // Ambil nama matakuliah berdasarkan ID
        $get_matakuliah_sql = "SELECT matakuliah, sks FROM jwl_matakuliah WHERE id = ?";
        if ($get_matakuliah_stmt = $mysqli->prepare($get_matakuliah_sql)) {
            $get_matakuliah_stmt->bind_param("i", $matakuliah_id);
            $get_matakuliah_stmt->execute();
            $get_matakuliah_stmt->bind_result($nama_matakuliah, $sks_matakuliah);
            $get_matakuliah_stmt->fetch();
            $get_matakuliah_stmt->close();
        }

        // Cek apakah total SKS tidak melebihi kuota
        if (($total_sks + $sks_matakuliah) > $kuota_sks) {
            $sisa_kuota = $kuota_sks - $total_sks;
            $_SESSION['flash_message'] = [
                'type' => 'error',
                'message' => "Kuota SKS Anda sudah terlampaui. Sisa kuota SKS yang bisa diambil: " . $sisa_kuota
            ];
        } else {
            // Cek apakah matakuliah sudah diambil berdasarkan nama
            $check_sql = "SELECT COUNT(*) FROM jwl_mhs WHERE mhs_id = ? AND matakuliah = ?";
            if ($check_stmt = $mysqli->prepare($check_sql)) {
                $check_stmt->bind_param("is", $mhs_id, $nama_matakuliah);
                $check_stmt->execute();
                $check_stmt->bind_result($count);
                $check_stmt->fetch();
                $check_stmt->close();

                if ($count > 0) {
                    $_SESSION['flash_message'] = ['type' => 'error', 'message' => "Anda sudah mengambil matakuliah ini."];
                } else {
                    // Query untuk menyimpan data ke jwl_mhs
                    $sql = "INSERT INTO jwl_mhs (mhs_id, matakuliah, sks, kelp, ruangan) SELECT ?, matakuliah, sks, kelp, ruangan FROM jwl_matakuliah WHERE id = ?";
                    // Persiapkan statement
                    if ($stmt = $mysqli->prepare($sql)) {
                        $stmt->bind_param("si", $mhs_id, $matakuliah_id);
                        // Eksekusi statement
                        if ($stmt->execute()) {
                            $_SESSION['flash_message'] = ['type' => 'success', 'message' => "Matakuliah berhasil disimpan."];

                        // Update tabel inputmhs untuk menyimpan nama matakuliah
                        $update_inputmhs_sql = "UPDATE inputmhs SET matakuliah = CONCAT(IFNULL(matakuliah, ''), IF(CHAR_LENGTH(matakuliah) > 0, ', ', ''), ?) WHERE id = ?";
                        if ($update_stmt = $mysqli->prepare($update_inputmhs_sql)) {
                            $update_stmt->bind_param("si", $nama_matakuliah, $mhs_id);
                            $update_stmt->execute();
                            $update_stmt->close();
                        } else {
                            $_SESSION['flash_message'] = ['type' => 'error', 'message' => "Gagal menyiapkan query untuk memperbarui inputmhs: " . $mysqli->error];
                        }
                        } else {
                            $_SESSION['flash_message'] = ['type' => 'error', 'message' => "Gagal menyimpan data: " . $stmt->error];
                        }
                        $stmt->close();
                    } else {
                        $_SESSION['flash_message'] = ['type' => 'error', 'message' => "Gagal menyiapkan query untuk menyimpan data: " . $mysqli->error];
                    }
                }
            } else {
                $_SESSION['flash_message'] = ['type' => 'error', 'message' => "Gagal menyiapkan query untuk memeriksa matakuliah: " . $mysqli->error];
            }
        }
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => "Data tidak lengkap."];
    }
    // Redirect setelah proses
    header('Location: input-krs.php?idMhs=' . $mhs_id);
    exit;
}

// Hapus data KRS
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && isset($_GET['idMhs'])) {
    $id = (int)$_GET['id'];
    $mhs_id = (int)$_GET['idMhs']; 
    // Query untuk menghapus data dari jwl_mhs
    $delete_query_jwl_mhs = "DELETE FROM jwl_mhs WHERE id = ?";
    $delete_stmt_jwl_mhs = $mysqli->prepare($delete_query_jwl_mhs);
    
    if ($delete_stmt_jwl_mhs) {
        $delete_stmt_jwl_mhs->bind_param("i", $id);
        
        if ($delete_stmt_jwl_mhs->execute()) {
            $_SESSION['flash_message'] = ['type' => 'success', 'message' => 'Matakuliah berhasil dihapus'];

            // Ambil semua nama matakuliah yang tersisa setelah penghapusan
            $matakuliah_sql = "SELECT GROUP_CONCAT(matakuliah SEPARATOR ', ') AS matakuliah_list 
                               FROM jwl_mhs 
                               WHERE mhs_id = ?";
            if ($matakuliah_stmt = $mysqli->prepare($matakuliah_sql)) {
                $matakuliah_stmt->bind_param("i", $mhs_id);
                $matakuliah_stmt->execute();
                $matakuliah_stmt->bind_result($matakuliah_list);
                $matakuliah_stmt->fetch();
                $matakuliah_stmt->close();

                // Simpan matakuliah yang tersisa ke inputmhs
                $insert_inputmhs_sql = "UPDATE inputmhs SET matakuliah = ? WHERE id = ?";
                if ($inputmhs_stmt = $mysqli->prepare($insert_inputmhs_sql)) {
                    $inputmhs_stmt->bind_param("si", $matakuliah_list, $mhs_id);
                    $inputmhs_stmt->execute();
                    $inputmhs_stmt->close();
                }
            }
        } else {
            $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal menghapus data dari jwl_mhs: ' . $delete_stmt_jwl_mhs->error];
        }
        
        $delete_stmt_jwl_mhs->close();
    } else {
        $_SESSION['flash_message'] = ['type' => 'error', 'message' => 'Gagal menyiapkan query untuk menghapus dari jwl_mhs: ' . $mysqli->error];
    }

    // Redirect setelah penghapusan
    header('Location: input-krs.php?action=edit&idMhs=' . $mhs_id); 
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
    <p>Input Data Matakuliah disini!</p>
</div>
<div class="input-container">
<div class="alert alert-info d-flex justify-content-between" role="alert">
    <span>
        <b>Mahasiswa:</b> <?php echo htmlspecialchars($nama_mhs); ?> | 
        <b>NIM:</b> <?php echo htmlspecialchars($nim_mhs); ?> | 
        <b>IPK:</b> <?php echo htmlspecialchars($ipk_mhs); ?>
    </span>
    <a href="../" class="bg-warning p-1 rounded text-dark" style="text-decoration:none;">
        <span>Kembali ke data mahasiswa</span>
    </a>
</div>
<form class="row g-3" method="POST" action="">
<div class="col-md-8">
    <label for="matakuliah" class="form-label">Matakuliah</label>
    <select class="form-control" id="matakuliah" name="matakuliah" required>
        <option value="">Pilih Matakuliah</option>
        <?php
        // Cek apakah ada hasil
        if ($result->num_rows > 0) {
            // Output data dari setiap baris
            while ($row = $result->fetch_assoc()) {
                echo '<option value="' . $row['id'] . '">' . $row['matakuliah'] . ' (' . $row['sks'] . ' SKS)</option>';
            }
        } else {
            echo '<option value="">Tidak ada matakuliah tersedia</option>';
        }
        ?>
    </select>
</div>
  <div class="col-12">
    <input type="hidden" name="mhs_id" value="<?php echo $mhs_id; ?>">
    <button type="submit" class="btn btn-primary w-100">Simpan</button>
  </div>
</form>
<table class="table table-striped table-bordered mt-4">
  <thead class="text-center">
    <tr>
      <th scope="col">No</th>
      <th scope="col">Matakuliah</th>
      <th scope="col">SKS</th>
      <th scope="col">Kelp</th>
      <th scope="col">Ruangan</th>
      <th scope="col">Aksi</th>
    </tr>
  </thead>
  <tbody class="text-center">
    <?php
    $no = 1;
    // Fetch and display each row
    while ($row = $jwl_mhs->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . htmlspecialchars($row['matakuliah']) . "</td>";
        echo "<td>" . htmlspecialchars($row['sks']) . "</td>";
        echo "<td>" . htmlspecialchars($row['kelp'])  . "</td>";
        echo "<td>" . htmlspecialchars($row['ruangan']?? "-") . "</td>";
        echo "<td>
        <button class='btn btn-sm btn-danger' onclick=\"confirmDelete('" . $row['id'] . "', '" . $row['mhs_id'] . "')\"><i class='bi bi-trash-fill'></i> Hapus</button>
    </td>";
    }
    ?>

  </tbody>
  
</table>

</div>
<?php
// Hitung sisa kuota SKS
$sisa_kuota = $kuota_sks - $total_sks;
?>

<p class="text-danger  pt-3 pb-3">
    <b>
    <?php
        echo "Sisa Kuota SKS: " . $sisa_kuota;
    ?>
    </b>
</p>
    </div>

<script>
    //delete confirm
    function confirmDelete(id, mhsId) {
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
            location.href = 'input-krs.php?action=delete&id=' + id + '&idMhs=' + mhsId; 
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