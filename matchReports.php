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
    <title>SportsWatch Hub - Match Reports</title>
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
                <a href="#" class="sidebar-link flex items-center mt-2 px-4 py-3 rounded-lg active"><i data-lucide="file-text" class="w-5 h-5 mr-3"></i>Match Reports</a>
                <a href="./myMatches.php" class="sidebar-link flex items-center mt-2 px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg"><i data-lucide="clipboard-check" class="w-5 h-5 mr-3"></i>My Matches</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
             <header class="bg-white shadow-sm p-4 flex justify-between items-center">
                <h2 class="text-2xl font-bold text-gray-800">Match Reports</h2>
                <div class="flex items-center space-x-4">
                    <button class="text-gray-500 hover:text-gray-700"><i data-lucide="bell" class="w-6 h-6"></i></button>
                    <div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/FB923C/4A5568?text=V" alt="Volunteer avatar"><span class="font-semibold text-gray-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-orange-100 text-orange-600 font-bold px-2 py-1 rounded-md">VOLUNTEER</span></div>
                    <a href="logout.php" class="flex items-center space-x-2 text-gray-600 hover:text-gray-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a>
                </div>
            </header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 id="pending-reports-title" class="text-lg font-bold text-gray-800">Pending Reports (0)</h3>
                        <button id="open-modal-btn" class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700 flex items-center"><i data-lucide="plus" class="w-5 h-5 mr-2"></i>Submit New Report</button>
                    </div>
                    <div id="pending-reports-list" class="space-y-4"></div>
                </div>
                 <div class="mt-6 bg-white rounded-xl shadow-sm p-6">
                    <h3 id="submitted-reports-title" class="text-lg font-bold text-gray-800 mb-4">Submitted Reports (0)</h3>
                     <div id="submitted-reports-list" class="space-y-4"></div>
                </div>
            </main>
        </div>
    </div>

    <!-- Submit Report Modal -->
    <div id="report-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-lg">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">Submit Match Report</h3>
                <button id="close-modal-btn" class="text-gray-500 hover:text-gray-800"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <form id="new-report-form">
                <div class="space-y-4">
                    <div>
                        <label for="report-match-select" class="block text-sm font-medium text-gray-700">Select Match</label>
                        <select id="report-match-select" name="match_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" required></select>
                    </div>
                    <div>
                        <label for="report-summary" class="block text-sm font-medium text-gray-700">Match Summary</label>
                        <textarea id="report-summary" name="summary" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm" placeholder="e.g., A close game with a last-minute winner..." required></textarea>
                    </div>
                </div>
                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" id="cancel-btn" class="bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700">Submit Report</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();
            const pendingList = document.getElementById('pending-reports-list');
            const submittedList = document.getElementById('submitted-reports-list');
            const pendingTitle = document.getElementById('pending-reports-title');
            const submittedTitle = document.getElementById('submitted-reports-title');
            const modal = document.getElementById('report-modal');
            const openModalBtn = document.getElementById('open-modal-btn');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const cancelBtn = document.getElementById('cancel-btn');
            const reportForm = document.getElementById('new-report-form');
            const matchSelect = document.getElementById('report-match-select');
            let pendingReportsData = [];

            async function fetchData() {
                try {
                    const response = await fetch('api.php?action=getVolunteerReportData');
                    const result = await response.json();
                    if (result.status === 'success') {
                        const { pending, submitted } = result.data;
                        pendingReportsData = pending;
                        renderLists(pending, submitted);
                    } else {
                        pendingList.innerHTML = `<p class="text-red-500">${result.message}</p>`;
                    }
                } catch(e) {
                    pendingList.innerHTML = `<p class="text-red-500">Failed to load report data.</p>`;
                }
            }

            function renderLists(pending, submitted) {
                pendingTitle.textContent = `Pending Reports (${pending.length})`;
                submittedTitle.textContent = `Submitted Reports (${submitted.length})`;

                pendingList.innerHTML = pending.length > 0 ? pending.map(match => `
                    <div class="bg-gray-50 rounded-lg p-4 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-gray-800">${match.team1_name} vs ${match.team2_name}</p>
                            <p class="text-sm text-gray-500">${match.sport_name} - Completed on ${new Date(match.datetime).toLocaleDateString('en-CA')}</p>
                        </div>
                        <button class="font-medium text-indigo-600 hover:text-indigo-800" onclick="openReportModal(${match.id})">Submit Report</button>
                    </div>
                `).join('') : '<p class="text-center text-gray-500 py-4">No pending reports.</p>';

                submittedList.innerHTML = submitted.length > 0 ? submitted.map(match => `
                    <div class="bg-gray-50 rounded-lg p-4 flex justify-between items-center">
                        <div>
                            <p class="font-bold text-gray-800">${match.team1_name} vs ${match.team2_name}</p>
                            <p class="text-sm text-gray-500">${match.sport_name} - Submitted on ${new Date(match.datetime).toLocaleDateString('en-CA')}</p>
                        </div>
                        <span class="text-sm font-medium text-gray-500">Submitted</span>
                    </div>
                `).join('') : '<p class="text-center text-gray-500 py-4">No submitted reports yet.</p>';
            }

            window.openReportModal = (matchId = null) => {
                matchSelect.innerHTML = pendingReportsData.map(m => `<option value="${m.id}">${m.team1_name} vs ${m.team2_name}</option>`).join('');
                
                if (matchId) matchSelect.value = matchId;

                if (pendingReportsData.length === 0) {
                    alert("There are no pending reports to submit.");
                    return;
                }
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                reportForm.reset();
            }

            openModalBtn.addEventListener('click', () => openReportModal());
            closeModalBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            reportForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const formData = new FormData(reportForm);
                try {
                    const response = await fetch('api.php?action=submitMatchReport', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.status === 'success') {
                        alert(result.message);
                        fetchData(); // Refresh the lists
                        closeModal();
                    } else {
                        alert(`Error: ${result.message}`);
                    }
                } catch (error) {
                    alert('An error occurred while submitting the report.');
                }
            });

            fetchData();
        });
    </script>
</body>
</html>
