<?php
require_once 'session_auth.php';
check_auth(['student']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Live Scores</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; /* Lighter gray */ }
        .sidebar-link.active { background-color: #eef2ff; color: #4f46e5; font-weight: 600; }
        .sidebar-link:not(.active):hover { background-color: #f9fafb; }
    </style>
</head>
<body class="bg-slate-50">
    <div class="flex h-screen">
        <aside class="w-64 bg-white shadow-md flex-shrink-0 hidden lg:flex flex-col">
            <div class="p-6 flex items-center space-x-3"><div class="bg-indigo-600 p-2 rounded-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg></div><h1 class="text-xl font-bold text-gray-800">SportsWatch Hub</h1></div>
            <nav id="sidebar-nav" class="mt-6 flex-1 px-4">
                <a href="./dashboard.php" class="idebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <!-- <a href="./championship.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="award" class="w-5 h-5 mr-3"></i>Championship</a> -->
                <a href="./schedule.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="calendar-days" class="w-5 h-5 mr-3"></i>Match Schedule</a>
                <a href="./points.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="table" class="w-5 h-5 mr-3"></i>Points Table</a>
                <a href="#" class="sidebar-link flex items-center px-4 py-3 rounded-lg active"><i data-lucide="radio-tower" class="w-5 h-5 mr-3"></i>Live Scores<span id="live-nav-badge" class="ml-auto text-xs bg-red-500 text-white font-semibold px-2 py-0.5 rounded-full hidden">LIVE</span></a>
                <a href="./predictions.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="lightbulb" class="w-5 h-5 mr-3"></i>Predictions</a>
                <a href="./stats.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="bar-chart-3" class="w-5 h-5 mr-3"></i>Team Stats</a>
                <a href="./myPredictions.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>My Predictions</a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
             <header class="sticky top-0 z-20 bg-white/80 backdrop-blur-sm border-b border-slate-200">
                <div class="px-6 py-4 flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <i data-lucide="radio-tower" class="w-8 h-8 text-indigo-600"></i>
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800">Live Scores</h2>
                            <p class="text-sm text-slate-500">Real-time match updates and scores.</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-6">
                        <div class="flex items-center space-x-4">
                            <img class="w-10 h-10 rounded-full" src="https://placehold.co/100x100/E2E8F0/4A5568?text=S" alt="User avatar">
                            <div class="text-right">
                                <span class="font-semibold text-slate-700 block"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                                <span class="text-xs bg-indigo-100 text-indigo-600 font-bold px-2 py-0.5 rounded-md">STUDENT</span>
                            </div>
                        </div>
                        <a href="logout.php" class="text-slate-500 hover:text-red-600" title="Logout">
                            <i data-lucide="log-out" class="w-6 h-6"></i>
                        </a>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto p-6 lg:p-8">
                <div class="mb-8 p-4 bg-white rounded-xl shadow-sm border border-slate-200 flex items-center space-x-6">
                    <div class="flex items-center space-x-2">
                        <i data-lucide="filter" class="w-5 h-5 text-slate-500"></i>
                        <h4 class="text-sm font-semibold text-slate-700">Filter by:</h4>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label for="sport-filter" class="text-sm font-medium text-slate-600">Sport</label>
                        <select id="sport-filter" class="bg-slate-100 border-transparent text-slate-900 text-sm font-medium rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-40 p-2 transition">
                            <option value="all" selected>All Sports</option>
                        </select>
                    </div>
                    <div class="flex items-center space-x-2">
                        <label for="team-filter" class="text-sm font-medium text-slate-600">Team</label>
                        <select id="team-filter" class="bg-slate-100 border-transparent text-slate-900 text-sm font-medium rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 block w-48 p-2 transition">
                            <option value="all" selected>All Teams</option>
                        </select>
                    </div>
                    <button onclick="location.reload()" class="ml-auto flex items-center space-x-2 text-slate-600 hover:text-indigo-600">
                        <i data-lucide="rotate-cw" class="w-4 h-4"></i>
                        <span class="text-sm font-medium">Refresh Data</span>
                    </button>
                </div>

                <div id="main-grid" class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
                    <div class="lg:col-span-2 space-y-8">
                        <section>
                            <h3 class="text-xl font-bold text-slate-800 mb-4 px-2">Live Matches</h3>
                            <div id="live-matches-list" class="space-y-6"></div>
                        </section>
                        <section>
                            <h3 class="text-xl font-bold text-slate-800 mb-4 px-2">Upcoming</h3>
                            <div id="upcoming-matches-list" class="space-y-6"></div>
                        </section>
                    </div>

                    <div class="space-y-8">
                        <section>
                            <h3 class="text-xl font-bold text-slate-800 mb-4 px-2">Recent Results</h3>
                            <div id="recent-results-list" class="space-y-6"></div>
                        </section>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        const getTeamInitials = (name) => {
            if (!name) return '?';
            const words = name.split(' ');
            if (words.length > 1) return (words[0][0] + words[1][0]).toUpperCase();
            return name.substring(0, 2).toUpperCase();
        };

        const createPlaceholderCard = (message, icon) => `<div class="bg-white rounded-xl shadow-sm p-8 text-center text-slate-500 flex flex-col items-center justify-center border border-slate-200 min-h-[180px]"><i data-lucide="${icon}" class="w-12 h-12 text-slate-300 mb-4"></i><p class="font-medium max-w-xs">${message}</p></div>`;

        document.addEventListener('DOMContentLoaded', () => {
            let allMatchesData = null;

            const liveList = document.getElementById('live-matches-list');
            const upcomingList = document.getElementById('upcoming-matches-list');
            const recentList = document.getElementById('recent-results-list');
            const sportFilterSelect = document.getElementById('sport-filter');
            const teamFilterSelect = document.getElementById('team-filter');
            
            const renderMatches = (data) => {
                const { live, upcoming, completed } = data;
                const placeholderMsg = "No matches found for your selected filters.";

                liveList.innerHTML = live.length > 0 ? live.map(match => `
                    <div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow duration-300 border border-slate-200 overflow-hidden">
                        <div class="p-5">
                            <div class="flex justify-between items-center mb-4">
                                <span class="flex items-center text-red-600 font-semibold text-sm">
                                    <span class="relative flex h-2.5 w-2.5 mr-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-500 opacity-75"></span><span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-600"></span></span>
                                    LIVE
                                </span>
                                <span class="text-sm font-medium bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md">${match.sport_name}</span>
                            </div>
                            <div class="flex items-center text-center">
                                <div class="flex-1 flex flex-col items-center space-y-2"><div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center font-bold text-xl text-slate-600">${getTeamInitials(match.team1_name)}</div><p class="font-semibold text-lg text-slate-800 mt-2">${match.team1_name}</p></div>
                                <div class="w-1/3"><p class="text-5xl font-bold text-slate-800">${match.score1} - ${match.score2}</p></div>
                                <div class="flex-1 flex flex-col items-center space-y-2"><div class="w-16 h-16 bg-slate-200 rounded-full flex items-center justify-center font-bold text-xl text-slate-600">${getTeamInitials(match.team2_name)}</div><p class="font-semibold text-lg text-slate-800 mt-2">${match.team2_name}</p></div>
                            </div>
                        </div>
                        ${match.commentary ? `<div class="bg-red-50 text-red-800 text-sm font-medium text-center p-3 border-t border-red-200">${match.commentary}</div>` : ''}
                    </div>`).join('') : createPlaceholderCard(placeholderMsg, 'search-x');
                
                upcomingList.innerHTML = upcoming.length > 0 ? upcoming.slice(0, 2).map(match => {
                    const time = new Date(match.datetime).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                    const date = new Date(match.datetime).toLocaleDateString([], { month: 'short', day: 'numeric' });
                    return `<div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow duration-300 border border-slate-200 p-5">
                        <div class="flex justify-between items-center mb-4">
                            <span class="text-sm font-medium bg-blue-100 text-blue-700 px-2.5 py-1 rounded-md">UPCOMING</span>
                            <span class="text-sm font-medium bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md">${match.sport_name}</span>
                        </div>
                        <div class="flex items-center text-center my-4">
                            <div class="flex-1 text-right pr-4"><p class="font-semibold text-lg text-slate-800">${match.team1_name}</p></div>
                            <div class="text-slate-400 font-bold text-xl">vs</div>
                            <div class="flex-1 text-left pl-4"><p class="font-semibold text-lg text-slate-800">${match.team2_name}</p></div>
                        </div>
                        <div class="text-center text-sm text-slate-500 pt-4 border-t border-slate-200 flex items-center justify-center space-x-4"><span><i data-lucide="calendar" class="w-4 h-4 inline-block mr-1.5 text-slate-400"></i>${date} at ${time}</span><span><i data-lucide="map-pin" class="w-4 h-4 inline-block mr-1.5 text-slate-400"></i>${match.venue}</span></div>
                    </div>`
                }).join('') : createPlaceholderCard(placeholderMsg, 'search-x');

                recentList.innerHTML = completed.length > 0 ? completed.slice(0, 3).map(match => {
                    const isWinner1 = parseInt(match.score1) > parseInt(match.score2), isWinner2 = parseInt(match.score2) > parseInt(match.score1);
                    return `<div class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow duration-300 border border-slate-200 p-5">
                        <div class="flex justify-between items-center mb-4"><span class="flex items-center text-sm font-medium bg-green-100 text-green-700 px-2.5 py-1 rounded-md"><i data-lucide="check-circle-2" class="w-4 h-4 mr-1.5"></i>FINISHED</span><span class="text-sm font-medium bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md">${match.sport_name}</span></div>
                        <div class="space-y-2 py-2">
                           <div class="flex justify-between items-center"><p class="font-semibold text-base ${isWinner1 ? 'text-slate-800' : 'text-slate-500'}">${match.team1_name}</p><p class="font-bold text-base ${isWinner1 ? 'text-slate-800' : 'text-slate-500'}">${match.score1}</p></div>
                           <div class="flex justify-between items-center"><p class="font-semibold text-base ${isWinner2 ? 'text-slate-800' : 'text-slate-500'}">${match.team2_name}</p><p class="font-bold text-base ${isWinner2 ? 'text-slate-800' : 'text-slate-500'}">${match.score2}</p></div>
                        </div>
                        ${match.commentary ? `<div class="text-center text-sm text-slate-500 pt-3 border-t border-slate-200 mt-3">${match.commentary}</div>` : ''}
                        </div>`
                }).join('') : createPlaceholderCard(placeholderMsg, 'search-x');

                lucide.createIcons();
            };
            
            const populateFilters = (data) => {
                const allMatches = [...data.live, ...data.upcoming, ...data.completed];
                const uniqueSports = [...new Set(allMatches.map(m => m.sport_name))].sort();
                const uniqueTeams = [...new Set(allMatches.flatMap(m => [m.team1_name, m.team2_name]))].sort();
                uniqueSports.forEach(sport => sportFilterSelect.add(new Option(sport, sport)));
                uniqueTeams.forEach(team => teamFilterSelect.add(new Option(team, team)));
            };

            const applyFilters = () => {
                if (!allMatchesData) return;
                const selectedSport = sportFilterSelect.value;
                const selectedTeam = teamFilterSelect.value;
                let data = JSON.parse(JSON.stringify(allMatchesData)); 

                if (selectedSport !== 'all') {
                    data.live = data.live.filter(m => m.sport_name === selectedSport);
                    data.upcoming = data.upcoming.filter(m => m.sport_name === selectedSport);
                    data.completed = data.completed.filter(m => m.sport_name === selectedSport);
                }
                if (selectedTeam !== 'all') {
                    data.live = data.live.filter(m => m.team1_name === selectedTeam || m.team2_name === selectedTeam);
                    data.upcoming = data.upcoming.filter(m => m.team1_name === selectedTeam || m.team2_name === selectedTeam);
                    data.completed = data.completed.filter(m => m.team1_name === selectedTeam || m.team2_name === selectedTeam);
                }
                renderMatches(data);
            }

            const main = async () => {
    try {
        // Ensure the path to api.php is correct relative to this file
        const response = await fetch('api.php?action=getLiveScoreData');
        
        if (!response.ok) {
            throw new Error(`Server responded with ${response.status}: ${response.statusText}`);
        }

        const text = await response.text(); // Get raw text first to debug
        try {
            const result = JSON.parse(text);
            if (result.status === 'success') {
                allMatchesData = result.data;
                populateFilters(allMatchesData);
                applyFilters();
                sportFilterSelect.addEventListener('change', applyFilters);
                teamFilterSelect.addEventListener('change', applyFilters);
            } else {
                console.error("Backend Error:", result.message);
                document.getElementById('main-grid').innerHTML = 
                    `<p class="text-red-500 p-6 bg-white rounded-lg col-span-full">Database Error: ${result.message}</p>`;
            }
        } catch (jsonErr) {
            console.error("Raw response from server:", text);
            throw new Error("Invalid JSON response from server. Check Console (F12).");
        }

    } catch (error) {
        console.error("Fetch Error:", error);
        document.getElementById('main-grid').innerHTML = 
            `<p class="text-red-500 p-6 bg-white rounded-lg col-span-full">Error: ${error.message}</p>`;
    }
};

            main();
        });
    </script>
</body>
</html>
