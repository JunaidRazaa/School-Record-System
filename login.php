<?php
// login.php - Handles user login

// Start the session
session_start();

// Include the database connection file
include 'db_connect.php';

$message = ''; // Variable to store login messages

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    // If logged in, redirect to the dashboard
    header("Location: index.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data and sanitize it
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);

    // Prepare SQL statement to fetch user by username
    $sql = "SELECT UserID, Username, PasswordHash, Role FROM users WHERE Username = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Database error: Could not prepare statement.</div>";
    } else {
        // Bind parameter
        $stmt->bind_param("s", $username); // 's' for string

        // Execute statement
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            // User found, fetch the user data
            $user = $result->fetch_assoc();

            // Verify the submitted password against the stored hash
            if (password_verify($password, $user['PasswordHash'])) {
                // Password is correct, start session and store user info
                $_SESSION['user_id'] = $user['UserID'];
                $_SESSION['username'] = $user['Username'];
                $_SESSION['role'] = $user['Role']; // Store the user's role

                // Redirect to the dashboard
                header("Location: index.php");
                exit();

            } else {
                // Password incorrect
                $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Invalid username or password.</div>";
            }
        } else {
            // User not found
            $message = "<div class='bg-red-800 border border-red-600 text-red-200 px-4 py-3 rounded relative mb-4' role='alert'>Invalid username or password.</div>";
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
    <title>Login - School Record System</title>
    <script src="https://cdn.tailwindcss.com"></script>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
     <style>
        /* Custom styles for font and dark mode background */
        body {
            font-family: "Inter", sans-serif; /* Using Inter font */
            background-color: #1a202c; /* Tailwind gray-900 */
            color: #e2e8f0; /* Tailwind gray-200 */
        }
        /* Style for cards in dark mode */
        .dark-card {
            background-color: #2d3748; /* Tailwind gray-800 */
            color: #e2e8f0; /* Tailwind gray-200 */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
         .dark-form input {
            background-color: #4a5568; /* Tailwind gray-700 */
            color: #e2e8f0; /* Tailwind gray-200 */
            border: 1px solid #636b7a; /* Tailwind gray-600 */
        }
         .dark-form input:focus {
             border-color: #4299e1; /* Tailwind blue-400 */
             box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.5);
         }
         .dark-form label {
             color: #e2e8f0; /* Tailwind gray-200 */
         }


    </style>
</head>
<body class="dark flex items-center justify-center min-h-screen">

    <div class="dark-card rounded-xl shadow-md p-8 max-w-sm w-full">
        <h1 class="text-2xl font-bold mb-6 text-center text-blue-300">
             <i class="fas fa-lock mr-2"></i> System Login
        </h1>

        <?php echo $message; ?>

        <form action="login.php" method="POST" class="dark-form">
            <div class="mb-4">
                <label for="username" class="block text-sm font-bold mb-2">Username:</label>
                <input type="text" name="username" id="username" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-bold mb-2">Password:</label>
                <input type="password" name="password" id="password" class="shadow appearance-none rounded-lg w-full py-2 px-3 leading-tight focus:outline-none focus:shadow-outline" required>
            </div>

            <div class="flex items-center justify-center">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg focus:outline-none focus:shadow-outline transition duration-300 ease-in-out">
                     <i class="fas fa-sign-in-alt mr-2"></i> Login
                </button>
            </div>
        </form>
    </div>

</body>
</html>
