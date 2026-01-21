<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SportsWatch Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 flex items-center justify-center h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-lg rounded-xl p-8">
            <div class="flex justify-center mb-6">
                 <div class="bg-indigo-600 p-3 rounded-full">
                    <i data-lucide="shield-check" class="w-8 h-8 text-white"></i>
                </div>
            </div>
            <h2 class="text-2xl font-bold text-center text-slate-800">Welcome Back!</h2>
            <p class="text-center text-slate-500 mt-2 mb-6">Sign in to continue to SportsWatch Hub.</p>
            
            <!-- The form now submits directly to auth.php -->
            <form id="login-form" method="POST" action="auth.php?action=login">
                <div class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700">Username</label>
                        <input type="text" id="username" name="username" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        <input type="password" id="password" name="password" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                </div>
                <div id="error-message" class="text-red-500 text-sm mt-4 text-center hidden"></div>
                <div class="mt-6">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-indigo-700 transition-colors flex items-center justify-center">
                        Login
                    </button>
                </div>
            </form>
            <div class="mt-6 text-center text-sm text-slate-500 space-y-2">
                <p>
                    Don't have an account? <a href="./register.php" class="font-semibold text-indigo-600 hover:underline">Register here</a>
                </p>
                <p>
                    Want to volunteer? <a href="./applyVolunteer.php" class="font-semibold text-indigo-600 hover:underline">Apply here</a>
                </p>
            </div>
        </div>
    </div>
    <script>
        lucide.createIcons();
        
        // This script now only checks for error messages in the URL.
        const errorMessage = document.getElementById('error-message');
        const urlParams = new URLSearchParams(window.location.search);

        if (urlParams.has('error')) {
            if (urlParams.get('error') === 'invalid_role') {
                errorMessage.textContent = 'Your user role is invalid. Please contact support.';
            } else {
                errorMessage.textContent = 'Incorrect username or password.';
            }
            errorMessage.classList.remove('hidden');
        }
    </script>
</body>
</html>