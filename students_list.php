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
// students_list.php - Page to list students with CRUD links

// Include the database connection file
include 'db_connect.php';

// --- Fetch Students Data ---
$sql = "SELECT StudentID, FirstName, LastName, Email, PhoneNumber FROM Students ORDER BY StudentID DESC"; // Order by ID for latest first
$result = $conn->query($sql);

$students = []; // Array to hold student data
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $students[] = $row;
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
    <title>Manage Students</title>
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
        /* Table styling for dark mode */
        .dark-table th {
            background-color: #4a5568; /* Tailwind gray-700 */
            color: #e2e8f0; /* Tailwind gray-200 */
        }
        .dark-table td {
             border-bottom: 1px solid #4a5568; /* Tailwind gray-700 */
        }
         .dark-table tr:hover {
            background-color: #4a5568; /* Tailwind gray-700 */
        }
         /* Style for action links */
        .action-link {
            color: #63b3ed; /* Tailwind blue-300 */
            margin-right: 0.75rem; /* mr-3 */
        }
         .action-link:hover {
            color: #4299e1; /* Tailwind blue-400 */
        }
         .delete-link {
            color: #fc8181; /* Tailwind red-300 */
        }
         .delete-link:hover {
            color: #f56565; /* Tailwind red-400 */
        }

    </style>
</head>
<body class="dark">

    <div class="container mx-auto p-6">
        <h1 class="text-3xl font-bold mb-8 text-center text-blue-400">
            <i class="fas fa-users mr-3"></i> Manage Students
        </h1>

        <div class="mb-6 text-center">
            <a href="students_add.php" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out shadow-lg">
                 <i class="fas fa-user-plus mr-2"></i> Add New Student
            </a>
             <a href="index.php" class="ml-4 bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded-lg transition duration-300 ease-in-out shadow-lg">
                 <i class="fas fa-arrow-left mr-2"></i> Back to Dashboard
            </a>
        </div>

        <div class="dark-card rounded-xl p-6">
             <h2 class="text-xl font-semibold mb-4 text-blue-300">All Students</h2>
             <?php if (count($students) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full rounded-lg overflow-hidden dark-table">
                        <thead class="bg-gray-700">
                            <tr>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-300">ID</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-300">First Name</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-300">Last Name</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-300">Email</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-300">Phone</th>
                                <th class="py-3 px-4 text-left text-sm font-semibold text-gray-300">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-200">
                            <?php foreach ($students as $student): ?>
                                <tr class="border-b border-gray-700 hover:bg-gray-700">
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($student['StudentID']); ?></td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($student['FirstName']); ?></td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($student['LastName']); ?></td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($student['Email']); ?></td>
                                    <td class="py-3 px-4"><?php echo htmlspecialchars($student['PhoneNumber']); ?></td>
                                    <td class="py-3 px-4">
                                        <a href="students_edit.php?id=<?php echo $student['StudentID']; ?>" class="action-link" title="Edit Student">
                                            <i class="fas fa-edit"></i> Edit
                                        </a>
                                        <a href="students_delete.php?id=<?php echo $student['StudentID']; ?>" class="action-link delete-link" title="Delete Student" onclick="return confirm('Are you sure you want to delete this student?');">
                                            <i class="fas fa-trash-alt"></i> Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-gray-400">No students found in the database.</p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
