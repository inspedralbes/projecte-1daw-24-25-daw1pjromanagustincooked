CREATE DATABASE IF NOT EXISTS tickets CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER IF NOT EXISTS 'usuari'@'%' IDENTIFIED BY 'paraula_de_pas';

GRANT ALL PRIVILEGES ON tickets.* TO 'usuari'@'%';
FLUSH PRIVILEGES;

USE tickets;

CREATE TABLE IF NOT EXISTS technicians (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100)
);

INSERT INTO technicians (name) VALUES ('Alice'), ('Bob'), ('Charlie');

CREATE TABLE IF NOT EXISTS incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    department VARCHAR(100),
    incident_date DATE,
    description TEXT,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    priority ENUM('Low', 'Medium', 'High') DEFAULT 'Medium',
    resolution_time VARCHAR(50),
    resolution_description TEXT,
    status ENUM('Waiting', 'In Process', 'Done') DEFAULT 'Waiting',
    technician_id INT,
    FOREIGN KEY (technician_id) REFERENCES technicians(id) ON DELETE SET NULL
);



