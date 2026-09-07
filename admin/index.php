<?php

session_start();

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| التحقق من تسجيل الدخول
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {

    header("Location: ../login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| التحقق من صلاحية المدير
|--------------------------------------------------------------------------
*/

if ($_SESSION["user_role"] !== "admin") {

    header("Location: ../index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| جلب عدد المستخدمين
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM users"
);

$users_count = $result->fetch_assoc()["total"];


/*
|--------------------------------------------------------------------------
| جلب عدد المنتجات
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM products"
);

$products_count = $result->fetch_assoc()["total"];


/*
|--------------------------------------------------------------------------
| جلب عدد الطلبات
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT COUNT(*) AS total
     FROM orders"
);

$orders_count = $result->fetch_assoc()["total"];


/*
|--------------------------------------------------------------------------
| جلب إجمالي المبيعات
|--------------------------------------------------------------------------
*/

$result = $conn->query(
    "SELECT COALESCE(SUM(total), 0) AS total
     FROM orders
     WHERE status != 'cancelled'"
);

$sales_total = $result->fetch_assoc()["total"];

?>

<!DOCTYPE html>

<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        لوحة تحكم المدير - المتجر الإلكتروني
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body>


<!-- ==============================
     Navbar
============================== -->

<header>

    <nav class="navbar">

        <div class="container">

            <h1 class="logo">
                لوحة الإدارة
            </h1>


            <ul class="nav-links">

                <li>
                    <a href="../index.php">
                        المتجر
                    </a>
                </li>


                <li>
                    <a href="index.php">
                        لوحة التحكم
                    </a>
                </li>


                <li>
                    <a href="../logout.php">
                        تسجيل الخروج
                    </a>
                </li>

            </ul>

        </div>

    </nav>

</header>


<!-- ==============================
     Dashboard
============================== -->

<main>

    <section class="admin-section">

        <div class="container">


            <h2 class="section-title">
                لوحة تحكم المدير
            </h2>


            <p class="admin-welcome">

                مرحبًا بك،

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $_SESSION["user_name"]
                    );
                    ?>

                </strong>

            </p>


            <!-- ==============================
                 Statistics
            ============================== -->

            <div class="dashboard-cards">


                <!-- Users -->

                <div class="dashboard-card">

                    <div class="dashboard-icon">
                        👥
                    </div>

                    <h3>
                        المستخدمون
                    </h3>

                    <strong>
                        <?php
                        echo $users_count;
                        ?>
                    </strong>

                </div>


                <!-- Products -->

                <div class="dashboard-card">

                    <div class="dashboard-icon">
                        📦
                    </div>

                    <h3>
                        المنتجات
                    </h3>

                    <strong>
                        <?php
                        echo $products_count;
                        ?>
                    </strong>

                </div>


                <!-- Orders -->

                <div class="dashboard-card">

                    <div class="dashboard-icon">
                        🛒
                    </div>

                    <h3>
                        الطلبات
                    </h3>

                    <strong>
                        <?php
                        echo $orders_count;
                        ?>
                    </strong>

                </div>


                <!-- Sales -->

                <div class="dashboard-card">

                    <div class="dashboard-icon">
                        💰
                    </div>

                    <h3>
                        إجمالي المبيعات
                    </h3>

                    <strong>

                        $

                        <?php
                        echo number_format(
                            $sales_total,
                            2
                        );
                        ?>

                    </strong>

                </div>

            </div>


            <!-- ==============================
                 Management Links
            ============================== -->

            <div class="admin-menu">

                <h3>
                    إدارة المتجر
                </h3>


                <div class="admin-menu-grid">


                    <a
                        href="products.php"
                        class="admin-menu-item"
                    >

                        <span>
                            📦
                        </span>

                        <strong>
                            إدارة المنتجات
                        </strong>

                        <small>
                            إضافة وتعديل وحذف المنتجات
                        </small>

                    </a>


                    <a
                        href="categories.php"
                        class="admin-menu-item"
                    >

                        <span>
                            🗂️
                        </span>

                        <strong>
                            إدارة التصنيفات
                        </strong>

                        <small>
                            إضافة وتعديل التصنيفات
                        </small>

                    </a>


                    <a
                        href="orders.php"
                        class="admin-menu-item"
                    >

                        <span>
                            🛒
                        </span>

                        <strong>
                            إدارة الطلبات
                        </strong>

                        <small>
                            عرض ومتابعة طلبات العملاء
                        </small>

                    </a>


                    <a
                        href="../products.php"
                        class="admin-menu-item"
                    >

                        <span>
                            👁️
                        </span>

                        <strong>
                            مشاهدة المتجر
                        </strong>

                        <small>
                            الانتقال إلى واجهة المتجر
                        </small>

                    </a>

                </div>

            </div>

        </div>

    </section>

</main>


<!-- ==============================
     Footer
============================== -->

<footer>

    <p>

        جميع الحقوق محفوظة © 2026
        المتجر الإلكتروني

    </p>

</footer>


</body>

</html>
