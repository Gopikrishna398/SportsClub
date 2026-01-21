<?php
// This block of PHP code will run when the form is submitted.

// Initialize message variables
$success_message = null;
$error_message = null;

// Check if the form was submitted by checking the request method
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Include the database configuration
    require_once 'config.php';

    // Check if the required fields are set
    if (isset($_POST['name'], $_POST['email'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';

        // Basic validation
        if (empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = 'Invalid name or email provided.';
        } else {
            // Check if email already exists in users table or applications table
            $stmt = $conn->prepare("SELECT email FROM users WHERE email = ? UNION SELECT email FROM volunteer_applications WHERE email = ?");
            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error_message = 'This email is already in use or has a pending application.';
            } else {
                // Insert the application into the database
                $stmt = $conn->prepare("INSERT INTO volunteer_applications (name, email, reason) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $name, $email, $reason);
                
                if ($stmt->execute()) {
                    $success_message = 'Application submitted successfully! The admin will review it shortly.';
                } else {
                    $error_message = 'Database error. Could not submit application.';
                }
            }
            $stmt->close();
        }
    } else {
        $error_message = 'Please provide your name and email.';
    }
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Application - SportsWatch Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex items-center justify-center h-screen py-12">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-lg rounded-xl p-8">
            <div class="flex justify-center mb-6">
                 <div class="bg-indigo-600 p-3 rounded-full">
                    <i data-lucide="send" class="w-8 h-8 text-white"></i>
                </div>
            </div>
            <h2 class="text-2xl font-bold text-center text-slate-800">Apply to be a Volunteer</h2>
            <p class="text-center text-slate-500 mt-2 mb-6">Submit your application to help manage the tournament.</p>
            
            <!-- The form action is empty, so it submits to this same file -->
            <form id="apply-form" method="POST" action="applyVolunteer.php">
                <div class="space-y-4">
                    <div>
                        <label for="name" class="block text-sm font-medium text-slate-700">Full Name</label>
                        <input type="text" id="name" name="name" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm" required>
                    </div>
                     <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                        <input type="email" id="email" name="email" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm" required>
                    </div>
                    <div>
                        <label for="reason" class="block text-sm font-medium text-slate-700">Why do you want to volunteer? (Optional)</label>
                        <textarea id="reason" name="reason" rows="3" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm"></textarea>
                    </div>
                </div>

                <!-- PHP will display success or error messages here -->
                <?php if ($error_message): ?>
                    <div class="text-red-500 text-sm mt-4 text-center"><?php echo $error_message; ?></div>
                <?php endif; ?>
                <?php if ($success_message): ?>
                    <div class="text-green-500 text-sm mt-4 text-center"><?php echo $success_message; ?></div>
                <?php endif; ?>

                <div class="mt-6">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-indigo-700 transition-colors">
                        Submit Application
                    </button>
                </div>
            </form>
            <p class="text-center text-sm text-slate-500 mt-6">
                <a href="index.php" class="font-semibold text-indigo-600 hover:underline">&larr; Back to Login</a>
            </p>
        </div>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>