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
// students_edit.php - Page to edit an existing student

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store success or error messages
$student = null; // Variable to hold the student data being edited

// Check if a student ID was provided in the URL (GET request)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $studentId = $_GET['id'];

    // --- Fetch Student Data for Editing ---
    $sql_fetch = "SELECT StudentID, FirstName, LastName, DateOfBirth, Gender, Address, PhoneNumber, Email FROM Students WHERE StudentID = ?";
    $stmt_fetch = $conn->prepare($sql_fetch);

    if ($stmt_fetch === false) {
         $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing fetch statement: " . $conn->error . "</div>";
    } else {
        $stmt_fetch->bind_param("i", $studentId); // 'i' for integer
        $stmt_fetch->execute();
        $result_fetch = $stmt_fetch->get_result();

        if ($result_fetch->num_rows == 1) {
            $student = $result_fetch->fetch_assoc(); // Get the student data
        } else {
            $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Student not found.</div>";
        }
        $stmt_fetch->close();
    }

} elseif ($_SERVER["REQUEST_METHOD"] == "POST") {
    // --- Process Form Submission (UPDATE functionality) ---

    // Get form data and sanitize it
    $studentId = $_POST['student_id']; // Get the hidden student ID
    $firstName = htmlspecialchars($_POST['first_name']);
    $lastName = htmlspecialchars($_POST['last_name']);
    $dateOfBirth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $address = htmlspecialchars($_POST['address']);
    $phoneNumber = htmlspecialchars($_POST['phone_number']);
    $email = htmlspecialchars($_POST['email']);

    // Prepare an SQL statement to update the student record
    $sql_update = "UPDATE Students SET FirstName = ?, LastName = ?, DateOfBirth = ?, Gender = ?, Address = ?, PhoneNumber = ?, Email = ? WHERE StudentID = ?";

    $stmt_update = $conn->prepare($sql_update);

    if ($stmt_update === false) {
         $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing update statement: " . $conn->error . "</div>";
    } else {
        // Bind parameters
        $stmt_update->bind_param("sssssssi", $firstName, $lastName, $dateOfBirth, $gender, $address, $phoneNumber, $email, $studentId); // 'i' for integer ID

        // Execute the update statement
        if ($stmt_update->execute()) {
            $message = "<div class='bg-green-800 border border-green-600 text-green-200 px-4 py-3 rounded relative mb-4' role='alert'>Student updated successfully!</div>";
            // Re-fetch the updated student data to show current values in the form
            $sql_re_fetch = "SELECT StudentID, FirstName, LastName, DateOfBirth, Gender, Address, PhoneNumber, Email FROM Students WHERE StudentID = ?";
            $stmt_re_fetch = $conn->prepare($sql_re_fetch);
            if ($stmt_re_fetch) {
                $stmt_re_fetch->bind_param("i", $studentId);
                $stmt_re_fetch->execute();
                $result_re_fetch = $stmt_re_fetch->get_result();
                 if ($result_re_fetch->num_rows == 1) {
                    $student = $result_re_fetch->fetch_assoc();
                }
                $stmt_re_fetch->close();
            }

        } else {
            $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error updating student: " . $stmt_update->error . "</div>";
             // If update failed, try to retain the submitted data in the form (basic approach)
             $student = $_POST;
             $student['StudentID'] = $studentId; // Make sure ID is retained
        }

        // Close the statement
        $stmt_update->close();
    }

} else {
    // No ID provided in GET or POST
    $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>No student ID provided for editing.</div>";
}


// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Student</title>
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
             <i class="fas fa-user-edit mr-2"></i> Edit Student
        </h1>

        <?php echo $message; ?>

        <?php if ($student): // Only show the form if student data was fetched ?>
            <form action="students_edit.php" method="POST" class="dark-form">
                <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['StudentID']); ?>">

                <div class="mb-4">
                    <label for="first_name" class="block text-sm font-bold mb-2">First Name:</label>
                    <input type="text" name="first_name" id="first_name" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($student['FirstName']); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="last_name" class="block text-sm font-bold mb-2">Last Name:</label>
                    <input type="text" name="last_name" id="last_name" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($student['LastName']); ?>" required>
                </div>
                <div class="mb-4">
                    <label for="date_of_birth" class="block text-sm font-bold mb-2">Date of Birth:</label>
                    <input type="date" name="date_of_birth" id="date_of_birth" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($student['DateOfBirth']); ?>">
                </div>
                <div class="mb-4">
                    <label for="gender" class="block text-sm font-bold mb-2">Gender:</label>
                    <select name="gender" id="gender" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
                        <option value="Male" <?php if ($student['Gender'] == 'Male') echo 'selected'; ?>>Male</option>
                        <option value="Female" <?php if ($student['Gender'] == 'Female') echo 'selected'; ?>>Female</option>
                        <option value="Other" <?php if ($student['Gender'] == 'Other') echo 'selected'; ?>>Other</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label for="address" class="block text-sm font-bold mb-2">Address:</label>
                    <input type="text" name="address" id="address" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($student['Address']); ?>">
                </div>
                <div class="mb-4">
                    <label for="phone_number" class="block text-sm font-bold mb-2">Phone Number:</label>
                    <input type="text" name="phone_number" id="phone_number" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($student['PhoneNumber']); ?>">
                </div>
                <div class="mb-6">
                    <label for="email" class="block text-sm font-bold mb-2">Email:</label>
                    <input type="email" name="email" id="email" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" value="<?php echo htmlspecialchars($student['Email']); ?>">
                </div>

                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                         <i class="fas fa-save mr-2"></i> Update Student
                    </button>
                    <a href="students_list.php" class="inline-block align-baseline font-bold text-sm text-blue-400 hover:text-blue-600">
                         <i class="fas fa-times-circle mr-1"></i> Cancel
                    </a>
                </div>
            </form>
        <?php endif; ?>

    </div>

</body>
</html>
