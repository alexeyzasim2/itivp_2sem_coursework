<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$username = $_SESSION['username'] ?? 'Пользователь';
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DreamJournal - Мои сны</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <span>DreamJournal</span>
            </div>
            <div class="nav-menu">
                <span class="welcome-text">Пользователь: <?php echo htmlspecialchars($username); ?></span>
                <a href="add_dream.php" class="btn btn-primary">Добавить сон</a>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin.php" class="btn btn-secondary">Админ-панель</a>
                <?php endif; ?>
                <button id="logoutBtn" class="btn btn-secondary">Выход</button>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="stats-section">
            <h2>Статистика</h2>
            <div class="stats-grid" id="statsGrid">
                <div class="stat-card">
                    <div class="stat-value" id="totalDreams">0</div>
                    <div class="stat-label">Всего снов</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="topMood">-</div>
                    <div class="stat-label">Частое настроение</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value" id="topWord">-</div>
                    <div class="stat-label">Частое слово</div>
                </div>
            </div>
            
            <div class="mood-distribution" id="moodDistribution"></div>
            <div class="top-words" id="topWords"></div>
        </div>
        
        <div class="dreams-section">
            <h2>Мои сны</h2>
            <div class="filter-section">
                <input type="text" id="searchInput" placeholder="Поиск по названию или содержанию...">
                <select id="moodFilter">
                    <option value="">Все настроения</option>
                    <option value="Счастливый">Счастливый</option>
                    <option value="Спокойный">Спокойный</option>
                    <option value="Тревожный">Тревожный</option>
                    <option value="Страшный">Страшный</option>
                    <option value="Странный">Странный</option>
                    <option value="Грустный">Грустный</option>
                </select>
            </div>
            <div id="dreamsContainer" class="dreams-grid"></div>
            <div id="noDreams" class="no-dreams" style="display: none;">
                <p>У вас пока нет записей снов</p>
                <a href="add_dream.php" class="btn btn-primary">Добавить первый сон</a>
            </div>
        </div>
    </div>
    
    <script>
        let allDreams = [];
        
        document.addEventListener('DOMContentLoaded', () => {
            loadDreams();
            loadStats();
        });
        
        document.getElementById('logoutBtn').addEventListener('click', async () => {
            try {
                await fetch('/backend/api/auth.php?action=logout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'logout' })
                });
                window.location.href = 'index.php';
            } catch (error) {
                console.error('Logout error:', error);
                window.location.href = 'index.php';
            }
        });
        
        async function loadDreams() {
            try {
                const response = await fetch('/backend/api/dreams.php');
                const data = await response.json();
                
                if (data.success) {
                    allDreams = data.dreams;
                    displayDreams(allDreams);
                }
            } catch (error) {
                console.error('Error loading dreams:', error);
            }
        }
        
        function displayDreams(dreams) {
            const container = document.getElementById('dreamsContainer');
            const noDreams = document.getElementById('noDreams');
            
            if (dreams.length === 0) {
                container.innerHTML = '';
                noDreams.style.display = 'block';
                return;
            }
            
            noDreams.style.display = 'none';
            container.innerHTML = dreams.map(dream => `
                <div class="dream-card" id="dream-${dream.id}">
                    <div class="dream-header">
                        <h3>${escapeHtml(dream.title)}</h3>
                        <span class="dream-mood">${escapeHtml(dream.mood || 'Не указано')}</span>
                    </div>
                    <div class="dream-date">${formatDate(dream.dream_date)}</div>
                    <div class="dream-content">${escapeHtml(dream.content.substring(0, 150))}${dream.content.length > 150 ? '...' : ''}</div>
                    <div class="dream-interpretation" id="interpretation-${dream.id}" style="display: none;">
                        <h4 class="interpretation-title">Интерпретация:</h4>
                        <div id="interpretation-content-${dream.id}" class="interpretation-content"></div>
                    </div>
                    <div class="dream-actions">
                        <button onclick="analyzeDream(${dream.id})" class="btn btn-small btn-primary">Анализировать</button>
                        <a href="edit_dream.php?id=${dream.id}" class="btn btn-small btn-secondary">Редактировать</a>
                        <button onclick="deleteDream(${dream.id})" class="btn btn-small btn-danger">Удалить</button>
                    </div>
                </div>
            `).join('');
        }
        
        async function loadStats() {
            try {
                const response = await fetch('/backend/api/stats.php');
                const data = await response.json();
                
                if (data.success) {
                    const stats = data.stats;
                    
                    document.getElementById('totalDreams').textContent = stats.total_dreams;
                    
                    if (stats.mood_distribution.length > 0) {
                        document.getElementById('topMood').textContent = stats.mood_distribution[0].mood || 'Не указано';
                    }
                    
                    if (stats.top_words.length > 0) {
                        document.getElementById('topWord').textContent = stats.top_words[0].word;
                    }
                    
                    if (stats.mood_distribution.length > 0) {
                        const moodHtml = '<h3>Распределение по настроениям:</h3>' +
                            stats.mood_distribution.map(item => `
                                <div class="mood-item">
                                    <span>${escapeHtml(item.mood || 'Не указано')}</span>
                                    <span class="mood-count">${item.count}</span>
                                </div>
                            `).join('');
                        document.getElementById('moodDistribution').innerHTML = moodHtml;
                    }
                    
                    if (stats.top_words.length > 0) {
                        const wordsHtml = '<h3>Топ-3 слова:</h3>' +
                            stats.top_words.map((item, index) => `
                                <div class="word-item">
                                    <span>${index + 1}. ${escapeHtml(item.word)}</span>
                                    <span class="word-count">${item.count} раз</span>
                                </div>
                            `).join('');
                        document.getElementById('topWords').innerHTML = wordsHtml;
                    }
                }
            } catch (error) {
                console.error('Error loading stats:', error);
            }
        }
        
        async function analyzeDream(id) {
            const interpretationDiv = document.getElementById(`interpretation-${id}`);
            const contentDiv = document.getElementById(`interpretation-content-${id}`);
            
            if (interpretationDiv.style.display === 'none') {
                contentDiv.innerHTML = '<div class="interpretation-empty">Загрузка...</div>';
                interpretationDiv.style.display = 'block';
                
                try {
                    const response = await fetch(`/backend/api/analyze.php/${id}`);
                    const data = await response.json();
                    
                    if (data.success) {
                        if (data.symbols.length > 0) {
                            contentDiv.innerHTML = data.symbols.map(symbol => `
                                <div class="interpretation-item">
                                    <strong>${escapeHtml(symbol.word)}:</strong>
                                    <span>${escapeHtml(symbol.meaning)}</span>
                                </div>
                            `).join('');
                        } else {
                            contentDiv.innerHTML = '<div class="interpretation-empty">В этом сне не найдено известных символов для интерпретации.</div>';
                        }
                    } else {
                        contentDiv.innerHTML = '<div class="interpretation-error">Ошибка: ' + escapeHtml(data.error) + '</div>';
                    }
                } catch (error) {
                    contentDiv.innerHTML = '<div class="interpretation-error">Ошибка сети при анализе</div>';
                }
            } else {
                interpretationDiv.style.display = 'none';
            }
        }
        
        async function deleteDream(id) {
            if (!confirm('Вы уверены, что хотите удалить этот сон?')) {
                return;
            }
            
            try {
                const response = await fetch(`/backend/api/dreams.php/${id}`, {
                    method: 'DELETE'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadDreams();
                    loadStats();
                } else {
                    alert('Ошибка удаления: ' + data.error);
                }
            } catch (error) {
                alert('Ошибка сети при удалении');
            }
        }
        
        document.getElementById('searchInput').addEventListener('input', filterDreams);
        document.getElementById('moodFilter').addEventListener('change', filterDreams);
        
        function filterDreams() {
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            const moodFilter = document.getElementById('moodFilter').value;
            
            const filtered = allDreams.filter(dream => {
                const matchesSearch = !searchTerm || 
                    dream.title.toLowerCase().includes(searchTerm) ||
                    dream.content.toLowerCase().includes(searchTerm);
                    
                const matchesMood = !moodFilter || dream.mood === moodFilter;
                
                return matchesSearch && matchesMood;
            });
            
            displayDreams(filtered);
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('ru-RU', { year: 'numeric', month: 'long', day: 'numeric' });
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>
</html>

