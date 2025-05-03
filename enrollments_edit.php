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
// enrollments_edit.php - Page to edit an existing enrollment

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store success or error messages
$enrollment = null; // Variable to hold the enrollment data being edited
$students = []; // Array to hold students for dropdown
$classes = []; // Array to hold classes for dropdown


// --- Fetch Students for Dropdown ---
$sql_students = "SELECT StudentID, FirstName, LastName FROM Students ORDER BY LastName, FirstName";
$result_students = $conn->query($sql_students);
if ($result_students && $result_students->num_rows > 0) {
    while($row = $result_students->fetch_assoc()) {
        $students[] = $row;
    }
}

// --- Fetch Classes for Dropdown ---
// Joining with Courses and Teachers to make class options more descriptive
$sql_classes = "SELECT cl.ClassID, co.CourseTitle, t.FirstName AS TeacherFirstName, t.LastName AS TeacherLastName, cl.Semester, cl.Year, cl.RoomNumber
                FROM Classes cl
                JOIN Courses co ON cl.CourseID = co.CourseID
                JOIN Teachers t ON cl.TeacherID = t.TeacherID
                ORDER BY co.CourseTitle, cl.Year, cl.Semester";
$result_classes = $conn->query($sql_classes);
if ($result_classes && $result_classes->num_rows > 0) {
    while($row = $result_classes->fetch_assoc()) {
        $classes[] = $row;
    }
}


// Check if an enrollment ID was provided in the URL (GET request)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $enrollmentId = $_GET['id'];

    // --- Fetch Enrollment Data for Editing ---
    $sql_fetch = "SELECT EnrollmentID, StudentID, ClassID, EnrollmentDate, Grade FROM Enrollments WHERE EnrollmentID = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);

    if ($stmt_fetch === false) {
         $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing fetch statement: " . $conn->error . "</div>";
    } else {
        $stmt_fetch->bind_param("i", $enrollmentId); // 'i' for integer
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();

        if ($result_fetch->num_rows == 1) {
            $enrollment = $result_fetch->fetch_assoc(); // Get the enrollment data
        } else {
            $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Enrollment not found.</div>";
        }
        $stmt_fetch->close();
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Process Form Submission (UPDATE functionality) ---

    // Get form data and sanitize it
    $enrollmentId = $_POST['enrollment_id']; // Get the hidden enrollment ID
    $studentId = $_POST['student_id'];
    $classId = $_POST['class_id'];
    $enrollmentDate = $_POST['enrollment_date'];
    $grade = htmlspecialchars($_POST['grade']);

    // Handle optional fields
    $enrollmentDate = !empty($enrollmentDate) ? $enrollmentDate : null;
    $grade = !empty($grade) ? $grade : null;


    // Prepare an SQL statement to update the enrollment record
    $sql_update = "UPDATE Enrollments SET StudentID = ?, ClassID = ?, EnrollmentDate = ?, Grade = ? WHERE EnrollmentID = ?";

    $stmt_update = $conn->prepare($sql_update);

    if ($stmt_update === false) {
         $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing update statement: " . $conn->error . "</div>";
    } else {
        // Bind parameters
        $stmt_update->bind_param("iissi", $studentId, $classId, $enrollmentDate, $grade, $enrollmentId); // 'i' integers, 's' strings

        // Execute the update statement
        if ($stmt_update->execute()) {
            $message = "<div class='bg-green-800 border border-green-600 text-green-200 px-4 py-3 rounded relative mb-4' role='alert'>Enrollment updated successfully!</div>";
            // Re-fetch the updated enrollment data to show current values in the form
            $sql_re_fetch = "SELECT EnrollmentID, StudentID, ClassID, EnrollmentDate, Grade FROM Enrollments WHERE EnrollmentID = ?";
            $stmt_re_fetch = $conn->prepare($sql_re_fetch);
            if ($stmt_re_fetch) {
                $stmt_re_fetch->bind_param("i", $enrollmentId);
                $stmt_re_fetch->execute();
                $result_re_fetch = $stmt_re_fetch->get_result();
                 if ($result_re_fetch->num_rows == 1) {
                    $enrollment = $result_re_fetch->fetch_assoc();
                }
                $stmt_re_fetch->close();
            }

        } else {
            $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error updating enrollment: " . $stmt_update->error . "</div>";
             // If update failed, try to retain the submitted data in the form (basic approach)
             $enrollment = $_POST;
             $enrollment['EnrollmentID'] = $enrollmentId; // Make sure ID is retained
        }

        // Close the statement
        $stmt_update->close();
    }

} else {
    // No ID provided in GET or POST
    $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>No enrollment ID provided for editing.</div>";
}


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Enrollment</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <style>
        /* Custom styles for font and dark mode background */
        body {
            font-family: "Inter", sans-serif;
            background-color: #1a202c; /* Tailwind gray-900 */
            color: #e2e8f0; /* Tailwind gray-200 */
        }
        /* Style for cards in dark mode */
        .dark-card {
            background-color: #2d3748; /* Tailwind gray-800 */
            color: #e2e8f0; /* Tailwind gray-200 */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
         .dark-form input, .dark-form select {
            background-color: #4a5568; /* Tailwind gray-700 */
            color: #e2e8f0; /* Tailwind gray-200 */
            border: 1px solid #636b7a; /* Tailwind gray-600 */
        }
         .dark-form input:focus, .dark-form select:focus {
             border-color: #4299e1; /* Tailwind blue-400 */
             box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5);
         }
         .dark-form label {
             color: #e2e8f0; /* Tailwind gray-200 */
         }


    </style>
