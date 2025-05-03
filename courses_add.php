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
// courses_add.php - Page to add a new course

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store success or error messages
$departments = []; // Array to hold departments for dropdown

// --- Fetch Departments for Dropdown ---
$sql_departments = "SELECT DepartmentID, DepartmentName FROM Departments ORDER BY DepartmentName";
$result_departments = $conn->query($sql_departments);
if ($result_departments && $result_departments->num_rows > 0) {
    while($row = $result_departments->fetch_assoc()) {
        $departments[] = $row;
    }
}


// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize it
    $courseTitle = htmlspecialchars($_POST['course_title']);
    $credits = htmlspecialchars($_POST['credits']); // Handle as string initially, convert to float/decimal for DB
    $departmentId = $_POST['department_id']; // Get selected department ID

    // Validate and convert credits to a numeric type (decimal in DB)
    $credits_decimal = is_numeric($credits) ? (float)$credits : 0.0;


    // --- Insert Data into Courses Table ---

    // Prepare an SQL statement to prevent SQL injection
    $sql = "INSERT INTO Courses (CourseTitle, Credits, DepartmentID) VALUES (?, ?, ?)";

    // Use prepared statements for security
    $stmt = $conn->prepare($sql);

     if ($stmt === false) {
        $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing statement: " . $conn->error . "</div>";
    } else {
        // Bind parameters to the prepared statement
        // 's' for string, 'd' for double/decimal, 'i' for integer
        $stmt->bind_param("sdi", $courseTitle, $credits_decimal, $departmentId);

        // Execute the prepared statement
        if ($stmt->execute()) {
            $message = "<div class='bg-green-800 border border-green-600 text-green-200 px-4 py-3 rounded relative mb-4' role='alert'>New course added successfully!</div>";
             // Clear form fields after successful submission (optional)
             // $_POST = array(); // This won't clear the select box easily, but clears text inputs
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
    <title>Add New Course</title>
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
             <i class="fas fa-plus-circle mr-2"></i> Add New Course
        </h1>

        <?php echo $message; ?>

        <form action="courses_add.php" method="POST" class="dark-form">
            <div class="mb-4">
                <label for="course_title" class="block text-sm font-bold mb-2">Course Title:</label>
                <input type="text" name="course_title" id="course_title" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="credits" class="block text-sm font-bold mb-2">Credits:</label>
                <input type="number" step="0.1" name="credits" id="credits" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
             <div class="mb-6">
                <label for="department_id" class="block text-sm font-bold mb-2">Department:</label>
                <select name="department_id" id="department_id" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" required>
                     <?php if (count($departments) > 0): ?>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?php echo htmlspecialchars($department['DepartmentID']); ?>">
                                <?php echo htmlspecialchars($department['DepartmentName']); ?>
                            </option>
                        <?php endforeach; ?>
                     <?php else: ?>
                         <option value="">No departments found</option>
                     <?php endif; ?>
                </select>
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                     <i class="fas fa-save mr-2"></i> Add Course
                </button>
                <a href="courses_list.php" class="inline-block align-baseline font-bold text-sm text-blue-400 hover:text-blue-600">
                     <i class="fas fa-times-circle mr-1"></i> Cancel
                </a>
            </div>
        </form>
    </div>

</body>
</html>
