
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SportsWatch Hub</title>
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
                    <i data-lucide="user-plus" class="w-8 h-8 text-white"></i>
                </div>
            </div>
            <h2 class="text-2xl font-bold text-center text-slate-800">Create a Student Account</h2>
            <p class="text-center text-slate-500 mt-2 mb-6">Get started with SportsWatch Hub today.</p>
            
            <form id="register-form">
                <div class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-slate-700">Username</label>
                        <input type="text" id="username" name="username" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                     <div>
                        <label for="email" class="block text-sm font-medium text-slate-700">Email Address</label>
                        <input type="email" id="email" name="email" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <div>
                        <label for="password" class="block text-sm font-medium text-slate-700">Password</label>
                        <input type="password" id="password" name="password" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                    </div>
                    <!-- Role is now fixed to 'student' -->
                    <input type="hidden" name="role" value="student">
                </div>
                 <div id="error-message" class="text-red-500 text-sm mt-4 text-center hidden"></div>
                 <div id="success-message" class="text-green-500 text-sm mt-4 text-center hidden"></div>
                <div class="mt-6">
                    <button type="submit" class="w-full bg-indigo-600 text-white font-semibold py-3 px-4 rounded-lg hover:bg-indigo-700 transition-colors">
                        Create Account
                    </button>
                </div>
            </form>
            <p class="text-center text-sm text-slate-500 mt-6">
                Already have an account? <a href="./login.html" class="font-semibold text-indigo-600 hover:underline">Login here</a>
            </p>
        </div>
    </div>
    <script>
        lucide.createIcons();
        
        const form = document.getElementById('register-form');
        const errorMessage = document.getElementById('error-message');
        const successMessage = document.getElementById('success-message');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            errorMessage.classList.add('hidden');
            successMessage.classList.add('hidden');

            const formData = new FormData(form);

            try {
                const response = await fetch('auth.php?action=register', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.status === 'success') {
                    successMessage.textContent = result.message;
                    successMessage.classList.remove('hidden');
                    form.reset();
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 2000);
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