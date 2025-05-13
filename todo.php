<?php
session_start();
include 'koneksi.php'; // Pastikan koneksi database sudah benar

function loggedin() {
    return isset($_SESSION['username']);
}

if (!loggedin()) {
    header("location:login.php");
    exit();
}

$username = $_SESSION['username'];

// Menambahkan tugas baru
if (isset($_POST['add_task'])) {
    $task = mysqli_real_escape_string($conn, $_POST['task']); // Melindungi dari SQL Injection
    if (!empty($task)) {
        $query = "INSERT INTO tasks (username, task, done) VALUES ('$username', '$task', 0)";
        if (mysqli_query($conn, $query)) {
            // Redirect ke halaman untuk memperbarui daftar tugas
            header('Location: todo.php');
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}

// Menyimpan status tugas (checked/unchecked)
if (isset($_POST['save_tasks'])) {
    $task_status = isset($_POST['task_status']) ? $_POST['task_status'] : [];
    $query = "SELECT taskid FROM tasks WHERE username='$username'";
    $result = mysqli_query($conn, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $taskid = $row['taskid'];
        $done = isset($task_status[$taskid]) ? 1 : 0;
        $update_query = "UPDATE tasks SET done = $done WHERE taskid = $taskid";
        mysqli_query($conn, $update_query);
    }
}

// Menghapus tugas
if (isset($_GET['delete_task'])) {
    $taskid = $_GET['taskid'];
    $query = "DELETE FROM tasks WHERE taskid = $taskid";
    mysqli_query($conn, $query);
}

// Pencarian
$search_keyword = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Mengambil daftar tugas berdasarkan pencarian
$query = "SELECT * FROM tasks WHERE username='$username' AND task LIKE '%$search_keyword%'";
$result = mysqli_query($conn, $query);

// Mengambil jumlah tugas selesai dan tertunda
$query_completed = "SELECT COUNT(*) as completed FROM tasks WHERE username='$username' AND done=1";
$result_completed = mysqli_query($conn, $query_completed);
$completed_tasks = mysqli_fetch_assoc($result_completed)['completed'];

$query_pending = "SELECT COUNT(*) as pending FROM tasks WHERE username='$username' AND done=0";
$result_pending = mysqli_query($conn, $query_pending);
$pending_tasks = mysqli_fetch_assoc($result_pending)['pending'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* General Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f7fc;
            color: #333;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 30px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        header {
            text-align: center;
            margin-bottom: 20px;
        }

        header h1 {
            font-size: 24px;
            color: #333;
        }

        header p {
            font-size: 14px;
            color: #777;
        }

        nav {
            text-align: center;
            margin-bottom: 20px;
        }

        nav a {
            color: #007bff;
            text-decoration: none;
            margin: 0 10px;
            font-size: 14px;
        }

        nav a:hover {
            text-decoration: underline;
        }

        .todo-section {
            margin-top: 20px;
        }

        h2 {
            font-size: 20px;
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }

        .add-task-form {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .add-task-form input {
            width: 60%;
            padding: 10px;
            font-size: 16px;
            border: 2px solid #ddd;
            border-radius: 4px;
            margin-right: 10px;
        }

        .add-task-form button {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .add-task-form button:hover {
            background-color: #0056b3;
        }

        .task-list {
            list-style-type: none;
            padding: 0;
        }

        .task-list li {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px;
            border-bottom: 1px solid #ddd;
            font-size: 16px;
        }

        .task-list li.done {
            text-decoration: line-through;
            color: #888;
        }

        .task-list input[type="checkbox"] {
            margin-right: 10px;
        }

        .delete-task {
            color: #e74c3c;
            font-size: 14px;
            text-decoration: none;
        }

        .delete-task:hover {
            text-decoration: underline;
        }

        /* Mobile Responsiveness */
        @media (max-width: 600px) {
            .add-task-form input {
                width: 70%;
            }

            .add-task-form button {
                width: 25%;
            }
        }

        /* Style untuk tombol Save */
        button[name="save_tasks"] {
            padding: 10px 20px;
            font-size: 16px;
            background-color: #28a745;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
        }

        button[name="save_tasks"]:hover {
            background-color: #218838;
        }

   /* Style untuk ikon pencarian */
   .search-icon {
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border-radius: 50%;
            cursor: pointer;
            font-size: 18px;
        }

        .search-icon:hover {
            background-color: #0056b3;
        }

        /* Style untuk container pencarian */
        .search-form-container {
            position: fixed;
            top: 20px;
            right: -100%; /* Mulai di luar layar */
            width: 300px;
            height: 40px;
            background-color: #f4f7fc;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 10px;
            transition: right 0.5s ease; /* Efek bergulir */
        }

        .search-form-container form {
            display: flex;
            width: 100%;
        }

        .search-form-container input {
            width: 80%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }

        .search-form-container button {
            width: 20%;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .search-form-container button i {
            font-size: 18px;
        }

        /* Ketika form pencarian ditampilkan */
        .search-form-container.show {
            right: 0; /* Menarik form ke dalam layar */
        }


        /* Style untuk ikon data pribadi */
        .profile-icon {
            position: fixed;
            top: 50px;
            left: 1000px;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background-color: #007bff;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 24px;
            font-weight: bold;
            cursor: pointer;
        }

        .profile-icon:hover {
            color: #0056b3;
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
    </style>
</head>
<body>
    <div class="container">

        <header>
            <h1>Welcome, <?php echo $username; ?></h1>
            <p>Ujikompetensi SMK Yaspim - Program Studi Rekayasa Perangkat Lunak</p>
        </header>

        <nav>
            <a href="logout.php">Logout</a>
            <a href="change_password.php">Change Password</a>
            <a href="delete_account.php">Delete Account</a>
        </nav>

        

        <section class="todo-section">
            <h2>Your To-Do List</h2>
            <form method="POST" class="add-task-form">
                <input type="text" name="task" placeholder="Masukkan tugas" required>
                <button type="submit" name="add_task">Add</button>
            </form>
            <!-- Ikon Pencarian -->
<div class="search-icon" onclick="toggleSearch()">
    <i class="fas fa-search"></i>
</div>

<!-- Form Pencarian -->
<div class="search-form-container" id="searchForm">
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Cari tugas..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
        <button type="submit"><i class="fas fa-times"></i></button>
    </form>
</div>
         <form method="POST">
                <ul class="task-list">
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <li class="<?php echo $row['done'] == 1 ? 'done' : ''; ?>">
                            <input type="checkbox" name="task_status[<?php echo $row['taskid']; ?>]" <?php echo $row['done'] == 1 ? 'checked' : ''; ?>>
                            <?php echo $row['task']; ?>
                            <a href="todo.php?delete_task=1&taskid=<?php echo $row['taskid']; ?>" class="delete-task">Delete</a>
                        </li>
                    <?php } ?>
                </ul>
                <button type="submit" name="save_tasks">Save</button>
            </form>
        </section>
       
    </div>
    <script>
        // Fungsi untuk membuka dan menutup form pencarian dengan efek slide
        function toggleSearch() {
            var searchForm = document.getElementById("searchForm");
            searchForm.classList.toggle("show");
        }
    </script>
</body>
<div class="nav-icons">
    <a href="todo.php" title="Tasks"><i class="fas fa-tasks"></i></a>
    <a href="kalender.php" title="Calendar"><i class="fas fa-calendar-alt"></i></a>
    <a href="profile.php" title="Profile"><i class="fas fa-user-circle"></i></a>
 </div>

</html>
