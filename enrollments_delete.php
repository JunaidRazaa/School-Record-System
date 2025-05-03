<?php
// enrollments_delete.php - Script to delete an enrollment record

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store message

// Check if an enrollment ID was provided in the URL (GET request)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $enrollmentId = $_GET['id'];

    // --- Delete Data from Enrollments Table ---

    // Prepare an SQL statement to prevent SQL injection
    $sql = "DELETE FROM Enrollments WHERE EnrollmentID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $message = "Error preparing delete statement: " . $conn->error;
    } else {
        // Bind the enrollment ID parameter
        $stmt->bind_param("i", $enrollmentId); // 'i' for integer

        // Execute the delete statement
        if ($stmt->execute()) {
            // Check if any rows were affected (meaning an enrollment was actually deleted)
            if ($stmt->affected_rows > 0) {
                 $message = "Enrollment with ID " . htmlspecialchars($enrollmentId) . " deleted successfully.";
            } else {
                 $message = "No enrollment found with ID " . htmlspecialchars($enrollmentId) . ".";
            }

        } else {
            $message = "Error deleting enrollment: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

} else {
    // No ID provided
    $message = "No enrollment ID provided for deletion.";
}

// Close the database connection
$conn->close();

// --- Redirect back to the enrollments list page after deletion ---
// We pass the message back via a URL parameter to display it on the list page
header("Location: enrollments_list.php?message=" . urlencode($message)); // Redirect back to the list page
exit(); // Stop script execution after redirect
?>
