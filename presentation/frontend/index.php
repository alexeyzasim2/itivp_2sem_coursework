<?php
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DreamJournal - Вход</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <div class="auth-container">
            <div class="logo">
                <h1>DreamJournal</h1>
                <p>Дневник снов</p>
            </div>
            
            <div class="tabs">
                <button class="tab-btn active" data-tab="login">Вход</button>
                <button class="tab-btn" data-tab="register">Регистрация</button>
            </div>
            
            <form id="loginForm" class="auth-form active">
                <h2>Войти в аккаунт</h2>
                <div class="form-group">
                    <label for="loginUsername">Имя пользователя</label>
                    <input type="text" id="loginUsername" name="username" required minlength="3">
                </div>
                <div class="form-group">
                    <label for="loginPassword">Пароль</label>
                    <input type="password" id="loginPassword" name="password" required minlength="6">
                </div>
                <div class="error-message" id="loginError"></div>
                <button type="submit" class="btn btn-primary">Войти</button>
            </form>
            
            <form id="registerForm" class="auth-form">
                <h2>Создать аккаунт</h2>
                <div class="form-group">
                    <label for="registerUsername">Имя пользователя</label>
                    <input type="text" id="registerUsername" name="username" required minlength="3">
                    <small>Минимум 3 символа</small>
                </div>
                <div class="form-group">
                    <label for="registerPassword">Пароль</label>
                    <input type="password" id="registerPassword" name="password" required minlength="6">
                    <small>Минимум 6 символов</small>
                </div>
                <div class="form-group">
                    <label for="registerPasswordConfirm">Подтвердите пароль</label>
                    <input type="password" id="registerPasswordConfirm" name="passwordConfirm" required minlength="6">
                </div>
                <div class="error-message" id="registerError"></div>
                <button type="submit" class="btn btn-primary">Зарегистрироваться</button>
            </form>
        </div>
    </div>
    
    <script>
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const tab = btn.getAttribute('data-tab');
                
                document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                document.querySelectorAll('.auth-form').forEach(form => form.classList.remove('active'));
                if (tab === 'login') {
                    document.getElementById('loginForm').classList.add('active');
                } else {
                    document.getElementById('registerForm').classList.add('active');
                }
            });
        });
        
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('loginUsername').value.trim();
            const password = document.getElementById('loginPassword').value;
            const errorDiv = document.getElementById('loginError');
            
            if (username.length < 3) {
                showError(errorDiv, 'Имя пользователя должно быть не менее 3 символов');
                return;
            }
            
            if (password.length < 6) {
                showError(errorDiv, 'Пароль должен быть не менее 6 символов');
                return;
            }
            
            try {
                const response = await fetch('/backend/api/auth.php?action=login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'login', username, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    showError(errorDiv, data.error || 'Ошибка входа');
                }
            } catch (error) {
                showError(errorDiv, 'Ошибка сети. Проверьте подключение.');
            }
        });
        
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const username = document.getElementById('registerUsername').value.trim();
            const password = document.getElementById('registerPassword').value;
            const passwordConfirm = document.getElementById('registerPasswordConfirm').value;
            const errorDiv = document.getElementById('registerError');
            
            if (username.length < 3) {
                showError(errorDiv, 'Имя пользователя должно быть не менее 3 символов');
                return;
            }
            
            if (password.length < 6) {
                showError(errorDiv, 'Пароль должен быть не менее 6 символов');
                return;
            }
            
            if (password !== passwordConfirm) {
                showError(errorDiv, 'Пароли не совпадают');
                return;
            }
            
            try {
                const response = await fetch('/backend/api/auth.php?action=register', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'register', username, password })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    window.location.href = 'dashboard.php';
                } else {
                    showError(errorDiv, data.error || 'Ошибка регистрации');
                }
            } catch (error) {
                showError(errorDiv, 'Ошибка сети. Проверьте подключение.');
            }
        });
        
        function showError(element, message) {
            element.textContent = message;
            element.style.display = 'block';
            setTimeout(() => {
                element.style.display = 'none';
            }, 5000);
        }
    </script>
</body>
</html>

