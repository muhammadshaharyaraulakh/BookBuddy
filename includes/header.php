<?php 
require __DIR__."/../config/config.php";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Book Store Website</title>
    <link rel="stylesheet" href="/assests/css/style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="apple-touch-icon" sizes="57x57" href="favicon/apple-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="60x60" href="favicon/apple-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="72x72" href="favicon/apple-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="76x76" href="favicon/apple-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="114x114" href="favicon/apple-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="120x120" href="favicon/apple-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="144x144" href="favicon/apple-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="152x152" href="favicon/apple-icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="favicon/apple-icon-180x180.png">
    <link rel="icon" type="image/png" sizes="192x192" href="favicon/android-icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="96x96" href="favicon/favicon-96x96.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon/favicon-16x16.png">
    <link rel="manifest" href="favicon/manifest.json">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="/ms-icon-144x144.png">
    <meta name="theme-color" content="#ffffff">
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <div class="img">
                    <img src="/images/logo.png" alt="Logo" />
                </div>
                <div class="logo-header">
                    <h4><a href="/index.php">Book Buddy</a></h4>
                    <small>Book Store Website</small>
                    
                </div>
            </div>

            <ul class="nav-list">

                <div class="logo logo-header-heisenberger">
                    <div class="img">
                        <img src="images/logo.png" alt="Logo" />
                    </div>
                    <div class="logo-header">
                        <h4><a href="/index.php">Book Buddy</a></h4>
                        <small>Book Store Website</small>
                    </div>

                    <button class="close"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <li><a href="/index.php">Home</a></li>
                <li><a href="/pages/books.php">Books</a></li>
                <?php if(empty($_SESSION['id'])): ?>
                <button class="login"><a href="/auth/login/login.php">Log In</a></button>
                <button class="signup">
                    <i class="fa-solid fa-user"></i><a href="/auth/registration/registration.php">Sign Up</a>
                </button>
                <?php else: ?>
                <button class="signup">
                    <a href="/auth/logout.php">Logout</a>
                </button>
                <?php endif; ?>    
            </ul>

            <div class="nav-actions">

                <div class="nav-end">
                    <?php if (!empty($_SESSION['id'])) : ?>
                    <button class="likebtn">
                        <i class="fa-regular fa-heart"></i>
                        <span>2</span>
                    </button>
                    <button class="cart">
                        <a href="/pages/cart.php" style="color: inherit;"><i class="fa-solid fa-cart-shopping"></i></a>
                        <span>2</span>
                    </button>
                    <div class="profile-img">
                        <a href="/pages/profile.php">
                            <img src="/userImages/<?= htmlspecialchars($_SESSION['image'] ?? 'image.png') ?>" alt="Profile">
                        </a>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="hamburger">
                    <div class="line"></div>
                    <div class="line"></div>
                    <div class="line"></div>
                </div>
            </div>
        </nav>
    </header>