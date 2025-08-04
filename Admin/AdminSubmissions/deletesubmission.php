<?php
include "../Components/checkifadmin.php";
include "../../Config/db.php";

if (isset($_GET['id'])) {
    $submissionID = intval($_GET['id']);

    $stmt = $conn->prepare("DELETE FROM submissions WHERE SubmissionID = ?");
    $stmt->bind_param("i", $submissionID);

    if ($stmt->execute()) {
        header("Location: submissions.php?success=Submission deleted.");
    } else {
        echo "Failed to delete submission.";
    }

    $stmt->close();
} else {
    echo "Invalid request.";
}
?>
