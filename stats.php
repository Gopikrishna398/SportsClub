<?php
// --- Security Gatekeeper ---
require_once 'session_auth.php';
// This page is only for students.
check_auth(['student']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Team Stats</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; }
        .sidebar-link.active { background-color: #f3f4f6; color: #4f46e5; font-weight: 600; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden lg:flex flex-col">
            <div class="p-6 flex items-center space-x-3"><div class="bg-indigo-600 p-2 rounded-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg></div><h1 class="text-xl font-bold text-gray-800">SportsWatch Hub</h1></div>
            <nav id="sidebar-nav" class="mt-6 flex-1 px-4">
                <a href="./dashboard.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <!-- <a href="./topPerformers.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="award" class="w-5 h-5 mr-3"></i>Top Performers</a> -->
                <a href="./schedule.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="calendar-days" class="w-5 h-5 mr-3"></i>Match Schedule</a>
                <a href="./points.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="table" class="w-5 h-5 mr-3"></i>Points Table</a>
                <a href="./liveScore.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg relative"><i data-lucide="radio-tower" class="w-5 h-5 mr-3"></i>Live Scores<span id="live-nav-badge" class="ml-auto text-xs bg-red-500 text-white font-semibold px-2 py-0.5 rounded-full hidden">LIVE</span></a>
                <a href="./predictions.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="lightbulb" class="w-5 h-5 mr-3"></i>Predictions</a>
                <a href="#" class="sidebar-link flex items-center mt-2 px-4 py-3 rounded-lg active"><i data-lucide="bar-chart-3" class="w-5 h-5 mr-3"></i>Team Stats</a>
                <a href="./myPredictions.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>My Predictions</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center"><h2 class="text-2xl font-bold text-gray-800">Team Statistics</h2><div class="flex items-center space-x-4"><button class="text-gray-500 hover:text-gray-700"><i data-lucide="bell" class="w-6 h-6"></i></button><div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/E2E8F0/4A5568?text=S" alt="User avatar"><span class="font-semibold text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-indigo-100 text-indigo-600 font-bold px-2 py-1 rounded-md">STUDENT</span></div><a href="logout.php" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a></div></header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="space-y-6">
                    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center">
                        <div class="flex items-center space-x-3"><i data-lucide="bar-chart-big" class="w-8 h-8 text-indigo-600"></i><div><h3 class="text-xl font-bold text-gray-800">Team Statistics</h3><p class="text-gray-500">Detailed team performance and player statistics</p></div></div>
                        <div class="flex items-center space-x-2 mt-4 sm:mt-0">
                            <select id="sport-selector" class="bg-white border border-gray-300 text-gray-700 py-2 px-3 pr-8 rounded-lg focus:outline-none focus:border-indigo-500"></select>
                            <select id="team-selector" class="bg-white border border-gray-300 text-gray-700 py-2 px-3 pr-8 rounded-lg focus:outline-none focus:border-indigo-500" disabled></select>
                        </div>
                    </div>
                    <div id="stats-content">
                        <!-- Dynamic content will be loaded here -->
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sportSelector = document.getElementById('sport-selector');
            const teamSelector = document.getElementById('team-selector');
            const statsContent = document.getElementById('stats-content');
            let allTeamsWithStats = [];
            let competitionChart, winLossChart;

            async function initializePage() {
                try {
                    const response = await fetch('api.php?action=getAllTeamStats');
                    const result = await response.json();

                    if (result.status !== 'success') throw new Error(result.message);
                    
                    allTeamsWithStats = result.data;
                    const sports = [...new Map(allTeamsWithStats.map(team => [team.sport_id, {id: team.sport_id, name: team.sport_name}])).values()];

                    sportSelector.innerHTML = '<option value="">Filter by Sport</option>';
                    sports.forEach(sport => {
                        const option = document.createElement('option');
                        option.value = sport.id;
                        option.textContent = sport.name;
                        sportSelector.appendChild(option);
                    });

                    // --- Set default selections ---
                    if (sports.length > 0) {
                        sportSelector.value = sports[0].id; // Default to the first sport
                        sportSelector.dispatchEvent(new Event('change'));

                        const teamsInFirstSport = allTeamsWithStats.filter(t => t.sport_id == sports[0].id);
                        if (teamsInFirstSport.length > 0) {
                            teamSelector.value = teamsInFirstSport[0].id; // Default to the first team in that sport
                            teamSelector.dispatchEvent(new Event('change'));
                        }
                    }

                } catch (error) {
                    statsContent.innerHTML = `<p class="text-red-500 p-6">${error.message}</p>`;
                }
            }

            function renderStats(teamId) {
                const team = allTeamsWithStats.find(t => t.id == teamId);
                if (team) {
                    const recentFormHtml = team.recentForm && team.recentForm.length > 0
                        ? team.recentForm.map(r => `<span class="flex items-center justify-center w-7 h-7 rounded-full ${r === 'W' ? 'bg-green-500' : 'bg-red-500'} text-white font-bold text-sm">${r}</span>`).join('')
                        : '<span class="text-sm text-gray-500">No recent matches found.</span>';

                    statsContent.innerHTML = `
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                            <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Matches Played</p><p class="text-3xl font-bold text-gray-800">${team.matches_played}</p></div><div class="bg-blue-100 p-3 rounded-full"><i data-lucide="calendar-days" class="w-6 h-6 text-blue-500"></i></div></div>
                            <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Win Rate</p><p class="text-3xl font-bold text-gray-800">${team.win_rate}%</p></div><div class="bg-green-100 p-3 rounded-full"><i data-lucide="target" class="w-6 h-6 text-green-500"></i></div></div>
                            <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Points</p><p class="text-3xl font-bold text-gray-800">${team.points}</p></div><div class="bg-yellow-100 p-3 rounded-full"><i data-lucide="trophy" class="w-6 h-6 text-yellow-500"></i></div></div>
                            <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Position</p><p class="text-3xl font-bold text-gray-800">#${team.position || 'N/A'}</p></div><div class="bg-purple-100 p-3 rounded-full"><i data-lucide="trending-up" class="w-6 h-6 text-purple-500"></i></div></div>
                        </div>
                        <div class="mt-6 bg-white rounded-xl shadow-sm p-5">
                            <h4 class="text-lg font-bold text-gray-800 mb-3">Recent Form</h4>
                            <div class="flex items-center space-x-2">
                                <span class="text-sm text-gray-500">Last ${team.recentForm ? team.recentForm.length : 0} matches:</span>
                                ${recentFormHtml}
                            </div>
                        </div>
                        <div class="mt-6 grid grid-cols-1 lg:grid-cols-5 gap-6">
                            <div class="lg:col-span-3 bg-white rounded-xl shadow-sm p-6">
                                <h4 class="text-lg font-bold text-gray-800 mb-4">Competition Snapshot (${team.sport_name})</h4>
                                <div class="h-80"><canvas id="competitionChart"></canvas></div>
                            </div>
                            <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
                                <h4 class="text-lg font-bold text-gray-800 mb-4">Win / Loss Breakdown</h4>
                                <div class="h-80"><canvas id="winLossChart"></canvas></div>
                            </div>
                        </div>
                    `;
                    lucide.createIcons();
                    renderCompetitionChart(team);
                    renderWinLossChart(team);
                } else {
                    statsContent.innerHTML = `<p class="text-gray-500 p-6">Select a team to view their stats.</p>`;
                }
            }

            function renderCompetitionChart(selectedTeam) {
                if (competitionChart) competitionChart.destroy();
                const teamsInSport = allTeamsWithStats.filter(t => t.sport_id == selectedTeam.sport_id);
                const ctx = document.getElementById('competitionChart').getContext('2d');
                competitionChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: teamsInSport.map(t => t.abbreviation || t.name),
                        datasets: [{
                            label: 'Win Rate %',
                            data: teamsInSport.map(t => t.win_rate),
                            backgroundColor: teamsInSport.map(t => t.id == selectedTeam.id ? 'rgba(79, 70, 229, 0.8)' : 'rgba(203, 213, 225, 0.8)'),
                            borderColor: teamsInSport.map(t => t.id == selectedTeam.id ? 'rgb(79, 70, 229)' : 'rgb(203, 213, 225)'),
                            borderWidth: 1
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100 } }, plugins: { legend: { display: false } } }
                });
            }

            function renderWinLossChart(selectedTeam) {
                if (winLossChart) winLossChart.destroy();
                const ctx = document.getElementById('winLossChart').getContext('2d');
                winLossChart = new Chart(ctx, {
                    type: 'pie',
                    data: {
                        labels: ['Wins', 'Losses'],
                        datasets: [{
                            label: 'Match Outcomes',
                            data: [selectedTeam.wins, selectedTeam.losses],
                            backgroundColor: ['rgb(34, 197, 94)', 'rgb(239, 68, 68)'],
                            hoverOffset: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            sportSelector.addEventListener('change', () => {
                const selectedSportId = sportSelector.value;
                teamSelector.innerHTML = '<option value="">Select a Team</option>';
                
                if (selectedSportId) {
                    const teamsOfSport = allTeamsWithStats.filter(t => t.sport_id == selectedSportId);
                    teamsOfSport.forEach(team => {
                        const option = document.createElement('option');
                        option.value = team.id;
                        option.textContent = team.name;
                        teamSelector.appendChild(option);
                    });
                    teamSelector.disabled = false;
                    statsContent.innerHTML = '';
                } else {
                    teamSelector.disabled = true;
                    statsContent.innerHTML = '';
                }
            });

            teamSelector.addEventListener('change', () => {
                const selectedTeamId = teamSelector.value;
                if (selectedTeamId) {
                    renderStats(selectedTeamId);
                } else {
                    statsContent.innerHTML = '';
                }
            });

            initializePage();
        });
    </script>
</body>
</html>
