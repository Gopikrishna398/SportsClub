<?php
// --- Security Gatekeeper ---
require_once 'session_auth.php';
check_auth(['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Manage Applications</title>
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
                <a href="#" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg active"><i data-lucide="inbox" class="w-5 h-5 mr-3"></i>Applications</a>
                <a href="./analytics.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="bar-chart-2" class="w-5 h-5 mr-3"></i>Analytics</a>
                <a href="./createMatch.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="plus-circle" class="w-5 h-5 mr-3"></i>Create Match</a>
                <a href="./manageTeams.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="users" class="w-5 h-5 mr-3"></i>Manage Teams</a>
                <a href="./userManagement.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="user-cog" class="w-5 h-5 mr-3"></i>User Management</a>
                <a href="./manageVolunteers.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>Manage Volunteers</a>
                <a href="./systemSettings.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="settings" class="w-5 h-5 mr-3"></i>System Settings</a>
                <a href="./reports.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="file-text" class="w-5 h-5 mr-3"></i>Reports</a>
                <!-- Other admin links -->
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b border-slate-200"><h2 class="text-2xl font-bold text-slate-800">Manage Volunteer Applications</h2><div class="flex items-center space-x-4"><button class="text-slate-500 hover:text-slate-700"><i data-lucide="bell" class="w-6 h-6"></i></button><div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/DC2626/FFFFFF?text=A" alt="Admin avatar"><span class="font-semibold text-slate-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-red-500 text-white font-bold px-2 py-1 rounded-md">ADMIN</span></div><a href="logout.php" class="flex items-center space-x-2 text-slate-600 hover:text-slate-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a></div></header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-6">
                <div class="bg-white border border-slate-200 rounded-lg p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Pending Applications</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left"><thead class="text-xs text-gray-700 uppercase bg-gray-50"><tr><th class="px-6 py-3">Applicant Name</th><th class="px-6 py-3">Email</th><th class="px-6 py-3">Reason</th><th class="px-6 py-3 text-center">Actions</th></tr></thead>
                            <tbody id="applications-table-body">
                                <!-- Data will be injected here -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tableBody = document.getElementById('applications-table-body');

            async function fetchApplications() {
                try {
                    const response = await fetch('api.php?action=getVolunteerApplications');
                    const result = await response.json();
                    if (result.status === 'success') {
                        renderTable(result.data);
                    } else {
                        tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-red-500">${result.message}</td></tr>`;
                    }
                } catch (error) {
                    tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-red-500">Failed to load applications.</td></tr>`;
                }
            }

            function renderTable(applications) {
                tableBody.innerHTML = '';
                if (applications.length === 0) {
                    tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-gray-500">No pending volunteer applications.</td></tr>`;
                    return;
                }
                applications.forEach(app => {
                    const row = document.createElement('tr');
                    row.className = 'bg-white border-b';
                    row.innerHTML = `
                        <td class="px-6 py-4 font-medium text-gray-900">${app.name}</td>
                        <td class="px-6 py-4">${app.email}</td>
                        <td class="px-6 py-4 text-slate-600">${app.reason || 'N/A'}</td>
                        <td class="px-6 py-4 text-center space-x-2">
                            <button class="font-medium text-green-600 hover:underline" onclick="handleApproval(${app.id})">Approve</button>
                            <button class="font-medium text-red-600 hover:underline" onclick="handleDenial(${app.id})">Deny</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            }

            window.handleApproval = async (id) => {
                if (!confirm('Are you sure you want to approve this volunteer? A new user account will be created.')) return;
                
                const formData = new FormData();
                formData.append('id', id);

                const response = await fetch('api.php?action=approveVolunteerApplication', { method: 'POST', body: formData });
                const result = await response.json();
                alert(result.message);
                if(result.status === 'success') fetchApplications();
            };

            window.handleDenial = async (id) => {
                if (!confirm('Are you sure you want to deny this application?')) return;

                const formData = new FormData();
                formData.append('id', id);
                
                const response = await fetch('api.php?action=denyVolunteerApplication', { method: 'POST', body: formData });
                const result = await response.json();
                alert(result.message);
                if(result.status === 'success') fetchApplications();
            };

            fetchApplications();
            lucide.createIcons();
        });
    </script>
</body>
</html>