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
// enrollments_add.php - Page to add a new enrollment

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store success or error messages
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


// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize it
    $studentId = $_POST['student_id']; // Get selected student ID
    $classId = $_POST['class_id']; // Get selected class ID
    $enrollmentDate = $_POST['enrollment_date']; // Optional: Date format YYYY-MM-DD
    $grade = htmlspecialchars($_POST['grade']); // Optional: Grade

    // Handle optional fields
    $enrollmentDate = !empty($enrollmentDate) ? $enrollmentDate : null;
    $grade = !empty($grade) ? $grade : null;


    // --- Insert Data into Enrollments Table ---

    // Prepare an SQL statement to prevent SQL injection
    $sql = "INSERT INTO Enrollments (StudentID, ClassID, EnrollmentDate, Grade) VALUES (?, ?, ?, ?)";

    // Use prepared statements for security
    $stmt = $conn->prepare($sql);

     if ($stmt === false) {
        $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing statement: " . $conn->error . "</div>";
    } else {
        // Bind parameters to the prepared statement
        // 'i' for integer, 's' for string (for date and grade which can be null)
        $stmt->bind_param("iiss", $studentId, $classId, $enrollmentDate, $grade);

        // Execute the prepared statement
        if ($stmt->execute()) {
            $message = "<div class='bg-green-800 border border-green-600 text-green-200 px-4 py-3 rounded relative mb-4' role='alert'>New enrollment added successfully!</div>";
             // Clear form fields after successful submission (optional)
             // $_POST = array(); // This won't clear the select boxes easily, but clears text inputs
        } else {
            // This error might occur if the student is already enrolled in this class (UNIQUE constraint)
            $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error: " . $stmt->error . "</div>";
        }

        // Close the statement
        $stmt->close();
    }
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Enrollment</title>
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
             <i class="fas fa-plus-circle mr-2"></i> Add New Enrollment
        </h1>

        <?php echo $message; ?>

        <form action="enrollments_add.php" method="POST" class="dark-form">
             <div class="mb-4">
                <label for="student_id" class="block text-sm font-bold mb-2">Student:</label>
                <select name="student_id" id="student_id" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" required>
                     <?php if (count($students) > 0): ?>
                        <?php foreach ($students as $student): ?>
                            <option value="<?php echo htmlspecialchars($student['StudentID']); ?>">
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
                            <option value="<?php echo htmlspecialchars($class['ClassID']); ?>">
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
                <input type="date" name="enrollment_date" id="enrollment_date" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo date('Y-m-d'); ?>"> </div>
             <div class="mb-6">
                <label for="grade" class="block text-sm font-bold mb-2">Grade (Optional):</label>
                <input type="text" name="grade" id="grade" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                     <i class="fas fa-save mr-2"></i> Add Enrollment
                </button>
                <a href="enrollments_list.php" class="inline-block align-baseline font-bold text-sm text-blue-400 hover:text-blue-600">
                     <i class="fas fa-times-circle mr-1"></i> Cancel
                </a>
            </div>
        </form>
    </div>

</body>
</html>
