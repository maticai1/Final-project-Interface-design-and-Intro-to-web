<?php
session_start();
require 'includes/db.php';


if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$users = [];
$stmt = $conn->prepare("SELECT id, name, email, created_at FROM users");
$stmt->execute();
$result = $stmt->get_result();
while($row = $result->fetch_assoc()) {
    $users[] = $row;
}
$stmt->close();

include 'includes/header.php';
?>

<link rel="stylesheet" href="assets/css/private_styles.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

<div class="profile-container">
    <div class="profile-header">
        <h2><i class="fas fa-users-cog"></i> Users Panel</h2>
        <p>Edit all the users on our platform</p>
    </div>

    <div class="users-table-container">
        <table class="users-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Register Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($users as $user): ?>
                <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= date('d/m/Y', strtotime($user['created_at'])) ?></td>
                    <td class="actions">
                        <a href="edit_user.php?id=<?= $user['id'] ?>" class="action-btn edit-btn">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="delete_user.php?id=<?= $user['id'] ?>" class="action-btn delete-btn" 
                           onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                            <i class="fas fa-trash-alt"></i> Delete
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'includes/footer.php'; ?>