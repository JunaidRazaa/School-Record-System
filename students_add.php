<?php
// students_add.php - Page to add a new student

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store success or error messages

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize it
    $firstName = htmlspecialchars($_POST['first_name']);
    $lastName = htmlspecialchars($_POST['last_name']);
    $dateOfBirth = $_POST['date_of_birth']; // Date format should be YYYY-MM-DD
    $gender = $_POST['gender'];
    $address = htmlspecialchars($_POST['address']);
    $phoneNumber = htmlspecialchars($_POST['phone_number']);
    $email = htmlspecialchars($_POST['email']);

    // --- Insert Data into Students Table ---

    // Prepare an SQL statement to prevent SQL injection
    $sql = "INSERT INTO Students (FirstName, LastName, DateOfBirth, Gender, Address, PhoneNumber, Email) VALUES (?, ?, ?, ?, ?, ?, ?)";

    // Use prepared statements for security
    $stmt = $conn->prepare($sql);

     if ($stmt === false) {
        $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Error preparing statement: " . $conn->error . "</div>";
    } else {
        // Bind parameters to the prepared statement
        // 's' for string, 'd' for double, 'i' for integer, 'b' for blob
        $stmt->bind_param("sssssss", $firstName, $lastName, $dateOfBirth, $gender, $address, $phoneNumber, $email);

        // Execute the prepared statement
        if ($stmt->execute()) {
            $message = "<div class='bg-green-800 border border-green-600 text-green-200 px-4 py-3 rounded relative mb-4' role='alert'>New student added successfully!</div>";
             // Clear form fields after successful submission (optional)
             $_POST = array();
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
    <title>Add New Student</title>
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
             <i class="fas fa-user-plus mr-2"></i> Add New Student
        </h1>

        <?php echo $message; ?>

        <form action="students_add.php" method="POST" class="dark-form">
            <div class="mb-4">
                <label for="first_name" class="block text-sm font-bold mb-2">First Name:</label>
                <input type="text" name="first_name" id="first_name" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="last_name" class="block text-sm font-bold mb-2">Last Name:</label>
                <input type="text" name="last_name" id="last_name" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-4">
                <label for="date_of_birth" class="block text-sm font-bold mb-2">Date of Birth:</label>
                <input type="date" name="date_of_birth" id="date_of_birth" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="gender" class="block text-sm font-bold mb-2">Gender:</label>
                <select name="gender" id="gender" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="mb-4">
                <label for="address" class="block text-sm font-bold mb-2">Address:</label>
                <input type="text" name="address" id="address" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-4">
                <label for="phone_number" class="block text-sm font-bold mb-2">Phone Number:</label>
                <input type="text" name="phone_number" id="phone_number" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>
            <div class="mb-6">
                <label for="email" class="block text-sm font-bold mb-2">Email:</label>
                <input type="email" name="email" id="email" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline">
            </div>

            <div class="flex items-center justify-between">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                     <i class="fas fa-save mr-2"></i> Add Student
                </button>
                <a href="students_list.php" class="inline-block align-baseline font-bold text-sm text-blue-400 hover:text-blue-600">
                     <i class="fas fa-times-circle mr-1"></i> Cancel
                </a>
            </div>
        </form>
    </div>

</body>
</html>
