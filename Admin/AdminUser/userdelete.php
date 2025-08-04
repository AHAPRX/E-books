<?php
include "../Components/checkifadmin.php";
include "../../Config/db.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "DELETE FROM users WHERE UserID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: admin.users.php");
        exit();
    } else {
        echo "Not Succesfull";
    }

    $stmt->close();
} else {
    echo "Something Went Wrong";
}

$conn->close();
?>
