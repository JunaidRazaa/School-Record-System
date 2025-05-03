<?php
// Start the session
session_start();

// Check if the user is NOT logged in
if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to the login page
    header("Location: login.php");
    exit(); // Stop script execution
}

// User is logged in, continue with the rest of the page logic
// You can access user info like $_SESSION['user_id'], $_SESSION['username'], $_SESSION['role']
?>
<?php
// students_delete.php - Script to delete a student record

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store message

// Check if a student ID was provided in the URL (GET request)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $studentId = $_GET['id'];

    // --- Delete Data from Students Table ---

    // Prepare an SQL statement to prevent SQL injection
    $sql = "DELETE FROM Students WHERE StudentID = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $message = "Error preparing delete statement: " . $conn->error;
    } else {
        // Bind the student ID parameter
        $stmt->bind_param("i", $studentId); // 'i' for integer

        // Execute the delete statement
        if ($stmt->execute()) {
            // Check if any rows were affected (meaning a student was actually deleted)
            if ($stmt->affected_rows > 0) {
                 $message = "Student with ID " . htmlspecialchars($studentId) . " deleted successfully.";
            } else {
                 $message = "No student found with ID " . htmlspecialchars($studentId) . ".";
            }

        } else {
            $message = "Error deleting student: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    }

} else {
    // No ID provided
    $message = "No student ID provided for deletion.";
}

// Close the database connection
$conn->close();

// --- Redirect back to the students list page after deletion ---
// You can pass the message back to the list page via a URL parameter if desired
// For simplicity, we'll just redirect. You could add a session variable for messages.
header("Location: students_list.php?message=" . urlencode($message)); // Redirect back to the list page
exit(); // Stop script execution after redirect
?>
