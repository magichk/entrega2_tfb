DROP TABLE IF EXISTS teams;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    is_admin INTEGER DEFAULT 0,
    points INTEGER DEFAULT 0,
    team_name TEXT,
    formation TEXT DEFAULT '4-3-3',
    budget REAL DEFAULT 100000000,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE teams (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    player_name TEXT NOT NULL,
    position TEXT NOT NULL,
    points INTEGER DEFAULT 0,
    market_value REAL DEFAULT 0,
    club TEXT,
    nationality TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

INSERT INTO users (username, password, is_admin, points, team_name, formation, budget) VALUES 
('admin', 'admin123', 1, 0, 'ADMIN TEAM', '4-4-2', 150000000),
('jugador1', 'pass123', 0, 1285, 'Los Galácticos', '4-3-3', 95000000),
('jugador2', 'pass456', 0, 1120, 'Dream Team FC', '3-5-2', 88000000),
('jugador3', 'pass789', 0, 980, 'Fantasy Stars', '4-2-3-1', 92000000);

-- Equipo de jugador1 (Los Galácticos)
INSERT INTO teams (user_id, player_name, position, points, market_value, club, nationality) VALUES
(2, 'Thibaut Courtois', 'POR', 185, 35000000, 'Real Madrid', 'Bélgica'),
(2, 'Dani Carvajal', 'DEF', 142, 25000000, 'Real Madrid', 'España'),
(2, 'Antonio Rüdiger', 'DEF', 158, 35000000, 'Real Madrid', 'Alemania'),
(2, 'David Alaba', 'DEF', 145, 30000000, 'Real Madrid', 'Austria'),
(2, 'Ferland Mendy', 'DEF', 138, 28000000, 'Real Madrid', 'Francia'),
(2, 'Federico Valverde', 'MED', 195, 100000000, 'Real Madrid', 'Uruguay'),
(2, 'Luka Modrić', 'MED', 172, 15000000, 'Real Madrid', 'Croacia'),
(2, 'Jude Bellingham', 'MED', 245, 150000000, 'Real Madrid', 'Inglaterra'),
(2, 'Vinícius Jr', 'DEL', 298, 180000000, 'Real Madrid', 'Brasil'),
(2, 'Kylian Mbappé', 'DEL', 285, 180000000, 'Real Madrid', 'Francia'),
(2, 'Rodrygo', 'DEL', 168, 80000000, 'Real Madrid', 'Brasil');

-- Equipo de jugador2 (Dream Team FC)
INSERT INTO teams (user_id, player_name, position, points, market_value, club, nationality) VALUES
(3, 'Ederson', 'POR', 195, 40000000, 'Man City', 'Brasil'),
(3, 'Kyle Walker', 'DEF', 148, 25000000, 'Man City', 'Inglaterra'),
(3, 'Rúben Dias', 'DEF', 175, 75000000, 'Man City', 'Portugal'),
(3, 'John Stones', 'DEF', 162, 50000000, 'Man City', 'Inglaterra'),
(3, 'Rodri', 'MED', 215, 120000000, 'Man City', 'España'),
(3, 'Kevin De Bruyne', 'MED', 235, 80000000, 'Man City', 'Bélgica'),
(3, 'Phil Foden', 'MED', 198, 110000000, 'Man City', 'Inglaterra'),
(3, 'Bernardo Silva', 'MED', 185, 80000000, 'Man City', 'Portugal'),
(3, 'Jack Grealish', 'MED', 155, 70000000, 'Man City', 'Inglaterra'),
(3, 'Erling Haaland', 'DEL', 312, 180000000, 'Man City', 'Noruega'),
(3, 'Julián Álvarez', 'DEL', 145, 70000000, 'Man City', 'Argentina');

-- Equipo de jugador3 (Fantasy Stars)
INSERT INTO teams (user_id, player_name, position, points, market_value, club, nationality) VALUES
(4, 'Marc-André ter Stegen', 'POR', 178, 35000000, 'Barcelona', 'Alemania'),
(4, 'Jules Koundé', 'DEF', 165, 60000000, 'Barcelona', 'Francia'),
(4, 'Ronald Araújo', 'DEF', 152, 70000000, 'Barcelona', 'Uruguay'),
(4, 'Alejandro Balde', 'DEF', 138, 40000000, 'Barcelona', 'España'),
(4, 'Frenkie de Jong', 'MED', 188, 80000000, 'Barcelona', 'Holanda'),
(4, 'Pedri', 'MED', 195, 100000000, 'Barcelona', 'España'),
(4, 'Gavi', 'MED', 165, 90000000, 'Barcelona', 'España'),
(4, 'Raphinha', 'DEL', 142, 60000000, 'Barcelona', 'Brasil'),
(4, 'Robert Lewandowski', 'DEL', 268, 45000000, 'Barcelona', 'Polonia'),
(4, 'Lamine Yamal', 'DEL', 185, 120000000, 'Barcelona', 'España'),
(4, 'Ferran Torres', 'DEL', 128, 40000000, 'Barcelona', 'España');