</head>
<body class="dark">

    <div class="container mx-auto dark-card rounded-xl shadow-md p-8 max-w-md">
        <h1 class="text-2xl font-bold mb-6 text-blue-300">
             <i class="fas fa-user-edit mr-2"></i> Edit Enrollment
        </h1>

        <?php echo $message; ?>

        <?php if ($enrollment): // Only show the form if enrollment data was fetched ?>
            <form action="enrollments_edit.php" method="POST" class="dark-form">
                <input type="hidden" name="enrollment_id" value="<?php echo htmlspecialchars($enrollment['EnrollmentID']); ?>">

                 <div class="mb-4">
                    <label for="student_id" class="block text-sm font-bold mb-2">Student:</label>
                    <select name="student_id" id="student_id" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" required>
                         <?php if (count($students) > 0): ?>
                            <?php foreach ($students as $student): ?>
                                <option value="<?php echo htmlspecialchars($student['StudentID']); ?>"
                                    <?php if ($enrollment['StudentID'] == $student['StudentID']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($student['FirstName'] . ' ' . $student['LastName']); ?>
                                </option>
                            <?php endforeach; ?>
                         <?php else: ?>
                             <option value="">No students found</option>
                         <?php endif; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="class_id" class="block text-sm font-bold mb-2">Class:</label>
                    <select name="class_id" id="class_id" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" required>
                         <?php if (count($classes) > 0): ?>
                            <?php foreach ($classes as $class): ?>
                                <option value="<?php echo htmlspecialchars($class['ClassID']); ?>"
                                    <?php if ($enrollment['ClassID'] == $class['ClassID']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($class['CourseTitle'] . ' - ' . $class['Semester'] . ' ' . $class['Year'] . ' (' . $class['RoomNumber'] . ')'); ?>
                                </option>
                            <?php endforeach; ?>
                         <?php else: ?>
                             <option value="">No classes found</option>
                         <?php endif; ?>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="enrollment_date" class="block text-sm font-bold mb-2">Enrollment Date:</label>
                    <input type="date" name="enrollment_date" id="enrollment_date" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($enrollment['EnrollmentDate']); ?>">
                </div>
                 <div class="mb-6">
                    <label for="grade" class="block text-sm font-bold mb-2">Grade (Optional):</label>
                    <input type="text" name="grade" id="grade" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($enrollment['Grade']); ?>">
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                         <i class="fas fa-save mr-2"></i> Update Enrollment
                    </button>
                    <a href="enrollments_list.php" class="inline-block align-baseline font-bold text-sm text-blue-400 hover:text-blue-600">
                         <i class="fas fa-times-circle mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        <?php else: ?>
             <p class="text-gray-400">Enrollment data could not be loaded for editing.</p>
        <?php endif; ?>

    </div>

</body>
</html>
