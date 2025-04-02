<?php
include 'includes/db.php';

$sql = file_get_contents('sql/database.sql');

if ($conn->multi_query($sql)) {
    echo "Database created.";
} else {
    echo "Error creating database: " . $conn->error;
}

$conn->close();
?>
