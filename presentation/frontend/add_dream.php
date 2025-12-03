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
    <title>DreamJournal - Добавить сон</title>
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
                <a href="dashboard.php" class="btn btn-secondary">Назад</a>
                <button id="logoutBtn" class="btn btn-secondary">Выход</button>
            </div>
        </div>
    </nav>
    
    <div class="container">
        <div class="form-container">
            <h1>Добавить новый сон</h1>
            
            <form id="addDreamForm">
                <div class="form-group">
                    <label for="title">Название сна *</label>
                    <input type="text" id="title" name="title" required maxlength="255" placeholder="Краткое описание сна">
                </div>
                
                <div class="form-group">
                    <label for="dreamDate">Дата сна *</label>
                    <input type="date" id="dreamDate" name="dream_date" required>
                </div>
                
                <div class="form-group">
                    <label for="mood">Настроение</label>
                    <select id="mood" name="mood">
                        <option value="">Не указано</option>
                        <option value="Счастливый">Счастливый</option>
                        <option value="Спокойный">Спокойный</option>
                        <option value="Тревожный">Тревожный</option>
                        <option value="Страшный">Страшный</option>
                        <option value="Странный">Странный</option>
                        <option value="Грустный">Грустный</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="content">Описание сна *</label>
                    <textarea id="content" name="content" required rows="10" placeholder="Опишите ваш сон..."></textarea>
                </div>
                
                <div class="error-message" id="errorMessage"></div>
                <div class="success-message" id="successMessage"></div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Сохранить</button>
                    <a href="dashboard.php" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        document.getElementById('dreamDate').valueAsDate = new Date();
        
        document.getElementById('logoutBtn').addEventListener('click', async () => {
            try {
                await fetch('/backend/api/auth.php?action=logout', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'logout' })
                });
                window.location.href = 'index.php';
            } catch (error) {
                window.location.href = 'index.php';
            }
        });
        
        document.getElementById('addDreamForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const formData = {
                title: document.getElementById('title').value.trim(),
                content: document.getElementById('content').value.trim(),
                mood: document.getElementById('mood').value,
                dream_date: document.getElementById('dreamDate').value
            };
            
            const errorDiv = document.getElementById('errorMessage');
            const successDiv = document.getElementById('successMessage');
            
            errorDiv.style.display = 'none';
            successDiv.style.display = 'none';
            
            if (!formData.title) {
                showError(errorDiv, 'Пожалуйста, введите название сна');
                return;
            }
            
            if (!formData.content) {
                showError(errorDiv, 'Пожалуйста, опишите ваш сон');
                return;
            }
            
            if (!formData.dream_date) {
                showError(errorDiv, 'Пожалуйста, укажите дату сна');
                return;
            }
            
            try {
                const response = await fetch('/backend/api/dreams.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(formData)
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showSuccess(successDiv, 'Сон успешно сохранён!');
                    setTimeout(() => {
                        window.location.href = 'dashboard.php';
                    }, 1500);
                } else {
                    showError(errorDiv, data.error || 'Ошибка при сохранении сна');
                }
            } catch (error) {
                showError(errorDiv, 'Ошибка сети. Проверьте подключение.');
            }
        });
        
        function showError(element, message) {
            element.textContent = message;
            element.style.display = 'block';
            element.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
        function showSuccess(element, message) {
            element.textContent = message;
            element.style.display = 'block';
            element.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    </script>
</body>
</html>

