<?php
require_once 'session_auth.php';
check_auth(['student']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Match Predictions</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        .sidebar-link.active { background-color: #eef2ff; color: #4f46e5; font-weight: 600; border-left: 3px solid #4f46e5; padding-left: calc(1rem - 3px); }
        .sidebar-link:not(.active):hover { background-color: #f8fafc; }
        .prediction-card-button {
            transition: all 0.2s ease-in-out;
        }
        .prediction-card-button:hover {
            transform: translateY(-2px);
            border-color: #4f46e5;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="text-gray-800">
    <div class="flex h-screen bg-gray-100">
        <aside class="w-64 bg-white shadow-lg flex-shrink-0 hidden lg:flex flex-col">
            <div class="p-4 flex items-center space-x-3 border-b border-gray-200">
                <div class="bg-indigo-600 p-2 rounded-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v11.494m-9-5.747h18"></path><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg>
                </div>
                <h1 class="text-xl font-bold text-gray-800">SportsWatch</h1>
            </div>
            <nav id="sidebar-nav" class="mt-4 flex-1 px-2">
                <a href="./dashboard.php" class="sidebar-link flex items-center px-4 py-2.5 text-gray-600 rounded-lg"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <a href="./schedule.php" class="sidebar-link flex items-center mt-1 px-4 py-2.5 text-gray-600 rounded-lg"><i data-lucide="calendar-days" class="w-5 h-5 mr-3"></i>Match Schedule</a>
                <a href="./points.php" class="sidebar-link flex items-center mt-1 px-4 py-2.5 text-gray-600 rounded-lg"><i data-lucide="table" class="w-5 h-5 mr-3"></i>Points Table</a>
                <a href="./liveScore.php" class="sidebar-link flex items-center mt-1 px-4 py-2.5 text-gray-600 rounded-lg relative"><i data-lucide="radio-tower" class="w-5 h-5 mr-3"></i>Live Scores<span id="live-nav-badge" class="ml-auto text-xs bg-red-500 text-white font-semibold px-2 py-0.5 rounded-full hidden">LIVE</span></a>
                <a href="#" class="sidebar-link flex items-center mt-1 px-4 py-2.5 rounded-lg active"><i data-lucide="lightbulb" class="w-5 h-5 mr-3"></i>Predictions</a>
                <a href="./stats.php" class="sidebar-link flex items-center mt-1 px-4 py-2.5 text-gray-600 rounded-lg"><i data-lucide="bar-chart-3" class="w-5 h-5 mr-3"></i>Team Stats</a>
                <a href="./myPredictions.php" class="sidebar-link flex items-center mt-1 px-4 py-2.5 text-gray-600 rounded-lg"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>My Predictions</a>
            </nav>
            <div class="p-4 border-t border-gray-200">
                 <a href="logout.php" class="flex items-center space-x-2 text-gray-600 hover:text-indigo-600"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a>
            </div>
        </aside>

        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center z-10">
                <h2 class="text-2xl font-bold text-gray-800">Match Predictions</h2>
                <div class="flex items-center space-x-4">
                     <button class="text-gray-500 hover:text-gray-700 relative">
                        <i data-lucide="bell" class="w-6 h-6"></i>
                        <span class="absolute -top-1 -right-1 flex h-3 w-3"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span></span>
                    </button>
                    <div class="flex items-center space-x-2">
                        <img class="w-9 h-9 rounded-full" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['username']); ?>&background=eef2ff&color=4f46e5&font-size=0.5" alt="User avatar">
                        <span class="font-semibold text-gray-700 hidden sm:block"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                        <span class="text-xs bg-indigo-100 text-indigo-600 font-bold px-2 py-1 rounded-md">STUDENT</span>
                    </div>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-4 md:p-6">
                 <div class="max-w-7xl mx-auto space-y-8">
                    <div>
                        <div class="flex items-center mb-4">
                            <i data-lucide="zap" class="w-6 h-6 text-green-500 mr-3"></i>
                            <h2 class="text-xl font-bold text-gray-800">Open for Predictions</h2>
                        </div>
                        <div id="upcoming-predictions-container" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                            </div>
                    </div>
                    
                    <hr class="border-gray-200" />
                    
                    <div>
                        <div class="flex items-center mb-4">
                            <i data-lucide="history" class="w-6 h-6 text-gray-500 mr-3"></i>
                            <h2 class="text-xl font-bold text-gray-800">Past Predictions & Deadlines</h2>
                        </div>
                        <div id="completed-predictions-container" class="grid grid-cols-1 xl:grid-cols-2 gap-6">
                            </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        // --- Helper function to create a message card ---
        function createMessageCard(container, message, iconName) {
            container.innerHTML = `<div class="col-span-1 xl:col-span-2 bg-white rounded-lg p-8 text-center text-gray-500 shadow-sm">
                <i data-lucide="${iconName}" class="w-12 h-12 mx-auto text-gray-400 mb-4"></i>
                <p>${message}</p>
            </div>`;
        }
        
        // --- Helper function to create a prediction card ---
        function createPredictionCard(match) {
            const date = new Date(match.datetime);
            const formattedDate = date.toLocaleDateString('en-GB', { day: '2-digit', month: 'long', year: 'numeric' });
            const formattedTime = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true });

            const team1Initial = match.team1_name.charAt(0);
            const team2Initial = match.team2_name.charAt(0);
            
            let contentHtml = '';
            
            if (match.status !== 'UPCOMING') {
                contentHtml = `
                    <div class="flex flex-col md:flex-row items-center justify-around space-y-4 md:space-y-0 md:space-x-4 opacity-70">
                        <div class="flex-1 w-full text-center p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <img src="https://placehold.co/80x80/f1f5f9/475569?text=${team1Initial}" class="mx-auto h-16 w-16 rounded-full mb-3 shadow-md" alt="${match.team1_name} logo"/>
                            <span class="block font-bold text-gray-700 text-lg">${match.team1_name}</span>
                        </div>
                        <div class="text-2xl font-bold text-gray-400">VS</div>
                        <div class="flex-1 w-full text-center p-4 border border-gray-200 rounded-lg bg-gray-50">
                            <img src="https://placehold.co/80x80/f1f5f9/475569?text=${team2Initial}" class="mx-auto h-16 w-16 rounded-full mb-3 shadow-md" alt="${match.team2_name} logo"/>
                            <span class="block font-bold text-gray-700 text-lg">${match.team2_name}</span>
                        </div>
                    </div>
                    <p class="text-center text-orange-600 font-semibold mt-4">The prediction window for this match has closed.</p>
                `;
            } else if (match.predicted_winner_team_id) {
                const isTeam1Predicted = match.predicted_winner_team_id == match.team1_id;
                contentHtml = `
                    <div class="flex flex-col md:flex-row items-center justify-around space-y-4 md:space-y-0 md:space-x-4">
                        <div class="flex-1 w-full text-center p-4 border-2 rounded-xl transition-all ${isTeam1Predicted ? 'border-green-500 bg-green-50' : 'border-gray-200 opacity-60'}">
                            <img src="https://placehold.co/80x80/f1f5f9/475569?text=${team1Initial}" class="mx-auto h-16 w-16 rounded-full mb-3" alt="${match.team1_name} logo"/>
                            <span class="block font-bold text-gray-800 text-lg">${match.team1_name}</span>
                            ${isTeam1Predicted ? `<span class="flex items-center justify-center text-green-600 font-semibold mt-2"><i data-lucide="check-circle-2" class="w-5 h-5 mr-1.5"></i>Your Prediction</span>` : ''}
                        </div>
                        <div class="text-2xl font-bold text-gray-400">VS</div>
                        <div class="flex-1 w-full text-center p-4 border-2 rounded-xl transition-all ${!isTeam1Predicted ? 'border-green-500 bg-green-50' : 'border-gray-200 opacity-60'}">
                            <img src="https://placehold.co/80x80/f1f5f9/475569?text=${team2Initial}" class="mx-auto h-16 w-16 rounded-full mb-3" alt="${match.team2_name} logo"/>
                            <span class="block font-bold text-gray-800 text-lg">${match.team2_name}</span>
                            ${!isTeam1Predicted ? `<span class="flex items-center justify-center text-green-600 font-semibold mt-2"><i data-lucide="check-circle-2" class="w-5 h-5 mr-1.5"></i>Your Prediction</span>` : ''}
                        </div>
                    </div>
                `;
            } else {
                contentHtml = `
                    <div class="flex flex-col md:flex-row items-center justify-around space-y-4 md:space-y-0 md:space-x-4">
                        <button class="prediction-card-button flex-1 w-full text-center p-4 border-2 border-dashed border-gray-300 rounded-xl" onclick="handlePrediction(${match.id}, ${match.team1_id})">
                            <img src="https://placehold.co/80x80/f1f5f9/475569?text=${team1Initial}" class="mx-auto h-16 w-16 rounded-full mb-3" alt="${match.team1_name} logo"/>
                            <span class="block font-bold text-gray-800 text-lg">${match.team1_name}</span>
                            <span class="text-indigo-600 font-semibold mt-2 inline-flex items-center"><i data-lucide="mouse-pointer-click" class="w-4 h-4 mr-2"></i>Predict to Win</span>
                        </button>
                        <div class="text-2xl font-bold text-gray-400">VS</div>
                        <button class="prediction-card-button flex-1 w-full text-center p-4 border-2 border-dashed border-gray-300 rounded-xl" onclick="handlePrediction(${match.id}, ${match.team2_id})">
                            <img src="https://placehold.co/80x80/f1f5f9/475569?text=${team2Initial}" class="mx-auto h-16 w-16 rounded-full mb-3" alt="${match.team2_name} logo"/>
                            <span class="block font-bold text-gray-800 text-lg">${match.team2_name}</span>
                            <span class="text-indigo-600 font-semibold mt-2 inline-flex items-center"><i data-lucide="mouse-pointer-click" class="w-4 h-4 mr-2"></i>Predict to Win</span>
                        </button>
                    </div>
                `;
            }

            return `
                <div class="bg-white rounded-xl shadow-sm transition-all duration-300 hover:shadow-lg flex flex-col">
                    <div class="p-4 border-b border-gray-200">
                        <div class="flex flex-wrap justify-between items-center gap-2">
                            <div class="flex items-center space-x-3">
                                <span class="text-sm font-semibold bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full">${match.sport_name}</span>
                                <div class="text-sm text-gray-500 hidden md:flex items-center space-x-3">
                                    <span><i data-lucide="map-pin" class="inline w-4 h-4 mr-1"></i>${match.venue}</span>
                                    <span><i data-lucide="calendar" class="inline w-4 h-4 mr-1"></i>${formattedDate} at ${formattedTime}</span>
                                </div>
                            </div>
                            ${match.status !== 'UPCOMING' 
                                ? `<span class="text-xs font-bold text-red-600 bg-red-100 px-3 py-1.5 rounded-full">Deadline Passed</span>` 
                                : `<span class="text-xs font-bold text-green-600 bg-green-100 px-3 py-1.5 rounded-full">Open</span>`
                            }
                        </div>
                    </div>
                    <div class="p-5 flex-grow flex items-center">
                        <div class="w-full">${contentHtml}</div>
                    </div>
                </div>
            `;
        }

        document.addEventListener('DOMContentLoaded', () => {
            const upcomingContainer = document.getElementById('upcoming-predictions-container');
            const completedContainer = document.getElementById('completed-predictions-container');

            lucide.createIcons(); // Initial icon render

            async function renderPredictions() {
                try {
                    const response = await fetch('api.php?action=getPredictionMatches');
                    const result = await response.json();

                    if (result.status !== 'success') {
                        createMessageCard(upcomingContainer, result.message, 'alert-triangle');
                        completedContainer.innerHTML = ''; // Clear other container
                        lucide.createIcons();
                        return;
                    }
                    
                    const matches = result.data;
                    const upcomingMatches = matches.filter(m => m.status === 'UPCOMING');
                    const completedMatches = matches.filter(m => m.status !== 'UPCOMING');

                    // Render Upcoming Matches
                    upcomingContainer.innerHTML = '';
                    if (upcomingMatches.length > 0) {
                        upcomingMatches.forEach(match => {
                            upcomingContainer.innerHTML += createPredictionCard(match);
                        });
                    } else {
                        createMessageCard(upcomingContainer, 'No new matches are available for prediction right now. Check back later!', 'calendar-check');
                    }
                    
                    // Render Completed Matches
                    completedContainer.innerHTML = '';
                    if (completedMatches.length > 0) {
                        completedMatches.forEach(match => {
                            completedContainer.innerHTML += createPredictionCard(match);
                        });
                    } else {
                        createMessageCard(completedContainer, 'You have no past predictions or matches with closed deadlines.', 'archive');
                    }

                    lucide.createIcons();
                } catch (error) {
                    console.error("Error fetching predictions:", error);
                    createMessageCard(upcomingContainer, 'Error loading prediction data. Please try refreshing the page.', 'wifi-off');
                }
            }

            window.handlePrediction = async (matchId, predictedWinnerId) => {
                const mainContent = document.querySelector('main');
                mainContent.style.opacity = '0.5';
                mainContent.style.pointerEvents = 'none';

                const formData = new FormData();
                formData.append('match_id', matchId);
                formData.append('predicted_winner_id', predictedWinnerId);

                try {
                    const response = await fetch('api.php?action=makePrediction', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    if (result.status === 'success') {
                        await renderPredictions(); 
                    } else {
                        alert(`Error: ${result.message}`);
                    }
                } catch (error) {
                    alert('An error occurred while saving your prediction.');
                } finally {
                    mainContent.style.opacity = '1';
                    mainContent.style.pointerEvents = 'auto';
                }
            };

            renderPredictions();
        });
    </script>
</body>
</html>