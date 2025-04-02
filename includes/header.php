<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Store</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="assets/img/Logo.webp" alt="Logo of valorant">
        </div>
        <nav>
            <ul>
                <li><a href="index.php">Startup</a></li>
                <li><a href="catalogue.php">Catalogue</a></li>
                <li><a href="about.php">About</a></li>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="user_panel.php"><i class="fas fa-users-cog"></i> User Panel</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Log out (<?= htmlspecialchars($_SESSION['user_name']); ?>)</a></li>
                <?php else: ?>
                    <li><a href="register.php">Register</a></li>
                    <li><a href="login.php">Login</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>