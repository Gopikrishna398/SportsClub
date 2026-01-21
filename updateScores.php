<?php
require_once 'session_auth.php';
check_auth(['volunteer']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Update Scores</title>
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
                <a href="./volunteerDashboard.php" class="sidebar-link flex items-center px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <a href="./scheduleMatch.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="calendar-plus" class="w-5 h-5 mr-3"></i>Schedule Match</a>
                <a href="#" class="sidebar-link flex items-center mt-2 px-4 py-3 rounded-lg active"><i data-lucide="pencil-line" class="w-5 h-5 mr-3"></i>Update Scores</a>
                <a href="./matchReports.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="file-text" class="w-5 h-5 mr-3"></i>Match Reports</a>
                <a href="./myMatches.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="clipboard-check" class="w-5 h-5 mr-3"></i>My Matches</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
             <header class="bg-white shadow-sm p-4 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Update Match Score</h2>
                <div class="flex items-center space-x-4">
                    <button class="text-gray-500 hover:text-gray-700"><i data-lucide="bell" class="w-6 h-6"></i></button>
                    <div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/FB923C/4A5568?text=V" alt="Volunteer avatar"><span class="font-semibold text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-orange-100 text-orange-600 font-bold px-2 py-1 rounded-md">VOLUNTEER</span></div>
                    <a href="logout.php" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="max-w-4xl mx-auto">
                    <div id="update-score-container" class="bg-white rounded-xl shadow-sm p-8">
                        <!-- Form content will be dynamically generated here -->
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();
            const container = document.getElementById('update-score-container');
            let liveMatches = [];

            function renderForm(matchId) {
                const match = liveMatches.find(m => m.id == matchId);
                if (!match) {
                    container.innerHTML = `<p class="text-center text-gray-600">You have no live matches to update currently.</p>`;
                    return;
                }
                
                let scoreInputsHtml = '';
                if (match.sport_name === 'Cricket') {
                    scoreInputsHtml = `
                        <div class="grid grid-cols-2 gap-8 items-start">
                            <div class="text-center space-y-2">
                                <label class="text-lg font-bold text-gray-800">${match.team1_name}</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="score1" value="${match.score1}" class="text-4xl font-bold text-center w-full bg-gray-100 rounded-lg border-gray-300" placeholder="Runs">
                                    <span class="text-2xl font-light text-gray-400">/</span>
                                    <input type="number" name="wickets1" value="${match.wickets1 || ''}" class="text-4xl font-bold text-center w-2/3 bg-gray-100 rounded-lg border-gray-300" placeholder="W">
                                </div>
                            </div>
                            <div class="text-center space-y-2">
                                <label class="text-lg font-bold text-gray-800">${match.team2_name}</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="score2" value="${match.score2}" class="text-4xl font-bold text-center w-full bg-gray-100 rounded-lg border-gray-300" placeholder="Runs">
                                    <span class="text-2xl font-light text-gray-400">/</span>
                                    <input type="number" name="wickets2" value="${match.wickets2 || ''}" class="text-4xl font-bold text-center w-2/3 bg-gray-100 rounded-lg border-gray-300" placeholder="W">
                                </div>
                            </div>
                        </div>
                    `;
                } else {
                    scoreInputsHtml = `
                        <div class="grid grid-cols-2 gap-8 items-center">
                            <div class="text-center"><label for="score1" class="text-lg font-bold text-gray-800">${match.team1_name}</label><input type="number" id="score1" name="score1" value="${match.score1}" class="mt-2 text-5xl font-bold text-center w-full bg-gray-100 rounded-lg border-gray-300"></div>
                            <div class="text-center"><label for="score2" class="text-lg font-bold text-gray-800">${match.team2_name}</label><input type="number" id="score2" name="score2" value="${match.score2}" class="mt-2 text-5xl font-bold text-center w-full bg-gray-100 rounded-lg border-gray-300"></div>
                        </div>
                    `;
                }

                container.innerHTML = `
                    <form id="score-form">
                        <input type="hidden" name="match_id" value="${match.id}">
                        <div class="mb-6">
                            <label for="match-select" class="block text-sm font-medium text-gray-700 mb-1">Select Live Match</label>
                            <select id="match-select" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-indigo-500 focus:border-indigo-500 block p-2.5">
                                ${liveMatches.map(m => `<option value="${m.id}" ${m.id == matchId ? 'selected' : ''}>${m.team1_name} vs ${m.team2_name} (${m.sport_name})</option>`).join('')}
                            </select>
                        </div>
                        <div class="border-t pt-6">
                            ${scoreInputsHtml}
                            <div class="mt-6"><label for="commentary" class="block text-sm font-medium text-gray-700 mb-1">Match Status / Commentary</label><input type="text" id="commentary" name="commentary" value="${match.commentary || ''}" class="w-full bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg p-2.5"></div>
                             <div class="mt-6 flex justify-end items-center gap-4">
                                <button type="button" id="finish-match-btn" class="bg-green-600 text-white font-semibold py-3 px-6 rounded-lg hover:bg-green-700 transition-colors flex items-center"><i data-lucide="flag" class="w-5 h-5 mr-2"></i>Finish Match</button>
                                <button type="submit" id="update-score-btn" class="bg-red-500 text-white font-semibold py-3 px-6 rounded-lg hover:bg-red-600 transition-colors flex items-center"><i data-lucide="save" class="w-5 h-5 mr-2"></i>Update Score</button>
                            </div>
                        </div>
                    </form>
                `;
                lucide.createIcons();
                attachFormListeners();
            }

            function attachFormListeners() {
                const form = document.getElementById('score-form');
                const matchSelect = document.getElementById('match-select');
                
                matchSelect.addEventListener('change', () => renderForm(matchSelect.value));

                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    submitScoreUpdate('LIVE');
                });

                document.getElementById('finish-match-btn').addEventListener('click', () => {
                    if (confirm('Are you sure you want to finish this match? This action cannot be undone.')) {
                        submitScoreUpdate('COMPLETED');
                    }
                });
            }

            async function submitScoreUpdate(status) {
                const form = document.getElementById('score-form');
                const formData = new FormData(form);
                formData.append('status', status);

                try {
                    const response = await fetch('api.php?action=updateMatchScore', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.status === 'success') {
                        if (status === 'COMPLETED') {
                            alert('Match has been marked as complete!');
                            window.location.href = './volunteerDashboard.php';
                        } else {
                            alert('Score updated successfully!');
                            const matchId = formData.get('match_id');
                            const updatedResponse = await fetch('api.php?action=getVolunteerLiveMatches');
                            liveMatches = (await updatedResponse.json()).data;
                            renderForm(matchId);
                        }
                    } else {
                        alert(`Error: ${result.message}`);
                    }
                } catch (error) {
                    alert('An error occurred while updating the score.');
                }
            }

            // --- Initial Load ---
            try {
                const response = await fetch('api.php?action=getVolunteerLiveMatches');
                const result = await response.json();
                if (result.status === 'success') {
                    liveMatches = result.data;
                    const urlParams = new URLSearchParams(window.location.search);
                    const initialMatchId = urlParams.get('matchId') || (liveMatches[0] ? liveMatches[0].id : null);
                    renderForm(initialMatchId);
                } else {
                    container.innerHTML = `<p class="text-red-500">${result.message}</p>`;
                }
            } catch (error) {
                container.innerHTML = `<p class="text-red-500">Failed to load live match data.</p>`;
            }
        });
    </script>
</body>
</html>
