```php
<?php

session_start();

require_once "config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    if (empty($name) || empty($email) || empty($password)) {

        $message = "يرجى تعبئة جميع الحقول.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "البريد الإلكتروني غير صحيح.";

    } elseif (strlen($password) < 6) {

        $message = "كلمة المرور يجب أن تكون 6 أحرف على الأقل.";

    } else {

        $check_sql = "SELECT id FROM users WHERE email = ?";

        $check_stmt = $conn->prepare($check_sql);

        $check_stmt->bind_param("s", $email);

        $check_stmt->execute();

        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {

            $message = "البريد الإلكتروني مستخدم مسبقًا.";

        } else {

            $hashed_password = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $sql = "INSERT INTO users
                    (name, email, password, role)
                    VALUES (?, ?, ?, 'customer')";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "sss",
                $name,
                $email,
                $hashed_password
            );

            if ($stmt->execute()) {

                $message = "تم إنشاء الحساب بنجاح. يمكنك تسجيل الدخول الآن.";

            } else {

                $message = "حدث خطأ أثناء إنشاء الحساب.";

            }

        }

    }

}

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>إنشاء حساب</title>

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
                <a href="login.php">تسجيل الدخول</a>
            </li>

        </ul>

    </div>

</nav>


<section class="auth-section">

    <div class="auth-box">

        <h2>إنشاء حساب جديد</h2>

        <?php if (!empty($message)): ?>

            <div class="message">
                <?php echo htmlspecialchars($message); ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <label>الاسم</label>

            <input
                type="text"
                name="name"
                required
            >


            <label>البريد الإلكتروني</label>

            <input
                type="email"
                name="email"
                required
            >


            <label>كلمة المرور</label>

            <input
                type="password"
                name="password"
                required
            >


            <button type="submit" class="btn">
                إنشاء الحساب
            </button>

        </form>


        <p>
            لديك حساب بالفعل؟
            <a href="login.php">
                تسجيل الدخول
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
