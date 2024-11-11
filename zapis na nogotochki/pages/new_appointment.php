<?php
session_start();

// Проверка, если клиент не авторизован, перенаправление на страницу входа
if (!isset($_SESSION['username']) || !isset($_SESSION['client_id'])) {
    header("Location: login.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nail_booking";

$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Получение client_id из сессии
$client_id = $_SESSION['client_id'];

// Обработка формы
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $master_name = $_POST['master'];
    $appointment_date = $_POST['appointment_date'];
    $appointment_time = $_POST['appointment_time'];
    $appointment_datetime = $appointment_date . ' ' . $appointment_time . ':00';

    // Вставка новой заявки
    $insert_query = $conn->prepare("INSERT INTO appointments (client_id, service, master, appointment_date, status) VALUES (?, ?, ?, ?, 'Новое')");
    $service = "Маникюр"; // Пример услуги
    $insert_query->bind_param("isss", $client_id, $service, $master_name, $appointment_datetime);

    if ($insert_query->execute()) {
        header("Location: appointments.php");
        exit();
    } else {
        $error_message = "Ошибка при создании заявки: " . $conn->error;
    }

    $insert_query->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://necolas.github.io/normalize.css/8.0.1/normalize.css">
    <title>Новая Заявка</title>
</head>
<body>
    <header>
        <div class="logo"><h1>Записываемся на Ноготочки</h1></div>
        <div class="nav-bar">
            <a href="appointments.php">Заявки</a>
            <a href="admin.php">Админ</a>
            <a href="?logout=true">Выход</a>
        </div>
    </header>

    <main>
        <div class="container">
            <h1>Новая Заявка</h1>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form action="new_appointment.php" method="post" class="act-form">
                <!-- Выбор мастера -->
                <label for="master">Выберите мастера:</label>
                <select id="master" name="master" required>
                    <option value="">Выберите мастера</option>
                    <option value="Анна Смирнова">Анна Смирнова</option>
                    <option value="Мария Иванова">Мария Иванова</option>
                    <option value="Ольга Петрова">Ольга Петрова</option>
                    <option value="Екатерина Сидорова">Екатерина Сидорова</option>
                </select>

                <!-- Выбор даты -->
                <label for="appointment_date">Дата:</label>
                <input type="date" id="appointment_date" name="appointment_date" required>

                <!-- Выбор времени -->
                <label for="appointment_time">Время:</label>
                <select id="appointment_time" name="appointment_time" required>
                    <?php for ($hour = 8; $hour <= 18; $hour++): ?>
                        <option value="<?php echo sprintf("%02d:00", $hour); ?>">
                            <?php echo sprintf("%02d:00", $hour); ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <button type="submit">Записаться</button>
            </form>
        </div>
    </main>
</body>
</html>
