<?php
require_once 'session_auth.php';
check_auth(['student']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Championship</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
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
                <a href="#" class="sidebar-link flex items-center mt-2 px-4 py-3 rounded-lg active"><i data-lucide="trophy" class="w-5 h-5 mr-3"></i>Championship</a>
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
            <header class="bg-white shadow-sm p-4 flex justify-between items-center"><h2 class="text-2xl font-bold text-gray-800">Championship Standings</h2><div class="flex items-center space-x-4"><button class="text-gray-500 hover:text-gray-700"><i data-lucide="bell" class="w-6 h-6"></i></button><div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/E2E8F0/4A5568?text=S" alt="User avatar"><span class="font-semibold text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-indigo-100 text-indigo-600 font-bold px-2 py-1 rounded-md">STUDENT</span></div><a href="logout.php" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a></div></header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div id="champion-container" class="lg:col-span-1 bg-white rounded-xl shadow-sm p-6">
                        <!-- Champion will be dynamically inserted here -->
                    </div>
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-4">Championship Leaderboard</h3>
                        <p class="text-gray-500 mb-4">Ranking based on the number of sports won by each branch.</p>
                        <div id="leaderboard-container" class="space-y-2">
                            <!-- Leaderboard will be dynamically inserted here -->
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();
            const championContainer = document.getElementById('champion-container');
            const leaderboardContainer = document.getElementById('leaderboard-container');

            try {
                const response = await fetch('api.php?action=getChampionshipStandings');
                const result = await response.json();

                if (result.status === 'success') {
                    const { leaderboard, champion } = result.data;

                    // Render Champion Card
                    if (champion) {
                        championContainer.innerHTML = `
                            <h3 class="text-xl font-bold text-gray-800 mb-4 text-center">Overall Champion</h3>
                            <div class="bg-yellow-50 rounded-lg p-4 text-center">
                                <i data-lucide="trophy" class="w-16 h-16 text-yellow-400 mx-auto"></i>
                                <p class="mt-4 text-3xl font-bold text-slate-800">${champion.branch_name}</p>
                                <p class="mt-2 text-yellow-600 font-bold text-xl">${champion.championships_won} Championships Won</p>
                            </div>
                        `;
                    } else {
                        championContainer.innerHTML = `<h3 class="text-xl font-bold text-gray-800 mb-4">Overall Champion</h3><p class="text-gray-500">The champion has not been decided yet.</p>`;
                    }

                    // Render Leaderboard
                    if (leaderboard.length > 0) {
                        leaderboardContainer.innerHTML = leaderboard.map((branch, index) => {
                            const rank = index + 1;
                            const medalColor = rank === 1 ? 'text-yellow-400' : (rank === 2 ? 'text-slate-400' : 'text-orange-400');
                            return `
                                <div class="flex items-center justify-between p-3 rounded-lg ${rank === 1 ? 'bg-slate-100' : ''}">
                                    <div class="flex items-center">
                                        <span class="font-bold text-slate-600 w-8">${rank}.</span>
                                        ${rank <= 3 ? `<i data-lucide="medal" class="w-5 h-5 ${medalColor} mr-2"></i>` : '<div class="w-5 mr-2"></div>'}
                                        <span class="font-semibold text-slate-800">${branch.branch_name}</span>
                                    </div>
                                    <span class="font-bold text-indigo-600 text-lg">${branch.championships_won} Wins</span>
                                </div>
                            `;
                        }).join('');
                    } else {
                        leaderboardContainer.innerHTML = '<p class="text-gray-500">No championship data available yet.</p>';
                    }

                    lucide.createIcons();
                } else {
                    championContainer.innerHTML = `<p class="text-red-500">Error: ${result.message}</p>`;
                }
            } catch (error) {
                console.error("Fetch Error:", error);
                championContainer.innerHTML = `<p class="text-red-500">Failed to load championship data.</p>`;
            }
        });
    </script>
</body>
</html>
