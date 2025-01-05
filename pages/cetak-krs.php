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
    <h2>Kartu Rencana Studi</h2>
    <p>Lihat jadwal matakuliah yang telah diinputkan disini!</p>
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
<table class="table table-striped table-bordered mt-4">
  <thead class="text-center">
    <tr>
      <th scope="col">No</th>
      <th scope="col">Matakuliah</th>
      <th scope="col">SKS</th>
      <th scope="col">Kelp</th>
      <th scope="col">Ruangan</th>
    </tr>
  </thead>
  <tbody class="text-center">
    <?php
    $no = 1;
    $total_sks = 0; // Inisialisasi total SKS

    // Fetch and display each row
    while ($row = $jwl_mhs->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $no++ . "</td>";
        echo "<td>" . htmlspecialchars($row['matakuliah']) . "</td>";
        echo "<td>" . htmlspecialchars($row['sks']) . "</td>";
        echo "<td>" . htmlspecialchars($row['kelp'])  . "</td>";
        echo "<td>" . htmlspecialchars($row['ruangan'] ?? "-") . "</td>";
        echo "</tr>";

        // Tambahkan SKS ke total
        $total_sks += (int)$row['sks']; 
    }
    ?>

    <?php if ($no > 1): // Cek apakah ada data ?>
        <tr>
            <td colspan="2"><strong>Total SKS</strong></td>
            <td><strong><?php echo $total_sks; ?></strong></td>
            <td colspan="2"></td>
        </tr>
    <?php endif; ?>
</tbody>
</table>
<button class="bg-success mb-3 p-1 text-white border-0 rounded-1" onclick="window.location.href='cetak-pdf.php?idMhs=<?php echo $mhs_id; ?>'">
    Cetak PDF
</button>
</div>
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