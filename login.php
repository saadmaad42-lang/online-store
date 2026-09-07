```php
<?php

session_start();

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($email) || empty($password)) {

        $message = "يرجى إدخال البريد الإلكتروني وكلمة المرور.";

    } else {

        $sql = "SELECT id, name, email, password, role
                FROM users
                WHERE email = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param("s", $email);

        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows === 1) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user["password"])) {

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["user_name"] = $user["name"];
                $_SESSION["user_email"] = $user["email"];
                $_SESSION["user_role"] = $user["role"];

                if ($user["role"] === "admin") {

                    header("Location: admin/index.php");

                } else {

                    header("Location: index.php");

                }

                exit;

            } else {

                $message = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";

            }

        } else {

            $message = "البريد الإلكتروني أو كلمة المرور غير صحيحة.";

        }

    }

}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>تسجيل الدخول</title>

    <link rel="stylesheet" href="assets/css/style.css">

</head>

<body>

<nav class="navbar">

    <div class="container">

        <h1 class="logo">🛒 متجري</h1>

        <ul class="nav-links">

            <li>
                <a href="index.php">الرئيسية</a>
            </li>

            <li>
                <a href="products.php">المنتجات</a>
            </li>

            <li>
                <a href="register.php">إنشاء حساب</a>
            </li>

        </ul>

    </div>

</nav>


<section class="auth-section">

    <div class="auth-box">

        <h2>تسجيل الدخول</h2>

        <?php if (!empty($message)): ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <label>
                البريد الإلكتروني
            </label>

            <input
                type="email"
                name="email"
                required
            >


            <label>
                كلمة المرور
            </label>

            <input
                type="password"
                name="password"
                required
            >


            <button type="submit" class="btn">
                تسجيل الدخول
            </button>

        </form>


        <p>

            ليس لديك حساب؟

            <a href="register.php">
                إنشاء حساب جديد
            </a>

        </p>

    </div>

</section>


<footer>

    <div class="container">

        <p>
            © 2026 المتجر الإلكتروني - جميع الحقوق محفوظة
        </p>

    </div>

</footer>

</body>

</html>
```
