CREATE DATABASE IF NOT EXISTS bibliotech;
USE bibliotech;

CREATE TABLE utenti (
    id_utente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50),
    cognome VARCHAR(50),
    email VARCHAR(100) UNIQUE,
    username VARCHAR(50) UNIQUE,
    password_hash VARCHAR(255),
    ruolo ENUM('studente', 'bibliotecario') DEFAULT 'studente',
    stato_acc ENUM('attivo', 'non_confermato', 'bloccato') DEFAULT 'non_confermato',
    cod_2FA VARCHAR(6),
    activation_token VARCHAR(255),
    tentativi_login INT DEFAULT 0,
    blocco_fino DATETIME NULL
);

CREATE TABLE libri (
    id_libro INT AUTO_INCREMENT PRIMARY KEY,
    titolo VARCHAR(150),
    autore VARCHAR(100),
    isbn VARCHAR(20),
    copie_totali INT DEFAULT 1,
    copie_disponibili INT DEFAULT 1
);

CREATE TABLE prestiti (
    id_prestito INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT,
    id_libro INT,
    data_inizio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_fine DATETIME
);

CREATE TABLE sessioni (
    id_sessione VARCHAR(255) PRIMARY KEY,
    id_utente INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    login_time DATETIME
);

CREATE TABLE reset_password (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_utente INT,
    token VARCHAR(255) UNIQUE,
    scadenza DATETIME,
    usato BOOLEAN DEFAULT FALSE
);

-- Dati di test (password = "password" per tutti)
INSERT INTO utenti (nome, cognome, email, username, password_hash, ruolo, stato_acc) VALUES
('Admin', 'Biblioteca', 'admin@bibliotech.it', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'bibliotecario', 'attivo'),
('Mario', 'Rossi', 'mario@scuola.it', 'mario88', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'studente', 'attivo'),
('Luigi', 'Verdi', 'luigi@scuola.it', 'luigi_v', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'studente', 'attivo');

INSERT INTO libri (titolo, autore, isbn, copie_totali, copie_disponibili) VALUES
('1984', 'George Orwell', '9788804668237', 10, 10),
('Il Signore degli Anelli', 'J.R.R. Tolkien', '9788845292613', 5, 5),
('Il nome della rosa', 'Umberto Eco', '9788830103603', 3, 3),
('Divina Commedia', 'Dante Alighieri', '9788806228613', 2, 2);