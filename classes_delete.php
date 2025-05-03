<?php
// classes_delete.php - Script to delete a class record

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store message

// Check if a class ID was provided in the URL (GET request)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $classId = $_GET['id'];

    // --- Delete Data from Classes Table ---

    // Prepare an SQL statement to prevent SQL injection
    // NOTE: Deleting a class might fail if there are related records in the Enrollments table
    // due to foreign key constraints. You might need to delete related enrollments first,
    // or configure ON DELETE CASCADE in your database schema if that behavior is desired.
    $sql = "DELETE FROM Classes WHERE ClassID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $message = "Error preparing delete statement: " . $conn->error;
    } else {
        // Bind the class ID parameter
        $stmt->bind_param("i", $classId); // 'i' for integer

        // Execute the delete statement
        if ($stmt->execute()) {
            // Check if any rows were affected (meaning a class was actually deleted)
            if ($stmt->affected_rows > 0) {
                 $message = "Class with ID " . htmlspecialchars($classId) . " deleted successfully.";
            } else {
                 $message = "No class found with ID " . htmlspecialchars($classId) . ".";
            }

        } else {
            // This error might occur due to foreign key constraints (e.g., class has enrollments)
            $message = "Error deleting class: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

} else {
    // No ID provided
    $message = "No class ID provided for deletion.";
}

// Close the database connection
$conn->close();

// --- Redirect back to the classes list page after deletion ---
// We pass the message back via a URL parameter to display it on the list page
header("Location: classes_list.php?message=" . urlencode($message)); // Redirect back to the list page
exit(); // Stop script execution after redirect
?>
