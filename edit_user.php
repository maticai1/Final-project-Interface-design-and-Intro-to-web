<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$name = $email = '';
$success_message = $error_message = '';


$stmt = $conn->prepare("SELECT name, email, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($name, $email, $profile_image);
    $stmt->fetch();
} else {
    $error_message = "User not found";
    header("Location: index.php");
    exit();
}
$stmt->close();


if (isset($_POST['update'])) {
    $new_name = trim($_POST['name']);
    $new_email = trim($_POST['email']);
    $new_password = trim($_POST['password']);
    
   
    if (empty($new_name) || empty($new_email)) {
        $error_message = "Name and email are mandatory";
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Email invalid";
    } else {
        try {
            
            $conn->begin_transaction();
            
            
            $update_stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            $update_stmt->bind_param("ssi", $new_name, $new_email, $user_id);
            $update_stmt->execute();
            
            
            if (!empty($new_password)) {
                $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                $pass_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $pass_stmt->bind_param("si", $hashed_password, $user_id);
                $pass_stmt->execute();
                $pass_stmt->close();
            }
            
            
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
                $upload_dir = 'uploads/profiles/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $new_filename = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
                $target_file = $upload_dir . $new_filename;
                
                
                $valid_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                if (in_array(strtolower($file_ext), $valid_extensions)) {
                    if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
                        
                        $img_stmt = $conn->prepare("UPDATE users SET profile_image = ? WHERE id = ?");
                        $img_stmt->bind_param("si", $new_filename, $user_id);
                        $img_stmt->execute();
                        $img_stmt->close();
                        
                        
                        if (!empty($profile_image) && file_exists($upload_dir . $profile_image)) {
                            unlink($upload_dir . $profile_image);
                        }
                    }
                }
            }
            
            $conn->commit();
            $success_message = "Profile update succesfully";
            
            
            $_SESSION['user_name'] = $new_name;
            
           
            header("Location: edit_user.php");
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error_message = "Error on updating: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/private_styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="main-content">
        <div class="profile-container">
            <div class="wave-effect"></div> 
            
            <div class="profile-header">
                <h2><i class="fas fa-user-edit"></i> EDIT YOUR PROFILE</h2>
                
                <div class="profile-picture">
                    <img src="<?= !empty($profile_image) ? 'uploads/profiles/' . htmlspecialchars($profile_image) : 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=150&background=' . str_replace('#', '', '1a237e') . '&color=ffffff'; ?>" alt="Foto de perfil">
                    <div class="edit-icon" onclick="document.getElementById('profile_image').click()">
                        <i class="fas fa-camera"></i>
                    </div>
                </div>
            </div>
            
            <?php if ($success_message): ?>
                <div class="message success"><?= htmlspecialchars($success_message) ?></div>
            <?php endif; ?>
            
            <?php if ($error_message): ?>
                <div class="message error"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            
            <form class="profile-form" method="post" enctype="multipart/form-data">
                <input type="file" id="profile_image" name="profile_image" accept="image/*" class="hidden">
                
                <div class="form-group">
                    <label for="name"><i class="fas fa-signature"></i> Full Name</label>
                    <input type="text" id="name" name="name" value="<?= htmlspecialchars($name) ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email"><i class="fas fa-envelope"></i> Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>
                </div>
                
                <div class="form-group password-toggle">
                    <label for="password"><i class="fas fa-lock"></i> New Password</label>
                    <input type="password" id="password" name="password" placeholder="If you dont want to change the password, dont put anything">
                    <i class="fas fa-eye" id="togglePassword"></i>
                </div>
                
                <button type="submit" name="update" class="btn btn-block">
                    <i class="fas fa-save"></i> Confirm
                </button>
            </form>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#password');
        
        togglePassword.addEventListener('click', function() {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye-slash');
        });
        
       
        document.getElementById('profile_image').addEventListener('change', function(e) {
            if (this.files && this.files[0]) {
                const reader = new FileReader();
                const profileImg = document.querySelector('.profile-picture img');
                
                reader.onload = function(e) {
                    profileImg.src = e.target.result;
                }
                
                reader.readAsDataURL(this.files[0]);
            }
        });
    </script>
</body>
</html>