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

$message = "";
$error = "";

// تغيير حالة الطلب
if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["order_id"]) &&
    isset($_POST["status"])
) {

    $order_id = (int)$_POST["order_id"];
    $status = $_POST["status"];

    // الحالات المسموح بها فقط
    $allowed_statuses = [
        "pending",
        "processing",
        "completed",
        "cancelled"
    ];

    if (!in_array($status, $allowed_statuses, true)) {

        $error = "حالة الطلب غير صحيحة.";

    } else {

        $stmt = $conn->prepare(
            "UPDATE orders
             SET status = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "si",
            $status,
            $order_id
        );

        if ($stmt->execute()) {

            $message = "تم تحديث حالة الطلب بنجاح.";

        } else {

            $error = "حدث خطأ أثناء تحديث حالة الطلب.";
        }
    }
}

// جلب جميع الطلبات
$orders_result = $conn->query(
    "SELECT
        o.id,
        o.user_id,
        o.total,
        o.status,
        o.created_at,
        u.name AS customer_name,
        u.email AS customer_email
     FROM orders o
     INNER JOIN users u
        ON o.user_id = u.id
     ORDER BY o.id DESC"
);


// تحويل حالة الطلب إلى العربية
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

    <title>إدارة الطلبات - المتجر الإلكتروني</title>

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

            <div class="admin-page-header">

                <h2 class="section-title">
                    إدارة الطلبات
                </h2>

                <a
                    href="index.php"
                    class="btn"
                >
                    العودة للوحة التحكم
                </a>

            </div>


            <?php if (!empty($message)): ?>

                <div class="success-message">

                    <?php
                    echo htmlspecialchars($message);
                    ?>

                </div>

            <?php endif; ?>


            <?php if (!empty($error)): ?>

                <div class="message">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <div class="admin-table-container">

                <table class="admin-table">

                    <thead>

                        <tr>

                            <th>
                                رقم الطلب
                            </th>

                            <th>
                                العميل
                            </th>

                            <th>
                                البريد الإلكتروني
                            </th>

                            <th>
                                الإجمالي
                            </th>

                            <th>
                                الحالة
                            </th>

                            <th>
                                تاريخ الطلب
                            </th>

                            <th>
                                التفاصيل
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php if ($orders_result->num_rows > 0): ?>

                            <?php while ($order = $orders_result->fetch_assoc()): ?>

                                <tr>

                                    <td>

                                        <strong>
                                            #<?php
                                            echo $order["id"];
                                            ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $order["customer_name"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $order["customer_email"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <strong>
                                            $
                                            <?php
                                            echo number_format(
                                                $order["total"],
                                                2
                                            );
                                            ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <form
                                            method="POST"
                                            class="status-form"
                                        >

                                            <input
                                                type="hidden"
                                                name="order_id"
                                                value="<?php
                                                echo $order["id"];
                                                ?>"
                                            >

                                            <select
                                                name="status"
                                                onchange="this.form.submit()"
                                            >

                                                <option
                                                    value="pending"
                                                    <?php
                                                    if (
                                                        $order["status"]
                                                        === "pending"
                                                    ) {
                                                        echo "selected";
                                                    }
                                                    ?>
                                                >
                                                    قيد الانتظار
                                                </option>


                                                <option
                                                    value="processing"
                                                    <?php
                                                    if (
                                                        $order["status"]
                                                        === "processing"
                                                    ) {
                                                        echo "selected";
                                                    }
                                                    ?>
                                                >
                                                    قيد المعالجة
                                                </option>


                                                <option
                                                    value="completed"
                                                    <?php
                                                    if (
                                                        $order["status"]
                                                        === "completed"
                                                    ) {
                                                        echo "selected";
                                                    }
                                                    ?>
                                                >
                                                    مكتمل
                                                </option>


                                                <option
                                                    value="cancelled"
                                                    <?php
                                                    if (
                                                        $order["status"]
                                                        === "cancelled"
                                                    ) {
                                                        echo "selected";
                                                    }
                                                    ?>
                                                >
                                                    ملغي
                                                </option>

                                            </select>

                                        </form>

                                    </td>


                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $order["created_at"]
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <a
                                            href="order_details.php?id=<?php
                                            echo $order["id"];
                                            ?>"
                                            class="edit-btn"
                                        >
                                            عرض التفاصيل
                                        </a>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="7"
                                    class="no-data"
                                >
                                    لا توجد طلبات حاليًا.
                                </td>

                            </tr>

                        <?php endif; ?>

                    </tbody>

                </table>

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
