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
    <title>SportsWatch Hub - Manage Teams</title>
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
                <a href="#" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg active"><i data-lucide="users" class="w-5 h-5 mr-3"></i>Manage Teams</a>
                <a href="./userManagement.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="user-cog" class="w-5 h-5 mr-3"></i>User Management</a>
                <a href="./manageVolunteers.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>Manage Volunteers</a>
                <a href="./systemSettings.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="settings" class="w-5 h-5 mr-3"></i>System Settings</a>
                <a href="./reports.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="file-text" class="w-5 h-5 mr-3"></i>Reports</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b border-slate-200"><h2 class="text-2xl font-bold text-slate-800">Manage Teams</h2><div class="flex items-center space-x-4"><button class="text-slate-500 hover:text-slate-700"><i data-lucide="bell" class="w-6 h-6"></i></button><div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/DC2626/FFFFFF?text=A" alt="Admin avatar"><span class="font-semibold text-slate-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-red-500 text-white font-bold px-2 py-1 rounded-md">ADMIN</span></div><a href="logout.php" class="flex items-center space-x-2 text-slate-600 hover:text-slate-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a></div></header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-6">
                <div class="bg-white border border-slate-200 rounded-lg p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800">All Teams</h3>
                        <div class="flex items-center space-x-4">
                             <select id="sport-filter" class="bg-white border border-gray-300 text-gray-700 py-2 px-3 rounded-lg focus:outline-none focus:border-indigo-500 text-sm"><option value="all">All Sports</option></select>
                            <button id="add-team-btn" class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700 flex items-center"><i data-lucide="plus" class="w-5 h-5 mr-2"></i>Add New Team</button>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left"><thead class="text-xs text-gray-700 uppercase bg-gray-50"><tr><th class="px-6 py-3">Team Name</th><th class="px-6 py-3">Sport</th><th class="px-6 py-3 text-center">Actions</th></tr></thead>
                            <tbody id="teams-table-body"></tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add/Edit Team Modal -->
    <div id="team-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md">
            <div class="flex justify-between items-center mb-6">
                <h3 id="modal-title" class="text-xl font-bold text-gray-800">Add New Team</h3>
                <button id="close-modal-btn" class="text-gray-500 hover:text-gray-800"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <form id="team-form">
                <input type="hidden" id="edit-team-id" name="id">
                <div class="space-y-4">
                    <div><label for="team-name" class="block text-sm font-medium text-slate-700">Team Name</label><input type="text" id="team-name" name="name" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm" required></div>
                    <div><label for="team-sport" class="block text-sm font-medium text-slate-700">Sport</label><select id="team-sport" name="sport_id" class="mt-1 block w-full border-slate-300 rounded-md shadow-sm" required></select></div>
                </div>
                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" id="cancel-btn" class="bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" id="save-btn" class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700">Add Team</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();
            const tableBody = document.getElementById('teams-table-body');
            const sportFilter = document.getElementById('sport-filter');
            const modal = document.getElementById('team-modal');
            const modalTitle = document.getElementById('modal-title');
            const openModalBtn = document.getElementById('add-team-btn');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const cancelBtn = document.getElementById('cancel-btn');
            const teamForm = document.getElementById('team-form');
            const teamIdInput = document.getElementById('edit-team-id');
            const teamNameInput = document.getElementById('team-name');
            const teamSportSelect = document.getElementById('team-sport');
            const saveBtn = document.getElementById('save-btn');
            let allSports = [];
            let allTeams = [];

            async function fetchData() {
                try {
                    const response = await fetch('api.php?action=getTeamsAndSports');
                    const result = await response.json();
                    if (result.status === 'success') {
                        allSports = result.data.sports;
                        allTeams = result.data.teams;
                        populateFilter();
                        renderTable();
                    } else {
                        tableBody.innerHTML = `<tr><td colspan="3" class="text-center py-10 text-red-500">${result.message}</td></tr>`;
                    }
                } catch (error) {
                    tableBody.innerHTML = `<tr><td colspan="3" class="text-center py-10 text-red-500">Failed to load data.</td></tr>`;
                }
            }

            function populateFilter() {
                if (sportFilter.options.length > 1) return; // Only populate once
                allSports.forEach(sport => {
                    const option = document.createElement('option');
                    option.value = sport.id;
                    option.textContent = sport.name;
                    sportFilter.appendChild(option);
                });
            }

            function renderTable() {
                const selectedSportId = sportFilter.value;
                const filteredTeams = selectedSportId === 'all' ? allTeams : allTeams.filter(team => team.sport_id == selectedSportId);
                tableBody.innerHTML = '';
                filteredTeams.forEach(team => {
                    const row = document.createElement('tr');
                    row.className = 'bg-white border-b';
                    row.innerHTML = `
                        <td class="px-6 py-4 font-medium text-gray-900">${team.name}</td>
                        <td class="px-6 py-4">${team.sport_name}</td>
                        <td class="px-6 py-4 text-center space-x-4">
                            <button class="font-medium text-blue-600 hover:underline" onclick="openEditModal(${team.id})">Edit</button>
                            <button class="font-medium text-red-600 hover:underline" onclick="handleDelete(${team.id})">Delete</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            }

            function openAddModal() {
                teamForm.reset();
                teamIdInput.value = '';
                modalTitle.textContent = 'Add New Team';
                saveBtn.textContent = 'Add Team';
                teamSportSelect.innerHTML = allSports.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }

            window.openEditModal = (id) => {
                const team = allTeams.find(t => t.id == id);
                if (!team) { alert('Error: Team not found.'); return; }
                teamForm.reset();
                teamIdInput.value = team.id;
                modalTitle.textContent = 'Edit Team';
                teamNameInput.value = team.name;
                teamSportSelect.innerHTML = allSports.map(s => `<option value="${s.id}" ${s.id == team.sport_id ? 'selected' : ''}>${s.name}</option>`).join('');
                saveBtn.textContent = 'Save Changes';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            window.handleDelete = async (id) => {
                const team = allTeams.find(t => t.id == id);
                if (!team) { alert('Error: Team not found.'); return; }
                if (!confirm(`Are you sure you want to delete "${team.name}"?`)) return;
                const formData = new FormData();
                formData.append('id', id);
                try {
                    const response = await fetch('api.php?action=deleteTeam', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.status === 'success') {
                        fetchData();
                    } else {
                        alert(`Error: ${result.message}`);
                    }
                } catch (e) { alert('An error occurred.'); }
            };

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            openModalBtn.addEventListener('click', openAddModal);
            closeModalBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);
            sportFilter.addEventListener('change', renderTable);

            teamForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const id = teamIdInput.value;
                const action = id ? 'updateTeam' : 'addTeam';
                const formData = new FormData(teamForm);
                try {
                    const response = await fetch(`api.php?action=${action}`, { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.status === 'success') {
                        fetchData();
                        closeModal();
                    } else {
                        alert(`Error: ${result.message}`);
                    }
                } catch (e) { alert('An error occurred.'); }
            });

            fetchData();
        });
    </script>
</body>
</html>
