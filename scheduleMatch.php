<?php
require_once 'session_auth.php';
check_auth(['volunteer']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Schedule Match</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        /* Original User Styles - UNCHANGED */
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; }
        .sidebar-link.active { background-color: #f3f4f6; color: #4f46e5; font-weight: 600; }

        /* Style for the date/time picker icon to make it consistent */
        input[type="datetime-local"]::-webkit-calendar-picker-indicator {
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>');
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden lg:flex flex-col">
            <div class="p-6 flex items-center space-x-3"><div class="bg-indigo-600 p-2 rounded-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg></div><h1 class="text-xl font-bold text-gray-800">SportsWatch Hub</h1></div>
            <nav id="sidebar-nav" class="mt-6 flex-1 px-4">
                <a href="./volunteerDashboard.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <a href="#" class="sidebar-link flex items-center mt-2 px-4 py-3 rounded-lg active"><i data-lucide="calendar-plus" class="w-5 h-5 mr-3"></i>Schedule Match</a>
                <a href="./updateScores.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="pencil-line" class="w-5 h-5 mr-3"></i>Update Scores</a>
                <a href="./matchReports.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="file-text" class="w-5 h-5 mr-3"></i>Match Reports</a>
                <a href="./myMatches.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="clipboard-check" class="w-5 h-5 mr-3"></i>My Matches</a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
             <header class="bg-white shadow-sm p-4 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Schedule a New Match</h2>
                <div class="flex items-center space-x-4">
                    <button class="text-gray-500 hover:text-gray-700"><i data-lucide="bell" class="w-6 h-6"></i></button>
                    <div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/FB923C/4A5568?text=V" alt="Volunteer avatar"><span class="font-semibold text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-orange-100 text-orange-600 font-bold px-2 py-1 rounded-md">VOLUNTEER</span></div>
                    <a href="logout.php" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a>
                </div>
            </header>
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-4xl mx-auto">
                    <form id="schedule-form" class="bg-white rounded-xl shadow-lg p-8 space-y-8">
                        <div>
                             <h2 class="text-2xl font-bold text-gray-900">Match Details</h2>
                             <p class="text-gray-500 mt-1">Fill in the details below to schedule a new match.</p>
                        </div>

                        <div id="form-messages">
                            <div id="success-alert" class="hidden flex items-center p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50" role="alert">
                                <i data-lucide="check-circle-2" class="w-5 h-5 mr-3"></i>
                                <span class="font-medium" id="success-message"></span>
                            </div>
                            <div id="error-alert" class="hidden flex items-center p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50" role="alert">
                                <i data-lucide="alert-triangle" class="w-5 h-5 mr-3"></i>
                                <span class="font-medium" id="error-message"></span>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-8 space-y-6">
                            <h3 class="text-lg font-semibold text-gray-700">Step 1: Select Sport & Teams</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="sport_id" class="block mb-2 text-sm font-medium text-gray-900">Your Assigned Sport</label>
                                    <select id="sport_id" name="sport_id" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required></select>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-[1fr_auto_1fr] items-center gap-4 md:col-span-2">
                                     <div>
                                        <label for="team1_id" class="block mb-2 text-sm font-medium text-gray-900">Team 1 (Branch)</label>
                                        <select id="team1_id" name="team1_id" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required disabled><option value="">Select a sport first</option></select>
                                    </div>
                                    <span class="font-bold text-gray-500 text-center mt-6">VS</span>
                                     <div>
                                        <label for="team2_id" class="block mb-2 text-sm font-medium text-gray-900">Team 2 (Branch)</label>
                                        <select id="team2_id" name="team2_id" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full p-2.5" required disabled><option value="">Select a sport first</option></select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 pt-8 space-y-6">
                             <h3 class="text-lg font-semibold text-gray-700">Step 2: Set Time & Location</h3>
                             <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="datetime" class="block mb-2 text-sm font-medium text-gray-900">Date & Time</label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i data-lucide="calendar" class="w-5 h-5 text-gray-500"></i></div>
                                        <input type="datetime-local" id="datetime" name="datetime" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5" required>
                                    </div>
                                </div>
                                <div>
                                    <label for="venue" class="block mb-2 text-sm font-medium text-gray-900">Venue</label>
                                    <div class="relative">
                                         <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none"><i data-lucide="map-pin" class="w-5 h-5 text-gray-500"></i></div>
                                        <input type="text" id="venue" name="venue" class="bg-gray-50 border border-gray-300 text-gray-900 sm:text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 p-2.5" placeholder="e.g., Main University Ground" required>
                                    </div>
                                </div>
                             </div>
                        </div>
                        
                        <div class="mt-8 flex justify-end border-t border-gray-200 pt-6">
                            <button type="submit" class="w-full sm:w-auto bg-indigo-600 text-white font-semibold py-3 px-8 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-300 flex items-center justify-center transition-all duration-300">
                                <i data-lucide="calendar-plus" class="w-5 h-5 mr-2"></i>
                                <span>Schedule Match</span>
                            </button>
                        </div>
                    </form>
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
            const form = document.getElementById('schedule-form');
            
            const successAlert = document.getElementById('success-alert');
            const errorAlert = document.getElementById('error-alert');
            const successMessage = document.getElementById('success-message');
            const errorMessage = document.getElementById('error-message');

            let allTeams = [];

            const showMessage = (type, message) => {
                successAlert.classList.add('hidden');
                errorAlert.classList.add('hidden');
                if (type === 'success') {
                    successMessage.textContent = message;
                    successAlert.classList.remove('hidden');
                } else {
                    errorMessage.textContent = message;
                    errorAlert.classList.remove('hidden');
                }
            };

            try {
                const response = await fetch('api.php?action=getVolunteerPrerequisites');
                const result = await response.json();

                if (result.status === 'success') {
                    const { assigned_sports, all_teams } = result.data;
                    allTeams = all_teams;
                    
                    sportSelect.innerHTML = '<option value="">Select a Sport</option>';
                    assigned_sports.forEach(sport => {
                        sportSelect.innerHTML += `<option value="${sport.id}">${sport.name}</option>`;
                    });
                } else {
                    showMessage('error', result.message);
                }
            } catch(e) {
                showMessage('error', 'Could not load required form data. Check your connection.');
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
                successAlert.classList.add('hidden');
                errorAlert.classList.add('hidden');
                
                if (team1Select.value && team1Select.value === team2Select.value) {
                    showMessage('error', 'Team 1 and Team 2 cannot be the same branch.');
                    return;
                }

                const formData = new FormData(form);
                const submitButton = form.querySelector('button[type="submit"]');
                submitButton.disabled = true;
                submitButton.innerHTML = `<i data-lucide="loader-2" class="animate-spin w-5 h-5 mr-2"></i><span>Scheduling...</span>`;
                lucide.createIcons();
                
                try {
                    const response = await fetch('api.php?action=scheduleVolunteerMatch', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.status === 'success') {
                        showMessage('success', result.message);
                        form.reset();
                        team1Select.innerHTML = '<option value="">Select a sport first</option>';
                        team2Select.innerHTML = '<option value="">Select a sport first</option>';
                        team1Select.disabled = true;
                        team2Select.disabled = true;
                         // Scroll to top to see message
                        form.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        showMessage('error', result.message);
                    }
                } catch(e) {
                    showMessage('error', 'An unexpected error occurred. Please try again.');
                } finally {
                    submitButton.disabled = false;
                    submitButton.innerHTML = `<i data-lucide="calendar-plus" class="w-5 h-5 mr-2"></i><span>Schedule Match</span>`;
                    lucide.createIcons();
                }
            });
        });
    </script>
</body>
</html>