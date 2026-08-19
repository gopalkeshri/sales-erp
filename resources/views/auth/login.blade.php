<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950 text-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In - Sales ERP Enterprise Suite</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        :root {
            --primary: #6366f1;
            --primary-hover: #4f46e5;
            --primary-light: rgba(99, 102, 241, 0.15);
            --accent-cyan: #06b6d4;
            --accent-emerald: #10b981;
            --accent-amber: #f59e0b;
            --bg-dark: #090d16;
            --bg-card: rgba(17, 24, 39, 0.75);
            --border-color: rgba(255, 255, 255, 0.08);
            --border-glow: rgba(99, 102, 241, 0.4);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background-color: var(--bg-dark);
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 24px;
        }

        /* Ambient Glow Background Orbs */
        .ambient-orb-1 {
            position: absolute;
            top: -150px;
            left: -100px;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.25) 0%, rgba(99, 102, 241, 0) 70%);
            border-radius: 50%;
            filter: blur(60px);
            z-index: 1;
            pointer-events: none;
        }

        .ambient-orb-2 {
            position: absolute;
            bottom: -150px;
            right: -100px;
            width: 550px;
            height: 550px;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.2) 0%, rgba(6, 182, 212, 0) 70%);
            border-radius: 50%;
            filter: blur(70px);
            z-index: 1;
            pointer-events: none;
        }

        .login-container {
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 10;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .brand-logo {
            width: 54px;
            height: 54px;
            border-radius: 16px;
            background: linear-gradient(135deg, #6366f1 0%, #06b6d4 100%);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.45);
            color: white;
            margin-bottom: 16px;
        }

        .brand-title {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -0.02em;
            background: linear-gradient(90deg, #ffffff 0%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .brand-subtitle {
            font-size: 13px;
            color: #94a3b8;
            margin-top: 4px;
            font-weight: 500;
        }

        /* Glassmorphic Login Card */
        .login-card {
            background: var(--bg-card);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 36px 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05);
            position: relative;
            overflow: hidden;
        }

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
            background: linear-gradient(90deg, #6366f1, #06b6d4, #10b981);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 12px;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            color: #64748b;
            width: 18px;
            height: 18px;
            pointer-events: none;
        }

        .form-control {
            width: 100%;
            background: rgba(0, 0, 0, 0.35);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 12px 14px 12px 42px;
            font-size: 14px;
            color: #ffffff;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--primary);
            background: rgba(0, 0, 0, 0.5);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.2);
        }

        .toggle-password {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            padding: 0;
        }

        .toggle-password:hover {
            color: #94a3b8;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
            font-size: 13px;
        }

        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            color: #94a3b8;
            user-select: none;
        }

        .checkbox-container input {
            accent-color: var(--primary);
            width: 16px;
            height: 16px;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            padding: 13px 20px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            color: white;
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: 1px solid rgba(255, 255, 255, 0.15);
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.35);
        }

        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 12px 25px rgba(99, 102, 241, 0.45);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        /* Demo Quick Login Section */
        .demo-section {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--border-color);
        }

        .demo-title {
            font-size: 11px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            text-align: center;
            margin-bottom: 12px;
        }

        .demo-cards {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
        }

        .demo-btn {
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--border-color);
            border-radius: 10px;
            padding: 10px 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: center;
            color: #cbd5e1;
        }

        .demo-btn:hover {
            background: rgba(99, 102, 241, 0.15);
            border-color: rgba(99, 102, 241, 0.4);
            color: #ffffff;
            transform: translateY(-2px);
        }

        .demo-role {
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 2px;
        }

        .demo-badge {
            font-size: 9.5px;
            font-weight: 600;
            color: var(--accent-cyan);
            text-transform: uppercase;
        }

        /* Alert Box */
        .alert-danger {
            background: rgba(244, 63, 94, 0.15);
            border: 1px solid rgba(244, 63, 94, 0.3);
            color: #fb7185;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-info {
            background: rgba(6, 182, 212, 0.15);
            border: 1px solid rgba(6, 182, 212, 0.3);
            color: #22d3ee;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>

    <div class="ambient-orb-1"></div>
    <div class="ambient-orb-2"></div>

    <div class="login-container">
        <!-- Brand Header -->
        <div class="brand-header">
            <div class="brand-logo">
                <i data-lucide="layers" style="width: 28px; height: 28px;"></i>
            </div>
            <h1 class="brand-title">SALES ERP</h1>
            <p class="brand-subtitle">B2B Enterprise Revenue & Operations Portal</p>
        </div>

        <!-- Login Card -->
        <div class="login-card">
            @if ($errors->any())
                <div class="alert-danger">
                    <i data-lucide="alert-circle" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if (session('info'))
                <div class="alert-info">
                    <i data-lucide="info" style="width: 18px; height: 18px; flex-shrink: 0;"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <form action="{{ route('login.submit') }}" method="POST">
                @csrf
                
                <!-- Email Field -->
                <div class="form-group">
                    <label class="form-label" for="email">Business Email</label>
                    <div class="input-wrapper">
                        <i data-lucide="mail" class="input-icon"></i>
                        <input 
                            type="email" 
                            name="email" 
                            id="email" 
                            class="form-control" 
                            placeholder="name@company.com" 
                            value="{{ old('email', 'admin@saleserp.com') }}" 
                            required 
                            autofocus
                        >
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrapper">
                        <i data-lucide="lock" class="input-icon"></i>
                        <input 
                            type="password" 
                            name="password" 
                            id="password" 
                            class="form-control" 
                            placeholder="••••••••••••" 
                            value="password123"
                            required
                        >
                        <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                            <i data-lucide="eye" id="eyeIcon" style="width: 18px; height: 18px;"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="form-options">
                    <label class="checkbox-container">
                        <input type="checkbox" name="remember" value="1" checked>
                        <span>Keep me signed in</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-submit">
                    <span>Sign In to ERP Portal</span>
                    <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                </button>
            </form>

            <!-- Quick Demo Login Presets -->
            <div class="demo-section">
                <div class="demo-title">1-Click Role Login Demo</div>
                <div class="demo-cards">
                    <div class="demo-btn" onclick="fillCredentials('admin@saleserp.com', 'password123')">
                        <div class="demo-role">👑 Admin</div>
                        <div class="demo-badge">Full System</div>
                    </div>
                    <div class="demo-btn" onclick="fillCredentials('sarah.manager@saleserp.com', 'password123')">
                        <div class="demo-role">💼 Manager</div>
                        <div class="demo-badge">US West Lead</div>
                    </div>
                    <div class="demo-btn" onclick="fillCredentials('alex.rep@saleserp.com', 'password123')">
                        <div class="demo-role">🎯 Sales Rep</div>
                        <div class="demo-badge">Enterprise</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            const icon = document.getElementById('eyeIcon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                pwd.type = 'password';
                icon.setAttribute('data-lucide', 'eye');
            }
            lucide.createIcons();
        }

        function fillCredentials(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }
    </script>
</body>
</html>
