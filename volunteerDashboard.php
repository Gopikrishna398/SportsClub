<?php
// --- Security Gatekeeper ---
require_once 'session_auth.php';
// This page is only for volunteers.
check_auth(['volunteer']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Volunteer Dashboard</title>
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
        <!-- Volunteer Sidebar -->
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden lg:flex flex-col">
            <div class="p-6 flex items-center space-x-3"><div class="bg-indigo-600 p-2 rounded-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg></div><h1 class="text-xl font-bold text-gray-800">SportsWatch Hub</h1></div>
            <nav id="sidebar-nav" class="mt-6 flex-1 px-4">
                <a href="#" class="sidebar-link flex items-center px-4 py-3 rounded-lg active"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <a href="./scheduleMatch.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="calendar-plus" class="w-5 h-5 mr-3"></i>Schedule Match</a>
                <a href="./updateScores.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="pencil-line" class="w-5 h-5 mr-3"></i>Update Scores</a>
                <a href="./matchReports.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="file-text" class="w-5 h-5 mr-3"></i>Match Reports</a>
                <a href="./myMatches.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="clipboard-check" class="w-5 h-5 mr-3"></i>My Matches</a>
            </nav>
            <div id="assigned-sports-sidebar" class="px-6 py-4 mt-auto">
                <h3 class="text-sm font-semibold text-gray-500 uppercase tracking-wider">Assigned Sports</h3>
                <div class="mt-3 space-x-2"></div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
                <div class="flex items-center space-x-4">
                    <button class="text-gray-500 hover:text-gray-700"><i data-lucide="bell" class="w-6 h-6"></i></button>
                    <div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/FB923C/4A5568?text=V" alt="Volunteer avatar"><span class="font-semibold text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-orange-100 text-orange-600 font-bold px-2 py-1 rounded-md">VOLUNTEER</span></div>
                    <a href="logout.php" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="space-y-6">
                    <div id="welcome-banner" class="p-6 rounded-xl shadow-sm text-white bg-orange-500"></div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Live Matches</p><p id="live-matches-count" class="text-3xl font-bold text-gray-800">0</p></div><div class="bg-red-100 p-3 rounded-full"><i data-lucide="radio-tower" class="w-6 h-6 text-red-500"></i></div></div>
                        <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Assigned Matches</p><p id="assigned-matches-count" class="text-3xl font-bold text-gray-800">0</p></div><div class="bg-blue-100 p-3 rounded-full"><i data-lucide="calendar-check" class="w-6 h-6 text-blue-500"></i></div></div>
                        <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Pending Reports</p><p id="pending-reports-count" class="text-3xl font-bold text-gray-800">0</p></div><div class="bg-yellow-100 p-3 rounded-full"><i data-lucide="file-clock" class="w-6 h-6 text-yellow-500"></i></div></div>
                        <div class="bg-white p-5 rounded-xl shadow-sm flex items-center justify-between"><div><p class="text-sm text-gray-500">Completed</p><p id="completed-matches-count" class="text-3xl font-bold text-gray-800">0</p></div><div class="bg-green-100 p-3 rounded-full"><i data-lucide="check-circle-2" class="w-6 h-6 text-green-500"></i></div></div>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div id="live-matches-widget" class="bg-white rounded-xl shadow-sm p-6"></div>
                        <div id="assigned-matches-widget" class="bg-white rounded-xl shadow-sm p-6"></div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();

            try {
                const response = await fetch('api.php?action=getVolunteerDashboardData');
                const result = await response.json();

                if (result.status === 'success') {
                    const data = result.data;
                    const username = "<?php echo htmlspecialchars($_SESSION['username']); ?>";

                    // --- Populate Header & Welcome Banner ---
                    const welcomeBanner = document.getElementById('welcome-banner');
                    welcomeBanner.innerHTML = `
                        <h3 class="text-2xl font-bold">Welcome, ${username}!</h3>
                        <p class="mt-1 text-orange-100">Manage your assigned sports and keep the tournament running smoothly.</p>
                        <div class="mt-3 space-x-2">${data.assigned_sports.map(sport => `<span class="inline-block bg-white/20 text-white text-xs font-semibold px-2.5 py-1 rounded-full">${sport}</span>`).join('')}</div>
                    `;
                    const assignedSportsSidebar = document.getElementById('assigned-sports-sidebar').querySelector('div');
                    assignedSportsSidebar.innerHTML = data.assigned_sports.map(sport => `<span class="inline-block bg-orange-100 text-orange-700 text-xs font-semibold px-2.5 py-1 rounded-full">${sport}</span>`).join(' ');

                    // --- Populate Stat Cards ---
                    document.getElementById('live-matches-count').textContent = data.stats.live_matches || 0;
                    document.getElementById('assigned-matches-count').textContent = data.stats.total_assigned || 0;
                    document.getElementById('pending-reports-count').textContent = data.stats.pending_reports || 0;
                    document.getElementById('completed-matches-count').textContent = data.stats.completed_matches || 0;

                    // --- Populate Widgets ---
                    const liveWidget = document.getElementById('live-matches-widget');
                    liveWidget.innerHTML = `<h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center"><i data-lucide="radio-tower" class="w-5 h-5 mr-2 text-red-500"></i>Live Matches</h4><p class="text-sm text-gray-500 -mt-3 mb-4">Currently ongoing matches you're managing</p><div id="live-widget-list" class="space-y-3"></div>`;
                    const liveWidgetList = document.getElementById('live-widget-list');
                    if(data.live_match_widget) {
                        const match = data.live_match_widget;
                        liveWidgetList.innerHTML = `
                            <div class="bg-gray-50 rounded-lg p-4">
                                <div class="flex justify-between items-center">
                                    <div><p class="font-bold text-gray-800">${match.team1_name} vs ${match.team2_name}</p><p class="text-sm text-gray-500">${match.sport_name} - ${match.venue}</p></div>
                                    <div class="text-right"><p class="font-bold text-xl text-gray-800">${match.score1}-${match.score2}</p><span class="text-xs bg-red-500 text-white font-semibold px-2 py-0.5 rounded-full">LIVE</span></div>
                                </div>
                                <a href="./updateScores.php?matchId=${match.id}" class="mt-4 w-full bg-red-500 text-white font-semibold py-2.5 px-4 rounded-lg hover:bg-red-600 transition-colors flex items-center justify-center"><i data-lucide="edit" class="w-4 h-4 mr-2"></i>Update Score</a>
                            </div>`;
                    } else {
                        liveWidgetList.innerHTML = `<p class="text-gray-500 text-center py-4">No live matches assigned to you.</p>`;
                    }

                    const assignedWidget = document.getElementById('assigned-matches-widget');
                    assignedWidget.innerHTML = `<h4 class="text-lg font-bold text-gray-800 mb-4 flex items-center"><i data-lucide="clipboard-check" class="w-5 h-5 mr-2 text-blue-500"></i>My Assigned Matches</h4><p class="text-sm text-gray-500 -mt-3 mb-4">Matches you're responsible for</p><div id="assigned-widget-list" class="space-y-4"></div><a href="./myMatches.php" class="mt-6 block text-center w-full py-2.5 border border-gray-200 rounded-lg text-sm font-semibold text-gray-700 hover:bg-gray-50">View All My Matches &rarr;</a>`;
                    const assignedWidgetList = document.getElementById('assigned-widget-list');
                    if(data.assigned_matches_widget.length > 0) {
                        data.assigned_matches_widget.forEach(match => {
                            const date = new Date(match.datetime);
                            const time = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                            const day = date.toDateString() === new Date().toDateString() ? 'Today' : 'Yesterday';
                            const statusBadge = match.status === 'UPCOMING' ? `<span class="text-xs font-bold text-blue-500 bg-blue-100 px-3 py-1.5 rounded-full">UPCOMING</span>` : `<span class="text-xs font-bold text-green-500 bg-green-100 px-3 py-1.5 rounded-full">COMPLETED</span>`;
                            assignedWidgetList.innerHTML += `
                                <div class="bg-gray-50 rounded-lg p-4 flex justify-between items-center">
                                    <div><p class="font-bold text-gray-800">${match.team1_name} vs ${match.team2_name}</p><p class="text-sm text-gray-500">${match.sport_name} - ${match.venue}</p></div>
                                    <div class="text-right">${statusBadge}<p class="text-sm text-gray-500 mt-1">${time} - ${day}</p></div>
                                </div>`;
                        });
                    } else {
                        assignedWidgetList.innerHTML = `<p class="text-gray-500 text-center py-4">No other matches assigned.</p>`;
                    }

                    lucide.createIcons();
                } else {
                    document.querySelector('main').innerHTML = `<p class="text-red-500 p-6">Error: ${result.message}</p>`;
                }
            } catch(error) {
                console.error("Fetch Error:", error);
                document.querySelector('main').innerHTML = `<p class="text-red-500 p-6">A critical error occurred while fetching dashboard data.</p>`;
            }
        });
    </script>
</body>
</html>