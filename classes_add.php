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
// classes_add.php - Page to add a new class

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store success or error messages
$courses = []; // Array to hold courses for dropdown
$teachers = []; // Array to hold teachers for dropdown

// --- Fetch Courses for Dropdown ---
$sql_courses = "SELECT CourseID, CourseTitle FROM Courses ORDER BY CourseTitle";
$result_courses = $conn->query($sql_courses);
if ($result_courses && $result_courses->num_rows > 0) {
    while($row = $result_courses->fetch_assoc()) {
        $courses[] = $row;
    }
}

// --- Fetch Teachers for Dropdown ---
$sql_teachers = "SELECT TeacherID, FirstName, LastName FROM Teachers ORDER BY LastName, FirstName";
$result_teachers = $conn->query($sql_teachers);
if ($result_teachers && $result_teachers->num_rows > 0) {
    while($row = $result_teachers->fetch_assoc()) {
        $teachers[] = $row;
    }
}


// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize it
    $courseId = $_POST['course_id']; // Get selected course ID
    $teacherId = $_POST['teacher_id']; // Get selected teacher ID
    $semester = htmlspecialchars($_POST['semester']);
    $year = htmlspecialchars($_POST['year']); // Handle as string initially, convert to int for DB
    $scheduleTime = htmlspecialchars($_POST['schedule_time']);
    $roomNumber = htmlspecialchars($_POST['room_number']);

    // Validate and convert year to integer
    $year_int = is_numeric($year) ? (int)$year : null;


    // --- Insert Data into Classes Table ---

    // Prepare an SQL statement to prevent SQL injection
    $sql = "INSERT INTO Classes (CourseID, TeacherID, Semester, Year, ScheduleTime, RoomNumber) VALUES (?, ?, ?, ?, ?, ?)";

    // Use prepared statements for security
    $stmt = $conn->prepare($sql);

     if ($stmt === false) {
        $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing statement: " . $conn->error . "</div>";
    } else {
        // Bind parameters to the prepared statement
        // 'i' for integer, 's' for string
        $stmt->bind_param("iisiis", $courseId, $teacherId, $semester, $year_int, $scheduleTime, $roomNumber);

        // Execute the prepared statement
        if ($stmt->execute()) {
            $message = "<div class='bg-green-800 border border-green-600 text-green-200 px-4 py-3 rounded relative mb-4' role='alert'>New class added successfully!</div>";
             // Clear form fields after successful submission (optional)
             // $_POST = array(); // This won't clear the select boxes easily, but clears text inputs
        } else {
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
    <title>Add New Class</title>
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
             <i class="fas fa-plus-circle mr-2"></i> Add New Class
        </h1>

        <?php echo $message; ?>

        <form action="classes_add.php" method="POST" class="dark-form">
             <div class="mb-4">
                <label for="course_id" class="block text-sm font-bold mb-2">Course:</label>
                <select name="course_id" id="course_id" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" required>
                     <?php if (count($courses) > 0): ?>
                        <?php foreach ($courses as $course): ?>
                            <option value="<?php echo htmlspecialchars($course['CourseID']); ?>">
                                <?php echo htmlspecialchars($course['CourseTitle']); ?>
                            </option>
                        <?php endforeach; ?>
                     <?php else: ?>
                         <option value="">No courses found</option>
                     <?php endif; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="teacher_id" class="block text-sm font-bold mb-2">Teacher:</label>
                <select name="teacher_id" id="teacher_id" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" required>
                     <?php if (count($teachers) > 0): ?>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?php echo htmlspecialchars($teacher['TeacherID']); ?>">
                                <?php echo htmlspecialchars($teacher['FirstName'] . ' ' . $teacher['LastName']); ?>
                            </option>
                        <?php endforeach; ?>
                     <?php else: ?>
                         <option value="">No teachers found</option>
                     <?php endif; ?>
                </select>
            </div>
            <div class="mb-4">
                <label for="semester" class="block text-sm font-bold mb-2">Semester:</label>
                <input type="text" name="semester" id="semester" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="year" class="block text-sm font-bold mb-2">Year:</label>
                <input type="number" name="year" id="year" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
             <div class="mb-4">
                <label for="schedule_time" class="block text-sm font-bold mb-2">Schedule Time:</label>
                <input type="text" name="schedule_time" id="schedule_time" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
             <div class="mb-6">
                <label for="room_number" class="block text-sm font-bold mb-2">Room Number:</label>
                <input type="text" name="room_number" id="room_number" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                     <i class="fas fa-save mr-2"></i> Add Class
                </button>
                <a href="classes_list.php" class="inline-block align-baseline font-bold text-sm text-blue-400 hover:text-blue-600">
                     <i class="fas fa-times-circle mr-1"></i> Cancel
                </a>
            </div>
        </form>
    </div>

</body>
</html>
