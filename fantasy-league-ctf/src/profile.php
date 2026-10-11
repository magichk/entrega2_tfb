<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$db = new PDO('sqlite:/var/db/fantasy.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$user_id = $_SESSION['user_id'];
$is_admin = $_SESSION['is_admin'] ?? 0;

// VULNERABILIDAD IDOR CRÍTICA - permite editar cualquier perfil
$target_user_id = $_GET['user_id'] ?? $user_id;

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$target_user_id]);
$target = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$target) {
    header('Location: profile.php');
    exit;
}

$success_msg = '';
$error_msg = '';

// Cambiar contraseña (IDOR VULNERABLE - no valida permisos)
if (isset($_POST['change_password'])) {
    $change_user_id = $_POST['user_id'];
    $current_pass = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    
    if ($new_password !== $confirm_pass) {
        $error_msg = "Las contraseñas no coinciden";
    } else {
        // VULNERABILIDAD: No verifica si el usuario actual tiene permisos
        // No verifica la contraseña actual
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_password, $change_user_id]);
        
        $success_msg = "¡Contraseña cambiada exitosamente para " . htmlspecialchars($target['username']) . "!";
        
        // Si cambió la contraseña del admin (id=1), mostrar FLAG
        if ($change_user_id == 1) {
            $flag = getenv('FLAG_IDOR') ?: 'MCSEH{1d0r_ch4mp10n_l34gu3_h4ck3r}';
            $success_msg = "🎉 ¡FLAG ENCONTRADA! 🎉<br><br><strong style='font-size:20px; color:#ffd700;'>" . $flag . "</strong><br><br>Has explotado exitosamente la vulnerabilidad IDOR cambiando la contraseña del administrador.";
        }
    }
}

// Actualizar datos de perfil (IDOR vulnerable)
if (isset($_POST['update_profile'])) {
    $update_user_id = $_POST['user_id'];
    $team_name = $_POST['team_name'];
    $formation = $_POST['formation'];
    
    // VULNERABILIDAD: No valida permisos
    $stmt = $db->prepare("UPDATE users SET team_name = ?, formation = ? WHERE id = ?");
    $stmt->execute([$team_name, $formation, $update_user_id]);
    
    $success_msg = "¡Perfil actualizado exitosamente!";
    
    // Recargar datos
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$update_user_id]);
    $target = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Calcular estadísticas
