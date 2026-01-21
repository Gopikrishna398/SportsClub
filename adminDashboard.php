<?php
// --- Security Gatekeeper ---
require_once 'session_auth.php';
// This page is only for admins. Redirect if not an admin.
check_auth(['admin']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SportsWatch Hub - Admin Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .sidebar-link.active { background-color: #f1f5f9; color: #1e293b; font-weight: 600; border-left: 3px solid #4f46e5; }
        .action-card { background-color: white; border: 1px solid #e2e8f0; border-radius: 0.75rem; padding: 1.5rem; text-align: center; transition: all 0.2s ease-in-out; }
        .action-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1); }
    </style>
</head>
<body class="bg-slate-50">
    <div class="flex h-screen">
        <!-- Admin Sidebar -->
        <aside class="w-64 bg-white shadow-sm flex-shrink-0 hidden lg:flex flex-col border-r border-slate-200">
            <div class="p-6 flex items-center space-x-3"><div class="bg-indigo-600 p-2 rounded-lg"><svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"></path></svg></div><h1 class="text-xl font-bold text-slate-800">SportsWatch Hub</h1></div>
            <nav class="mt-6 flex-1 px-4">
                <a href="#" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg active"><i data-lucide="layout-dashboard" class="w-5 h-5 mr-3"></i>Dashboard</a>
                <a href="./manageApplications.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="inbox" class="w-5 h-5 mr-3"></i>Applications</a>
                <a href="./analytics.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="bar-chart-2" class="w-5 h-5 mr-3"></i>Analytics</a>
                <a href="./createMatch.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="plus-circle" class="w-5 h-5 mr-3"></i>Create Match</a>
                <a href="./manageTeams.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="users" class="w-5 h-5 mr-3"></i>Manage Teams</a>
                <a href="./userManagement.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="user-cog" class="w-5 h-5 mr-3"></i>User Management</a>
                <a href="./manageVolunteers.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>Manage Volunteers</a>
                <a href="./systemSettings.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="settings" class="w-5 h-5 mr-3"></i>System Settings</a>
                <a href="./reports.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="file-text" class="w-5 h-5 mr-3"></i>Reports</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b border-slate-200"><h2 class="text-2xl font-bold text-slate-800">Admin Dashboard</h2><div class="flex items-center space-x-4"><button class="text-slate-500 hover:text-slate-700"><i data-lucide="bell" class="w-6 h-6"></i></button><div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/DC2626/FFFFFF?text=A" alt="Admin avatar"><span class="font-semibold text-slate-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-red-500 text-white font-bold px-2 py-1 rounded-md">ADMIN</span></div><a href="logout.php" class="flex items-center space-x-2 text-slate-600 hover:text-slate-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a></div></header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-6">
                 <div class="p-6 rounded-xl text-white bg-gradient-to-r from-green-500 to-blue-600 mb-6">
                    <h3 class="text-2xl font-bold">Admin Dashboard</h3>
                    <p class="mt-1 text-blue-100">Complete control over the tournament management system.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="bg-white p-5 rounded-xl border border-slate-200 flex items-center justify-between"><div><p class="text-sm text-slate-500">Total Users</p><p id="total-users" class="text-3xl font-bold text-slate-800">0</p></div><div class="bg-blue-100 p-3 rounded-full"><i data-lucide="users" class="w-6 h-6 text-blue-500"></i></div></div>
                    <div class="bg-white p-5 rounded-xl border border-slate-200 flex items-center justify-between"><div><p class="text-sm text-slate-500">Active Volunteers</p><p id="total-volunteers" class="text-3xl font-bold text-slate-800">0</p></div><div class="bg-green-100 p-3 rounded-full"><i data-lucide="user-check" class="w-6 h-6 text-green-500"></i></div></div>
                    <div class="bg-white p-5 rounded-xl border border-slate-200 flex items-center justify-between"><div><p class="text-sm text-slate-500">Total Matches</p><p id="total-matches" class="text-3xl font-bold text-slate-800">0</p></div><div class="bg-yellow-100 p-3 rounded-full"><i data-lucide="trophy" class="w-6 h-6 text-yellow-500"></i></div></div>
                    <div class="bg-white p-5 rounded-xl border border-slate-200 flex items-center justify-between"><div><p class="text-sm text-slate-500">Live Matches</p><p id="live-matches" class="text-3xl font-bold text-slate-800">0</p></div><div class="bg-red-100 p-3 rounded-full"><i data-lucide="radio-tower" class="w-6 h-6 text-red-500"></i></div></div>
                </div>
                <div class="mt-6 grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 bg-white border border-slate-200 rounded-lg p-6">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center mb-4"><i data-lucide="cpu" class="w-5 h-5 mr-2 text-indigo-500"></i>Admin Actions</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <a href="./createMatch.php" class="action-card"><i data-lucide="plus-circle" class="mx-auto w-8 h-8 text-indigo-500 mb-2"></i><span class="font-semibold text-slate-700">Create Match</span></a>
                            <a href="./manageTeams.php" class="action-card"><i data-lucide="users" class="mx-auto w-8 h-8 text-indigo-500 mb-2"></i><span class="font-semibold text-slate-700">Manage Teams</span></a>
                            <a href="./userManagement.php" class="action-card"><i data-lucide="user-cog" class="mx-auto w-8 h-8 text-indigo-500 mb-2"></i><span class="font-semibold text-slate-700">User Management</span></a>
                            <a href="./analytics.php" class="action-card"><i data-lucide="pie-chart" class="mx-auto w-8 h-8 text-indigo-500 mb-2"></i><span class="font-semibold text-slate-700">Analytics</span></a>
                            <a href="./systemSettings.php" class="action-card"><i data-lucide="settings" class="mx-auto w-8 h-8 text-indigo-500 mb-2"></i><span class="font-semibold text-slate-700">System Settings</span></a>
                            <a href="./reports.php" class="action-card"><i data-lucide="file-text" class="mx-auto w-8 h-8 text-indigo-500 mb-2"></i><span class="font-semibold text-slate-700">Reports</span></a>
                        </div>
                    </div>
                    <div class="bg-white border border-slate-200 rounded-lg p-6">
                        <h3 class="text-lg font-bold text-slate-800 mb-4">System Health</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center"><span class="text-sm font-medium text-slate-600">Database</span><span class="text-xs font-bold text-green-700 bg-green-100 px-3 py-1 rounded-full">Healthy</span></div>
                            <div class="flex justify-between items-center"><span class="text-sm font-medium text-slate-600">Live Updates</span><span class="text-xs font-bold text-green-700 bg-green-100 px-3 py-1 rounded-full">Active</span></div>
                            <div class="flex justify-between items-center"><span class="text-sm font-medium text-slate-600">Predictions</span><span class="text-xs font-bold text-yellow-700 bg-yellow-100 px-3 py-1 rounded-full">Working</span></div>
                            <div class="flex justify-between items-center"><span class="text-sm font-medium text-slate-600">Notifications</span><span class="text-xs font-bold text-green-700 bg-green-100 px-3 py-1 rounded-full">Online</span></div>
                        </div>
                         <button class="mt-4 w-full bg-slate-100 text-slate-700 font-semibold py-2 px-4 rounded-lg hover:bg-slate-200 text-sm flex items-center justify-center"><i data-lucide="binary" class="w-4 h-4 mr-2"></i>System Logs</button>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();
            try {
                const response = await fetch('api.php?action=getDashboardStats');
                const result = await response.json();
                if (result.status === 'success') {
                    const stats = result.data;
                    document.getElementById('total-users').textContent = stats.totalUsers || 0;
                    document.getElementById('total-volunteers').textContent = stats.totalVolunteers || 0;
                    document.getElementById('total-matches').textContent = stats.totalMatches || 0;
                    document.getElementById('live-matches').textContent = stats.liveMatches || 0;
                } else {
                    console.error("Failed to fetch dashboard stats:", result.message);
                }
            } catch (error) {
                console.error("Error fetching dashboard stats:", error);
            }
        });
    </script>
</body>
</html>
