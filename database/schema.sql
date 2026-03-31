-- =============================================================
-- Playlist App - Schema + Seed
-- MySQL 5.7 | utf8mb4
-- Usuário de teste: admin@teste.com / password
-- =============================================================

CREATE DATABASE IF NOT EXISTS playlist_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE playlist_db;

-- -------------------------------------------------------------
-- Tabelas
-- -------------------------------------------------------------

CREATE TABLE IF NOT EXISTS users (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name       VARCHAR(120) NOT NULL,
    email      VARCHAR(191) NOT NULL,
    password   VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS playlists (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id     INT UNSIGNED NOT NULL,
    name        VARCHAR(200) NOT NULL,
    description TEXT,
    cover_color VARCHAR(7) NOT NULL DEFAULT '#1DB954',
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_playlists_user (user_id),
    CONSTRAINT fk_playlists_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS tracks (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title      VARCHAR(255) NOT NULL,
    artist     VARCHAR(255) NOT NULL,
    album      VARCHAR(255),
    duration_s SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_tracks_artist (artist(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS playlist_tracks (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    playlist_id INT UNSIGNED NOT NULL,
    track_id    INT UNSIGNED NOT NULL,
    position    SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    added_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_playlist_track (playlist_id, track_id),
    KEY idx_pt_playlist (playlist_id),
    KEY idx_pt_track (track_id),
    CONSTRAINT fk_pt_playlist FOREIGN KEY (playlist_id) REFERENCES playlists(id) ON DELETE CASCADE,
    CONSTRAINT fk_pt_track    FOREIGN KEY (track_id)    REFERENCES tracks(id)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -------------------------------------------------------------
-- Seed — Usuário de teste
-- Senha: password (bcrypt)
-- -------------------------------------------------------------

INSERT INTO users (name, email, password) VALUES
('Admin Teste', 'admin@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- -------------------------------------------------------------
-- Seed — Tracks do catálogo
-- -------------------------------------------------------------

INSERT INTO tracks (title, artist, album, duration_s) VALUES
('Bohemian Rhapsody',       'Queen',              'A Night at the Opera',   354),
('Hotel California',        'Eagles',             'Hotel California',        391),
('Stairway to Heaven',      'Led Zeppelin',        'Led Zeppelin IV',         482),
('Smells Like Teen Spirit',  'Nirvana',            'Nevermind',               301),
('Imagine',                 'John Lennon',         'Imagine',                 187),
('Like a Rolling Stone',    'Bob Dylan',           'Highway 61 Revisited',    369),
('Purple Haze',             'Jimi Hendrix',        'Are You Experienced',     170),
('Johnny B. Goode',         'Chuck Berry',         'Chuck Berry Is on Top',   162),
('What''s Going On',        'Marvin Gaye',         'What''s Going On',        235),
('Superstition',            'Stevie Wonder',       'Talking Book',            245),
('Billie Jean',             'Michael Jackson',     'Thriller',                294),
('Sweet Child O'' Mine',    'Guns N'' Roses',      'Appetite for Destruction', 356),
('Comfortably Numb',        'Pink Floyd',          'The Wall',                382),
('Under Pressure',          'Queen & David Bowie', 'Hot Space',               248),
('Losing My Religion',      'R.E.M.',              'Out of Time',             269),
('Black',                   'Pearl Jam',           'Ten',                     336),
('Nothing Else Matters',    'Metallica',           'Metallica',               389),
('With or Without You',     'U2',                  'The Joshua Tree',         296),
('Creep',                   'Radiohead',           'Pablo Honey',             238),
('Mr. Brightside',          'The Killers',         'Hot Fuss',                222);

-- -------------------------------------------------------------
-- Seed — Playlists de exemplo para o usuário admin
-- -------------------------------------------------------------

INSERT INTO playlists (user_id, name, description, cover_color) VALUES
(1, 'Rock Clássico',   'Os maiores clássicos do rock',     '#E91429'),
(1, 'Anos 80',         'O melhor da década de ouro',       '#509BF5'),
(1, 'Para Trabalhar',  'Foco e produtividade',             '#1DB954');

-- -------------------------------------------------------------
-- Seed — Tracks nas playlists
-- -------------------------------------------------------------

-- Rock Clássico
INSERT INTO playlist_tracks (playlist_id, track_id, position) VALUES
(1, 1, 1), (1, 2, 2), (1, 3, 3), (1, 4, 4), (1, 7, 5), (1, 8, 6);

-- Anos 80
INSERT INTO playlist_tracks (playlist_id, track_id, position) VALUES
(2, 11, 1), (2, 12, 2), (2, 14, 3), (2, 18, 4);

-- Para Trabalhar
INSERT INTO playlist_tracks (playlist_id, track_id, position) VALUES
(3, 13, 1), (3, 17, 2), (3, 19, 3), (3, 20, 4);
