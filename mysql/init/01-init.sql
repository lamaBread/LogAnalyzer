CREATE DATABASE IF NOT EXISTS conversations;
USE conversations;

DROP TABLE IF EXISTS conversation;

CREATE TABLE conversation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    theme VARCHAR(20),
    font VARCHAR(50),
    font_size VARCHAR(10),
    log_path VARCHAR(255)
);
