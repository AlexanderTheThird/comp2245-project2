DROP DATABASE IF EXISTS helpdesk;
CREATE DATABASE helpdesk CHARACTER SET utf8mb4;
USE helpdesk;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'member') NOT NULL DEFAULT 'member',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    priority ENUM('Low', 'Medium', 'High') NOT NULL DEFAULT 'Medium',
    status ENUM('Open', 'In progress', 'Closed') NOT NULL DEFAULT 'Open',
    created_by INT UNSIGNED NOT NULL,
    assigned_to INT UNSIGNED NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_tickets_created_by FOREIGN KEY (created_by)
        REFERENCES users(id),
    CONSTRAINT fk_tickets_assigned_to FOREIGN KEY (assigned_to)
        REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    body TEXT NOT NULL,
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notes_ticket FOREIGN KEY (ticket_id)
        REFERENCES tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_notes_created_by FOREIGN KEY (created_by)
        REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO users (firstname, lastname, email, password, role) VALUES
('Helpdesk', 'Admin', 'admin@fixit.test', '$2y$12$BKS4qhFXEooc44EAmtXlJ.740rNtmG7OZS4KJt7IeCwAhGrmMTd6m', 'admin'),
('Joshua', 'Hannays', 'hannays@fixit.test', '$2y$12$BKS4qhFXEooc44EAmtXlJ.740rNtmG7OZS4KJt7IeCwAhGrmMTd6m', 'member'),
('Naaryah', 'Naraine', 'naraine@fixit.test', '$2y$12$BKS4qhFXEooc44EAmtXlJ.740rNtmG7OZS4KJt7IeCwAhGrmMTd6m', 'member'),
('Jonathan', 'Payne', 'payne@fixit.test', '$2y$12$BKS4qhFXEooc44EAmtXlJ.740rNtmG7OZS4KJt7IeCwAhGrmMTd6m', 'member'),
('Michael', 'Ramharack', 'ramharack@fixit.test', '$2y$12$BKS4qhFXEooc44EAmtXlJ.740rNtmG7OZS4KJt7IeCwAhGrmMTd6m', 'member'),
('Britannia', 'Bucknall', 'bucknall@fixit.test', '$2y$12$BKS4qhFXEooc44EAmtXlJ.740rNtmG7OZS4KJt7IeCwAhGrmMTd6m', 'member');

INSERT INTO tickets (title, description, priority, status, created_by, assigned_to) VALUES
('Dashboard Disabled', 'Password is accepted but the dashboard does not load.', 'High', 'Open', 2, NULL),
('Printer out of paper', 'Office printer needs its paper tray refilled.', 'Low', 'Closed', 3, 2),
('Internet inconsistency', 'The wifi frequently drops throughout the day.', 'Medium', 'In progress', 2, 3),
('Email not syncing', 'New emails are not appearing in the inbox.', 'High', 'In progress', 3, 1),
('Update profile name', 'Request to correct a display name in the system.', 'Low', 'Open', 2, NULL);

INSERT INTO notes (ticket_id, body, created_by) VALUES
(1, 'Reported after attempting to sign in twice.', 2),
(1, 'Investigating authentication and session handling.', 1),
(3, 'Restarted the access point and will monitor the connection.', 3);

CREATE USER IF NOT EXISTS 'comp2245'@'localhost' IDENTIFIED BY 'comp2245-demo';
ALTER USER 'comp2245'@'localhost' IDENTIFIED BY 'comp2245-demo';
GRANT SELECT, INSERT, UPDATE, DELETE ON helpdesk.* TO 'comp2245'@'localhost';