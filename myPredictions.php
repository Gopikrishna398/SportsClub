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
    <title>SportsWatch Hub - My Predictions</title>
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
                <!-- <a href="./topPerformers.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="award" class="w-5 h-5 mr-3"></i>Top Performers</a> -->
                <a href="./schedule.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="calendar-days" class="w-5 h-5 mr-3"></i>Match Schedule</a>
                <a href="./points.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="table" class="w-5 h-5 mr-3"></i>Points Table</a>
                <a href="./liveScore.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg relative"><i data-lucide="radio-tower" class="w-5 h-5 mr-3"></i>Live Scores<span id="live-nav-badge" class="ml-auto text-xs bg-red-500 text-white font-semibold px-2 py-0.5 rounded-full hidden">LIVE</span></a>
                <a href="./predictions.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="lightbulb" class="w-5 h-5 mr-3"></i>Predictions</a>
                <a href="./stats.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="bar-chart-3" class="w-5 h-5 mr-3"></i>Team Stats</a>
                <a href="#" class="sidebar-link flex items-center mt-2 px-4 py-3 rounded-lg active"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>My Predictions</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center"><h2 class="text-2xl font-bold text-gray-800">My Predictions</h2><div class="flex items-center space-x-4"><button class="text-gray-500 hover:text-gray-700"><i data-lucide="bell" class="w-6 h-6"></i></button><div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/E2E8F0/4A5568?text=S" alt="User avatar"><span class="font-semibold text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-indigo-100 text-indigo-600 font-bold px-2 py-1 rounded-md">STUDENT</span></div><a href="logout.php" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a></div></header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="space-y-6">
                    <div class="flex items-center space-x-3"><i data-lucide="award" class="w-8 h-8 text-indigo-600"></i><div><h3 class="text-xl font-bold text-gray-800">My Predictions</h3><p class="text-gray-500">Track your prediction performance and accuracy</p></div></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Total Predictions</p><p id="total-predictions" class="text-3xl font-bold text-gray-800">0</p></div><div class="bg-blue-100 p-3 rounded-full"><i data-lucide="target" class="w-6 h-6 text-blue-500"></i></div></div>
                        <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Correct Predictions</p><p id="correct-predictions" class="text-3xl font-bold text-gray-800">0</p></div><div class="bg-green-100 p-3 rounded-full"><i data-lucide="check-circle" class="w-6 h-6 text-green-500"></i></div></div>
                        <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Accuracy Rate</p><p id="accuracy-rate-card" class="text-3xl font-bold text-gray-800">0%</p></div><div class="bg-yellow-100 p-3 rounded-full"><i data-lucide="trending-up" class="w-6 h-6 text-yellow-500"></i></div></div>
                        <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Global Rank</p><p id="global-rank" class="text-3xl font-bold text-gray-800">N/A</p></div><div class="bg-purple-100 p-3 rounded-full"><i data-lucide="trophy" class="w-6 h-6 text-purple-500"></i></div></div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm p-5">
                        <h4 class="text-lg font-bold text-gray-800 mb-3">Prediction Accuracy</h4><p class="text-sm text-gray-500">Your prediction success rate over time</p>
                        <div class="mt-4"><div class="flex justify-between text-sm font-medium mb-1"><span id="correct-count" class="text-green-600">Correct: 0</span><span id="incorrect-count" class="text-red-600">Incorrect: 0</span></div><div class="w-full bg-gray-200 rounded-full h-2.5"><div id="accuracy-bar" class="bg-green-500 h-2.5 rounded-full" style="width: 0%"></div></div><p id="accuracy-rate-text" class="text-center text-sm text-gray-500 mt-2">0% accuracy rate</p></div>
                    </div>
                    <div class="bg-white rounded-xl shadow-sm"><div class="p-5"><h4 class="text-lg font-bold text-gray-800 mb-4">Prediction History</h4><p class="text-sm text-gray-500 -mt-3 mb-4">All your past and upcoming predictions</p><div class="overflow-x-auto"><table class="w-full text-sm text-left"><thead class="text-xs text-gray-700 uppercase bg-gray-50"><tr><th scope="col" class="px-6 py-3">Match</th><th scope="col" class="px-6 py-3">Sport</th><th scope="col" class="px-6 py-3">Your Prediction</th><th scope="col" class="px-6 py-3">Actual Result</th><th scope="col" class="px-6 py-3">Date</th><th scope="col" class="px-6 py-3 text-center">Result</th></tr></thead><tbody id="prediction-history-body"></tbody></table></div></div></div>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();
            
            try {
                const response = await fetch('api.php?action=getMyPredictionsData');
                const result = await response.json();

                if (result.status === 'success') {
                    const { stats, history } = result.data;

                    // Populate Stats
                    document.getElementById('total-predictions').textContent = stats.total;
                    document.getElementById('correct-predictions').textContent = stats.correct;
                    document.getElementById('accuracy-rate-card').textContent = `${stats.accuracy}%`;
                    document.getElementById('correct-count').textContent = `Correct: ${stats.correct}`;
                    document.getElementById('incorrect-count').textContent = `Incorrect: ${stats.incorrect}`;
                    document.getElementById('accuracy-bar').style.width = `${stats.accuracy}%`;
                    document.getElementById('accuracy-rate-text').textContent = `${stats.accuracy}% accuracy rate`;

                    // --- NEW: Populate Global Rank ---
                    const rankEl = document.getElementById('global-rank');
                    if (stats.rank !== 'N/A') {
                        rankEl.innerHTML = `#${stats.rank} <span class="text-base font-normal text-gray-500">of ${stats.total_predictors}</span>`;
                    } else {
                        rankEl.textContent = 'N/A';
                    }

                    // Populate History Table
                    const historyTableBody = document.getElementById('prediction-history-body');
                    if (history.length > 0) {
                        history.forEach(p => {
                            const row = document.createElement('tr');
                            row.className = 'bg-white border-b';
                            let resultHtml = '';
                            switch(p.status) {
                                case 'correct': resultHtml = `<span class="flex items-center justify-center text-xs font-bold text-green-700 bg-green-100 px-3 py-1 rounded-full"><i data-lucide="check-circle-2" class="w-4 h-4 mr-1"></i>Correct</span>`; break;
                                case 'incorrect': resultHtml = `<span class="flex items-center justify-center text-xs font-bold text-red-700 bg-red-100 px-3 py-1 rounded-full"><i data-lucide="x-circle" class="w-4 h-4 mr-1"></i>Incorrect</span>`; break;
                                case 'upcoming': resultHtml = `<span class="flex items-center justify-center text-xs font-bold text-yellow-700 bg-yellow-100 px-3 py-1 rounded-full"><i data-lucide="clock" class="w-4 h-4 mr-1"></i>Upcoming</span>`; break;
                            }
                            row.innerHTML = `
                                <td class="px-6 py-4 font-medium text-gray-900">${p.team1_name} vs ${p.team2_name}</td>
                                <td class="px-6 py-4"><span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">${p.sport_name}</span></td>
                                <td class="px-6 py-4 font-semibold">${p.predicted_team_name}</td>
                                <td class="px-6 py-4 font-semibold ${p.actual_winner_team_name === 'TBD' ? 'text-gray-500' : ''}">${p.actual_winner_team_name}</td>
                                <td class="px-6 py-4">${new Date(p.datetime).toLocaleDateString('en-CA')}</td>
                                <td class="px-6 py-4 text-center">${resultHtml}</td>
                            `;
                            historyTableBody.appendChild(row);
                        });
                    } else {
                        historyTableBody.innerHTML = `<tr><td colspan="6" class="text-center py-10 text-gray-500">You haven't made any predictions yet.</td></tr>`;
                    }
                    lucide.createIcons();

                } else {
                    document.querySelector('main').innerHTML = `<p class="text-red-500 p-6">Error: ${result.message}</p>`;
                }
            } catch (error) {
                console.error("Fetch Error:", error);
                document.querySelector('main').innerHTML = `<p class="text-red-500 p-6">A critical error occurred while fetching your prediction data.</p>`;
            }
        });
    </script>
</body>
</html>
