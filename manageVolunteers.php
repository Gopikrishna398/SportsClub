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
    <title>SportsWatch Hub - Manage Volunteers</title>
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
                <a href="#" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg active"><i data-lucide="user-check" class="w-5 h-5 mr-3"></i>Manage Volunteers</a>
                <a href="./systemSettings.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="settings" class="w-5 h-5 mr-3"></i>System Settings</a>
                <a href="./reports.php" class="sidebar-link flex items-center px-4 py-3 text-slate-600 hover:bg-slate-100 rounded-lg"><i data-lucide="file-text" class="w-5 h-5 mr-3"></i>Reports</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <header class="bg-white shadow-sm p-4 flex justify-between items-center border-b border-slate-200"><h2 class="text-2xl font-bold text-slate-800">Manage Volunteers</h2><div class="flex items-center space-x-4"><button class="text-slate-500 hover:text-slate-700"><i data-lucide="bell" class="w-6 h-6"></i></button><div class="flex items-center space-x-2"><img class="w-9 h-9 rounded-full" src="https://placehold.co/100x100/DC2626/FFFFFF?text=A" alt="Admin avatar"><span class="font-semibold text-slate-700"><?php echo htmlspecialchars($_SESSION['username']); ?></span><span class="text-xs bg-red-500 text-white font-bold px-2 py-1 rounded-md">ADMIN</span></div><a href="logout.php" class="flex items-center space-x-2 text-slate-600 hover:text-slate-800"><i data-lucide="log-out" class="w-5 h-5"></i><span>Logout</span></a></div></header>
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-slate-50 p-6">
                <div class="bg-white border border-slate-200 rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left"><thead class="text-xs text-gray-700 uppercase bg-gray-50"><tr><th class="px-6 py-3">Volunteer</th><th class="px-6 py-3">Email</th><th class="px-6 py-3">Assigned Sports</th><th class="px-6 py-3 text-center">Actions</th></tr></thead>
                            <tbody id="volunteers-table-body"></tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Edit Volunteer Modal -->
    <div id="edit-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl p-8 w-full max-w-md">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800">Edit Volunteer</h3>
                <button id="close-modal-btn" class="text-gray-500 hover:text-gray-800"><i data-lucide="x" class="w-6 h-6"></i></button>
            </div>
            <form id="edit-volunteer-form">
                <input type="hidden" id="edit-volunteer-id">
                <div>
                    <p class="font-semibold text-slate-800" id="volunteer-name-modal"></p>
                    <p class="text-sm text-slate-500" id="volunteer-email-modal"></p>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Assign Sports</label>
                    <div id="sports-checkboxes" class="grid grid-cols-2 gap-2">
                        <!-- Checkboxes will be injected here -->
                    </div>
                </div>
                <div class="mt-8 flex justify-end space-x-3">
                    <button type="button" id="cancel-btn" class="bg-gray-200 text-gray-700 font-semibold py-2 px-4 rounded-lg hover:bg-gray-300">Cancel</button>
                    <button type="submit" class="bg-indigo-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-indigo-700">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            lucide.createIcons();
            const tableBody = document.getElementById('volunteers-table-body');
            const modal = document.getElementById('edit-modal');
            const closeModalBtn = document.getElementById('close-modal-btn');
            const cancelBtn = document.getElementById('cancel-btn');
            const editForm = document.getElementById('edit-volunteer-form');
            const volunteerIdInput = document.getElementById('edit-volunteer-id');
            const volunteerNameModal = document.getElementById('volunteer-name-modal');
            const volunteerEmailModal = document.getElementById('volunteer-email-modal');
            const sportsCheckboxesContainer = document.getElementById('sports-checkboxes');
            let allSports = [];
            let volunteers = [];

            async function fetchData() {
                try {
                    const response = await fetch('api.php?action=getVolunteersAndSports');
                    const result = await response.json();
                    if (result.status === 'success') {
                        allSports = result.data.sports;
                        volunteers = result.data.volunteers;
                        renderTable();
                    } else {
                        tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-red-500">${result.message}</td></tr>`;
                    }
                } catch (error) {
                    tableBody.innerHTML = `<tr><td colspan="4" class="text-center py-10 text-red-500">Failed to load data.</td></tr>`;
                }
            }

            function renderTable() {
                tableBody.innerHTML = '';
                volunteers.forEach(v => {
                    const row = document.createElement('tr');
                    row.className = 'bg-white border-b';
                    row.innerHTML = `
                        <td class="px-6 py-4 font-medium text-gray-900">${v.username}</td>
                        <td class="px-6 py-4">${v.email}</td>
                        <td class="px-6 py-4">
                            ${v.assigned_sports && v.assigned_sports.length > 0 ? v.assigned_sports.map(s => `<span class="bg-orange-100 text-orange-700 text-xs font-semibold px-2-5 py-1 rounded-full">${s}</span>`).join(' ') : '<span class="text-slate-400">None</span>'}
                        </td>
                        <td class="px-6 py-4 text-center space-x-4">
                            <button class="font-medium text-blue-600 hover:underline" onclick="openEditModal(${v.id})">Edit</button>
                            <button class="font-medium text-red-600 hover:underline" onclick="handleDelete(${v.id})">Delete</button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                });
            }

            window.openEditModal = (id) => {
                // --- FIX: Use non-strict comparison (==) to handle string vs number types ---
                const volunteer = volunteers.find(v => v.id == id);
                if (!volunteer) {
                    alert('Error: Could not find the selected volunteer.');
                    return;
                }
                volunteerIdInput.value = volunteer.id;
                volunteerNameModal.textContent = volunteer.username;
                volunteerEmailModal.textContent = volunteer.email;

                sportsCheckboxesContainer.innerHTML = allSports.map(sport => `
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="sports" value="${sport.id}" class="rounded border-gray-300 text-indigo-600 shadow-sm" ${volunteer.assigned_sports_ids.includes(sport.id) ? 'checked' : ''}>
                        <span>${sport.name}</span>
                    </label>
                `).join('');

                modal.classList.remove('hidden');
                modal.classList.add('flex');
            };

            window.handleDelete = async (id) => {
                // --- FIX: Use non-strict comparison (==) to handle string vs number types ---
                const volunteer = volunteers.find(v => v.id == id);
                if (!volunteer) {
                    alert('Error: Could not find the selected volunteer.');
                    return;
                }
                if (!confirm(`Are you sure you want to delete the volunteer "${volunteer.username}"? This action cannot be undone.`)) return;

                const formData = new FormData();
                formData.append('volunteer_id', id);

                try {
                    const response = await fetch('api.php?action=deleteVolunteer', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.status === 'success') {
                        fetchData(); // Refresh data from server
                    } else {
                        alert(`Error: ${result.message}`);
                    }
                } catch (error) {
                    alert('An error occurred while deleting the user.');
                }
            };

            function closeModal() {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }

            closeModalBtn.addEventListener('click', closeModal);
            cancelBtn.addEventListener('click', closeModal);

            editForm.addEventListener('submit', async (e) => {
                e.preventDefault();
                const id = volunteerIdInput.value;
                const selectedSports = Array.from(document.querySelectorAll('input[name="sports"]:checked')).map(cb => cb.value);
                
                const formData = new FormData();
                formData.append('volunteer_id', id);
                formData.append('sports_ids', JSON.stringify(selectedSports));

                try {
                    const response = await fetch('api.php?action=updateVolunteerAssignments', { method: 'POST', body: formData });
                    const result = await response.json();
                    if (result.status === 'success') {
                        fetchData(); // Refresh data from server
                        closeModal();
                    } else {
                        alert(`Error: ${result.message}`);
                    }
                } catch (error) {
                    alert('An error occurred while saving.');
                }
            });

            fetchData();
        });
    </script>
</body>
</html>
