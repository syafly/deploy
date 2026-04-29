<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    @vite('resources/halaman/login/index.css')
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <i class="fas fa-chalkboard-teacher"></i>
            <h2>Portal Guru</h2>
        </div>
        
        <div id="errorMessage" class="alert alert-danger d-none" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <span id="errorText"></span>
        </div>
        
        <form id="loginForm">
            @csrf
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                    <input type="text" class="form-control with-icon mb-0" id="username" name="username" placeholder="Masukkan username" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                    <input type="password" class="form-control with-icon mb-0" id="password" name="password" placeholder="Masukkan password" required>
                    <span class="input-group-text" id="togglePassword" style="cursor: pointer;">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>
            
            <button type="submit" class="btn btn-login" id="submitBtn">
                <span id="loginText">Masuk</span>
                <span id="loadingSpinner" class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
            </button>
        </form>
        
        <div class="copyright">
            &copy; 2025 Techno Kreatif Solusindo
        </div>
    </div>
    @vite('resources/halaman/login/index.js')
</body>
</html>

    