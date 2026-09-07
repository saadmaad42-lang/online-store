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
| جلب جميع المنتجات
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        products.id,
        products.name,
        products.description,
        products.price,
        products.image,
        products.stock,
        products.created_at,
        categories.name AS category_name
    FROM products

    INNER JOIN categories
        ON products.category_id = categories.id

    ORDER BY products.id DESC
";

$result = $conn->query($sql);

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
        إدارة المنتجات - المتجر الإلكتروني
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
                إدارة المنتجات
            </h1>


            <ul class="nav-links">

                <li>
                    <a href="index.php">
                        لوحة التحكم
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


<!-- ==============================
     Main
============================== -->

<main>

    <section class="admin-section">

        <div class="container">


            <div class="admin-page-header">

                <h2 class="section-title">
                    إدارة المنتجات
                </h2>


                <a
                    href="add_product.php"
                    class="btn"
                >
                    + إضافة منتج جديد
                </a>

            </div>


            <!-- ==============================
                 Products Table
            ============================== -->

            <div class="admin-table-container">

                <table class="admin-table">

                    <thead>

                        <tr>

                            <th>
                                #
                            </th>

                            <th>
                                الصورة
                            </th>

                            <th>
                                المنتج
                            </th>

                            <th>
                                التصنيف
                            </th>

                            <th>
                                السعر
                            </th>

                            <th>
                                المخزون
                            </th>

                            <th>
                                الإجراءات
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if ($result->num_rows > 0): ?>


                        <?php while ($product = $result->fetch_assoc()): ?>

                            <tr>


                                <!-- ID -->

                                <td>

                                    <?php
                                    echo $product["id"];
                                    ?>

                                </td>


                                <!-- Image -->

                                <td>

                                    <div class="admin-product-image">

                                        <?php if (!empty($product["image"])): ?>

                                            <img
                                                src="../<?php
                                                echo htmlspecialchars(
                                                    $product["image"]
                                                );
                                                ?>"
                                                alt="<?php
                                                echo htmlspecialchars(
                                                    $product["name"]
                                                );
                                                ?>"
                                            >

                                        <?php else: ?>

                                            📦

                                        <?php endif; ?>

                                    </div>

                                </td>


                                <!-- Product -->

                                <td>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $product["name"]
                                        );
                                        ?>

                                    </strong>


                                    <small>

                                        <?php
                                        echo htmlspecialchars(
                                            mb_substr(
                                                $product["description"],
                                                0,
                                                60
                                            )
                                        );
                                        ?>

                                    </small>

                                </td>


                                <!-- Category -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $product["category_name"]
                                    );
                                    ?>

                                </td>


                                <!-- Price -->

                                <td>

                                    $

                                    <?php
                                    echo number_format(
                                        $product["price"],
                                        2
                                    );
                                    ?>

                                </td>


                                <!-- Stock -->

                                <td>

                                    <?php if ($product["stock"] > 0): ?>

                                        <span class="stock-available">

                                            <?php
                                            echo $product["stock"];
                                            ?>

                                        </span>

                                    <?php else: ?>

                                        <span class="stock-empty">
                                            نفد المخزون
                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- Actions -->

                                <td>

                                    <div class="admin-actions">

                                        <a
                                            href="edit_product.php?id=<?php
                                            echo $product["id"];
                                            ?>"
                                            class="edit-btn"
                                        >
                                            تعديل
                                        </a>


                                        <a
                                            href="delete_product.php?id=<?php
                                            echo $product["id"];
                                            ?>"
                                            class="delete-btn"
                                            onclick="return confirm(
                                                'هل أنت متأكد من حذف هذا المنتج؟'
                                            );"
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
                                colspan="7"
                                class="no-data"
                            >

                                لا توجد منتجات حاليًا.

                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

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
