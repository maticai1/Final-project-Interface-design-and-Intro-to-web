<?php
require 'includes/db.php';

$error_message = "";
$success_message = "";

if (isset($_POST['register'])) {
    $first_name = trim($_POST['first-name']);
    $last_name = trim($_POST['last-name']);
    $age = intval($_POST['age']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Email invalid.";
    } else {
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_email->bind_param("s", $email);
        $check_email->execute();
        $check_email->store_result();

        if ($check_email->num_rows > 0) {
            $error_message = "Email already registered.";
        } else {
            $image_path = null;
            if (!empty($_FILES['profile-pic']['name'])) {
                $target_dir = "assets/img/";
                $image_name = basename($_FILES["profile-pic"]["name"]);
                $image_path = $target_dir . $image_name;
                
                $check = getimagesize($_FILES["profile-pic"]["tmp_name"]);
                if ($check !== false) {
                    move_uploaded_file($_FILES["profile-pic"]["tmp_name"], $image_path);
                } else {
                    $error_message = "Image not valid.";
                    $image_path = null;
                }
            }

            if (!$error_message) {
                $stmt = $conn->prepare("INSERT INTO users (name, last_name, age, email, password, profile_image) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssisss", $first_name, $last_name, $age, $email, $password, $image_path);

                if ($stmt->execute()) {
                    $success_message = "Successfully registered. <a href='login.php'>ILogin</a>";
                } else {
                    $error_message = "Error on the register.";
                }
                $stmt->close();
            }
        }
        $check_email->close();
    }
    $conn->close();
}

include 'includes/header.php';
?>

<div class="register-container">
    <h2>Create Your Account</h2>
    <form class="register-form" action="register.php" method="POST" enctype="multipart/form-data">
        <?php if ($error_message): ?>
            <p class="error"><?= htmlspecialchars($error_message) ?></p>
        <?php endif; ?>
        <?php if ($success_message): ?>
            <p class="success"><?= htmlspecialchars($success_message) ?></p>
        <?php endif; ?>
        <div class="form-group">
            <label for="first-name">First Name</label>
            <input type="text" id="first-name" name="first-name" required>
        </div>
        <div class="form-group">
            <label for="last-name">Last Name</label>
            <input type="text" id="last-name" name="last-name" required>
        </div>
        <div class="form-group">
            <label for="age">Age</label>
            <input type="number" id="age" name="age" required>
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="profile-pic">Profile Picture</label>
            <input type="file" id="profile-pic" name="profile-pic" accept="image/*">
        </div>
        <button type="submit" name="register">Register</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
