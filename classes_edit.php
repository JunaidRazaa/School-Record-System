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
// classes_edit.php - Page to edit an existing class

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store success or error messages
$class = null; // Variable to hold the class data being edited
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


// Check if a class ID was provided in the URL (GET request)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $classId = $_GET['id'];

    // --- Fetch Class Data for Editing ---
    $sql_fetch = "SELECT ClassID, CourseID, TeacherID, Semester, Year, ScheduleTime, RoomNumber FROM Classes WHERE ClassID = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);

    if ($stmt_fetch === false) {
         $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing fetch statement: " . $conn->error . "</div>";
    } else {
        $stmt_fetch->bind_param("i", $classId); // 'i' for integer
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();

        if ($result_fetch->num_rows == 1) {
            $class = $result_fetch->fetch_assoc(); // Get the class data
        } else {
            $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Class not found.</div>";
        }
        $stmt_fetch->close();
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Process Form Submission (UPDATE functionality) ---

    // Get form data and sanitize it
    $classId = $_POST['class_id']; // Get the hidden class ID
    $courseId = $_POST['course_id'];
    $teacherId = $_POST['teacher_id'];
    $semester = htmlspecialchars($_POST['semester']);
    $year = htmlspecialchars($_POST['year']);
    $scheduleTime = htmlspecialchars($_POST['schedule_time']);
    $roomNumber = htmlspecialchars($_POST['room_number']);

    // Validate and convert year to integer
    $year_int = is_numeric($year) ? (int)$year : null;


    // Prepare an SQL statement to update the class record
    $sql_update = "UPDATE Classes SET CourseID = ?, TeacherID = ?, Semester = ?, Year = ?, ScheduleTime = ?, RoomNumber = ? WHERE ClassID = ?";

    $stmt_update = $conn->prepare($sql_update);

    if ($stmt_update === false) {
         $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing update statement: " . $conn->error . "</div>";
    } else {
        // Bind parameters
        $stmt_update->bind_param("iisiisi", $courseId, $teacherId, $semester, $year_int, $scheduleTime, $roomNumber, $classId); // 'i' integers, 's' strings

        // Execute the update statement
        if ($stmt_update->execute()) {
            $message = "<div class='bg-green-800 border border-green-600 text-green-200 px-4 py-3 rounded relative mb-4' role='alert'>Class updated successfully!</div>";
            // Re-fetch the updated class data to show current values in the form
            $sql_re_fetch = "SELECT ClassID, CourseID, TeacherID, Semester, Year, ScheduleTime, RoomNumber FROM Classes WHERE ClassID = ?";
            $stmt_re_fetch = $conn->prepare($sql_re_fetch);
            if ($stmt_re_fetch) {
                $stmt_re_fetch->bind_param("i", $classId);
                $stmt_re_fetch->execute();
                $result_re_fetch = $stmt_re_fetch->get_result();
                 if ($result_re_fetch->num_rows == 1) {
                    $class = $result_re_fetch->fetch_assoc();
                }
                $stmt_re_fetch->close();
            }

        } else {
            $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error updating class: " . $stmt_update->error . "</div>";
             // If update failed, try to retain the submitted data in the form (basic approach)
             $class = $_POST;
             $class['ClassID'] = $classId; // Make sure ID is retained
        }

        // Close the statement
        $stmt_update->close();
    }

} else {
    // No ID provided in GET or POST
    $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>No class ID provided for editing.</div>";
}


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Class</title>
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
             <i class="fas fa-user-edit mr-2"></i> Edit Class
        </h1>

        <?php echo $message; ?>

        <?php if ($class): // Only show the form if class data was fetched ?>
            <form action="classes_edit.php" method="POST" class="dark-form">
                <input type="hidden" name="class_id" value="<?php echo htmlspecialchars($class['ClassID']); ?>">

                 <div class="mb-4">
                    <label for="course_id" class="block text-sm font-bold mb-2">Course:</label>
                    <select name="course_id" id="course_id" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" required>
                         <?php if (count($courses) > 0): ?>
                            <?php foreach ($courses as $course): ?>
                                <option value="<?php echo htmlspecialchars($course['CourseID']); ?>"
                                    <?php if ($class['CourseID'] == $course['CourseID']) echo 'selected'; ?>>
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
                                <option value="<?php echo htmlspecialchars($teacher['TeacherID']); ?>"
                                    <?php if ($class['TeacherID'] == $teacher['TeacherID']) echo 'selected'; ?>>
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
                    <input type="text" name="semester" id="semester" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($class['Semester']); ?>">
                </div>
                <div class="mb-4">
                    <label for="year" class="block text-sm font-bold mb-2">Year:</label>
                    <input type="number" name="year" id="year" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($class['Year']); ?>">
                </div>
                 <div class="mb-4">
                    <label for="schedule_time" class="block text-sm font-bold mb-2">Schedule Time:</label>
                    <input type="text" name="schedule_time" id="schedule_time" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($class['ScheduleTime']); ?>">
                </div>
                 <div class="mb-6">
                    <label for="room_number" class="block text-sm font-bold mb-2">Room Number:</label>
                    <input type="text" name="room_number" id="room_number" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($class['RoomNumber']); ?>">
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                         <i class="fas fa-save mr-2"></i> Update Class
                    </button>
                    <a href="classes_list.php" class="inline-block align-baseline font-bold text-sm text-blue-400 hover:text-blue-600">
                         <i class="fas fa-times-circle mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        <?php else: ?>
             <p class="text-gray-400">Class data could not be loaded for editing.</p>
        <?php endif; ?>

    </div>

</body>
</html>
