<?php
// --- Security Gatekeeper ---
require_once 'session_auth.php';
// This page is only for admins. Redirect if not an admin.
check_auth(['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Analytics</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Add Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-link.active { background-color: #f1f5f9; color: #1e293b; font-weight: 600; border-left: 3px solid #4f46e5; }
    </style>
</head>
<body class="bg-slate-50">
    <div class="flex h-screen">
        <!-- Admin Sidebar -->
        <aside class="w-64 bg-white shadow-sm flex-shrink-0 hidden lg:flex flex-col border-r border-slate-200">
            <div class="p-6 flex items-center space-x-3">
                <div class="bg-indigo-600 p-2 rounded-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg></div>
                <h1 class="text-xl font-bold text-slate-800">SportsWatch Hub</h1>
            </div>
            <nav class="mt-6 flex-1 px-4">
                <a href="./adminDashboard.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <a href="#" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg active"><i data-lucide="bar-chart-2" class="w-5 h-5 mr-3"></i>Analytics</a>
                <a href="./createMatch.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="plus-circle" class="w-5 h-5 mr-3"></i>Create Match</a>
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
                <h2 class="text-2xl font-bold text-slate-800">Analytics</h2>
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
                <div class="space-y-6">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-bold text-slate-800">Detailed Analytics</h3>
                        <div>
                            <label for="sport-filter" class="text-sm font-medium text-slate-600 mr-2">Filter by Sport:</label>
                            <select id="sport-filter" class="bg-white border border-gray-300 text-gray-700 py-2 px-3 rounded-lg focus:outline-none focus:border-indigo-500 text-sm">
                                <option value="all">All Sports</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                        <div class="lg:col-span-3 bg-white border border-slate-200 rounded-lg p-6">
                            <h3 class="text-lg font-bold text-slate-800 flex items-center mb-4"><i data-lucide="shield-check" class="w-5 h-5 mr-2 text-indigo-500"></i>Team Performance (Win Rate %)</h3>
                            <div class="h-80"><canvas id="teamPerformanceChart"></canvas></div>
                        </div>
                        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-lg p-6">
                            <h3 class="text-lg font-bold text-slate-800 flex items-center mb-4"><i data-lucide="pie-chart" class="w-5 h-5 mr-2 text-indigo-500"></i>Match Status Breakdown</h3>
                            <div class="h-80"><canvas id="matchStatusChart"></canvas></div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();

            const sportFilter = document.getElementById('sport-filter');
            const performanceCtx = document.getElementById('teamPerformanceChart').getContext('2d');
            const statusCtx = document.getElementById('matchStatusChart').getContext('2d');
            
            let performanceChart, statusChart;
            let analyticsData = {};

            function updateCharts(filter = 'all') {
                if (performanceChart) performanceChart.destroy();
                if (statusChart) statusChart.destroy();

                const filteredTeams = filter === 'all' ? analyticsData.teams : analyticsData.teams.filter(t => t.sport_name === filter);
                
                performanceChart = new Chart(performanceCtx, {
                    type: 'bar',
                    data: {
                        labels: filteredTeams.map(t => t.name),
                        datasets: [{
                            label: 'Win Rate %',
                            data: filteredTeams.map(t => t.win_rate),
                            backgroundColor: 'rgba(79, 70, 229, 0.7)',
                            borderColor: 'rgb(79, 70, 229)',
                            borderWidth: 1
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true, max: 100 } }, plugins: { legend: { display: false } } }
                });

                const filteredMatches = filter === 'all' ? analyticsData.matches : analyticsData.matches.filter(m => m.sport_name === filter);
                const statusCounts = {
                    LIVE: filteredMatches.filter(m => m.status === 'LIVE').length,
                    UPCOMING: filteredMatches.filter(m => m.status === 'UPCOMING').length,
                    COMPLETED: filteredMatches.filter(m => m.status === 'COMPLETED').length,
                };

                statusChart = new Chart(statusCtx, {
                    type: 'pie',
                    data: {
                        labels: ['Live', 'Upcoming', 'Completed'],
                        datasets: [{
                            label: 'Match Status',
                            data: [statusCounts.LIVE, statusCounts.UPCOMING, statusCounts.COMPLETED],
                            backgroundColor: ['rgb(239, 68, 68)', 'rgb(59, 130, 246)', 'rgb(34, 197, 94)'],
                            hoverOffset: 4
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false }
                });
            }

            try {
                const response = await fetch('api.php?action=getAnalyticsData');
                const result = await response.json();

                if (result.status === 'success') {
                    analyticsData = result.data;
                    
                    const sports = [...new Set(analyticsData.teams.map(t => t.sport_name))];
                    sports.forEach(sport => {
                        const option = document.createElement('option');
                        option.value = sport;
                        option.textContent = sport;
                        sportFilter.appendChild(option);
                    });

                    sportFilter.addEventListener('change', (e) => {
                        updateCharts(e.target.value);
                    });

                    updateCharts('all'); // Initial render
                } else {
                    document.querySelector('main').innerHTML = `<p class="text-red-500 p-6">Error: ${result.message}</p>`;
                }
            } catch (error) {
                console.error("Fetch Error:", error);
                document.querySelector('main').innerHTML = `<p class="text-red-500 p-6">A critical error occurred while fetching analytics data.</p>`;
            }
        });
    </script>
</body>
</html>