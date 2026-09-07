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

// إضافة تصنيف جديد
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");

    if ($name === "") {

        $error = "يرجى إدخال اسم التصنيف.";

    } else {

        // التحقق من عدم وجود التصنيف مسبقًا
        $stmt = $conn->prepare(
            "SELECT id
             FROM categories
             WHERE name = ?"
        );

        $stmt->bind_param("s", $name);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $error = "هذا التصنيف موجود بالفعل.";

        } else {

            // إضافة التصنيف
            $stmt = $conn->prepare(
                "INSERT INTO categories (name)
                 VALUES (?)"
            );

            $stmt->bind_param("s", $name);

            if ($stmt->execute()) {

                $message = "تمت إضافة التصنيف بنجاح.";

            } else {

                $error = "حدث خطأ أثناء إضافة التصنيف.";
            }
        }
    }
}

// حذف تصنيف
if (isset($_GET["delete"]) && is_numeric($_GET["delete"])) {

    $category_id = (int)$_GET["delete"];

    // التحقق هل يوجد منتجات مرتبطة بالتصنيف
    $stmt = $conn->prepare(
        "SELECT COUNT(*) AS total
         FROM products
         WHERE category_id = ?"
    );

    $stmt->bind_param("i", $category_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $products_count = (int)$row["total"];

    if ($products_count > 0) {

        $error = "لا يمكن حذف هذا التصنيف لأنه يحتوي على منتجات.";

    } else {

        // حذف التصنيف
        $stmt = $conn->prepare(
            "DELETE FROM categories
             WHERE id = ?"
        );

        $stmt->bind_param("i", $category_id);

        if ($stmt->execute()) {

            $message = "تم حذف التصنيف بنجاح.";

        } else {

            $error = "حدث خطأ أثناء حذف التصنيف.";
        }
    }
}

// جلب جميع التصنيفات
$categories_result = $conn->query(
    "SELECT 
        c.id,
        c.name,
        c.created_at,
        COUNT(p.id) AS products_count
     FROM categories c
     LEFT JOIN products p
        ON c.id = p.category_id
     GROUP BY c.id, c.name, c.created_at
     ORDER BY c.id DESC"
);

?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>إدارة التصنيفات - المتجر الإلكتروني</title>

    <link rel="stylesheet" href="../assets/css/style.css">

</head>

<body>

<header>

    <nav class="navbar">

        <div class="container">

            <h1 class="logo">لوحة الإدارة</h1>

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
                    إدارة التصنيفات
                </h2>

                <a href="index.php" class="btn">
                    العودة للوحة التحكم
                </a>

            </div>


            <?php if (!empty($message)): ?>

                <div class="success-message">

                    <?php echo htmlspecialchars($message); ?>

                </div>

            <?php endif; ?>


            <?php if (!empty($error)): ?>

                <div class="message">

                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <!-- إضافة تصنيف -->

            <div class="admin-form-box">

                <h3 class="section-title">
                    إضافة تصنيف جديد
                </h3>

                <form method="POST" class="admin-form">

                    <div class="form-group">

                        <label for="name">
                            اسم التصنيف
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="مثال: أجهزة لوحية"
                            required
                        >

                    </div>

                    <div class="form-buttons">

                        <button
                            type="submit"
                            class="btn"
                        >
                            إضافة التصنيف
                        </button>

                    </div>

                </form>

            </div>


            <!-- جدول التصنيفات -->

            <div class="admin-table-container">

                <table class="admin-table">

                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                اسم التصنيف
                            </th>

                            <th>
                                عدد المنتجات
                            </th>

                            <th>
                                تاريخ الإضافة
                            </th>

                            <th>
                                الإجراء
                            </th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php if ($categories_result->num_rows > 0): ?>

                            <?php while ($category = $categories_result->fetch_assoc()): ?>

                                <tr>

                                    <td>
                                        <?php echo $category["id"]; ?>
                                    </td>

                                    <td>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $category["name"]
                                            );
                                            ?>
                                        </strong>

                                    </td>

                                    <td>

                                        <?php
                                        echo $category["products_count"];
                                        ?>

                                    </td>

                                    <td>

                                        <?php
                                        echo htmlspecialchars(
                                            $category["created_at"]
                                        );
                                        ?>

                                    </td>

                                    <td>

                                        <div class="admin-actions">

                                            <a
                                                href="categories.php?delete=<?php echo $category["id"]; ?>"
                                                class="delete-btn"
                                                onclick="return confirm('هل أنت متأكد من حذف هذا التصنيف؟');"
                                            >
                                                حذف
                                            </a>

                                        </div>

                                    </td>

                                </tr>

                            <?php endwhile; ?>

                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="5"
                                    class="no-data"
                                >
                                    لا توجد تصنيفات حاليًا.
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
