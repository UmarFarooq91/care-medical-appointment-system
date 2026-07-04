

<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <link rel="stylesheet" href="css/style.css">

    <title>Document</title>
</head>
<body>


<div class="container-fluid navbar-page-container fixed-top">
    <nav class="navbar navbar-expand-lg navbar-page-navbar">
        <div class="container">

            <!-- Brand -->
            <a class="navbar-brand" href="index.php"><span>+</span> CARE</a>

            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler border" type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent"
                aria-expanded="false"
                aria-label="Toggle navigation">
                <i class="bi bi-list fs-2"></i>
            </button>

            <!-- Menu -->

            <div class="collapse navbar-collapse" id="navbarSupportedContent">

                <ul class="navbar-nav mb-2 mb-lg-0">

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>"" href="index.php">Home</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'about.php') ? 'active' : ''; ?>" href="about.php">About</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'contact.php') ? 'active' : ''; ?>" href="contact.php">Contact</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link <?php echo ($currentPage == 'finddoctor.php') ? 'active' : ''; ?>" href="finddoctor.php">Find Doctor</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link login-btn" href="auth.php">Login</a>
                    </li>

                </ul>

            </div>

        </div>
    </nav>
</div>

