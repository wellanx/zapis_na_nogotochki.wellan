<?php
session_start(); // Начало сессии для хранения данных о клиенте

// Проверка отправки формы
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Подключение к базе данных
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "nail_booking";

    $conn = new mysqli($servername, $username, $password, $dbname);

    // Проверка соединения
    if ($conn->connect_error) {
        die("Ошибка подключения: " . $conn->connect_error);
    }

    // Получение данных из формы
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Подготовка SQL-запроса
    $sql = "SELECT * FROM clients WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    // Проверка, существует ли клиент
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Проверка пароля
        if (password_verify($pass, $row['password'])) {
            // Сохранение данных клиента в сессию
            $_SESSION['username'] = $row['username'];
            $_SESSION['fullname'] = $row['fullname'];
            $_SESSION['client_id'] = $row['id']; // Сохранение ID клиента

            // Перенаправление после успешного входа
            header("Location: appointments.php");
            exit();
        } else {
            $error = "Неправильный логин или пароль!";
        }
    } else {
        $error = "Неправильный логин или пароль!";
    }

    $stmt->close();
    $conn->close();
}
?>


<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../css/style.css" />
    <link
      rel="stylesheet"
      href="https://necolas.github.io/normalize.css/8.0.1/normalize.css"
    />
    <title>Вход в систему</title>
  </head>
  <body>
    <header>
      <div class="logo"><h1>Записываемся на Ноготочки</h1></div>
      <div class="nav-bar">
        <a href="login.php">Вход</a>
        <a href="appointments.php">Записи</a>
        <a href="admin.php">Админ</a>
      </div>
    </header>
    <main>
      <div class="container">
        <h1>Вход в систему</h1>
        <h2>Нет аккаунта? <a href="registration.php">Регистрация</a></h2>
        
        <?php if (!empty($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>

        <form class="act-form" action="login.php" method="post">
          <input
            type="text"
            id="username"
            name="username"
            placeholder="Логин"
            required
          />
          <input
            type="password"
            id="password"
            name="password"
            placeholder="Пароль"
            required
          />
          <button type="submit">Войти</button>
        </form>
      </div>
    </main>
  </body>
</html>
