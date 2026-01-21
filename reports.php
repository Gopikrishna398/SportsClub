<?php
// --- Security Gatekeeper ---
require_once 'session_auth.php';
// This page is only for admins.
check_auth(['admin']);
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
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-link.active { background-color: #f1f5f9; color: #1e293b; font-weight: 600; border-left: 3px solid #4f46e5; }
    </style>
</head>
<body class="bg-slate-50">
    <div class="flex h-screen">
        <!-- Admin Sidebar -->
        <aside class="w-64 bg-white shadow-sm flex-shrink-0 hidden lg:flex flex-col border-r border-slate-200">
            <div class="p-6 flex items-center space-x-3"><div class="bg-indigo-600 p-2 rounded-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg></div><h1 class="text-xl font-bold text-slate-800">SportsWatch Hub</h1></div>
            <nav class="mt-6 flex-1 px-4">
                <a href="./adminDashboard.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <a href="./analytics.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="bar-chart-2" class="w-5 h-5 mr-3"></i>Analytics</a>
                <a href="./createMatch.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="plus-circle" class="w-5 h-5 mr-3"></i>Create Match</a>
                <a href="./manageTeams.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="users" class="w-5 h-5 mr-3"></i>Manage Teams</a>
                <a href="./userManagement.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="user-cog" class="w-5 h-5 mr-3"></i>User Management</a>
                <a href="./manageVolunteers.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>Manage Volunteers</a>
                <a href="./systemSettings.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="settings" class="w-5 h-5 mr-3"></i>System Settings</a>
                <a href="#" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg active"><i data-lucide="file-text" class="w-5 h-5 mr-3"></i>Reports</a>
            </nav>
        </aside>
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b border-slate-200"><h2 class="text-2xl font-bold text-slate-800">Match Reports</h2><div class="flex items-center space-x-4"><button class="text-slate-500 hover:text-slate-700"><i data-lucide="bell" class="w-6 h-6"></i></button><div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/DC2626/FFFFFF?text=A" alt="Admin avatar"><span class="font-semibold text-slate-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-red-500 text-white font-bold px-2 py-1 rounded-md">ADMIN</span></div><a href="logout.php" class="flex items-center space-x-2 text-slate-600 hover:text-slate-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a></div></header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-6">
                <div class="bg-white border border-slate-200 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Submitted Match Reports</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left"><thead class="text-xs text-gray-700 uppercase bg-gray-50"><tr><th class="px-6 py-3">Match</th><th class="px-6 py-3">Submitted By</th><th class="px-6 py-3">Date Submitted</th><th class="px-6 py-3 text-center">Actions</th></tr></thead>
                            <tbody id="reports-table-body"></tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- View Report Modal -->
    <div id="report-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-lg">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-xl font-bold text-gray-800">Match Report</h3>
                <button id="close-modal-btn" class="text-gray-500 hover:text-gray-800"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <div id="report-content">
                <p class="font-semibold text-slate-800" id="report-match-title"></p>
                <p class="text-sm text-slate-500 mb-4" id="report-submitted-by"></p>
                <div class="bg-slate-50 p-4 rounded-lg">
                    <p class="text-slate-700" id="report-summary"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();
            const tableBody = document.getElementById('reports-table-body');
            const modal = document.getElementById('report-modal');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const reportMatchTitle = document.getElementById('report-match-title');
            const reportSubmittedBy = document.getElementById('report-submitted-by');
            const reportSummary = document.getElementById('report-summary');
            let reportsData = [];

            try {
                const response = await fetch('api.php?action=getSubmittedReports');
                const result = await response.json();

                if (result.status === 'success') {
                    reportsData = result.data;
                    renderTable(reportsData);
                } else {
                    tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-red-500">${result.message}</td></tr>`;
                }
            } catch (error) {
                tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-red-500">Failed to load reports.</td></tr>`;
            }

            function renderTable(reports) {
                tableBody.innerHTML = '';
                if (reports.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-gray-500">No reports have been submitted yet.</td></tr>`;
                    return;
                }
                reports.forEach(report => {
                    const row = document.createElement('tr');
                    row.className = 'bg-white border-b';
                    row.innerHTML = `
                        <td class="px-6 py-4 font-medium text-gray-900">${report.team1_name} vs ${report.team2_name}</td>
                        <td class="px-6 py-4">${report.volunteer_name || 'N/A'}</td>
                        <td class="px-6 py-4">${new Date(report.datetime).toLocaleDateString('en-CA')}</td>
                        <td class="px-6 py-4 text-center">
                            <button class="font-medium text-blue-600 hover:underline" onclick="openViewModal(${report.id})">View Report</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            }

            window.openViewModal = (id) => {
                // --- THE FIX IS HERE ---
                // Use non-strict comparison (==) to match string from DB with number from onclick
                const report = reportsData.find(r => r.id == id);
                if (!report) {
                    alert('Error: Could not find the selected report.');
                    return;
                }

                reportMatchTitle.textContent = `${report.team1_name} vs ${report.team2_name}`;
                reportSubmittedBy.textContent = `Submitted by ${report.volunteer_name || 'N/A'} on ${new Date(report.datetime).toLocaleDateString()}`;
                reportSummary.textContent = report.reportSummary || 'No summary was provided.';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            closeModalBtn.addEventListener('click', () => {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            });
        });
    </script>
</body>
</html>
