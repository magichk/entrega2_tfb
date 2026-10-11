<?php
session_start();

$db = new PDO('sqlite:/var/db/fantasy.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$error = '';
$success = '';

// Login
if (isset($_POST['login'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['is_admin'] = $user['is_admin'];
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Credenciales inválidas';
    }
}

// Register
if (isset($_POST['register'])) {
    $username = $_POST['reg_username'] ?? '';
    $password = $_POST['reg_password'] ?? '';
    $team_name = $_POST['team_name'] ?? '';
    
    try {
        $stmt = $db->prepare("INSERT INTO users (username, password, team_name) VALUES (?, ?, ?)");
        $stmt->execute([$username, $password, $team_name]);
        $success = 'Registro exitoso. Ya puedes iniciar sesión.';
    } catch (PDOException $e) {
        $error = 'El usuario ya existe';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fantasy League - Login</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="top-bar">
        <div class="logo">FANTASY<span>LEAGUE</span></div>
    </div>
    
    <div class="hero-section">
        <h1>CREA TU <span class="highlight">EQUIPO DE ENSUEÑO</span></h1>
        <p class="hero-subtitle">LA MEJOR LIGA FANTASY DE FÚTBOL</p>
        <p>Compite contra otros managers, ficha a las estrellas y demuestra que eres el mejor</p>
    </div>

    <div class="login-container">
        <div class="login-box">
            <h2>INICIAR SESIÓN</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="username" placeholder="Usuario" required>
                </div>
                <div class="form-group">
                    <input type="password" name="password" placeholder="Contraseña" required>
                </div>
                <button type="submit" name="login" class="btn-primary">ENTRAR</button>
            </form>
            
            <div class="divider">O REGÍSTRATE</div>
            
            <form method="POST">
                <div class="form-group">
                    <input type="text" name="reg_username" placeholder="Nuevo usuario" required>
                </div>
                <div class="form-group">
                    <input type="password" name="reg_password" placeholder="Contraseña" required>
                </div>
                <div class="form-group">
                    <input type="text" name="team_name" placeholder="Nombre de tu equipo" required>
                </div>
                <button type="submit" name="register" class="btn-secondary">CREAR CUENTA</button>
            </form>
            
        </div>
    </div>
    
    <!-- FLAG CSP escondida en comentario -->
    <!-- FLAG_CSP_HINT: El admin tiene acceso a funcionalidades especiales. Intenta ejecutar código JavaScript -->
    
    <script>
        // Vulnerable: acepta parámetros de URL
        const urlParams = new URLSearchParams(window.location.search);
        const msg = urlParams.get('msg');
        if (msg) {
            document.body.innerHTML += '<div class="floating-msg">' + msg + '</div>';
        }
    </script>
</body>
