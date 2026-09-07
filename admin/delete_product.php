<?php

session_start();

require_once "../config/database.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

// التحقق من أن المستخدم مدير
if ($_SESSION["user_role"] !== "admin") {
    header("Location: ../index.php");
    exit;
}

// التحقق من وجود رقم المنتج
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: products.php");
    exit;
}

$product_id = (int)$_GET["id"];

// جلب بيانات المنتج
$stmt = $conn->prepare(
    "SELECT id, name
     FROM products
     WHERE id = ?"
);

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();

// إذا كان المنتج غير موجود
if ($result->num_rows === 0) {
    header("Location: products.php");
    exit;
}

$product = $result->fetch_assoc();

$product_name = $product["name"];

// التحقق هل المنتج موجود في طلبات سابقة
$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM order_items
     WHERE product_id = ?"
);

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();
$row = $result->fetch_assoc();

$order_items_count = (int)$row["total"];

// منع حذف المنتج إذا كان له طلبات سابقة
if ($order_items_count > 0) {
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>تعذر حذف المنتج - المتجر الإلكتروني</title>

        <link rel="stylesheet" href="../assets/css/style.css">
    </head>

    <body>

    <header>
        <nav class="navbar">

            <div class="container">

                <h1 class="logo">لوحة الإدارة</h1>

                <ul class="nav-links">

                    <li>
                        <a href="index.php">لوحة التحكم</a>
                    </li>

                    <li>
                        <a href="products.php">المنتجات</a>
                    </li>

                    <li>
                        <a href="../index.php">المتجر</a>
                    </li>

                    <li>
                        <a href="../logout.php">تسجيل الخروج</a>
                    </li>

                </ul>

            </div>

        </nav>
    </header>

    <main>

        <section class="admin-section">

            <div class="container">

                <div class="admin-form-box">

                    <h2 class="section-title">
                        تعذر حذف المنتج
                    </h2>

                    <div class="message">

                        لا يمكن حذف المنتج:

                        <strong>
                            <?php echo htmlspecialchars($product_name); ?>
                        </strong>

                        لأنه مرتبط بطلبات سابقة في النظام.

                        <br><br>

                        للحفاظ على سجل المبيعات، يرجى تعديل كمية المخزون إلى
                        <strong>0</strong>
                        بدلًا من حذف المنتج.

                    </div>

                    <div class="form-buttons">

                        <a href="products.php" class="btn">
                            العودة إلى المنتجات
                        </a>

                    </div>

                </div>

            </div>

        </section>

    </main>

    <footer>

        <p>
            جميع الحقوق محفوظة © 2026
            المتجر الإلكتروني
        </p>

    </footer>

    </body>

    </html>

    <?php
    exit;
}

// حذف المنتج
$stmt = $conn->prepare(
    "DELETE FROM products
     WHERE id = ?"
);

$stmt->bind_param("i", $product_id);

$stmt->execute();

// العودة إلى صفحة المنتجات
header("Location: products.php");
exit;

?>
