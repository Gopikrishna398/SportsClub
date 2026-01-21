<?php
require_once 'session_auth.php';
check_auth(['student']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Match Schedule</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { 
            font-family: 'Inter', sans-serif; 
            background-color: #f0f2f5; 
        }
        .sidebar-link.active { 
            background-color: #eef2ff; 
            color: #4f46e5; 
            font-weight: 600; 
        }
        .filter-btn.active {
            background-color: #4f46e5;
            color: white;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen">
        <aside class="w-64 bg-white shadow-lg flex-shrink-0 hidden lg:flex flex-col">
            <div class="p-6 flex items-center space-x-3 border-b border-gray-200">
                <div class="bg-indigo-600 p-2 rounded-lg"><i data-lucide="swords" class="w-6 h-6 text-white"></i></div>
                <h1 class="text-xl font-bold text-gray-800">SportsWatch</h1>
            </div>
            <nav id="sidebar-nav" class="mt-4 flex-1 px-4 space-y-2">
                <a href="./dashboard.php" class="sidebar-link flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <a href="#" class="sidebar-link flex items-center px-4 py-3 rounded-lg active"><i data-lucide="calendar-days" class="w-5 h-5 mr-3"></i>Match Schedule</a>
                <a href="./points.php" class="sidebar-link flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"><i data-lucide="table" class="w-5 h-5 mr-3"></i>Points Table</a>
                <a href="./liveScore.php" class="sidebar-link flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg relative transition-colors duration-200"><i data-lucide="radio-tower" class="w-5 h-5 mr-3"></i>Live Scores<span id="live-nav-badge" class="ml-auto text-xs bg-red-500 text-white font-semibold px-2 py-0.5 rounded-full hidden animate-pulse">LIVE</span></a>
                <a href="./predictions.php" class="sidebar-link flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"><i data-lucide="lightbulb" class="w-5 h-5 mr-3"></i>Predictions</a>
                <a href="./stats.php" class="sidebar-link flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"><i data-lucide="bar-chart-3" class="w-5 h-5 mr-3"></i>Team Stats</a>
                <a href="./myPredictions.php" class="sidebar-link flex items-center px-4 py-3 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors duration-200"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>My Predictions</a>
            </nav>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b border-gray-200">
                <h2 class="text-2xl font-bold text-gray-800">Match Schedule 🗓️</h2>
                <div class="flex items-center space-x-6">
                    <button class="text-gray-500 hover:text-gray-700 relative"><i data-lucide="bell" class="w-6 h-6"></i><span class="absolute -top-1 -right-1 flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span></span></button>
                    <div class="flex items-center space-x-3">
                        <img class="w-10 h-10 rounded-full border-2 border-indigo-200" src="https://placehold.co/100x100/E2E8F0/4A5568?text=<?php echo substr(htmlspecialchars($_SESSION['username']), 0, 1); ?>" alt="User avatar">
                        <div>
                            <span class="font-semibold text-gray-700 block"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                            <span class="text-xs bg-indigo-100 text-indigo-600 font-bold px-2 py-0.5 rounded-md">STUDENT</span>
                        </div>
                    </div>
                    <a href="logout.php" class="flex items-center space-x-2 text-gray-600 hover:text-red-500 transition-colors duration-200" title="Logout"><i data-lucide="log-out" class="w-5 h-5"></i></a>
                </div>
            </header>
            
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50 p-6">
                <div class="mb-6 bg-white p-2 rounded-lg shadow-sm flex items-center space-x-2">
                    <button data-filter="ALL" class="filter-btn active flex-1 text-center font-semibold text-gray-600 px-4 py-2 rounded-md transition-all duration-200 hover:bg-gray-100">All</button>
                    <button data-filter="LIVE" class="filter-btn flex-1 text-center font-semibold text-gray-600 px-4 py-2 rounded-md transition-all duration-200 hover:bg-gray-100">🔴 Live</button>
                    <button data-filter="UPCOMING" class="filter-btn flex-1 text-center font-semibold text-gray-600 px-4 py-2 rounded-md transition-all duration-200 hover:bg-gray-100">Upcoming</button>
                    <button data-filter="COMPLETED" class="filter-btn flex-1 text-center font-semibold text-gray-600 px-4 py-2 rounded-md transition-all duration-200 hover:bg-gray-100">Completed</button>
                </div>

                <div id="schedule-container" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                       <div class="text-center py-10 col-span-full"><p class="text-gray-500">Loading matches...</p></div>
                </div>
            </main>
        </div>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();
            const scheduleContainer = document.getElementById('schedule-container');
            const filterButtons = document.querySelectorAll('.filter-btn');
            let allMatches = [];

            const getInitials = (name) => {
                if (!name) return '';
                const words = name.split(' ');
                if (words.length > 1) return (words[0][0] + (words[1][0] || '')).toUpperCase();
                return name.substring(0, 2).toUpperCase();
            };
            
            const renderMatches = (matchesToRender) => {
                scheduleContainer.innerHTML = '';
                if (matchesToRender.length === 0) {
                    scheduleContainer.innerHTML = `<div class="col-span-full text-center py-16 bg-white rounded-lg shadow-md"><i data-lucide="search-x" class="w-16 h-16 mx-auto text-gray-300"></i><h3 class="mt-4 text-xl font-semibold text-gray-700">No Matches Found</h3><p class="mt-1 text-gray-500">There are no matches with the selected filter.</p></div>`;
                    lucide.createIcons();
                    return;
                }

                matchesToRender.forEach(match => {
                    const date = new Date(match.datetime);
                    const formattedDate = date.toLocaleDateString('en-US', { weekday: 'long', month: 'long', day: 'numeric' });
                    const formattedTime = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });

                    let statusBadge = '';
                    let winnerIndicatorHTML = '';
                    let team1Class = '';
                    let team2Class = '';
                    let team1Indicator = '';
                    let team2Indicator = '';
                    
                    const winnerIcon = `<div class="h-6"><i data-lucide="crown" class="w-6 h-6 text-amber-400"></i></div>`;

                    if (match.status === 'COMPLETED') {
                        if (match.winning_team_name && match.winning_team_name !== 'DRAW') {
                            if (match.winning_team_name === match.team1_name) {
                                team1Indicator = winnerIcon;
                                team2Indicator = '<div class="h-6"></div>';
                                team2Class = 'opacity-60';
                                winnerIndicatorHTML = `<span class="font-semibold text-gray-700">🏆 ${match.winning_team_name} won</span>`;
                            } else {
                                team2Indicator = winnerIcon;
                                team1Indicator = '<div class="h-6"></div>';
                                team1Class = 'opacity-60';
                                winnerIndicatorHTML = `<span class="font-semibold text-gray-700">🏆 ${match.winning_team_name} won</span>`;
                            }
                        } else {
                           winnerIndicatorHTML = `<span class="font-semibold text-gray-500">Match Drawn</span>`; 
                           team1Indicator = '<div class="h-6"></div>';
                           team2Indicator = '<div class="h-6"></div>';
                        }
                        statusBadge = `<span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-green-100 text-green-800">Completed</span>`;
                    } else {
                        team1Indicator = '<div class="h-6"></div>';
                        team2Indicator = '<div class="h-6"></div>';
                        if (match.status === 'LIVE') statusBadge = `<span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-red-100 text-red-800 animate-pulse">🔴 Live</span>`;
                        else if (match.status === 'UPCOMING') statusBadge = `<span class="inline-flex items-center gap-x-1.5 py-1.5 px-3 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Upcoming</span>`;
                    }

                    const team1Initials = getInitials(match.team1_name);
                    const team2Initials = getInitials(match.team2_name);

                    const matchCard = `
                    <div class="flex flex-col bg-white rounded-2xl shadow-md hover:shadow-xl hover:-translate-y-1 transition-all duration-300 ease-in-out">
                        <div class="p-6 flex justify-around items-start text-center">
                            <div class="flex flex-col items-center space-y-2 w-2/5 transition-opacity duration-300 ${team1Class}">
                                ${team1Indicator}
                                <img class="w-16 h-16 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center font-bold text-2xl" src="https://placehold.co/100x100/eef2ff/4f46e5?text=${team1Initials}&font=inter" alt="${match.team1_name} Logo">
                                <h4 class="text-lg font-bold text-gray-800 truncate w-full" title="${match.team1_name}">${match.team1_name}</h4>
                            </div>
                            <span class="text-2xl font-light text-gray-400 pt-6">vs</span>
                            <div class="flex flex-col items-center space-y-2 w-2/5 transition-opacity duration-300 ${team2Class}">
                                ${team2Indicator}
                                <img class="w-16 h-16 rounded-full bg-gray-100 text-gray-600 flex items-center justify-center font-bold text-2xl" src="https://placehold.co/100x100/f3f4f6/4b5563?text=${team2Initials}&font=inter" alt="${match.team2_name} Logo">
                                <h4 class="text-lg font-bold text-gray-800 truncate w-full" title="${match.team2_name}">${match.team2_name}</h4>
                            </div>
                        </div>
                        <div class="border-t border-gray-200 p-5 space-y-3 text-sm text-gray-600">
                            <div class="flex items-center"><i data-lucide="trophy" class="w-4 h-4 mr-3 text-indigo-500"></i><span class="font-semibold text-indigo-700 bg-indigo-50 px-2 py-1 rounded">${match.sport_name}</span></div>
                            <div class="flex items-center"><i data-lucide="map-pin" class="w-4 h-4 mr-3 text-indigo-500"></i><span>${match.venue}</span></div>
                            <div class="flex items-center"><i data-lucide="calendar" class="w-4 h-4 mr-3 text-indigo-500"></i><span>${formattedDate} at ${formattedTime}</span></div>
                        </div>
                        <div class="border-t border-gray-200 mt-auto p-4 flex justify-between items-center bg-gray-50 rounded-b-2xl">
                            ${statusBadge}
                            ${winnerIndicatorHTML}
                        </div>
                    </div>`;
                    scheduleContainer.innerHTML += matchCard;
                });
                lucide.createIcons();
            };

            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');
                    const filter = button.dataset.filter;
                    if (filter === 'ALL') {
                        renderMatches(allMatches);
                    } else {
                        const filteredMatches = allMatches.filter(match => match.status === filter);
                        renderMatches(filteredMatches);
                    }
                });
            });

            try {
                const response = await fetch('api.php?action=getAllMatches');
                const result = await response.json();
                if (result.status === 'success') {
                    allMatches = result.data;
                    renderMatches(allMatches);
                } else {
                    scheduleContainer.innerHTML = `<p class="text-red-500 col-span-full">Error: ${result.message}</p>`;
                }
            } catch (error) {
                console.error('Fetch Error:', error);
                scheduleContainer.innerHTML = `<p class="text-red-500 col-span-full">Error fetching schedule. Please check the console and try again later.</p>`;
            }
        });
    </script>
</body>
</html>