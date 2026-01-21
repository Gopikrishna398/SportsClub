<?php
require_once 'session_auth.php';
check_auth(['admin']); // Only allows 'admin'
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Prediction Leaderboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-link.active { background-color: #f1f5f9; color: #1e293b; font-weight: 600; border-left: 3px solid #4f46e5; }
    </style>
</head>
<body class="bg-slate-50">
    <!-- Mock Database Script (Self-contained for this file) -->
    <script>
        const db = {
            users: [
                { id: 1, username: 'student1' }, { id: 15, username: 'student15' },
                { id: 22, username: 'student22' }, { id: 8, username: 'student8' },
                { id: 4, username: 'student4' },
            ],
            teams: [
                { id: 1, name: 'Team Alpha' }, { id: 2, name: 'Team Beta' },
                { id: 3, name: 'Team Gamma' }, { id: 4, name: 'Team Delta' },
                { id: 5, name: 'Team Epsilon' }, { id: 6, name: 'Team Zeta' },
                { id: 7, name: 'Team Theta' }, { id: 8, name: 'Team Iota' },
            ],
            matches: [
                { id: 1, team1Id: 1, team2Id: 2, sport: 'Cricket', date: '2024-01-20' },
                { id: 2, team1Id: 3, team2Id: 4, sport: 'Football', date: '2024-01-19' },
                { id: 3, team1Id: 5, team2Id: 6, sport: 'Basketball', date: '2024-01-25' },
                { id: 4, team1Id: 7, team2Id: 8, sport: 'Cricket', date: '2024-01-18' },
            ],
            userPredictions: [
                // student1
                { userId: 1, matchId: 1, predictedWinnerId: 1, actualWinnerId: 1, status: 'correct' },
                { userId: 1, matchId: 2, predictedWinnerId: 3, actualWinnerId: 4, status: 'incorrect' },
                { userId: 1, matchId: 3, predictedWinnerId: 5, actualWinnerId: null, status: 'upcoming' },
                // student15
                { userId: 15, matchId: 1, predictedWinnerId: 1, actualWinnerId: 1, status: 'correct' },
                { userId: 15, matchId: 2, predictedWinnerId: 4, actualWinnerId: 4, status: 'correct' },
                { userId: 15, matchId: 3, predictedWinnerId: 5, actualWinnerId: 6, status: 'incorrect' },
                // student22
                { userId: 22, matchId: 1, predictedWinnerId: 1, actualWinnerId: 1, status: 'correct' },
                { userId: 22, matchId: 2, predictedWinnerId: 4, actualWinnerId: 4, status: 'correct' },
                { userId: 22, matchId: 3, predictedWinnerId: 5, actualWinnerId: 5, status: 'correct' },
                { userId: 22, matchId: 4, predictedWinnerId: 7, actualWinnerId: 7, status: 'correct' },
                 // student8
                { userId: 8, matchId: 1, predictedWinnerId: 2, actualWinnerId: 1, status: 'incorrect' },
                { userId: 8, matchId: 2, predictedWinnerId: 3, actualWinnerId: 4, status: 'incorrect' },
                { userId: 8, matchId: 3, predictedWinnerId: 5, actualWinnerId: 5, status: 'correct' },
            ]
        };
        function getTeamNameById(teamId) { const team = db.teams.find(t => t.id === teamId); return team ? team.name : 'Unknown'; }
        function getMatchById(matchId) { return db.matches.find(m => m.id === matchId); }
        function getUserById(userId) { return db.users.find(u => u.id === userId); }
    </script>

    <div class="flex h-screen">
        <!-- Admin Sidebar -->
        <aside class="w-64 bg-white shadow-sm flex-shrink-0 hidden lg:flex flex-col border-r border-slate-200">
            <div class="p-6 flex items-center space-x-3">
                <div class="bg-indigo-600 p-2 rounded-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg></div>
                <h1 class="text-xl font-bold text-slate-800">SportsWatch Hub</h1>
            </div>
            <nav class="mt-6 flex-1 px-4">
                 <a href="./adminDashboard.html" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <a href="./analytics.html" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="bar-chart-2" class="w-5 h-5 mr-3"></i>Analytics</a>
                <a href="#" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg active"><i data-lucide="bar-chart-2" class="w-5 h-5 mr-3"></i>Predictions</a>
                <a href="./createMatch.html" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="plus-circle" class="w-5 h-5 mr-3"></i>Create Match</a>
                <a href="./manageTeams.html" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="users" class="w-5 h-5 mr-3"></i>Manage Teams</a>
                <a href="./userManagement.html" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="user-cog" class="w-5 h-5 mr-3"></i>User Management</a>
                <a href="./systemSettings.html" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="settings" class="w-5 h-5 mr-3"></i>System Settings</a>
                <a href="./reports.html" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="file-text" class="w-5 h-5 mr-3"></i>Reports</a>
                <!-- Add other admin links here -->
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b border-slate-200">
                <h2 class="text-2xl font-bold text-slate-800">Prediction Leaderboard</h2>
                <div class="flex items-center space-x-4">
                    <button class="text-slate-500 hover:text-slate-700"><i data-lucide="bell" class="w-6 h-6"></i></button>
                    <div class="flex items-center space-x-2">
                        <img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/DC2626/FFFFFF?text=A" alt="Admin avatar">
                        <span class="font-semibold text-slate-700">admin1</span>
                        <span class="text-xs bg-red-500 text-white font-bold px-2 py-1 rounded-md">ADMIN</span>
                    </div>
                    <button class="flex items-center space-x-2 text-slate-600 hover:text-slate-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></button>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-6">
                <div class="space-y-6">
                    <!-- High-Level Stats Section -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Total Predictions Card -->
                        <div class="bg-white border border-slate-200 rounded-lg p-6 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-bold text-slate-800 flex items-center mb-2"><i data-lucide="list-checks" class="w-5 h-5 mr-2 text-indigo-500"></i>Total Predictions Made</h3>
                                <p id="total-predictions" class="text-4xl font-bold text-slate-900">0</p>
                            </div>
                        </div>
                        <!-- Prediction Accuracy Loader -->
                        <div class="bg-white border border-slate-200 rounded-lg p-6">
                            <h3 class="text-lg font-bold text-slate-800 flex items-center mb-4"><i data-lucide="target" class="w-5 h-5 mr-2 text-indigo-500"></i>Overall Prediction Accuracy</h3>
                            <div class="w-full bg-slate-200 rounded-full h-4 relative overflow-hidden">
                                <div id="correct-bar" class="bg-green-500 h-full absolute left-0 top-0"></div>
                                <div id="incorrect-bar" class="bg-red-500 h-full absolute right-0 top-0"></div>
                            </div>
                            <div class="flex justify-between mt-2 text-sm font-medium">
                                <span id="correct-label" class="text-green-600">Correct: 0 (0%)</span>
                                <span id="incorrect-label" class="text-red-600">Incorrect: 0 (0%)</span>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Top Predictors Leaderboard -->
                        <div class="lg:col-span-1 bg-white border border-slate-200 rounded-lg p-6">
                            <h3 class="text-lg font-bold text-slate-800 flex items-center mb-4"><i data-lucide="trophy" class="w-5 h-5 mr-2 text-yellow-500"></i>Top Predictors</h3>
                            <ol id="leaderboard-list" class="space-y-3"></ol>
                        </div>

                        <!-- Full Prediction History Table -->
                        <div class="lg:col-span-2 bg-white border border-slate-200 rounded-lg p-6">
                            <h3 class="text-lg font-bold text-slate-800 flex items-center mb-4"><i data-lucide="history" class="w-5 h-5 mr-2 text-indigo-500"></i>Full Prediction History</h3>
                            <div class="overflow-y-auto max-h-96">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50 sticky top-0">
                                        <tr>
                                            <th class="px-6 py-3">User</th>
                                            <th class="px-6 py-3">Match</th>
                                            <th class="px-6 py-3">Prediction</th>
                                            <th class="px-6 py-3">Actual Result</th>
                                            <th class="px-6 py-3 text-center">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody id="history-table-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const allPredictions = db.userPredictions;
            const completedPredictions = allPredictions.filter(p => p.status !== 'upcoming');
            const correctCount = completedPredictions.filter(p => p.status === 'correct').length;
            const incorrectCount = completedPredictions.filter(p => p.status === 'incorrect').length;
            const totalCompleted = correctCount + incorrectCount;
            const accuracy = totalCompleted > 0 ? Math.round((correctCount / totalCompleted) * 100) : 0;

            // --- Update Total Predictions Card ---
            document.getElementById('total-predictions').textContent = allPredictions.length;

            // --- Update Accuracy Loader ---
            document.getElementById('correct-bar').style.width = `${accuracy}%`;
            document.getElementById('incorrect-bar').style.width = `${100 - accuracy}%`;
            document.getElementById('correct-label').textContent = `Correct: ${correctCount} (${accuracy}%)`;
            document.getElementById('incorrect-label').textContent = `Incorrect: ${incorrectCount} (${100 - accuracy}%)`;

            // --- Calculate and Render Leaderboard ---
            const userScores = {};
            completedPredictions.forEach(p => {
                if (p.status === 'correct') {
                    userScores[p.userId] = (userScores[p.userId] || 0) + 1;
                }
            });
            const leaderboard = Object.entries(userScores)
                .map(([userId, score]) => ({ userId: parseInt(userId), score }))
                .sort((a, b) => b.score - a.score);
            
            const leaderboardList = document.getElementById('leaderboard-list');
            leaderboardList.innerHTML = leaderboard.map((entry, index) => {
                const user = getUserById(entry.userId);
                const medal = ['text-yellow-400', 'text-slate-400', 'text-orange-400'][index] || 'text-slate-300';
                return `
                    <li class="flex items-center justify-between bg-slate-50 p-2 rounded-md">
                        <div class="flex items-center">
                            <i data-lucide="medal" class="w-5 h-5 ${medal} mr-2"></i>
                            <span class="font-semibold text-slate-700">${user ? user.username : 'Unknown'}</span>
                        </div>
                        <span class="font-bold text-slate-800">${entry.score} Correct</span>
                    </li>
                `;
            }).join('');

            // --- Populate Full History Table ---
            const historyTableBody = document.getElementById('history-table-body');
            historyTableBody.innerHTML = allPredictions.map(p => {
                const user = getUserById(p.userId);
                const match = getMatchById(p.matchId);
                const predictedWinner = getTeamNameById(p.predictedWinnerId);
                const actualWinner = p.actualWinnerId ? getTeamNameById(p.actualWinnerId) : 'TBD';
                
                let statusBadge = '';
                switch(p.status) {
                    case 'correct': statusBadge = `<span class="flex items-center justify-center text-xs font-bold text-green-700 bg-green-100 px-3 py-1 rounded-full"><i data-lucide="check-circle-2" class="w-4 h-4 mr-1"></i>Correct</span>`; break;
                    case 'incorrect': statusBadge = `<span class="flex items-center justify-center text-xs font-bold text-red-700 bg-red-100 px-3 py-1 rounded-full"><i data-lucide="x-circle" class="w-4 h-4 mr-1"></i>Incorrect</span>`; break;
                    case 'upcoming': statusBadge = `<span class="flex items-center justify-center text-xs font-bold text-yellow-700 bg-yellow-100 px-3 py-1 rounded-full"><i data-lucide="clock" class="w-4 h-4 mr-1"></i>Upcoming</span>`; break;
                }

                return `
                    <tr class="bg-white border-b">
                        <td class="px-6 py-4 font-medium text-gray-900">${user ? user.username : 'N/A'}</td>
                        <td class="px-6 py-4">${match ? `${getTeamNameById(match.team1Id)} vs ${getTeamNameById(match.team2Id)}` : 'N/A'}</td>
                        <td class="px-6 py-4 font-semibold">${predictedWinner}</td>
                        <td class="px-6 py-4 ${p.actualWinnerId === null ? 'text-gray-500' : 'font-semibold'}">${actualWinner}</td>
                        <td class="px-6 py-4 text-center">${statusBadge}</td>
                    </tr>
                `;
            }).join('');
            
            lucide.createIcons();
        });
    </script>
</body>
</html>