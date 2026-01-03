-- 1. D'abord créer les tables sans dépendances
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Créer repositories SANS la colonne victim_id d'abord
CREATE TABLE repositories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- 3. Créer victims APRES repositories
CREATE TABLE victims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    role VARCHAR(100),
    disappearance_date DATE,
    last_seen_location VARCHAR(255),
    bio TEXT,
    profile_image VARCHAR(255),
    repository_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repository_id) REFERENCES repositories(id)
);

-- 4. MAINTENANT ajouter victim_id à repositories
ALTER TABLE repositories ADD COLUMN victim_id INT;
ALTER TABLE repositories ADD FOREIGN KEY (victim_id) REFERENCES victims(id);

-- 5. Créer les autres tables qui dépendent de repositories
CREATE TABLE commits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repository_id INT,
    commit_hash VARCHAR(40) UNIQUE NOT NULL,
    message TEXT,
    committed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repository_id) REFERENCES repositories(id)
);

CREATE TABLE issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repository_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repository_id) REFERENCES repositories(id)
);

CREATE TABLE pull_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repository_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repository_id) REFERENCES repositories(id)
);

-- 6. Maintenant créer branches (après users et repositories)
CREATE TABLE branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repository_id INT,
    name VARCHAR(100) NOT NULL,
    is_validated BOOLEAN DEFAULT FALSE,
    validation_code VARCHAR(50) UNIQUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    corrected_code LONGTEXT,
    is_correct BOOLEAN DEFAULT FALSE,
    test_result TEXT,
    investigation_notes TEXT,
    FOREIGN KEY (repository_id) REFERENCES repositories(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE user_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    repository_id INT,
    is_unlocked BOOLEAN DEFAULT FALSE,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (repository_id) REFERENCES repositories(id),
    UNIQUE(user_id, repository_id)
);

-- 7. Les autres tables
CREATE TABLE clues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repository_id INT,
    clue_type ENUM('code', 'commit', 'issue', 'file', 'log', 'hidden') NOT NULL,
    title VARCHAR(200),
    content TEXT NOT NULL,
    found_by INT,
    found_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_crucial BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (repository_id) REFERENCES repositories(id),
    FOREIGN KEY (found_by) REFERENCES users(id)
);

CREATE TABLE journal_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    entry_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    title VARCHAR(200),
    content TEXT,
    is_private BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE suspects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    description TEXT,
    motive TEXT,
    alibi TEXT,
    is_guilty BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE repository_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repository_id INT,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500),
    content LONGTEXT,
    language VARCHAR(50),
    is_suspicious BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (repository_id) REFERENCES repositories(id)
);

CREATE TABLE exercises (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repository_id INT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    initial_code LONGTEXT NOT NULL,
    expected_output TEXT,
    solution LONGTEXT,
    language VARCHAR(20) DEFAULT 'php',
    difficulty ENUM('easy', 'medium', 'hard') DEFAULT 'easy',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repository_id) REFERENCES repositories(id)
);

CREATE TABLE tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exercise_id INT,
    input_data TEXT,
    expected_output TEXT,
    test_type ENUM('function', 'output', 'comparison') DEFAULT 'output',
    FOREIGN KEY (exercise_id) REFERENCES exercises(id)
);

CREATE TABLE clue_connections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clue_id_1 INT,
    clue_id_2 INT,
    connection_type VARCHAR(50),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (clue_id_1) REFERENCES clues(id),
    FOREIGN KEY (clue_id_2) REFERENCES clues(id)
);

CREATE TABLE repository_access_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    repository_id INT,
    accessed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    time_spent INT,
    clues_found_during_access INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (repository_id) REFERENCES repositories(id)
);

CREATE TABLE hidden_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repository_id INT,
    message_type ENUM('console', 'comment', 'metadata', 'binary'),
    message_content TEXT NOT NULL,
    hint TEXT,
    unlock_condition TEXT,
    is_found BOOLEAN DEFAULT FALSE,
    found_by INT,
    found_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repository_id) REFERENCES repositories(id),
    FOREIGN KEY (found_by) REFERENCES users(id)
);