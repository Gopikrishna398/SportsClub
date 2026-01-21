<?php
require_once 'session_auth.php';
check_auth(['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Create Match</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-link.active { background-color: #f1f5f9; color: #1e293b; font-weight: 600; border-left: 3px solid #4f46e5; }
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
                <a href="#" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg active"><i data-lucide="plus-circle" class="w-5 h-5 mr-3"></i>Create Match</a>
                <a href="./manageTeams.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="users" class="w-5 h-5 mr-3"></i>Manage Teams</a>
                <a href="./userManagement.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="user-cog" class="w-5 h-5 mr-3"></i>User Management</a>
                <a href="./manageVolunteers.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>Manage Volunteers</a>
                <a href="./systemSettings.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="settings" class="w-5 h-5 mr-3"></i>System Settings</a>
                <a href="./reports.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="file-text" class="w-5 h-5 mr-3"></i>Reports</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b border-slate-200">
                <h2 class="text-2xl font-bold text-slate-800">Create New Match</h2>
                 <div class="flex items-center space-x-4">
                    <button class="text-slate-500 hover:text-slate-700"><i data-lucide="bell" class="w-6 h-6"></i></button>
                    <div class="flex items-center space-x-2">
                        <img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/DC2626/FFFFFF?text=A" alt="Admin avatar">
                        <span class="font-semibold text-slate-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <span class="text-xs bg-red-500 text-white font-bold px-2 py-1 rounded-md">ADMIN</span>
                    </div>
                    <a href="logout.php" class="flex items-center space-x-2 text-slate-600 hover:text-slate-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-6">
                <div class="max-w-4xl mx-auto">
                    <div class="bg-white border border-slate-200 rounded-lg p-8">
                        <form id="create-match-form">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="sport_id" class="block text-sm font-medium text-slate-700">Sport</label>
                                    <select id="sport_id" name="sport_id" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                                        <option value="">Select a Sport</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="volunteer_id" class="block text-sm font-medium text-slate-700">Assign Volunteer (Optional)</label>
                                    <select id="volunteer_id" name="volunteer_id" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="">None</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="team1_id" class="block text-sm font-medium text-slate-700">Team 1</label>
                                    <select id="team1_id" name="team1_id" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required disabled>
                                        <option value="">Select a sport first</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="team2_id" class="block text-sm font-medium text-slate-700">Team 2</label>
                                    <select id="team2_id" name="team2_id" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required disabled>
                                        <option value="">Select a sport first</option>
                                    </select>
                                </div>
                                <div>
                                    <label for="datetime" class="block text-sm font-medium text-slate-700">Date & Time</label>
                                    <input type="datetime-local" id="datetime" name="datetime" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm" required>
                                </div>
                                <div>
                                    <label for="venue" class="block text-sm font-medium text-slate-700">Venue</label>
                                    <input type="text" id="venue" name="venue" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm" placeholder="e.g., Main Stadium" required>
                                </div>
                            </div>
                             <div id="form-message" class="text-center mt-4"></div>
                            <div class="mt-8 flex justify-end">
                                <button type="submit" class="bg-indigo-600 text-white font-semibold py-2 px-6 rounded-lg hover:bg-indigo-700 flex items-center">
                                    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>Create Match
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();

            const sportSelect = document.getElementById('sport_id');
            const team1Select = document.getElementById('team1_id');
            const team2Select = document.getElementById('team2_id');
            const volunteerSelect = document.getElementById('volunteer_id');
            const form = document.getElementById('create-match-form');
            const formMessage = document.getElementById('form-message');
            let allTeams = [];

            try {
                const response = await fetch('api.php?action=getMatchCreationData');
                const result = await response.json();
                if (result.status === 'success') {
                    const { sports, teams, volunteers } = result.data;
                    allTeams = teams;

                    sports.forEach(s => sportSelect.innerHTML += `<option value="${s.id}">${s.name}</option>`);
                    volunteers.forEach(v => volunteerSelect.innerHTML += `<option value="${v.id}">${v.username}</option>`);
                } else {
                    formMessage.innerHTML = `<p class="text-red-500">${result.message}</p>`;
                }
            } catch (e) {
                formMessage.innerHTML = `<p class="text-red-500">Could not load form data.</p>`;
            }

            sportSelect.addEventListener('change', () => {
                const selectedSportId = sportSelect.value;
                const teamsOfSport = allTeams.filter(t => t.sport_id == selectedSportId);
                
                team1Select.innerHTML = '<option value="">Select Team 1</option>';
                team2Select.innerHTML = '<option value="">Select Team 2</option>';
                
                if (selectedSportId) {
                    team1Select.disabled = false;
                    team2Select.disabled = false;
                    teamsOfSport.forEach(team => {
                        team1Select.innerHTML += `<option value="${team.id}">${team.name}</option>`;
                        team2Select.innerHTML += `<option value="${team.id}">${team.name}</option>`;
                    });
                } else {
                    team1Select.disabled = true;
                    team2Select.disabled = true;
                }
            });

            form.addEventListener('submit', async (e) => {
                e.preventDefault();
                formMessage.innerHTML = '';
                
                if (team1Select.value === team2Select.value) {
                    formMessage.innerHTML = `<p class="text-red-500">Team 1 and Team 2 cannot be the same.</p>`;
                    return;
                }

                const formData = new FormData(form);
                try {
                    const response = await fetch('api.php?action=createMatch', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.status === 'success') {
                        formMessage.innerHTML = `<p class="text-green-500">${result.message}</p>`;
                        form.reset();
                        team1Select.disabled = true;
                        team2Select.disabled = true;
                    } else {
                        formMessage.innerHTML = `<p class="text-red-500">${result.message}</p>`;
                    }
                } catch(e) {
                    formMessage.innerHTML = `<p class="text-red-500">An error occurred.</p>`;
                }
            });
        });
    </script>
</body>
</html>
