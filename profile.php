<?php
session_start();
include 'koneksi.php'; // Pastikan koneksi database sudah benar

// Mengambil username dari session
$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Guest';

// Mengambil jumlah tugas selesai dan tertunda
$query_completed = "SELECT COUNT(*) as completed FROM tasks WHERE username='$username' AND done=1";
$result_completed = mysqli_query($conn, $query_completed);
$completed_tasks = mysqli_fetch_assoc($result_completed)['completed'];

$query_pending = "SELECT COUNT(*) as pending FROM tasks WHERE username='$username' AND done=0";
$result_pending = mysqli_query($conn, $query_pending);
$pending_tasks = mysqli_fetch_assoc($result_pending)['pending'];

// Generate profile icon berdasarkan huruf pertama dari username
$profile_icon = strtoupper($username[0]);

// Proses upload foto profil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['profile_photo']) && $_FILES['profile_photo']['error'] == 0) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["profile_photo"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Memastikan file yang diupload adalah gambar
        $check = getimagesize($_FILES["profile_photo"]["tmp_name"]);
        if ($check !== false) {
            // Validasi tipe gambar
            if ($imageFileType == "jpg" || $imageFileType == "jpeg" || $imageFileType == "png" || $imageFileType == "gif") {
                $new_target_file = $target_dir . $username . '.jpg'; // Ganti nama file sesuai username
                if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $new_target_file)) {
                    echo "Foto profil berhasil diupload.";
                } else {
                    echo "Terjadi kesalahan saat mengupload foto.";
                }
            } else {
                echo "Hanya file gambar (JPG, JPEG, PNG, GIF) yang diperbolehkan.";
            }
        } else {
            echo "File yang diupload bukan gambar.";
        }
    }

    // Proses hapus foto profil
    if (isset($_POST['delete_photo'])) {
        $photo_path = 'uploads/' . $username . '.jpg'; // Path foto berdasarkan username
        if (file_exists($photo_path)) {
            unlink($photo_path); // Menghapus foto
            echo "Foto profil berhasil dihapus.";
        }
    }

    // Redirect untuk memuat ulang halaman
    header('Location: profile.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Profile Saya</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> <!-- Menambahkan Chart.js -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Gaya untuk form profile */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            width: 90%;
            max-width: 600px;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 5px;
        }

        input[type="text"], input[type="date"], textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        /* Gaya untuk preview foto */
        .photo-preview {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #f0f0f0;
            margin-top: 10px;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
        }

        .photo-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 50px;
            font-weight: bold;
        }

        .camera-icon {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background-color: #fff;
            border-radius: 50%;
            padding: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            cursor: pointer;
            font-size: 24px;
        }

        /* Gaya untuk menu konteks */
        .context-menu {
            display: none;
            position: absolute;
            top: 0;
            left: 0;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .context-menu li {
            padding: 10px;
            cursor: pointer;
            list-style: none;
        }

        .context-menu li:hover {
            background-color: #007bff;
            color: white;
        }

        /* Gaya untuk kotak tugas */
        .tasks-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .task-box {
            width: 45%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border-radius: 10px;
            text-align: center;
        }

        .task-box .count {
            font-size: 24px;
            font-weight: bold;
        }

        .task-box .label {
            margin-top: 10px;
            font-size: 14px;
        }

        /* Gaya untuk chart */
        .chart-container {
            width: 100%;
            height: 300px;
            margin-top: 30px;
        }
        .nav-icons {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-top: 20px;
    }
        .nav-icons a {
        font-size: 24px;
        color: #007bff;
        text-decoration: none;
    }

    .nav-icons a:hover {
        color: #0056b3;
    }
    /* Menambahkan styling pada ikon dan teks di menu konteks */
.context-menu li {
    padding: 10px;
    cursor: pointer;
    list-style: none;
    display: flex;
    align-items: center;
    font-size: 16px;
}

.context-menu li i {
    margin-right: 8px; /* Memberi jarak antara ikon dan teks */
    font-size: 18px; /* Ukuran ikon */
}

.context-menu li:hover {
    background-color: #007bff;
    color: white;
}

.context-menu li i:hover {
    color: white;
}

    </style>
</head>
<body>

    <div class="container">
        <h2>Profile Saya</h2>
        <form id="profile-form" method="POST" enctype="multipart/form-data">
            <!-- Foto Profil -->
            <div class="form-group">
                <label for="photo">Foto Profil</label>
                <div id="photo-preview" class="photo-preview">
                    <?php
                    $photo_path = 'uploads/' . $username . '.jpg'; // Path foto berdasarkan username
                    if (file_exists($photo_path)) {
                        echo '<img src="' . $photo_path . '" alt="Foto Profil">';
                    } else {
                        echo '<div class="profile-icon">' . $profile_icon . '</div>';
                    }
                    ?>
                    <!-- Ikon Kamera untuk upload -->
                    <div class="camera-icon" id="camera-icon" oncontextmenu="showContextMenu(event)"><i class="fas fa-camera"></i></div>
                </div>
                <input type="file" name="profile_photo" accept="image/*" id="fileInput" style="display:none;" onchange="this.form.submit()">
            </div>

            <!-- Nama -->
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" placeholder="Nama Anda" value="<?php echo htmlspecialchars($username); ?>" required>
            </div>

            <!-- Tugas Selesai dan Tertunda -->
            <div class="tasks-container">
                <div class="task-box">
                    <div class="count"><?php echo $completed_tasks; ?></div>
                    <div class="label">Tugas Selesai</div>
                </div>
                <div class="task-box">
                    <div class="count"><?php echo $pending_tasks; ?></div>
                    <div class="label">Tugas Tertunda</div>
                </div>
            </div>
        </form>

        <!-- Menambahkan canvas untuk pie chart -->
        <div class="chart-container">
            <canvas id="taskChart"></canvas>
        </div>
    </div>

    <!-- Menu Konteks -->
    <ul class="context-menu" id="context-menu">
    <li onclick="addProfilePhoto()">
        <i class="fas fa-upload"></i> Tambah Foto Profil
    </li>
    <li onclick="changeProfilePhoto()">
        <i class="fas fa-sync-alt"></i> Ganti Foto Profil
    </li>
    <li onclick="deleteProfilePhoto()">
        <i class="fas fa-trash-alt"></i> Hapus Foto Profil
    </li>
</ul>
    <script>
        // Menangani chart pie dengan Chart.js
        var ctx = document.getElementById('taskChart').getContext('2d');
        var taskChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['Tugas Selesai', 'Tugas Tertunda'],
                datasets: [{
                    label: 'Jumlah Tugas',
                    data: [<?php echo $completed_tasks; ?>, <?php echo $pending_tasks; ?>],
                    backgroundColor: ['#28a745', '#0000ff'],
                    borderColor: ['#ffffff', '#ffffff'],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.label + ': ' + tooltipItem.raw;
                            }
                        }
                    }
                }
            }
        });

        // Fungsi untuk membuka input file
        function openFileInput() {
            document.getElementById('fileInput').click();
        }

        // Fungsi untuk menampilkan menu konteks
        function showContextMenu(event) {
            event.preventDefault();
            var menu = document.getElementById('context-menu');
            menu.style.display = 'block';
            menu.style.left = event.pageX + 'px';
            menu.style.top = event.pageY + 'px';
        }

        // Fungsi untuk menambah foto profil
        function addProfilePhoto() {
            document.getElementById('fileInput').click();
            closeContextMenu();
        }

        // Fungsi untuk mengganti foto profil
        function changeProfilePhoto() {
            document.getElementById('fileInput').click();
            closeContextMenu();
        }

        // Fungsi untuk menghapus foto profil
        function deleteProfilePhoto() {
            if (confirm('Apakah Anda yakin ingin menghapus foto profil?')) {
                var form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = '<input type="hidden" name="delete_photo" value="1">';
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Fungsi untuk menutup menu konteks
        function closeContextMenu() {
            document.getElementById('context-menu').style.display = 'none';
        }

        // Menutup menu konteks jika mengklik di luar menu
        window.addEventListener('click', function(event) {
            if (!event.target.closest('.context-menu') && !event.target.closest('#camera-icon')) {
                closeContextMenu();
            }
        });
    </script>

</body>
<div class="nav-icons">
    <a href="todo.php" title="Tasks"><i class="fas fa-tasks"></i></a>
    <a href="kalender.php" title="Calendar"><i class="fas fa-calendar-alt"></i></a>
    <a href="profile.php" title="Profile"><i class="fas fa-user-circle"></i></a>
 </div>
</html>
