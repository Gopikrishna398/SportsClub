<?php
require_once 'session_auth.php';
check_auth(['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - System Settings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-link.active { background-color: #f1f5f9; color: #1e293b; font-weight: 600; border-left: 3px solid #4f46e5; }
        /* Toggle Switch CSS */
        .toggle-checkbox:checked { right: 0; border-color: #4f46e5; }
        .toggle-checkbox:checked + .toggle-label { background-color: #4f46e5; }
    </style>
</head>
<body class="bg-slate-50">
    <div class="flex h-screen">
        <!-- Admin Sidebar -->
        <aside class="w-64 bg-white shadow-sm flex-shrink-0 hidden lg:flex flex-col border-r border-slate-200">
            <div class="p-6 flex items-center space-x-3"><div class="bg-indigo-600 p-2 rounded-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg></div><h1 class="text-xl font-bold text-slate-800">SportsWatch Hub</h1></div>
            <nav class="mt-6 flex-1 px-4">
                <a href="./adminDashboard.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <a href="./analytics.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="bar-chart-2" class="w-5 h-5 mr-3"></i>Analytics</a>
                <a href="./createMatch.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="plus-circle" class="w-5 h-5 mr-3"></i>Create Match</a>
                <a href="./manageTeams.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="users" class="w-5 h-5 mr-3"></i>Manage Teams</a>
                <a href="./userManagement.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="user-cog" class="w-5 h-5 mr-3"></i>User Management</a>
                <a href="./manageVolunteers.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>Manage Volunteers</a>
                <a href="#" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg active"><i data-lucide="settings" class="w-5 h-5 mr-3"></i>System Settings</a>
                <a href="./reports.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="file-text" class="w-5 h-5 mr-3"></i>Reports</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b border-slate-200"><h2 class="text-2xl font-bold text-slate-800">System Settings</h2><div class="flex items-center space-x-4"><button class="text-slate-500 hover:text-slate-700"><i data-lucide="bell" class="w-6 h-6"></i></button><div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/DC2626/FFFFFF?text=A" alt="Admin avatar"><span class="font-semibold text-slate-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-red-500 text-white font-bold px-2 py-1 rounded-md">ADMIN</span></div><a href="logout.php" class="flex items-center space-x-2 text-slate-600 hover:text-slate-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a></div></header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-6">
                <div class="max-w-4xl mx-auto">
                    <form id="settings-form">
                        <div class="bg-white border border-slate-200 rounded-lg shadow-sm">
                            <div class="p-6 space-y-6">
                                <!-- Prediction Settings -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-semibold text-slate-800">Enable Predictions</h4>
                                        <p class="text-sm text-slate-500">Allow students to make predictions on upcoming matches.</p>
                                    </div>
                                    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" name="predictions_enabled" id="predictions-toggle" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                                        <label for="predictions-toggle" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                    </div>
                                </div>
                                <hr/>
                                <!-- User Registration Settings -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-semibold text-slate-800">Enable New User Registration</h4>
                                        <p class="text-sm text-slate-500">Allow new students to sign up.</p>
                                    </div>
                                    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" name="registration_enabled" id="registration-toggle" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                                        <label for="registration-toggle" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                    </div>
                                </div>
                                <hr/>
                                <!-- Maintenance Mode Settings -->
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="font-semibold text-slate-800">Enable Maintenance Mode</h4>
                                        <p class="text-sm text-slate-500">Temporarily disable access to the site for non-admins.</p>
                                    </div>
                                    <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                                        <input type="checkbox" name="maintenance_mode" id="maintenance-toggle" class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer"/>
                                        <label for="maintenance-toggle" class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 px-6 py-4 border-t flex justify-end">
                                <button type="submit" class="bg-indigo-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-indigo-700">Save Changes</button>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();

            const predictionsToggle = document.getElementById('predictions-toggle');
            const registrationToggle = document.getElementById('registration-toggle');
            const maintenanceToggle = document.getElementById('maintenance-toggle');
            const form = document.getElementById('settings-form');

            // Load initial settings from API
            try {
                const response = await fetch('api.php?action=getSystemSettings');
                const result = await response.json();
                if (result.status === 'success') {
                    const settings = result.data;
                    predictionsToggle.checked = settings.predictions_enabled == '1';
                    registrationToggle.checked = settings.registration_enabled == '1';
                    maintenanceToggle.checked = settings.maintenance_mode == '1';
                } else {
                    alert(`Error loading settings: ${result.message}`);
                }
            } catch (e) {
                alert('Failed to load system settings.');
            }

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                
                const newSettings = {
                    predictions_enabled: predictionsToggle.checked,
                    registration_enabled: registrationToggle.checked,
                    maintenance_mode: maintenanceToggle.checked,
                };
                
                const formData = new FormData();
                formData.append('settings', JSON.stringify(newSettings));

                try {
                    const response = await fetch('api.php?action=updateSystemSettings', { method: 'POST', body: formData });
                    const result = await response.json();
                    alert(result.message);
                } catch(e) {
                    alert('An error occurred while saving settings.');
                }
            });
        });
    </script>
</body>
</html>
