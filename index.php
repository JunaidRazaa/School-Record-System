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
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Record System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Custom styles for font and dark mode background */
        body {
            font-family: "Inter", sans-serif; /* Using Inter font */
            background-color: #1a202c; /* Tailwind gray-900 */
            color: #e2e8f0; /* Tailwind gray-200 */
        }
         .dark-card {
            background-color: #2d3748; /* Tailwind gray-800 */
            color: #e2e8f0; /* Tailwind gray-200 */
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
         .dark-link-button {
             display: inline-block;
             background-color: #4a5568; /* Tailwind gray-700 */
             color: #e2e8f0; /* Tailwind gray-200 */
             font-weight: bold;
             padding: 1rem 1.5rem; /* py-4 px-6 */
             border-radius: 0.5rem; /* rounded-lg */
             transition: background-color 0.3s ease-in-out;
             text-decoration: none; /* Remove underline */
         }
         .dark-link-button:hover {
             background-color: #636b7a; /* Tailwind gray-600 */
         }

    </style>
</head>
<body class="dark flex items-center justify-center min-h-screen">

    <div class="container mx-auto p-6 text-center">
        <h1 class="text-4xl font-bold mb-10 text-blue-400">
            <i class="fas fa-school mr-4"></i> Welcome to School Record System
        </h1>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <a href="students_list.php" class="dark-link-button dark-card">
                <i class="fas fa-users mr-3"></i> Manage Students
            </a>
            <a href="teachers_list.php" class="dark-link-button dark-card">
                 <i class="fas fa-chalkboard-teacher mr-3"></i> Manage Teachers
            </a>
            <a href="courses_list.php" class="dark-link-button dark-card">
                 <i class="fas fa-book-open mr-3"></i> Manage Courses
            </a>
            <a href="classes_list.php" class="dark-link-button dark-card">
                 <i class="fas fa-chalkboard mr-3"></i> Manage Classes
            </a>
             <a href="departments_list.php" class="dark-link-button dark-card">
                 <i class="fas fa-building mr-3"></i> Manage Departments
            </a>
             <a href="enrollments_list.php" class="dark-link-button dark-card">
                 <i class="fas fa-user-check mr-3"></i> Manage Enrollments
            </a>
        </div>
        <div class="mt-8">   <a href="logout.php" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out shadow-lg ml-4">
    <i class="fas fa-sign-out-alt mr-2"></i> Logout
</a>
</div>
     
    </div>
   
</body>
</html>
