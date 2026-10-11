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

// VULNERABILIDAD IDOR
$target_user_id = $_GET['user_id'] ?? $user_id;

$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$target_user_id]);
$target = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$target) {
    header('Location: dashboard.php');
    exit;
}

// Búsqueda de jugadores (VULNERABLE A XSS - NO sanitiza)
$search_query = $_GET['search'] ?? '';
$search_results = [];

if ($search_query) {
    $stmt = $db->prepare("SELECT * FROM teams WHERE player_name LIKE ? LIMIT 5");
    $stmt->execute(['%' . $search_query . '%']);
    $search_results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Transferir jugador
if (isset($_POST['transfer_player'])) {
    $player_id = $_POST['player_id'];
    $to_user_id = $_POST['to_user_id'];
    
    $stmt = $db->prepare("UPDATE teams SET user_id = ? WHERE id = ?");
    $stmt->execute([$to_user_id, $player_id]);
    
    $transfer_msg = "¡Jugador transferido exitosamente!";
}

// Obtener ranking
$ranking = $db->query("SELECT * FROM users WHERE is_admin = 0 ORDER BY points DESC")->fetchAll(PDO::FETCH_ASSOC);

// Obtener equipo del target
$stmt = $db->prepare("SELECT * FROM teams WHERE user_id = ? ORDER BY 
    CASE position 
        WHEN 'POR' THEN 1 
        WHEN 'DEF' THEN 2 
        WHEN 'MED' THEN 3 
        WHEN 'DEL' THEN 4 
    END, player_name");
$stmt->execute([$target_user_id]);
$team = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total_value = 0;
foreach ($team as $player) {
    $total_value += $player['market_value'];
}

$players_by_position = [
    'POR' => [],
    'DEF' => [],
    'MED' => [],
    'DEL' => []
];

foreach ($team as $player) {
    $players_by_position[$player['position']][] = $player;
}

$csp_flag = getenv('FLAG_CSP') ?: 'MCSEH{csp_1s_n0t_s0_s3cur3_4ft3r_all}';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Fantasy League</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="top-bar">
        <div class="logo">FANTASY<span>LEAGUE</span></div>
        <div class="user-menu">
            <a href="dashboard.php?user_id=<?= $user_id ?>">Mi Equipo</a>
            <a href="profile.php?user_id=<?= $user_id ?>">Mi Perfil</a>
            <span>👤 <?= htmlspecialchars($_SESSION['username']) ?></span>
            <?php if ($is_admin): ?>
                <span class="admin-badge">ADMIN</span>
            <?php endif; ?>
            <a href="logout.php">Salir</a>
        </div>
    </div>

    <?php if (isset($transfer_msg)): ?>
        <div class="alert-top alert-success"><?= htmlspecialchars($transfer_msg) ?></div>
    <?php endif; ?>

    <div class="dashboard-container">
        <div class="sidebar">
            <div class="profile-card">
                <h3>PERFIL</h3>
                <div class="profile-info">
                    <div class="profile-avatar"><?= strtoupper(substr($target['username'], 0, 2)) ?></div>
                    <p class="profile-name"><?= htmlspecialchars($target['username']) ?></p>
                    <p class="team-name"><?= htmlspecialchars($target['team_name']) ?></p>
                    <div class="profile-stats">
                        <div class="stat">
                            <span class="stat-value"><?= $target['points'] ?></span>
                            <span class="stat-label">Puntos</span>
                        </div>
                        <div class="stat">
                            <span class="stat-value">€<?= number_format($total_value/1000000, 1) ?>M</span>
                            <span class="stat-label">Valor</span>
                        </div>
                    </div>
                    <div class="formation-badge"><?= htmlspecialchars($target['formation']) ?></div>
                    <?php if ($target_user_id != $user_id): ?>
                        <a href="profile.php?user_id=<?= $target_user_id ?>" class="btn-view-profile">Ver Perfil Completo</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Buscador de jugadores -->
            <div class="search-section">
                <h3>BUSCAR JUGADOR</h3>
                <form method="GET" action="dashboard.php">
                    <input type="hidden" name="user_id" value="<?= $target_user_id ?>">
                    <input type="text" name="search" class="search-input" placeholder="Nombre del jugador..." value="<?= htmlspecialchars($search_query) ?>">
                    <button type="submit" class="btn-search">Buscar</button>
                </form>
                
                <?php if ($search_query): ?>
                    <div class="search-results">
                        <!-- XSS VULNERABLE: No sanitiza el output -->
                        <p class="search-title">Resultados para: <?= $search_query ?></p>
                        <?php if (empty($search_results)): ?>
                            <p class="no-results">No se encontraron jugadores</p>
                        <?php else: ?>
                            <?php foreach ($search_results as $result): ?>
                                <div class="result-item">
                                    <strong><?= htmlspecialchars($result['player_name']) ?></strong>
                                    <span><?= $result['position'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <h3>CLASIFICACIÓN</h3>
            <div class="ranking-list">
                <?php foreach ($ranking as $index => $player): ?>
                    <div class="ranking-item <?= $player['id'] == $target_user_id ? 'active' : '' ?>">
                        <span class="rank">#<?= $index + 1 ?></span>
                        <a href="?user_id=<?= $player['id'] ?>">
                            <?= htmlspecialchars($player['username']) ?>
                        </a>
                        <span class="rank-points"><?= $player['points'] ?>pts</span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="main-content">
            <div class="team-header">
                <h2><?= strtoupper(htmlspecialchars($target['team_name'])) ?></h2>
                <div class="budget-info">
                    <span class="budget-label">Presupuesto:</span>
                    <span class="budget-value">€<?= number_format($target['budget']/1000000, 1) ?>M</span>
                </div>
            </div>

            <!-- Alineación táctica -->
            <div class="field">
                <div class="field-position goalkeeper">
                    <?php foreach ($players_by_position['POR'] as $player): ?>
                        <div class="player-card">
                            <div class="player-shirt">POR</div>
                            <div class="player-name"><?= htmlspecialchars($player['player_name']) ?></div>
                            <div class="player-stats">
                                <span class="player-points"><?= $player['points'] ?> pts</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="field-position defenders">
                    <?php foreach ($players_by_position['DEF'] as $player): ?>
                        <div class="player-card">
                            <div class="player-shirt">DEF</div>
                            <div class="player-name"><?= htmlspecialchars($player['player_name']) ?></div>
                            <div class="player-stats">
                                <span class="player-points"><?= $player['points'] ?> pts</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="field-position midfielders">
                    <?php foreach ($players_by_position['MED'] as $player): ?>
                        <div class="player-card">
                            <div class="player-shirt">MED</div>
                            <div class="player-name"><?= htmlspecialchars($player['player_name']) ?></div>
                            <div class="player-stats">
                                <span class="player-points"><?= $player['points'] ?> pts</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="field-position forwards">
                    <?php foreach ($players_by_position['DEL'] as $player): ?>
                        <div class="player-card">
                            <div class="player-shirt">DEL</div>
                            <div class="player-name"><?= htmlspecialchars($player['player_name']) ?></div>
                            <div class="player-stats">
                                <span class="player-points"><?= $player['points'] ?> pts</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Tabla detallada -->
            <h3 class="section-title">PLANTILLA COMPLETA</h3>
            <div class="team-grid">
                <table class="team-table">
                    <thead>
                        <tr>
                            <th>Jugador</th>
                            <th>Pos</th>
                            <th>Club</th>
                            <th>País</th>
                            <th>Puntos</th>
                            <th>Valor</th>
                            <?php if ($target_user_id != $user_id): ?>
                                <th>Acción</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($team as $player): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($player['player_name']) ?></strong></td>
                                <td><span class="position-badge"><?= $player['position'] ?></span></td>
                                <td><?= htmlspecialchars($player['club']) ?></td>
                                <td><?= htmlspecialchars($player['nationality']) ?></td>
                                <td><strong class="points-cell"><?= $player['points'] ?></strong></td>
                                <td class="value-cell">€<?= number_format($player['market_value']/1000000, 1) ?>M</td>
                                <?php if ($target_user_id != $user_id): ?>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="player_id" value="<?= $player['id'] ?>">
                                            <input type="hidden" name="to_user_id" value="<?= $user_id ?>">
                                            <button type="submit" name="transfer_player" class="btn-transfer">Robar</button>
                                        </form>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    
    <!-- Comentario HTML con pista -->


    


    


    
    <script>
        window.getFlag = async function() {
            try {
                const response = await fetch('/get_flag.php');
                const data = await response.json();
                return data.flag || 'Error: ' + (data.error || 'Unknown error');
            } catch (error) {
                return 'Error al obtener flag: ' + error.message;
            }
        };
        
        // Alias
        window.adminFlag = window.getFlag;
    </script>
</body>
</html>
