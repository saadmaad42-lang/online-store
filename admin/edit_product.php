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
| التحقق من وجود رقم المنتج
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: products.php");
    exit;
}

$product_id = (int)$_GET["id"];


/*
|--------------------------------------------------------------------------
| المتغيرات
|--------------------------------------------------------------------------
*/

$name = "";
$description = "";
$price = "";
$stock = "";
$category_id = "";

$message = "";
$error = "";


/*
|--------------------------------------------------------------------------
| جلب بيانات المنتج
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare(
    "SELECT id, name, description, price, stock, category_id
     FROM products
     WHERE id = ?"
);

$stmt->bind_param("i", $product_id);
$stmt->execute();

$result = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| التحقق من وجود المنتج
|--------------------------------------------------------------------------
*/

if ($result->num_rows === 0) {

    header("Location: products.php");
    exit;
}


$product = $result->fetch_assoc();


/*
|--------------------------------------------------------------------------
| وضع بيانات المنتج في المتغيرات
|--------------------------------------------------------------------------
*/

$name = $product["name"];
$description = $product["description"];
$price = $product["price"];
$stock = $product["stock"];
$category_id = $product["category_id"];


/*
|--------------------------------------------------------------------------
| جلب التصنيفات
|--------------------------------------------------------------------------
*/

$categories_result = $conn->query(
    "SELECT id, name
     FROM categories
     ORDER BY name ASC"
);


/*
|--------------------------------------------------------------------------
| معالجة التعديل
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");
    $category_id = (int)($_POST["category_id"] ?? 0);


    /*
    |--------------------------------------------------------------------------
    | التحقق من البيانات
    |--------------------------------------------------------------------------
    */

    if ($name === "") {

        $error = "يرجى إدخال اسم المنتج.";

    } elseif ($category_id <= 0) {

        $error = "يرجى اختيار التصنيف.";

    } elseif (
        $price === "" ||
        !is_numeric($price) ||
        $price < 0
    ) {

        $error = "يرجى إدخال سعر صحيح.";

    } elseif (
        $stock === "" ||
        !is_numeric($stock) ||
        $stock < 0
    ) {

        $error = "يرجى إدخال كمية مخزون صحيحة.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | تحويل القيم
        |--------------------------------------------------------------------------
        */

        $price = (float)$price;
        $stock = (int)$stock;


        /*
        |--------------------------------------------------------------------------
        | تحديث المنتج
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare(
            "UPDATE products
             SET
                category_id = ?,
                name = ?,
                description = ?,
                price = ?,
                stock = ?
             WHERE id = ?"
        );

        $stmt->bind_param(
            "issdii",
            $category_id,
            $name,
            $description,
            $price,
            $stock,
            $product_id
        );


        if ($stmt->execute()) {

            $message = "تم تعديل المنتج بنجاح.";

        } else {

            $error = "حدث خطأ أثناء تعديل المنتج.";
        }
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
        تعديل المنتج - المتجر الإلكتروني
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

            <div class="admin-form-box">

                <h2 class="section-title">
                    تعديل المنتج
                </h2>


                <!-- رسالة النجاح -->

                <?php if (!empty($message)): ?>

                    <div class="success-message">

                        <?php
                        echo htmlspecialchars($message);
                        ?>

                    </div>

                <?php endif; ?>


                <!-- رسالة الخطأ -->

                <?php if (!empty($error)): ?>

                    <div class="message">

                        <?php
                        echo htmlspecialchars($error);
                        ?>

                    </div>

                <?php endif; ?>


                <!-- ==============================
                     Edit Form
                ============================== -->

                <form
                    method="POST"
                    class="admin-form"
                >


                    <!-- اسم المنتج -->

                    <div class="form-group">

                        <label for="name">
                            اسم المنتج
                        </label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?php
                            echo htmlspecialchars($name);
                            ?>"
                            required
                        >

                    </div>


                    <!-- التصنيف -->

                    <div class="form-group">

                        <label for="category_id">
                            التصنيف
                        </label>

                        <select
                            id="category_id"
                            name="category_id"
                            required
                        >

                            <option value="">
                                اختر التصنيف
                            </option>


                            <?php while (
                                $category =
                                $categories_result->fetch_assoc()
                            ): ?>

                                <option
                                    value="<?php
                                    echo $category["id"];
                                    ?>"
                                    <?php
                                    if (
                                        $category_id ==
                                        $category["id"]
                                    ) {
                                        echo "selected";
                                    }
                                    ?>
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $category["name"]
                                    );
                                    ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <!-- السعر -->

                    <div class="form-group">

                        <label for="price">
                            السعر بالدولار
                        </label>

                        <input
                            type="number"
                            id="price"
                            name="price"
                            value="<?php
                            echo htmlspecialchars($price);
                            ?>"
                            step="0.01"
                            min="0"
                            required
                        >

                    </div>


                    <!-- المخزون -->

                    <div class="form-group">

                        <label for="stock">
                            الكمية في المخزون
                        </label>

                        <input
                            type="number"
                            id="stock"
                            name="stock"
                            value="<?php
                            echo htmlspecialchars($stock);
                            ?>"
                            min="0"
                            required
                        >

                    </div>


                    <!-- الوصف -->

                    <div class="form-group">

                        <label for="description">
                            وصف المنتج
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            rows="5"
                        ><?php
                        echo htmlspecialchars($description);
                        ?></textarea>

                    </div>


                    <!-- الأزرار -->

                    <div class="form-buttons">

                        <button
                            type="submit"
                            class="btn"
                        >
                            حفظ التعديلات
                        </button>


                        <a
                            href="products.php"
                            class="cancel-btn"
                        >
                            إلغاء
                        </a>

                    </div>


                </form>

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
