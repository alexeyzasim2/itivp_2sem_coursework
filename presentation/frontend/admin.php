<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель - Dream Journal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">Dream Journal - Админ-панель</div>
            <div class="nav-menu">
                <span class="welcome-text">Добро пожаловать, <span id="adminUsername"></span></span>
                <a href="dashboard.php" class="btn btn-secondary">Мой дневник</a>
                <button onclick="logout()" class="btn btn-danger">Выход</button>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="tabs" style="margin-bottom: 2rem;">
            <button class="tab-btn active" onclick="showTab('users')">Пользователи</button>
            <button class="tab-btn" onclick="showTab('dreams')">Сны</button>
        </div>

        <div id="usersTab" class="tab-content">
            <div class="form-container">
                <h2>Управление пользователями</h2>
                <div id="usersList"></div>
            </div>
        </div>

        <div id="dreamsTab" class="tab-content" style="display: none;">
            <div class="form-container">
                <h2>Управление снами</h2>
                <div id="dreamsList"></div>
            </div>
        </div>
    </div>

    <div id="editUserModal" class="modal" style="display: none;">
        <div class="modal-content">
            <h3>Редактировать пользователя</h3>
            <form id="editUserForm">
                <input type="hidden" id="editUserId">
                <div class="form-group">
                    <label>Имя пользователя:</label>
                    <input type="text" id="editUsername" required>
                </div>
                <div class="form-group">
                    <label>Роль:</label>
                    <select id="editRole">
                        <option value="user">Пользователь</option>
                        <option value="admin">Администратор</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Новый пароль (оставьте пустым, чтобы не менять):</label>
                    <input type="password" id="editPassword">
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <button type="button" onclick="closeEditUserModal()" class="btn btn-secondary">Отмена</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        let currentUser = null;

        async function checkAuth() {
            try {
                const response = await fetch('/backend/api/auth.php?action=check');
                const data = await response.json();
                
                if (!data.logged_in || data.user.role !== 'admin') {
                    window.location.href = '/frontend/index.php';
                    return;
                }
                
                currentUser = data.user;
                document.getElementById('adminUsername').textContent = currentUser.username;
                loadUsers();
            } catch (error) {
                window.location.href = '/frontend/index.php';
            }
        }

        function showTab(tab) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            document.getElementById('usersTab').style.display = 'none';
            document.getElementById('dreamsTab').style.display = 'none';
            
            if (tab === 'users') {
                document.querySelector('.tab-btn').classList.add('active');
                document.getElementById('usersTab').style.display = 'block';
                loadUsers();
            } else {
                document.querySelectorAll('.tab-btn')[1].classList.add('active');
                document.getElementById('dreamsTab').style.display = 'block';
                loadDreams();
            }
        }

        async function loadUsers() {
            try {
                const response = await fetch('/backend/api/admin.php?users');
                const data = await response.json();
                
                if (data.success) {
                    const usersList = document.getElementById('usersList');
                    if (data.users.length === 0) {
                        usersList.innerHTML = '<p>Пользователи не найдены</p>';
                        return;
                    }
                    
                    usersList.innerHTML = data.users.map(user => `
                        <div class="dream-card">
                            <div class="dream-header">
                                <h3>${escapeHtml(user.username)}</h3>
                                <span class="dream-mood">${escapeHtml(user.role)}</span>
                            </div>
                            <div class="dream-date">Создан: ${formatDate(user.created_at)}</div>
                            <div class="dream-actions">
                                <button onclick="editUser(${user.id})" class="btn btn-small btn-primary">Редактировать</button>
                                ${user.id !== currentUser.id ? `<button onclick="deleteUser(${user.id})" class="btn btn-small btn-danger">Удалить</button>` : ''}
                            </div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                alert('Ошибка загрузки пользователей');
            }
        }

        async function loadDreams() {
            try {
                const response = await fetch('/backend/api/admin.php?dreams');
                const data = await response.json();
                
                if (data.success) {
                    const dreamsList = document.getElementById('dreamsList');
                    if (data.dreams.length === 0) {
                        dreamsList.innerHTML = '<p>Сны не найдены</p>';
                        return;
                    }
                    
                    dreamsList.innerHTML = data.dreams.map(dream => `
                        <div class="dream-card">
                            <div class="dream-header">
                                <h3>${escapeHtml(dream.title)}</h3>
                                <span class="dream-mood">${escapeHtml(dream.mood || 'Не указано')}</span>
                            </div>
                            <div class="dream-date">${formatDate(dream.dream_date)} | Автор: ${escapeHtml(dream.username)}</div>
                            <div class="dream-content">${escapeHtml(dream.content.substring(0, 150))}${dream.content.length > 150 ? '...' : ''}</div>
                            <div class="dream-actions">
                                <button onclick="deleteDream(${dream.id})" class="btn btn-small btn-danger">Удалить</button>
                            </div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                alert('Ошибка загрузки снов');
            }
        }

        async function editUser(userId) {
            try {
                const response = await fetch(`/backend/api/admin.php?action=user&id=${userId}`);
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('editUserId').value = data.user.id;
                    document.getElementById('editUsername').value = data.user.username;
                    document.getElementById('editRole').value = data.user.role;
                    document.getElementById('editPassword').value = '';
                    document.getElementById('editUserModal').style.display = 'block';
                }
            } catch (error) {
                alert('Ошибка загрузки пользователя');
            }
        }

        function closeEditUserModal() {
            document.getElementById('editUserModal').style.display = 'none';
        }

        document.getElementById('editUserForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const userId = document.getElementById('editUserId').value;
            const username = document.getElementById('editUsername').value;
            const role = document.getElementById('editRole').value;
            const password = document.getElementById('editPassword').value;
            
            const updateData = {
                username: username,
                role: role
            };
            
            if (password) {
                updateData.password = password;
            }
            
            try {
                const response = await fetch(`/backend/api/admin.php?action=user&id=${userId}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(updateData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Пользователь обновлен');
                    closeEditUserModal();
                    loadUsers();
                } else {
                    alert('Ошибка: ' + data.error);
                }
            } catch (error) {
                alert('Ошибка сети');
            }
        });

        async function deleteUser(userId) {
            if (!confirm('Вы уверены, что хотите удалить этого пользователя?')) {
                return;
            }
            
            try {
                const response = await fetch(`/backend/api/admin.php?action=user&id=${userId}`, {
                    method: 'DELETE'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Пользователь удален');
                    loadUsers();
                } else {
                    alert('Ошибка: ' + data.error);
                }
            } catch (error) {
                alert('Ошибка сети');
            }
        }

        async function deleteDream(dreamId) {
            if (!confirm('Вы уверены, что хотите удалить этот сон?')) {
                return;
            }
            
            try {
                const response = await fetch(`/backend/api/admin.php?action=dream&id=${dreamId}`, {
                    method: 'DELETE'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    alert('Сон удален');
                    loadDreams();
                } else {
                    alert('Ошибка: ' + data.error);
                }
            } catch (error) {
                alert('Ошибка сети');
            }
        }

        async function logout() {
            try {
                await fetch('/backend/api/auth.php?action=logout', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ action: 'logout' })
                });
                window.location.href = '/frontend/index.php';
            } catch (error) {
                window.location.href = '/frontend/index.php';
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('ru-RU', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        }

        checkAuth();
    </script>

    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            margin: 0;
            padding: 0;
        }

        .modal-content {
            background: #16213e;
            padding: 2rem;
            border-radius: 8px;
            border: 1px solid #0f3460;
            max-width: 500px;
            width: 90%;
            position: relative;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
            margin: auto;
        }

        .modal-content h3 {
            margin-bottom: 1.5rem;
            color: #4ecca3;
            text-align: center;
        }

        .tab-content {
            display: block;
        }
    </style>
</body>
</html>

