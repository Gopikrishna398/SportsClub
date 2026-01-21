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
    <title>SportsWatch Hub - My Matches</title>
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
                <a href="./volunteerDashboard.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <a href="./scheduleMatch.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="calendar-plus" class="w-5 h-5 mr-3"></i>Schedule Match</a>
                <a href="./updateScores.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="pencil-line" class="w-5 h-5 mr-3"></i>Update Scores</a>
                <a href="./matchReports.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="file-text" class="w-5 h-5 mr-3"></i>Match Reports</a>
                <a href="#" class="sidebar-link flex items-center mt-2 px-4 py-3 rounded-lg active"><i data-lucide="clipboard-check" class="w-5 h-5 mr-3"></i>My Matches</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">My Assigned Matches</h2>
                <div class="flex items-center space-x-4">
                    <button class="text-gray-500 hover:text-gray-700"><i data-lucide="bell" class="w-6 h-6"></i></button>
                    <div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/FB923C/4A5568?text=V" alt="Volunteer avatar"><span class="font-semibold text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-orange-100 text-orange-600 font-bold px-2 py-1 rounded-md">VOLUNTEER</span></div>
                    <a href="logout.php" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a>
                </div>
            </header>

            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="match-count" class="text-lg font-bold text-gray-800">All Matches (0)</h3>
                        <div class="flex space-x-2">
                            <select id="sport-filter" class="bg-white border border-gray-300 text-gray-700 py-2 px-3 rounded-lg focus:outline-none focus:border-indigo-500 text-sm">
                                <option value="all">All Sports</option>
                            </select>
                            <select id="status-filter" class="bg-white border border-gray-300 text-gray-700 py-2 px-3 rounded-lg focus:outline-none focus:border-indigo-500 text-sm">
                                <option value="all">All Statuses</option>
                                <option value="LIVE">Live</option>
                                <option value="UPCOMING">Upcoming</option>
                                <option value="COMPLETED">Completed</option>
                            </select>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left"><thead class="text-xs text-gray-700 uppercase bg-gray-50"><tr><th class="px-6 py-3">Match</th><th class="px-6 py-3">Date & Time</th><th class="px-6 py-3">Venue</th><th class="px-6 py-3 text-center">Status</th><th class="px-6 py-3 text-center">Actions</th></tr></thead>
                            <tbody id="matches-table-body"></tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableBody = document.getElementById('matches-table-body');
            const matchCountElement = document.getElementById('match-count');
            const sportFilter = document.getElementById('sport-filter');
            const statusFilter = document.getElementById('status-filter');
            let allMatches = [];

            async function fetchMatches() {
                 try {
                    const response = await fetch('api.php?action=getVolunteerMatches');
                    const result = await response.json();
                    if (result.status === 'success') {
                        allMatches = result.data;
                        
                        if(sportFilter.options.length <= 1) {
                            const sports = [...new Set(allMatches.map(m => m.sport_name))];
                            sports.forEach(sport => {
                                const option = document.createElement('option');
                                option.value = sport;
                                option.textContent = sport;
                                sportFilter.appendChild(option);
                            });
                        }
                        
                        renderTable();
                    } else {
                        tableBody.innerHTML = `<tr><td colspan="5" class="text-center py-10 text-red-500">${result.message}</td></tr>`;
                    }
                } catch (error) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center py-10 text-red-500">Failed to load match data.</td></tr>`;
                }
            }

            function renderTable() {
                const selectedSport = sportFilter.value;
                const selectedStatus = statusFilter.value;

                const filteredMatches = allMatches.filter(match => {
                    const sportMatch = selectedSport === 'all' || match.sport_name === selectedSport;
                    const statusMatch = selectedStatus === 'all' || match.status === selectedStatus;
                    return sportMatch && statusMatch;
                });

                tableBody.innerHTML = '';
                if (filteredMatches.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="5" class="text-center py-10 text-gray-500">No matches found for the selected filters.</td></tr>`;
                } else {
                    filteredMatches.forEach(match => {
                        const row = document.createElement('tr');
                        row.className = 'bg-white border-b';
                        const date = new Date(match.datetime);
                        const formattedDate = date.toLocaleDateString('en-CA');
                        const formattedTime = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: false });
                        let statusBadge = '';
                        let actionButton = '<span class="text-gray-400">N/A</span>';

                        switch(match.status) {
                            case 'LIVE':
                                statusBadge = `<span class="text-xs font-bold text-red-500 bg-red-100 px-3 py-1.5 rounded-full">LIVE</span>`;
                                actionButton = `<a href="./updateScores.php?matchId=${match.id}" class="font-medium text-indigo-600 hover:text-indigo-800">Update Score</a>`;
                                break;
                            case 'UPCOMING':
                                statusBadge = `<span class="text-xs font-bold text-blue-500 bg-blue-100 px-3 py-1.5 rounded-full">UPCOMING</span>`;
                                actionButton = `<button onclick="handleStartMatch(${match.id})" class="font-medium text-green-600 hover:text-green-800">Start Match</button>`;
                                break;
                            case 'COMPLETED':
                                statusBadge = `<span class="text-xs font-bold text-green-500 bg-green-100 px-3 py-1.5 rounded-full">COMPLETED</span>`;
                                actionButton = `<a href="./matchReports.php?matchId=${match.id}" class="font-medium text-indigo-600 hover:text-indigo-800">View Report</a>`;
                                break;
                        }
                        row.innerHTML = `
                            <td class="px-6 py-4 font-medium text-gray-900">${match.team1_name} vs ${match.team2_name}</td>
                            <td class="px-6 py-4">${formattedDate} at ${formattedTime}</td>
                            <td class="px-6 py-4">${match.venue}</td>
                            <td class="px-6 py-4 text-center">${statusBadge}</td>
                            <td class="px-6 py-4 text-center">${actionButton}</td>
                        `;
                        tableBody.appendChild(row);
                    });
                }
                matchCountElement.textContent = `All Matches (${filteredMatches.length})`;
            }

            window.handleStartMatch = async (matchId) => {
                if (!confirm('Are you sure you want to start this match now?')) return;

                const formData = new FormData();
                formData.append('match_id', matchId);

                try {
                    const response = await fetch('api.php?action=startMatch', {
                        method: 'POST',
                        body: formData
                    });
                    const result = await response.json();
                    alert(result.message);
                    if (result.status === 'success') {
                        fetchMatches(); // Refresh the table to show the new status
                    }
                } catch (error) {
                    alert('An error occurred while trying to start the match.');
                }
            };

            sportFilter.addEventListener('change', renderTable);
            statusFilter.addEventListener('change', renderTable);
            
            fetchMatches(); // Initial Load
            lucide.createIcons();
        });
    </script>
</body>
</html>