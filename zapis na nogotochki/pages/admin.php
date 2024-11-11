<?php
session_start();

// Проверка авторизации администратора
if (isset($_POST['login']) && isset($_POST['password'])) {
    $admin_login = "beauty";
    $admin_password = "pass";

    if ($_POST['login'] === $admin_login && $_POST['password'] === $admin_password) {
        $_SESSION['admin'] = true;
    } else {
        $error = "Неверный логин или пароль!";
    }
}

// Проверка, что администратор авторизован
if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
?>
    <!DOCTYPE html>
    <html lang="en">
      <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <link rel="stylesheet" href="../css/style.css" />
        <link rel="stylesheet" href="https://necolas.github.io/normalize.css/8.0.1/normalize.css" />
        <title>Панель Администратора</title>
      </head>
      <body>
        <header>
          <div class="logo"><h1>Панель Администратора</h1></div>
        </header>

        <main>
          <div class="container">
            <h1>Вход в панель администратора</h1>
            <?php if (isset($error)) echo "<p class='error-message'>$error</p>"; ?>
            <form class="act-form" action="admin.php" method="post">
              <input type="text" name="login" placeholder="Логин" required />
              <input type="password" name="password" placeholder="Пароль" required />
              <button type="submit">Войти</button>
            </form>
          </div>
        </main>
      </body>
    </html>
<?php
    exit();
}

// Подключение к базе данных для отображения заявок
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nail_booking";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Обработка изменения статуса
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_status'])) {
    $appointment_id = $_POST['appointment_id'];
    $new_status = $_POST['status'];
    
    // Подготовка и выполнение запроса на обновление статуса
    $update_query = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $update_query->bind_param("si", $new_status, $appointment_id);
    
    if ($update_query->execute()) {
        header("Location: admin.php"); // Перезагрузка страницы после обновления
        exit();
    } else {
        echo "Ошибка при обновлении статуса: " . $update_query->error;
    }
    
    $update_query->close();
}

// Получение всех заявок
$appointments_query = $conn->query("SELECT 
                                        appointments.id, 
                                        clients.fullname, 
                                        clients.phone, 
                                        appointments.appointment_date, 
                                        appointments.master, 
                                        appointments.status 
                                    FROM appointments 
                                    JOIN clients ON appointments.client_id = clients.id
                                    ORDER BY appointments.appointment_date DESC");

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="stylesheet" href="https://necolas.github.io/normalize.css/8.0.1/normalize.css" />
    <title>Панель Администратора</title>
  </head>
  <body>
    <header>
      <div class="logo"><h1>Панель Администратора</h1></div>
      <div class="nav-bar">
        <a href="appointments.php">Вернуться</a>
      </div>
    </header>

    <main>
      <div class="container">
        <h1>Все Заявки</h1>
        <div class="table-container">
          <?php if ($appointments_query->num_rows > 0): ?>
            <table>
              <thead>
                <tr>
                  <th>ФИО</th>
                  <th>Телефон</th>
                  <th>Дата и время</th>
                  <th>Мастер</th>
                  <th>Статус</th>
                  <th>Изменить статус</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = $appointments_query->fetch_assoc()): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['appointment_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['master']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td>
                      <form action="admin.php" method="post">
                        <input type="hidden" name="appointment_id" value="<?php echo $row['id']; ?>" />
                        <select name="status">
                          <option value="Новое" <?php if ($row['status'] == 'Новое') echo 'selected'; ?>>Новое</option>
                          <option value="Подтверждено" <?php if ($row['status'] == 'Подтверждено') echo 'selected'; ?>>Подтверждено</option>
                          <option value="Отклонено" <?php if ($row['status'] == 'Отклонено') echo 'selected'; ?>>Отклонено</option>
                        </select>
                        <button type="submit" name="update_status">Обновить</button>
                      </form>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php else: ?>
            <p>Нет заявок для отображения.</p>
          <?php endif; ?>
        </div>
      </div>
    </main>
  </body>
</html>
