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
                <?php if(empty($_SESSION['id'])): ?>
                <button class="login"><a href="/login">Log In</a></button>
                <button class="signup">
                    <i class="fa-solid fa-user"></i><a href="/register">Sign Up</a>
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
                        <a href="#" style="color: inherit;"><i class="fa-solid fa-cart-shopping"></i></a>
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