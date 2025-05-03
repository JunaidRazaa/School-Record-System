<?php
// departments_delete.php - Script to delete a department record

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store message

// Check if a department ID was provided in the URL (GET request)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $departmentId = $_GET['id'];

    // --- Delete Data from Departments Table ---

    // Prepare an SQL statement to prevent SQL injection
    // NOTE: Deleting a department might fail if there are related records in the Teachers or Courses tables
    // due to foreign key constraints. You might need to delete related teachers/courses first,
    // or configure ON DELETE CASCADE in your database schema if that behavior is desired.
    $sql = "DELETE FROM Departments WHERE DepartmentID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $message = "Error preparing delete statement: " . $conn->error;
    } else {
        // Bind the department ID parameter
        $stmt->bind_param("i", $departmentId); // 'i' for integer

        // Execute the delete statement
        if ($stmt->execute()) {
            // Check if any rows were affected (meaning a department was actually deleted)
            if ($stmt->affected_rows > 0) {
                 $message = "Department with ID " . htmlspecialchars($departmentId) . " deleted successfully.";
            } else {
                 $message = "No department found with ID " . htmlspecialchars($departmentId) . ".";
            }

        } else {
            // This error might occur due to foreign key constraints (e.g., department has teachers or courses)
            $message = "Error deleting department: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

} else {
    // No ID provided
    $message = "No department ID provided for deletion.";
}

// Close the database connection
$conn->close();

// --- Redirect back to the departments list page after deletion ---
// We pass the message back via a URL parameter to display it on the list page
header("Location: departments_list.php?message=" . urlencode($message)); // Redirect back to the list page
exit(); // Stop script execution after redirect
?>
