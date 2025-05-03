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
// courses_edit.php - Page to edit an existing course

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store success or error messages
$course = null; // Variable to hold the course data being edited
$departments = []; // Array to hold departments for dropdown

// --- Fetch Departments for Dropdown ---
$sql_departments = "SELECT DepartmentID, DepartmentName FROM Departments ORDER BY DepartmentName";
$result_departments = $conn->query($sql_departments);
if ($result_departments && $result_departments->num_rows > 0) {
    while($row = $result_departments->fetch_assoc()) {
        $departments[] = $row;
    }
}


// Check if a course ID was provided in the URL (GET request)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $courseId = $_GET['id'];

    // --- Fetch Course Data for Editing ---
    $sql_fetch = "SELECT CourseID, CourseTitle, Credits, DepartmentID FROM Courses WHERE CourseID = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);

    if ($stmt_fetch === false) {
         $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing fetch statement: " . $conn->error . "</div>";
    } else {
        $stmt_fetch->bind_param("i", $courseId); // 'i' for integer
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();

        if ($result_fetch->num_rows == 1) {
            $course = $result_fetch->fetch_assoc(); // Get the course data
        } else {
            $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Course not found.</div>";
        }
        $stmt_fetch->close();
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Process Form Submission (UPDATE functionality) ---

    // Get form data and sanitize it
    $courseId = $_POST['course_id']; // Get the hidden course ID
    $courseTitle = htmlspecialchars($_POST['course_title']);
    $credits = htmlspecialchars($_POST['credits']);
    $departmentId = $_POST['department_id'];

     // Validate and convert credits to a numeric type (decimal in DB)
    $credits_decimal = is_numeric($credits) ? (float)$credits : 0.0;


    // Prepare an SQL statement to update the course record
    $sql_update = "UPDATE Courses SET CourseTitle = ?, Credits = ?, DepartmentID = ? WHERE CourseID = ?";

    $stmt_update = $conn->prepare($sql_update);

    if ($stmt_update === false) {
         $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing update statement: " . $conn->error . "</div>";
    } else {
        // Bind parameters
        $stmt_update->bind_param("sdii", $courseTitle, $credits_decimal, $departmentId, $courseId); // 's' string, 'd' decimal, 'i' integers

        // Execute the update statement
        if ($stmt_update->execute()) {
            $message = "<div class='bg-green-800 border border-green-600 text-green-200 px-4 py-3 rounded relative mb-4' role='alert'>Course updated successfully!</div>";
            // Re-fetch the updated course data to show current values in the form
            $sql_re_fetch = "SELECT CourseID, CourseTitle, Credits, DepartmentID FROM Courses WHERE CourseID = ?";
            $stmt_re_fetch = $conn->prepare($sql_re_fetch);
            if ($stmt_re_fetch) {
                $stmt_re_fetch->bind_param("i", $courseId);
                $stmt_re_fetch->execute();
                $result_re_fetch = $stmt_re_fetch->get_result();
                 if ($result_re_fetch->num_rows == 1) {
                    $course = $result_re_fetch->fetch_assoc();
                }
                $stmt_re_fetch->close();
            }

        } else {
            $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error updating course: " . $stmt_update->error . "</div>";
             // If update failed, try to retain the submitted data in the form (basic approach)
             $course = $_POST;
             $course['CourseID'] = $courseId; // Make sure ID is retained
        }

        // Close the statement
        $stmt_update->close();
    }

} else {
    // No ID provided in GET or POST
    $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>No course ID provided for editing.</div>";
}


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
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
             <i class="fas fa-user-edit mr-2"></i> Edit Course
        </h1>

        <?php echo $message; ?>

        <?php if ($course): // Only show the form if course data was fetched ?>
            <form action="courses_edit.php" method="POST" class="dark-form">
                <input type="hidden" name="course_id" value="<?php echo htmlspecialchars($course['CourseID']); ?>">

                <div class="mb-4">
                    <label for="course_title" class="block text-sm font-bold mb-2">Course Title:</label>
                    <input type="text" name="course_title" id="course_title" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($course['CourseTitle']); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="credits" class="block text-sm font-bold mb-2">Credits:</label>
                    <input type="number" step="0.1" name="credits" id="credits" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($course['Credits']); ?>">
                </div>
                 <div class="mb-6">
                    <label for="department_id" class="block text-sm font-bold mb-2">Department:</label>
                    <select name="department_id" id="department_id" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" required>
                         <?php if (count($departments) > 0): ?>
                            <?php foreach ($departments as $department): ?>
                                <option value="<?php echo htmlspecialchars($department['DepartmentID']); ?>"
                                    <?php if ($course['DepartmentID'] == $department['DepartmentID']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($department['DepartmentName']); ?>
                                </option>
                            <?php endforeach; ?>
                         <?php else: ?>
                             <option value="">No departments found</option>
                         <?php endif; ?>
                    </select>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                         <i class="fas fa-save mr-2"></i> Update Course
                    </button>
                    <a href="courses_list.php" class="inline-block align-baseline font-bold text-sm text-blue-400 hover:text-blue-600">
                         <i class="fas fa-times-circle mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        <?php else: ?>
             <p class="text-gray-400">Course data could not be loaded for editing.</p>
        <?php endif; ?>

    </div>

</body>
</html>
