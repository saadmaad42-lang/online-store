<?php

session_start();

require_once "../config/database.php";

// التحقق من تسجيل الدخول
if (!isset($_SESSION["user_id"])) {
    header("Location: ../login.php");
    exit;
}

// التحقق من صلاحية المدير
if ($_SESSION["user_role"] !== "admin") {
    header("Location: ../index.php");
    exit;
}

// التحقق من رقم الطلب
if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: orders.php");
    exit;
}

$order_id = (int)$_GET["id"];

// جلب بيانات الطلب والعميل
$stmt = $conn->prepare(
    "SELECT
        o.id,
        o.total,
        o.status,
        o.created_at,
        u.name AS customer_name,
        u.email AS customer_email
     FROM orders o
     INNER JOIN users u
        ON o.user_id = u.id
     WHERE o.id = ?"
);

$stmt->bind_param("i", $order_id);
$stmt->execute();

$result = $stmt->get_result();

// إذا كان الطلب غير موجود
if ($result->num_rows === 0) {
    header("Location: orders.php");
    exit;
}

$order = $result->fetch_assoc();

// جلب المنتجات الموجودة في الطلب
$stmt = $conn->prepare(
    "SELECT
        oi.id,
        oi.quantity,
        oi.price,
        p.name AS product_name
     FROM order_items oi
     INNER JOIN products p
        ON oi.product_id = p.id
     WHERE oi.order_id = ?
     ORDER BY oi.id ASC"
);

$stmt->bind_param("i", $order_id);
$stmt->execute();

$items_result = $stmt->get_result();


// تحويل الحالة إلى العربية
function getStatusArabic($status)
{
    switch ($status) {

        case "pending":
            return "قيد الانتظار";

        case "processing":
            return "قيد المعالجة";

        case "completed":
            return "مكتمل";

        case "cancelled":
            return "ملغي";

        default:
            return "غير معروف";
    }
}

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
        تفاصيل الطلب #<?php echo $order["id"]; ?>
        - المتجر الإلكتروني
    </title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

<header>

    <nav class="navbar">

        <div class="container">

            <h1 class="logo">
                لوحة الإدارة
            </h1>

            <ul class="nav-links">

                <li>
                    <a href="index.php">
                        لوحة التحكم
                    </a>
                </li>

                <li>
                    <a href="products.php">
                        المنتجات
                    </a>
                </li>

                <li>
                    <a href="categories.php">
                        التصنيفات
                    </a>
                </li>

                <li>
                    <a href="orders.php">
                        الطلبات
                    </a>
                </li>

                <li>
                    <a href="../index.php">
                        المتجر
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


<main>

    <section class="admin-section">

        <div class="container">

            <!-- عنوان الصفحة -->

            <div class="admin-page-header">

                <h2 class="section-title">
                    تفاصيل الطلب
                    #<?php echo $order["id"]; ?>
                </h2>

                <a
                    href="orders.php"
                    class="btn"
                >
                    العودة إلى الطلبات
                </a>

            </div>


            <!-- معلومات العميل -->

            <div class="order-info-box">

                <h3>
                    معلومات العميل
                </h3>

                <div class="order-info-grid">

                    <div>

                        <strong>
                            اسم العميل
                        </strong>

                        <p>
                            <?php
                            echo htmlspecialchars(
                                $order["customer_name"]
                            );
                            ?>
                        </p>

                    </div>


                    <div>

                        <strong>
                            البريد الإلكتروني
                        </strong>

                        <p>
                            <?php
                            echo htmlspecialchars(
                                $order["customer_email"]
                            );
                            ?>
                        </p>

                    </div>


                    <div>

                        <strong>
                            رقم الطلب
                        </strong>

                        <p>
                            #<?php echo $order["id"]; ?>
                        </p>

                    </div>


                    <div>

                        <strong>
                            تاريخ الطلب
                        </strong>

                        <p>
                            <?php
                            echo htmlspecialchars(
                                $order["created_at"]
                            );
                            ?>
                        </p>

                    </div>


                    <div>

                        <strong>
                            حالة الطلب
                        </strong>

                        <p>
                            <?php
                            echo getStatusArabic(
                                $order["status"]
                            );
                            ?>
                        </p>

                    </div>

                </div>

            </div>


            <!-- المنتجات -->

            <div class="order-items-box">

                <h3>
                    منتجات الطلب
                </h3>

                <div class="admin-table-container">

                    <table class="admin-table">

                        <thead>

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    اسم المنتج
                                </th>

                                <th>
                                    سعر الوحدة
                                </th>

                                <th>
                                    الكمية
                                </th>

                                <th>
                                    الإجمالي
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if ($items_result->num_rows > 0): ?>

                                <?php
                                $calculated_total = 0;
                                ?>

                                <?php while ($item = $items_result->fetch_assoc()): ?>

                                    <?php

                                    $item_total =
                                        $item["price"]
                                        *
                                        $item["quantity"];

                                    $calculated_total += $item_total;

                                    ?>

                                    <tr>

                                        <td>
                                            <?php
                                            echo $item["id"];
                                            ?>
                                        </td>


                                        <td>

                                            <strong>
                                                <?php
                                                echo htmlspecialchars(
                                                    $item["product_name"]
                                                );
                                                ?>
                                            </strong>

                                        </td>


                                        <td>

                                            $
                                            <?php
                                            echo number_format(
                                                $item["price"],
                                                2
                                            );
                                            ?>

                                        </td>


                                        <td>

                                            <?php
                                            echo $item["quantity"];
                                            ?>

                                        </td>


                                        <td>

                                            <strong>

                                                $
                                                <?php
                                                echo number_format(
                                                    $item_total,
                                                    2
                                                );
                                                ?>

                                            </strong>

                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="5"
                                        class="no-data"
                                    >
                                        لا توجد منتجات في هذا الطلب.
                                    </td>

                                </tr>

                            <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- إجمالي الطلب -->

            <div class="order-total-box">

                <span>
                    إجمالي الطلب
                </span>

                <strong>

                    $
                    <?php
                    echo number_format(
                        $order["total"],
                        2
                    );
                    ?>

                </strong>

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
