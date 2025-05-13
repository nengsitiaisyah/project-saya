<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usernameToDelete = $_POST['username'];

    // Hapus semua tugas dan akun
    $query = "DELETE FROM tasks WHERE username = '$usernameToDelete'";
    mysqli_query($conn, $query);

    $query = "DELETE FROM users WHERE username = '$usernameToDelete'";
    mysqli_query($conn, $query);

    if ($usernameToDelete == $_SESSION['username']) {
        session_destroy();
        header("Location: login.php");
        exit();
    } else {
        $successMessage = "Account deleted successfully.";
    }
}

// Ambil semua akun dari tabel users
$query = "SELECT username FROM users";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Account</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            width: 90%;
            max-width: 400px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
        }

        .form-group select {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        button {
            width: 100%;
            padding: 10px;
            background-color: #dc3545;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        button:hover {
            background-color: #c82333;
        }

        .success-message {
            color: green;
            margin-top: 20px;
            font-weight: bold;
        }

        .logout-button {
            margin-top: 20px;
            background-color: #007bff;
        }

        .logout-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Delete Account</h2>
        <form method="POST" action="delete_account.php">
            <div class="form-group">
                <label for="username">Select Account to Delete</label>
                <select name="username" id="username" required>
                    <?php while ($row = mysqli_fetch_assoc($result)) { ?>
                        <option value="<?php echo $row['username']; ?>"><?php echo $row['username']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <button type="submit">Delete Account</button>
        </form>
        <?php if ($successMessage) { ?>
            <div class="success-message"><?php echo $successMessage; ?></div>
        <?php } ?>
        <form method="POST" action="todo.php">
            <button type="submit" class="logout-button">Kembali</button>
        </form>
    </div>
</body>
</html>