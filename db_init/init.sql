CREATE DATABASE IF NOT EXISTS tickets CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'usuari'@'%' IDENTIFIED BY 'paraula_de_pas';

GRANT ALL PRIVILEGES ON tickets.* TO 'usuari'@'%';
FLUSH PRIVILEGES;

USE tickets;

CREATE TABLE IF NOT EXISTS incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(100),
    incident_date DATE,
    description TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
