<?php
session_start(); // Начало сессии для отслеживания авторизации

// Проверка выхода из учетной записи
if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
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

// Проверка, если клиент авторизован, для загрузки его ID
$client_id = null;
if (isset($_SESSION['username'])) {
    $current_username = $_SESSION['username'];
    $client_query = $conn->prepare("SELECT id FROM clients WHERE username = ?");
    $client_query->bind_param("s", $current_username);
    $client_query->execute();
    $client_result = $client_query->get_result();
    $client_id = $client_result->fetch_assoc()['id'];
}

// Получение заявок текущего клиента
$appointments_query = $conn->prepare("SELECT service, status, appointment_date, created_at FROM appointments WHERE client_id = ? ORDER BY created_at DESC");
$appointments_query->bind_param("i", $client_id);
$appointments_query->execute();
$appointments_result = $appointments_query->get_result();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="https://necolas.github.io/normalize.css/8.0.1/normalize.css" />
    <title>Запись на Ноготочки - Мои Заявки</title>
  </head>
  <body>
    <header>
      <div class="logo"><h1>Записываемся на Ноготочки</h1></div>
      <div class="nav-bar">
        <?php if (isset($_SESSION['username'])): ?>
          <a href="appointments.php">Заявки</a>
          <a href="admin.php">Админ</a>
          <a href="?logout=true">Выход</a>
        <?php else: ?>
          <a href="login.php">Вход</a>
          <a href="appointments.php">Заявки</a>
          <a href="admin.php">Админ</a>
        <?php endif; ?>
      </div>
    </header>

    <main>
      <div class="container">
        <h1>Мои Заявки</h1>

        <!-- Список заявок клиента -->
        <div class="table-container">
          <?php if ($appointments_result->num_rows > 0): ?>
            <table>
              <thead>
                <tr>
                  <th>Услуга</th>
                  <th>Дата записи</th>
                  <th>Статус</th>
                  <th>Дата создания</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $appointments_result->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['service']); ?></td>
                    <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p>У вас нет заявок.</p>
          <?php endif; ?>
        </div>

        <!-- Кнопка для добавления новой заявки - доступна только авторизованным клиентам -->
        <?php if ($client_id): ?>
          <div class="act-form">
            <h2>Оставить новую заявку</h2>
            <form action="new_appointment.php" method="get">
              <button type="submit">Создать заявку</button>
            </form>
          </div>
        <?php else: ?>
          <p>Пожалуйста, <a href="login.php">войдите в систему</a>, чтобы оставить новую заявку.</p>
        <?php endif; ?>
      </div>
    </main>
  </body>
</html>
