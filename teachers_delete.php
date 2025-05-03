<?php
// teachers_delete.php - Script to delete a teacher record

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store message

// Check if a teacher ID was provided in the URL (GET request)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $teacherId = $_GET['id'];

    // --- Delete Data from Teachers Table ---

    // Prepare an SQL statement to prevent SQL injection
    // NOTE: Deleting a teacher might fail if there are related records in the Classes table
    // due to foreign key constraints. You might need to delete related classes first,
    // or configure ON DELETE CASCADE in your database schema if that behavior is desired.
    $sql = "DELETE FROM Teachers WHERE TeacherID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $message = "Error preparing delete statement: " . $conn->error;
    } else {
        // Bind the teacher ID parameter
        $stmt->bind_param("i", $teacherId); // 'i' for integer

        // Execute the delete statement
        if ($stmt->execute()) {
            // Check if any rows were affected (meaning a teacher was actually deleted)
            if ($stmt->affected_rows > 0) {
                 $message = "Teacher with ID " . htmlspecialchars($teacherId) . " deleted successfully.";
            } else {
                 $message = "No teacher found with ID " . htmlspecialchars($teacherId) . ".";
            }

        } else {
            // This error might occur due to foreign key constraints (e.g., teacher teaches a class)
            $message = "Error deleting teacher: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

} else {
    // No ID provided
    $message = "No teacher ID provided for deletion.";
}

// Close the database connection
$conn->close();

// --- Redirect back to the teachers list page after deletion ---
// We pass the message back via a URL parameter to display it on the list page
header("Location: teachers_list.php?message=" . urlencode($message)); // Redirect back to the list page
exit(); // Stop script execution after redirect
?>
