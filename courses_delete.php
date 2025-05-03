<?php
// courses_delete.php - Script to delete a course record

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store message

// Check if a course ID was provided in the URL (GET request)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $courseId = $_GET['id'];

    // --- Delete Data from Courses Table ---

    // Prepare an SQL statement to prevent SQL injection
    // NOTE: Deleting a course might fail if there are related records in the Classes table
    // due to foreign key constraints. You might need to delete related classes first,
    // or configure ON DELETE CASCADE in your database schema if that behavior is desired.
    $sql = "DELETE FROM Courses WHERE CourseID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $message = "Error preparing delete statement: " . $conn->error;
    } else {
        // Bind the course ID parameter
        $stmt->bind_param("i", $courseId); // 'i' for integer

        // Execute the delete statement
        if ($stmt->execute()) {
            // Check if any rows were affected (meaning a course was actually deleted)
            if ($stmt->affected_rows > 0) {
                 $message = "Course with ID " . htmlspecialchars($courseId) . " deleted successfully.";
            } else {
                 $message = "No course found with ID " . htmlspecialchars($courseId) . ".";
            }

        } else {
            // This error might occur due to foreign key constraints (e.g., course has classes)
            $message = "Error deleting course: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

} else {
    // No ID provided
    $message = "No course ID provided for deletion.";
}

// Close the database connection
$conn->close();

// --- Redirect back to the courses list page after deletion ---
// We pass the message back via a URL parameter to display it on the list page
header("Location: courses_list.php?message=" . urlencode($message)); // Redirect back to the list page
exit(); // Stop script execution after redirect
?>
