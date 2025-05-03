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
// departments_edit.php - Page to edit an existing department

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store success or error messages
$department = null; // Variable to hold the department data being edited


// Check if a department ID was provided in the URL (GET request)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $departmentId = $_GET['id'];

    // --- Fetch Department Data for Editing ---
    $sql_fetch = "SELECT DepartmentID, DepartmentName FROM Departments WHERE DepartmentID = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);

    if ($stmt_fetch === false) {
         $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing fetch statement: " . $conn->error . "</div>";
    } else {
        $stmt_fetch->bind_param("i", $departmentId); // 'i' for integer
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();

        if ($result_fetch->num_rows == 1) {
            $department = $result_fetch->fetch_assoc(); // Get the department data
        } else {
            $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Department not found.</div>";
        }
        $stmt_fetch->close();
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Process Form Submission (UPDATE functionality) ---

    // Get form data and sanitize it
    $departmentId = $_POST['department_id']; // Get the hidden department ID
    $departmentName = htmlspecialchars($_POST['department_name']);


    // Prepare an SQL statement to update the department record
    $sql_update = "UPDATE Departments SET DepartmentName = ? WHERE DepartmentID = ?";

    $stmt_update = $conn->prepare($sql_update);

    if ($stmt_update === false) {
         $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing update statement: " . $conn->error . "</div>";
    } else {
        // Bind parameters
        $stmt_update->bind_param("si", $departmentName, $departmentId); // 's' string, 'i' integer

        // Execute the update statement
        if ($stmt_update->execute()) {
            $message = "<div class='bg-green-800 border border-green-600 text-green-200 px-4 py-3 rounded relative mb-4' role='alert'>Department updated successfully!</div>";
            // Re-fetch the updated department data to show current values in the form
            $sql_re_fetch = "SELECT DepartmentID, DepartmentName FROM Departments WHERE DepartmentID = ?";
            $stmt_re_fetch = $conn->prepare($sql_re_fetch);
            if ($stmt_re_fetch) {
                $stmt_re_fetch->bind_param("i", $departmentId);
                $stmt_re_fetch->execute();
                $result_re_fetch = $stmt_re_fetch->get_result();
                 if ($result_re_fetch->num_rows == 1) {
                    $department = $result_re_fetch->fetch_assoc();
                }
                $stmt_re_fetch->close();
            }

        } else {
            $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error updating department: " . $stmt_update->error . "</div>";
             // If update failed, try to retain the submitted data in the form (basic approach)
             $department = $_POST;
             $department['DepartmentID'] = $departmentId; // Make sure ID is retained
        }

        // Close the statement
        $stmt_update->close();
    }

} else {
    // No ID provided in GET or POST
    $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>No department ID provided for editing.</div>";
}


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Department</title>
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
             <i class="fas fa-user-edit mr-2"></i> Edit Department
        </h1>

        <?php echo $message; ?>

        <?php if ($department): // Only show the form if department data was fetched ?>
            <form action="departments_edit.php" method="POST" class="dark-form">
                <input type="hidden" name="department_id" value="<?php echo htmlspecialchars($department['DepartmentID']); ?>">

                <div class="mb-4">
                    <label for="department_name" class="block text-sm font-bold mb-2">Department Name:</label>
                    <input type="text" name="department_name" id="department_name" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($department['DepartmentName']); ?>" required>
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                         <i class="fas fa-save mr-2"></i> Update Department
                    </button>
                    <a href="departments_list.php" class="inline-block align-baseline font-bold text-sm text-blue-400 hover:text-blue-600">
                         <i class="fas fa-times-circle mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        <?php else: ?>
             <p class="text-gray-400">Department data could not be loaded for editing.</p>
        <?php endif; ?>

    </div>

</body>
</html>