$stmt = $db->prepare("SELECT COUNT(*) as total, SUM(market_value) as total_value FROM teams WHERE user_id = ?");
$stmt->execute([$target_user_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Fantasy League</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="top-bar">
        <div class="logo">FANTASY<span>LEAGUE</span></div>
        <div class="user-menu">
            <a href="dashboard.php">Mi Equipo</a>
            <a href="profile.php">Mi Perfil</a>
            <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
            <?php if ($is_admin): ?>
                <span class="admin-badge">ADMIN</span>
            <?php endif; ?>
            <a href="logout.php">Salir</a>
        </div>
    </div>

    <?php if ($success_msg): ?>
        <div class="alert-top alert-success"><?= $success_msg ?></div>
    <?php endif; ?>
    
    <?php if ($error_msg): ?>
        <div class="alert-top alert-error"><?= htmlspecialchars($error_msg) ?></div>
    <?php endif; ?>

    <div class="profile-container">
        <div class="profile-sidebar">
            <div class="profile-card-large">
                <div class="profile-avatar-large"><?= strtoupper(substr($target['username'], 0, 2)) ?></div>
                <h2><?= htmlspecialchars($target['username']) ?></h2>
                <p class="user-role"><?= $target['is_admin'] ? 'Administrador' : 'Manager' ?></p>
                
                <div class="profile-stats-grid">
                    <div class="stat-box">
                        <div class="stat-icon">⚽</div>
                        <div class="stat-value"><?= $target['points'] ?></div>
                        <div class="stat-label">Puntos Totales</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon">👥</div>
                        <div class="stat-value"><?= $stats['total'] ?></div>
                        <div class="stat-label">Jugadores</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon">💰</div>
                        <div class="stat-value">€<?= number_format($stats['total_value']/1000000, 0) ?>M</div>
                        <div class="stat-label">Valor Equipo</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-icon">💵</div>
                        <div class="stat-value">€<?= number_format($target['budget']/1000000, 0) ?>M</div>
                        <div class="stat-label">Presupuesto</div>
                    </div>
                </div>
            </div>
            
            <?php if ($target_user_id != $user_id): ?>
            <?php endif; ?>
        </div>

        <div class="profile-main">
            <!-- Información del equipo -->
            <div class="profile-section">
                <h3>📋 INFORMACIÓN DEL EQUIPO</h3>
                <form method="POST" class="profile-form">
                    <input type="hidden" name="user_id" value="<?= $target_user_id ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Nombre del Equipo</label>
                            <input type="text" name="team_name" value="<?= htmlspecialchars($target['team_name']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Formación Táctica</label>
                            <select name="formation" required>
                                <option value="4-3-3" <?= $target['formation'] == '4-3-3' ? 'selected' : '' ?>>4-3-3</option>
                                <option value="4-4-2" <?= $target['formation'] == '4-4-2' ? 'selected' : '' ?>>4-4-2</option>
                                <option value="3-5-2" <?= $target['formation'] == '3-5-2' ? 'selected' : '' ?>>3-5-2</option>
                                <option value="4-2-3-1" <?= $target['formation'] == '4-2-3-1' ? 'selected' : '' ?>>4-2-3-1</option>
                                <option value="3-4-3" <?= $target['formation'] == '3-4-3' ? 'selected' : '' ?>>3-4-3</option>
                            </select>
                        </div>
                    </div>
                    
                    <button type="submit" name="update_profile" class="btn-primary">Actualizar Información</button>
                </form>
            </div>

            <!-- Cambiar contraseña - IDOR VULNERABLE -->
            <div class="profile-section security-section">
                <h3>🔒 SEGURIDAD - CAMBIAR CONTRASEÑA</h3>
                
                <?php if ($target_user_id != $user_id): ?>
                    <div class="danger-zone">
                        <p class="danger-text">⚠️ ZONA PELIGROSA: Estás modificando la contraseña de <strong><?= htmlspecialchars($target['username']) ?></strong></p>
                    </div>
                <?php endif; ?>
                
                <form method="POST" class="profile-form">
                    <input type="hidden" name="user_id" value="<?= $target_user_id ?>">
                    
                    <div class="form-group">
                        <label>Nueva Contraseña</label>
                        <input type="password" name="new_password" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Confirmar Nueva Contraseña</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    
                    <button type="submit" name="change_password" class="btn-danger">
                        <?= $target_user_id == $user_id ? 'Cambiar Mi Contraseña' : 'Cambiar Contraseña de ' . htmlspecialchars($target['username']) ?>
                    </button>
                    
                    <?php if ($target_user_id == 1): ?>
                        <p class="flag-hint">🎯  Recuerda guardar la contraseña d�administración� en un gestor de passwords para recordarla.</p>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Información de cuenta -->
            <div class="profile-section">
                <h3>ℹ️ INFORMACIÓN DE CUENTA</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">ID de Usuario:</span>
                        <span class="info-value"><?= $target_user_id ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Usuario:</span>
                        <span class="info-value"><?= htmlspecialchars($target['username']) ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tipo de Cuenta:</span>
                        <span class="info-value"><?= $target['is_admin'] ? '⭐ Administrador' : '👤 Usuario Normal' ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Miembro desde:</span>
                        <span class="info-value"><?= date('d/m/Y', strtotime($target['created_at'])) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>


    


    


    
    <script>
        // Obtener flag vía proxy interno
        window.getFlag = async function() {
            try {
                const response = await fetch('/get_flag.php');
                const data = await response.json();
                return data.flag || 'Error: ' + (data.error || 'Unknown error');
            } catch (error) {
                return 'Error al obtener flag: ' + error.message;
            }
        };
        
        window.adminFlag = window.getFlag;
    </script>
</body>
</html>
