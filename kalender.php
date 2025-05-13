<?php
session_start();

// Pastikan pengguna sudah login dan nama pengguna tersedia
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

$username = $_SESSION['username']; // Ambil nama pengguna dari session

// Menyimpan ulang tahun ke file JSON berdasarkan ID pengguna
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['birthday']) && isset($_POST['name'])) {
        $birthday = $_POST['birthday'];
        $name = $_POST['name'];

        // Nama file untuk menyimpan data ulang tahun berdasarkan ID pengguna
        $filename = 'ultah/' . $username . '_birthdays.json';

        // Cek jika file JSON sudah ada
        if (file_exists($filename)) {
            // Ambil data ulang tahun yang sudah ada
            $birthdays = json_decode(file_get_contents($filename), true);
        } else {
            // Jika file belum ada, buat array kosong
            $birthdays = [];
        }

        // Pastikan tidak ada data yang sama dalam array
        $exists = false;
        foreach ($birthdays as $b) {
            if ($b['date'] == $birthday && $b['name'] == $name) {
                $exists = true;
                break;
            }
        }

        // Menambah data ulang tahun jika belum ada
        if (!$exists) {
            $birthdays[] = ['date' => $birthday, 'name' => $name];
        }

        // Simpan data ulang tahun ke dalam file JSON
        if (file_put_contents($filename, json_encode($birthdays, JSON_PRETTY_PRINT))) {
            header('Location: kalender.php');
            exit();
        } else {
            echo "<script>alert('Terjadi kesalahan saat menyimpan data ulang tahun.');</script>";
        }
    }

    // Handle edit or delete actions via POST request (AJAX)
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $index = $_POST['index'];

        if ($action == 'edit' && isset($_POST['new_name'])) {
            // Update the birthday name
            $birthdays = json_decode(file_get_contents('ultah/' . $username . '_birthdays.json'), true);
            $birthdays[$index]['name'] = $_POST['new_name'];
            file_put_contents('ultah/' . $username . '_birthdays.json', json_encode($birthdays, JSON_PRETTY_PRINT));
        }

        if ($action == 'delete') {
            // Delete the birthday
            $birthdays = json_decode(file_get_contents('ultah/' . $username . '_birthdays.json'), true);
            array_splice($birthdays, $index, 1);
            file_put_contents('ultah/' . $username . '_birthdays.json', json_encode($birthdays, JSON_PRETTY_PRINT));
        }

        // Return the updated list of birthdays
        echo json_encode($birthdays);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kalender Ulang Tahun</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
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
            text-align: center;
        }

        h2 {
            margin-bottom: 20px;
        }

        .calendar {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            grid-gap: 5px;
            margin-top: 20px;
        }

        .calendar .day {
            padding: 10px;
            background-color: #f0f0f0;
            cursor: pointer;
            border-radius: 5px;
            position: relative;
        }

        .calendar .day:hover {
            background-color: #ddd;
        }

        .calendar .birthday {
            background-color: #ffcc00;
        }

        .birthday-name {
            font-size: 12px;
            color: #333;
            margin-top: 5px;
            text-align: center;
        }

        .form-group {
            margin-top: 20px;
        }

        input[type="date"], input[type="text"] {
            padding: 10px;
            font-size: 16px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        button {
            margin-top: 10px;
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
        }

        button:hover {
            background-color: #0056b3;
        }

        .month-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .month-controls button {
            padding: 10px;
            background-color: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .month-controls h3 {
            margin: 0;
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

        /* Context Menu */
        #context-menu {
            position: absolute;
            background-color: #fff;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: none;
            padding: 10px;
            border-radius: 5px;
        }

        #context-menu button {
            display: block;
            background-color: #007bff;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            margin-bottom: 10px;
        }

        #context-menu button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Kalender Ulang Tahun</h2>

    <!-- Form untuk memilih tanggal ulang tahun dan menambahkan nama -->
    <div class="form-group">
        <form method="POST" action="kalender.php">
            <label for="birthday">Pilih Tanggal Ulang Tahun:</label>
            <input type="date" id="birthday" name="birthday" required>
            <label for="name">Nama Orang yang Ulang Tahun:</label>
            <input type="text" id="name" name="name" placeholder="Nama Orang" required>
            <button type="submit">Tambah Ulang Tahun</button>
        </form>
    </div>

    <!-- Kontrol untuk mengganti bulan -->
    <div class="month-controls">
        <button onclick="changeMonth(-1)">&#10094; Sebelumnya</button>
        <h3 id="month-name">Januari 2025</h3>
        <button onclick="changeMonth(1)">Berikutnya &#10095;</button>
    </div>

    <div class="calendar">
        <!-- Kalender akan digenerate di sini -->
    </div>
</div>

<div class="nav-icons">
    <a href="todo.php" title="Tasks"><i class="fas fa-tasks"></i></a>
    <a href="kalender.php" title="Calendar"><i class="fas fa-calendar-alt"></i></a>
    <a href="profile.php" title="Profile"><i class="fas fa-user-circle"></i></a>
</div>

<!-- Context menu for edit and delete -->
<div id="context-menu">
    <button id="edit-birthday">Edit <i class="fas fa-edit"></i></button>
    <button id="delete-birthday">Delete <i class="fas fa-trash"></i></button>
</div>

<script>
    const calendarElement = document.querySelector('.calendar');
    const contextMenu = document.getElementById('context-menu');
    let selectedBirthdayIndex = null;

    const birthdays = <?php 
        $filename = 'ultah/' . $_SESSION['username'] . '_birthdays.json';
        echo json_encode(file_exists($filename) ? json_decode(file_get_contents($filename), true) : []); 
    ?>;

    function drawCalendar() {
        calendarElement.innerHTML = '';
        const currentDate = new Date();
        const currentYear = currentDate.getFullYear();
        const currentMonth = currentDate.getMonth();
        const firstDayOfMonth = new Date(currentYear, currentMonth, 1);
        const lastDayOfMonth = new Date(currentYear, currentMonth + 1, 0);

        const firstDay = firstDayOfMonth.getDay();
        const totalDaysInMonth = lastDayOfMonth.getDate();

        for (let i = 0; i < firstDay; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.classList.add('day');
            calendarElement.appendChild(emptyDay);
        }

        for (let day = 1; day <= totalDaysInMonth; day++) {
            const dayElement = document.createElement('div');
            dayElement.classList.add('day');
            dayElement.innerText = day;

            const dateString = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
            const birthdayData = birthdays.filter(birthday => birthday.date === dateString);

            if (birthdayData.length > 0) {
                dayElement.classList.add('birthday');
                birthdayData.forEach((b, index) => {
                    const birthdayName = document.createElement('div');
                    birthdayName.classList.add('birthday-name');
                    birthdayName.innerText = b.name;
                    birthdayName.dataset.index = index;

                    birthdayName.addEventListener('contextmenu', (event) => {
                        event.preventDefault();
                        selectedBirthdayIndex = index;
                        contextMenu.style.display = 'block';
                        contextMenu.style.left = `${event.pageX}px`;
                        contextMenu.style.top = `${event.pageY}px`;
                    });

                    dayElement.appendChild(birthdayName);
                });
            }

            calendarElement.appendChild(dayElement);
        }
    }

    document.addEventListener('click', () => {
        contextMenu.style.display = 'none';
    });

    document.getElementById('edit-birthday').addEventListener('click', () => {
        const newName = prompt('Enter new name:');
        if (newName) {
            updateBirthday('edit', newName);
        }
    });

    document.getElementById('delete-birthday').addEventListener('click', () => {
        if (confirm('Are you sure you want to delete this birthday?')) {
            updateBirthday('delete');
        }
    });

    function updateBirthday(action, newName = null) {
        const formData = new FormData();
        formData.append('action', action);
        formData.append('index', selectedBirthdayIndex);

        if (newName) {
            formData.append('new_name', newName);
        }

        fetch('kalender.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            birthdays.length = 0;
            data.forEach(b => birthdays.push(b));
            drawCalendar();
        });

        contextMenu.style.display = 'none';
    }

    drawCalendar();
</script>

</body>
</html>
