<?php
require_once 'session_auth.php';
check_auth(['student']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Dashboard</title>
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
                <a href="#" class="sidebar-link flex items-center px-4 py-3 rounded-lg active"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <!-- <a href="./championship.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="award" class="w-5 h-5 mr-3"></i>Championship</a> -->
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
            <header class="bg-white shadow-sm p-4 flex justify-between items-center"><h2 class="text-2xl font-bold text-gray-800">Dashboard</h2><div class="flex items-center space-x-4"><button class="text-gray-500 hover:text-gray-700"><i data-lucide="bell" class="w-6 h-6"></i></button><div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/E2E8F0/4A5568?text=S" alt="User avatar"><span class="font-semibold text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-indigo-100 text-indigo-600 font-bold px-2 py-1 rounded-md">STUDENT</span></div><a href="logout.php" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a></div></header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="space-y-6">
                    <div class="p-6 rounded-xl shadow-sm text-white" style="background: linear-gradient(90deg, #2563EB 0%, #F97316 100%);"><h3 class="text-2xl font-bold">Welcome Back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h3><p class="mt-1 text-blue-100">Stay updated with live matches, make predictions, and track your favorite teams.</p></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Live Matches</p><p id="live-matches-count" class="text-3xl font-bold text-gray-800">0</p></div><div class="bg-red-100 p-3 rounded-full"><i data-lucide="radio-tower" class="w-6 h-6 text-red-500"></i></div></div>
                        <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Upcoming Matches</p><p id="upcoming-matches-count" class="text-3xl font-bold text-gray-800">0</p></div><div class="bg-blue-100 p-3 rounded-full"><i data-lucide="calendar-clock" class="w-6 h-6 text-blue-500"></i></div></div>
                        <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Prediction Accuracy</p><p id="accuracy-rate-card" class="text-3xl font-bold text-gray-800">0%</p></div><div class="bg-green-100 p-3 rounded-full"><i data-lucide="target" class="w-6 h-6 text-green-500"></i></div></div>
                        <!-- NEW: Top Predictor Card -->
                        <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-500">Top Predictor</p>
                                <p id="top-predictor-name" class="text-xl font-bold text-gray-800">Loading...</p>
                                <p id="top-predictor-score" class="text-sm text-gray-500"></p>
                            </div>
                            <div class="bg-purple-100 p-3 rounded-full"><i data-lucide="user-check" class="w-6 h-6 text-purple-500"></i></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div id="live-match-widget" class="bg-white rounded-xl shadow-sm p-6"></div>
                        <div id="upcoming-matches-widget" class="bg-white rounded-xl shadow-sm p-6"></div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();

            try {
                const response = await fetch('api.php?action=getStudentDashboardData');
                const result = await response.json();

                if (result.status === 'success') {
                    const data = result.data;
                    
                    document.getElementById('live-matches-count').textContent = data.live_matches_count;
                    document.getElementById('upcoming-matches-count').textContent = data.upcoming_matches_count;
                    document.getElementById('accuracy-rate-card').textContent = `${data.accuracy}%`;

                    // --- NEW: Update Top Predictor Card ---
                    if (data.top_predictor) {
                        document.getElementById('top-predictor-name').textContent = data.top_predictor.username;
                        document.getElementById('top-predictor-score').textContent = `${data.top_predictor.score} Correct`;
                    } else {
                        document.getElementById('top-predictor-name').textContent = 'N/A';
                        document.getElementById('top-predictor-score').textContent = 'No correct predictions yet';
                    }

                    if (data.live_matches_count > 0) {
                        document.getElementById('live-nav-badge').classList.remove('hidden');
                    }

                    const liveWidget = document.getElementById('live-match-widget');
                    if (data.live_match) {
                        const match = data.live_match;
                        liveWidget.innerHTML = `
                            <div class="flex justify-between items-start">
                                <div><h4 class="font-bold text-gray-800">${match.team1_name} vs ${match.team2_name}</h4><p class="text-sm text-gray-500">${match.sport_name}</p></div>
                                <div class="text-right"><p class="text-xl font-bold text-red-500">${match.score1}-${match.score2}</p><span class="text-xs bg-orange-400 text-white font-semibold px-2 py-1 rounded-md">${match.commentary}</span></div>
                            </div>
                            <a href="./liveScore.php" class="mt-6 block text-center w-full py-2.5 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50">View All Live Scores &rarr;</a>
                        `;
                    } else {
                        liveWidget.innerHTML = `<h4 class="font-bold text-gray-800 mb-4">No Live Matches</h4><p class="text-gray-500">Check back later for live updates.</p>`;
                    }

                    const upcomingWidget = document.getElementById('upcoming-matches-widget');
                    upcomingWidget.innerHTML = `<h4 class="text-lg font-bold text-gray-800 mb-4">Upcoming Matches</h4><div class="space-y-4"></div>`;
                    const upcomingList = upcomingWidget.querySelector('.space-y-4');
                    if (data.upcoming_matches && data.upcoming_matches.length > 0) {
                        data.upcoming_matches.forEach(match => {
                            const date = new Date(match.datetime);
                            const time = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            const day = date.toDateString() === new Date().toDateString() ? 'Today' : date.toLocaleDateString([], { weekday: 'long' });
                            upcomingList.innerHTML += `<div class="flex items-center justify-between"><div><p class="font-semibold text-gray-700">${match.team1_name} vs ${match.team2_name}</p><p class="text-sm text-gray-500">${match.sport_name}</p></div><div class="text-right"><p class="font-semibold text-gray-700">${time}</p><p class="text-sm text-gray-500">${day}</p></div></div>`;
                        });
                    }
                    upcomingList.innerHTML += `<a href="./schedule.php" class="mt-6 block text-center w-full py-2.5 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50">View Full Schedule &rarr;</a>`;
                } else {
                    console.error('Failed to fetch dashboard data:', result.message);
                }
            } catch (error) {
                console.error('Error fetching data:', error);
            }
        });
    </script>
</body>
</html>
