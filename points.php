<?php
require_once 'session_auth.php';
check_auth(['student']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Points Table</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f0f2f5; }
        .sidebar-link.active { background-color: #f3f4f6; color: #4f46e5; font-weight: 600; }
        .points-table-tab.active { border-bottom: 2px solid #4f46e5; color: #4f46e5; font-weight: 600; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden lg:flex flex-col">
            <div class="p-6 flex items-center space-x-3"><div class="bg-indigo-600 p-2 rounded-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg></div><h1 class="text-xl font-bold text-gray-800">SportsWatch Hub</h1></div>
            <nav id="sidebar-nav" class="mt-6 flex-1 px-4">
                <a href="./dashboard.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <a href="./schedule.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="calendar-days" class="w-5 h-5 mr-3"></i>Match Schedule</a>
                <a href="#" class="sidebar-link flex items-center mt-2 px-4 py-3 rounded-lg active"><i data-lucide="table" class="w-5 h-5 mr-3"></i>Points Table</a>
                <a href="./liveScore.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg relative"><i data-lucide="radio-tower" class="w-5 h-5 mr-3"></i>Live Scores<span id="live-nav-badge" class="ml-auto text-xs bg-red-500 text-white font-semibold px-2 py-0.5 rounded-full hidden">LIVE</span></a>
                <a href="./predictions.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="lightbulb" class="w-5 h-5 mr-3"></i>Predictions</a>
                <a href="./stats.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="bar-chart-3" class="w-5 h-5 mr-3"></i>Team Stats</a>
                <a href="./myPredictions.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>My Predictions</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center"><h2 class="text-2xl font-bold text-gray-800">Points Table</h2><div class="flex items-center space-x-4"><button class="text-gray-500 hover:text-gray-700"><i data-lucide="bell" class="w-6 h-6"></i></button><div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/E2E8F0/4A5568?text=S" alt="User avatar"><span class="font-semibold text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-indigo-100 text-indigo-600 font-bold px-2 py-1 rounded-md">STUDENT</span></div><a href="logout.php" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a></div></header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="space-y-5">
                    <h3 class="text-xl font-bold text-gray-800">Points Table</h3>
                    <p class="text-gray-500">Tournament standings across all sports</p>
                    <div class="bg-white rounded-xl shadow-sm">
                        <div class="border-b border-gray-200">
                            <nav id="sports-tabs" class="flex space-x-1 p-2" aria-label="Tabs"></nav>
                        </div>
                        <div id="points-table-content" class="p-6"></div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();
            const sportsTabs = document.getElementById('sports-tabs');
            const tableContent = document.getElementById('points-table-content');
            let pointsData = {}; // To store the fetched data

            try {
                const response = await fetch('api.php?action=getPointsTableData');
                const result = await response.json();

                if (result.status === 'success') {
                    pointsData = result.data;
                    const availableSports = Object.keys(pointsData);

                    if (availableSports.length === 0) {
                        sportsTabs.innerHTML = '<p class="p-4 text-gray-500">No sports data available.</p>';
                        return;
                    }

                    // Create tabs
                    availableSports.forEach((sport, index) => {
                        const tab = document.createElement('button');
                        tab.className = 'points-table-tab px-4 py-2 text-sm text-gray-500 hover:text-indigo-600';
                        tab.textContent = sport;
                        tab.dataset.sport = sport;
                        if (index === 0) {
                            tab.classList.add('active');
                        }
                        tab.onclick = () => selectTab(sport);
                        sportsTabs.appendChild(tab);
                    });

                    // Initial render
                    renderTable(availableSports[0]);

                } else {
                    tableContent.innerHTML = `<p class="text-red-500">Error: ${result.message}</p>`;
                }
            } catch (error) {
                console.error("Fetch Error:", error);
                tableContent.innerHTML = `<p class="text-red-500">Error fetching points data. Please check the API and database connection.</p>`;
            }

            function renderTable(sport) {
                const data = pointsData[sport];
                if (!data) {
                    tableContent.innerHTML = '<p>No data available for this sport.</p>';
                    return;
                }

                const headers = ['Pos', 'Team', 'M', 'W', 'L', 'Pts', 'NRR'];

                let tableHTML = `
                    <div class="flex items-center space-x-3 mb-4">
                        <i data-lucide="trophy" class="w-6 h-6 text-yellow-500"></i>
                        <h4 class="text-lg font-bold text-gray-800">${sport} Standings</h4>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>${headers.map(h => `<th scope="col" class="px-6 py-3">${h}</th>`).join('')}</tr>
                            </thead>
                            <tbody>
                                ${data.map((row, index) => `
                                    <tr class="bg-white border-b">
                                        <td class="px-6 py-4">${index + 1}</td>
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">${row.name}</th>
                                        <td class="px-6 py-4">${row.matches_played || 0}</td>
                                        <td class="px-6 py-4">${row.wins || 0}</td>
                                        <td class="px-6 py-4">${row.losses || 0}</td>
                                        <td class="px-6 py-4 font-bold">${row.points || 0}</td>
                                        <td class="px-6 py-4">${row.nrr ? `<span class="${row.nrr.startsWith('+') ? 'bg-blue-100 text-blue-800' : 'bg-orange-100 text-orange-800'} text-xs font-medium mr-2 px-2.5 py-0.5 rounded">${row.nrr}</span>` : 'N/A'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
                tableContent.innerHTML = tableHTML;
                lucide.createIcons();
            }

            function selectTab(sport) {
                Array.from(sportsTabs.children).forEach(tab => {
                    tab.classList.toggle('active', tab.dataset.sport === sport);
                });
                renderTable(sport);
            }
        });
    </script>
</body>
</html>