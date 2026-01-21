<?php
// Secure this page: only logged-in users who NEED to change their password can see it.
require_once 'session_auth.php';
check_auth(); // No specific role needed, just needs to be logged in.
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Set Your Password - SportsWatch Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style> body { font-family: 'Inter', sans-serif; } </style>
</head>
<body class="bg-slate-50 flex items-center justify-center h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-lg rounded-xl p-8">
            <h2 class="text-2xl font-bold text-center text-slate-800">Set Your New Password</h2>
            <p class="text-center text-slate-500 mt-2 mb-6">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>! For security, please create a new password.</p>
            
            <form id="set-password-form">
                <div class="space-y-4">
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-slate-700">New Password</label>
                        <input type="password" id="new_password" name="new_password" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm" required>
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-slate-700">Confirm New Password</label>
                        <input type="password" id="confirm_password" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm" required>
                    </div>
                </div>
                <div id="error-message" class="text-red-500 text-sm mt-4 text-center hidden"></div>
                <div class="mt-6">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-indigo-700">Set Password and Continue</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        lucide.createIcons();
        const form = document.getElementById('set-password-form');
        const errorMessage = document.getElementById('error-message');
        const newPass = document.getElementById('new_password');
        const confirmPass = document.getElementById('confirm_password');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorMessage.classList.add('hidden');

            if (newPass.value !== confirmPass.value) {
                errorMessage.textContent = 'Passwords do not match.';
                errorMessage.classList.remove('hidden');
                return;
            }

            const formData = new FormData(form);
            try {
                const response = await fetch('api.php?action=updatePassword', { method: 'POST', body: formData });
                const result = await response.json();
                if (result.status === 'success') {
                    alert('Password updated successfully! You will now be redirected to your dashboard.');
                    // Get role from session to redirect correctly (a bit of a trick)
                    const userRole = "<?php echo $_SESSION['role']; ?>";
                    window.location.href = userRole + 'Dashboard.php';
                } else {
                    errorMessage.textContent = result.message;
                    errorMessage.classList.remove('hidden');
                }
            } catch (error) {
                errorMessage.textContent = 'An error occurred. Please try again.';
                errorMessage.classList.remove('hidden');
            }
        });
    </script>
</body>
</html>
