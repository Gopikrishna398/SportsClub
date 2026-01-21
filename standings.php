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
    <title>SportsWatch Hub - University Standings</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Add Chart.js library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; }
        .sidebar-link.active { background-color: #f3f4f6; color: #4f46e5; font-weight: 600; }
        .filter-btn.active { background-color: #4f46e5; color: white; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden lg:flex flex-col">
            <div class="p-6 flex items-center space-x-3"><div class="bg-indigo-600 p-2 rounded-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg></div><h1 class="text-xl font-bold text-gray-800">SportsWatch Hub</h1></div>
            <nav id="sidebar-nav" class="mt-6 flex-1 px-4">
                <a href="./dashboard.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <a href="#" class="sidebar-link flex items-center px-4 py-3 rounded-lg active"><i data-lucide="university" class="w-5 h-5 mr-3"></i>University Standings</a>
                <a href="./schedule.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="calendar-days" class="w-5 h-5 mr-3"></i>Match Schedule</a>
                <a href="./points.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="table" class="w-5 h-5 mr-3"></i>Points Table</a>
                <a href="./liveScore.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg relative"><i data-lucide="radio-tower" class="w-5 h-5 mr-3"></i>Live Scores<span id="live-nav-badge" class="ml-auto text-xs bg-red-500 text-white font-semibold px-2 py-0.5 rounded-full hidden">LIVE</span></a>
                <a href="./predictions.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="lightbulb" class="w-5 h-5 mr-3"></i>Predictions</a>
                <a href="./stats.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="bar-chart-3" class="w-5 h-5 mr-3"></i>Team Stats</a>
                <a href="./myPredictions.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>My Predictions</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center"><h2 class="text-2xl font-bold text-gray-800">University Standings</h2><div class="flex items-center space-x-4"><button class="text-gray-500 hover:text-gray-700"><i data-lucide="bell" class="w-6 h-6"></i></button><div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/E2E8F0/4A5568?text=S" alt="User avatar"><span class="font-semibold text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-indigo-100 text-indigo-600 font-bold px-2 py-1 rounded-md">STUDENT</span></div><a href="logout.php" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a></div></header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6">
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 flex items-center">
                                <i data-lucide="university" class="w-6 h-6 mr-3 text-indigo-600"></i>
                                University Standings
                            </h3>
                            <p class="text-gray-500 mt-1">View overall and sport-specific leaderboards.</p>
                        </div>
                        <div id="filter-buttons" class="flex space-x-2 mt-4 sm:mt-0 bg-slate-100 p-1 rounded-lg">
                            <!-- Filter buttons will be injected here -->
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <div class="lg:col-span-2 h-96">
                            <canvas id="standingsChart"></canvas>
                        </div>
                        <div id="standings-table" class="lg:col-span-1">
                            <!-- Standings table will be rendered here -->
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();
            const filterContainer = document.getElementById('filter-buttons');
            const tableContainer = document.getElementById('standings-table');
            const chartCanvas = document.getElementById('standingsChart');
            let standingsChart; // Variable to hold the chart instance

            async function fetchDataAndRender(sportId = 'all') {
                try {
                    const response = await fetch(`api.php?action=getDetailedStandings&sport_id=${sportId}`);
                    const result = await response.json();

                    if (result.status === 'success') {
                        const standings = result.data;
                        renderTable(standings);
                        renderChart(standings);
                    } else {
                        tableContainer.innerHTML = `<p class="text-red-500">${result.message}</p>`;
                    }
                } catch (error) {
                    console.error("Error fetching standings:", error);
                    tableContainer.innerHTML = `<p class="text-red-500">Could not load standings data.</p>`;
                }
            }
            
            function renderTable(standings) {
                let tableHtml = '<div class="space-y-2">';
                standings.forEach((branch, index) => {
                    const medal = ['text-yellow-400', 'text-slate-400', 'text-orange-400'][index];
                    tableHtml += `
                        <div class="flex items-center justify-between p-3 rounded-lg ${index < 3 ? 'bg-slate-50' : ''}">
                            <div class="flex items-center">
                                <span class="font-bold text-slate-600 w-8">${index + 1}.</span>
                                ${index < 3 ? `<i data-lucide="medal" class="w-5 h-5 ${medal} mr-2"></i>` : '<div class="w-5 mr-2"></div>'}
                                <span class="font-semibold text-slate-800">${branch.name}</span>
                            </div>
                            <span class="font-bold text-indigo-600 text-lg">${branch.points} pts</span>
                        </div>
                    `;
                });
                tableHtml += '</div>';
                tableContainer.innerHTML = tableHtml;
                lucide.createIcons();
            }

            function renderChart(standings) {
                if (standingsChart) {
                    standingsChart.destroy(); // Destroy old chart before creating a new one
                }
                standingsChart = new Chart(chartCanvas.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: standings.map(b => b.abbreviation),
                        datasets: [{
                            label: 'Total Points',
                            data: standings.map(b => b.points),
                            backgroundColor: 'rgba(79, 70, 229, 0.8)',
                            borderColor: 'rgb(79, 70, 229)',
                            borderWidth: 1
                        }]
                    },
                    options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
                });
            }

            // --- Create Filter Buttons ---
            const sports = [ {id: 'all', name: 'Overall'}, {id: 1, name: 'Cricket'}, {id: 2, name: 'Football'}, {id: 3, name: 'Basketball'} ];
            sports.forEach(sport => {
                const button = document.createElement('button');
                button.textContent = sport.name;
                button.dataset.sportId = sport.id;
                button.className = 'filter-btn px-4 py-2 text-sm font-semibold text-slate-600 rounded-md hover:bg-slate-200';
                if (sport.id === 'all') button.classList.add('active');
                
                button.onclick = () => {
                    document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    fetchDataAndRender(sport.id);
                };
                filterContainer.appendChild(button);
            });

            // Initial Load
            fetchDataAndRender('all');
        });
    </script>
</body>
</html>