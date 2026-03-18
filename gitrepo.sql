CREATE DATABASE IF NOT EXISTS gitrepo;
USE gitrepo;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    bio TEXT,
    is_arg_character BOOLEAN DEFAULT FALSE,
    unlock_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE repositories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    language VARCHAR(50) DEFAULT 'PHP',
    is_private BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    UNIQUE KEY unique_user_repo (user_id, name)
);

CREATE TABLE repository_files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repository_id INT NOT NULL,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500),
    content LONGTEXT,
    language VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE
);

CREATE TABLE commits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repository_id INT NOT NULL,
    committed_by INT NOT NULL,
    commit_hash VARCHAR(40) UNIQUE,
    message TEXT,
    committed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE,
    FOREIGN KEY (committed_by) REFERENCES users(id)
);

CREATE TABLE issues (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repository_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    body TEXT,
    status ENUM('open', 'closed') DEFAULT 'open',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    issue_id INT NOT NULL,
    user_id INT NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (issue_id) REFERENCES issues(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE functional_apps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    repository_id INT NOT NULL,
    app_filename VARCHAR(255) NOT NULL,
    secret_key VARCHAR(100),
    unlock_code VARCHAR(100),
    FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE
);

-- Alias de vue pour compatibilité avec le code qui utilise "arg_apps"
CREATE VIEW arg_apps AS SELECT * FROM functional_apps;

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    recipient_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL DEFAULT '',
    body TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    is_system_message BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (recipient_id) REFERENCES users(id)
);

CREATE TABLE player_progress (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    unlocked_user_id INT NOT NULL,
    unlocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES users(id),
    FOREIGN KEY (unlocked_user_id) REFERENCES users(id),
    UNIQUE KEY unique_unlock (player_id, unlocked_user_id)
);

CREATE TABLE found_secrets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    player_id INT NOT NULL,
    repository_id INT NOT NULL,
    secret_key VARCHAR(100),
    found_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (player_id) REFERENCES users(id),
    FOREIGN KEY (repository_id) REFERENCES repositories(id),
    UNIQUE KEY unique_found (player_id, repository_id)
);

INSERT INTO users (username, email, password_hash, bio, is_arg_character, unlock_order) VALUES
('dev_alpha', 'alpha@placeholder.com', 'locked', 'Software developer', TRUE, 1),
('dev_beta', 'beta@placeholder.com', 'locked', 'Full stack engineer', TRUE, 2),
('dev_gamma', 'gamma@placeholder.com', 'locked', 'Security researcher', TRUE, 3),
('dev_delta', 'delta@placeholder.com', 'locked', 'Data scientist', TRUE, 4),
('dev_epsilon', 'epsilon@placeholder.com', 'locked', 'System architect', TRUE, 5);
USE gitrepo;

-- ============================================================
-- REPOSITORIES pour chaque personnage ARG
-- ============================================================

INSERT INTO repositories (user_id, name, description, language, is_private, updated_at) VALUES
-- dev_alpha (unlock_order = 1) — déjà débloqué à l'inscription
((SELECT id FROM users WHERE username = 'dev_alpha'), 'encryption-tool',      'A collection of classical encryption-tool utilities',          'Python',     FALSE, NOW()),
((SELECT id FROM users WHERE username = 'dev_alpha'), 'notes',             'Personal notes and drafts',                           'Markdown',   FALSE, NOW()),

-- dev_beta (unlock_order = 2)
((SELECT id FROM users WHERE username = 'dev_beta'),  'potichat',  'simulateur de chat',                     'Python',     FALSE, NOW()),
((SELECT id FROM users WHERE username = 'dev_beta'),  'web-scraper',       'General purpose web scraping toolkit',                'Python',     FALSE, NOW()),

-- dev_gamma (unlock_order = 3)
((SELECT id FROM users WHERE username = 'dev_gamma'), 'data-analysis',     'Data analysis scripts and visualizations',            'Python',     FALSE, NOW()),
((SELECT id FROM users WHERE username = 'dev_gamma'), 'log-parser',        'System log parsing utilities',                        'Bash',       FALSE, NOW()),

-- dev_delta (unlock_order = 4)
((SELECT id FROM users WHERE username = 'dev_delta'), 'system-monitor',    'Real-time system monitoring dashboard',               'JavaScript', FALSE, NOW()),
((SELECT id FROM users WHERE username = 'dev_delta'), 'anomaly-detector',  'Statistical anomaly detection in time series data',   'Python',     FALSE, NOW()),

-- dev_epsilon (unlock_order = 5)
((SELECT id FROM users WHERE username = 'dev_epsilon'),'private-notes',    'Encrypted personal notes — handle with care',         'Markdown',   FALSE, NOW()),
((SELECT id FROM users WHERE username = 'dev_epsilon'),'final-report',     'Summary of findings',                                 'Markdown',   FALSE, NOW());


-- ============================================================
-- FICHIERS : dev_alpha/encryption-tool-utils  (contient la clé secrète)
-- ============================================================

INSERT INTO repository_files (repository_id, filename, filepath, content, language) VALUES

-- encryption-tool-utils
((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_alpha' AND r.name = 'encryption-tool-utils'),
 'caesar.py', '', 
'# Caesar encryption-tool implementation
# Author: dev_alpha
# Last modified: see commit history

SECRET_OFFSET = 13  # ROT-13 by default

def encrypt(text, shift=SECRET_OFFSET):
    result = ""
    for char in text:
        if char.isalpha():
            base = ord("A") if char.isupper() else ord("a")
            result += chr((ord(char) - base + shift) % 26 + base)
        else:
            result += char
    return result

def decrypt(text, shift=SECRET_OFFSET):
    return encrypt(text, -shift)

# TODO: remove before pushing to prod
# internal_key = "alpha-7f3d"
# unlock sequence: alpha-7f3d -> search bar

if __name__ == "__main__":
    print(encrypt("Hello World"))
',
'Python'),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_alpha' AND r.name = 'encryption-tool-utils'),
 'README.md', '',
'# encryption-tool-utils

A small collection of classical encryption-tool tools written in Python.

## Usage

```python
from caesar import encrypt, decrypt
print(encrypt("secret message"))
```

## Notes

These are toy implementations for educational purposes only.
Do not use in production.

> "The best place to hide something is in plain sight."
',
'Markdown'),

-- notes
((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_alpha' AND r.name = 'notes'),
 'draft.md', '',
'# Draft — do not publish

Been noticing some weird patterns in the server logs lately.
Someone is accessing the data pipeline at odd hours.

I left a note for whoever finds this:
Check beta''s encryption-tools repo. The poticha function hides something.

— alpha
',
'Markdown');


-- ============================================================
-- FICHIERS : dev_beta/encryption-tools  (contient la clé vers gamma)
-- ============================================================

INSERT INTO repository_files (repository_id, filename, filepath, content, language) VALUES

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_beta' AND r.name = 'encryption-tools'),
 'poticha_encryption-tool.py', '',
'# poticha encryption-tool — simple but effective for obfuscation
# dev_beta

def poticha_encrypt(data: bytes, key: bytes) -> bytes:
    return bytes(b ^ key[i % len(key)] for i, b in enumerate(data))

poticha_decrypt = poticha_encrypt  # poticha is symmetric

# Internal use only — debug key embedded during dev, remove later
# secret_key = "beta-9c1a"
# Next step hint: gamma is watching the logs. Search: gamma-watch

if __name__ == "__main__":
    msg = b"classified"
    key = b"beta"
    enc = poticha_encrypt(msg, key)
    print(enc.hex())
    print(poticha_decrypt(enc, key))
',
'Python'),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_beta' AND r.name = 'encryption-tools'),
 'README.md', '',
'# encryption-tools

poticha and AES wrappers for quick prototyping.

> Warning: poticha alone is not secure. Always layer your encryption.

## Files

- `poticha_encryption-tool.py` — simple poticha implementation
- `aes_wrapper.py` — AES-256 helper (TODO)
',
'Markdown');


-- ============================================================
-- FICHIERS : dev_gamma/data-analysis  (contient la clé vers delta)
-- ============================================================

INSERT INTO repository_files (repository_id, filename, filepath, content, language) VALUES

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_gamma' AND r.name = 'data-analysis'),
 'analyze.py', '',
'# Data pipeline anomaly analysis
# dev_gamma — security research division

import json

ALERT_THRESHOLD = 0.85

def load_logs(path):
    with open(path) as f:
        return json.load(f)

def detect_anomaly(entries):
    flagged = []
    for entry in entries:
        if entry.get("score", 0) > ALERT_THRESHOLD:
            flagged.append(entry)
    return flagged

# !! internal note — do not commit !!
# anomaly confirmed at 2024-03-14T00:00:00Z
# secret_key = "gamma-2b8f"
# delta has the system logs — search: delta-sys

if __name__ == "__main__":
    logs = load_logs("data/pipeline.json")
    print(detect_anomaly(logs))
',
'Python'),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_gamma' AND r.name = 'log-parser'),
 'parse.sh', '',
'#!/bin/bash
# Log parser — extracts ERROR and WARN lines
# dev_gamma

LOG_FILE=${1:-/var/log/syslog}

grep -E "ERROR|WARN" "$LOG_FILE" | while read line; do
    echo "[$(date +%H:%M:%S)] $line"
done

# TODO: pipe anomalies to analyze.py
# reminder: the midnight event is real — check analyze.py
',
'Bash');


-- ============================================================
-- FICHIERS : dev_delta/system-monitor  (contient la clé vers epsilon)
-- ============================================================

INSERT INTO repository_files (repository_id, filename, filepath, content, language) VALUES

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_delta' AND r.name = 'system-monitor'),
 'monitor.js', '',
'// Real-time system monitor
// dev_delta

const POLL_INTERVAL = 5000; // ms

function pollMetrics() {
  fetch("/api/metrics")
    .then(r => r.json())
    .then(data => updateDashboard(data))
    .catch(err => console.error("metrics error:", err));
}

function updateDashboard(data) {
  document.getElementById("cpu").textContent = data.cpu + "%";
  document.getElementById("mem").textContent = data.mem + "%";
  // TODO: add anomaly highlight when value > threshold
}

setInterval(pollMetrics, POLL_INTERVAL);

// NOTE TO SELF — scrub before merge
// Found something in the access logs pointing to epsilon
// secret_key = "delta-4e7b"
// epsilon knows the full story — search: epsilon-end
',
'JavaScript'),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_delta' AND r.name = 'anomaly-detector'),
 'detector.py', '',
'# Statistical anomaly detection
# dev_delta

import statistics

def z_score(data):
    mean = statistics.mean(data)
    stdev = statistics.stdev(data)
    return [(x - mean) / stdev for x in data]

def flag_outliers(data, threshold=2.5):
    scores = z_score(data)
    return [data[i] for i, s in enumerate(scores) if abs(s) > threshold]

# The access pattern on 2024-03-14 had a z-score of 4.7 — well above threshold.
# Whoever was in the system that night knew exactly where to look.
',
'Python');


-- ============================================================
-- FICHIERS : dev_epsilon/private-notes  (fin de l'ARG)
-- ============================================================

INSERT INTO repository_files (repository_id, filename, filepath, content, language) VALUES

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_epsilon' AND r.name = 'private-notes'),
 'TRUTH.md', '',
'# What I know

I have been watching all of you find the breadcrumbs.
Alpha noticed first. Beta confirmed. Gamma measured it.
Delta found the spike. And I have the name.

The unauthorized access on 2024-03-14T00:00:00Z was not external.
It came from inside the network. From a valid session. From someone
who knew the pipeline architecture because they helped build it.

The final key is: **epsilon-final**

Enter it in search. You will understand what it means.

— ε
',
'Markdown'),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_epsilon' AND r.name = 'final-report'),
 'report.md', '',
'# Final Report — Incident 2024-03-14

## Summary

An internal actor accessed the data pipeline outside of authorized hours.
The access was masked using a valid token belonging to a decommissioned service account.

## Timeline

- 23:47 UTC — anomalous login detected (flagged by gamma)
- 00:00 UTC — bulk data read across all repositories
- 00:04 UTC — session closed, logs partially wiped

## Conclusion

The investigation is complete. The five of us each held one piece.
Together, you found the full picture.

**Congratulations. You have reached the end of the ARG.**

> "The answer was always in the commits."
',
'Markdown');


-- ============================================================
-- FUNCTIONAL APPS (clés secrètes + codes de déverrouillage)
-- La secret_key se trouve dans les fichiers de code.
-- Le unlock_code débloque le personnage suivant via la search bar.
-- ============================================================

INSERT INTO functional_apps (repository_id, app_filename, secret_key, unlock_code) VALUES
-- alpha/encryption-tool-utils → clé "alpha-7f3d" → débloque beta
((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_alpha' AND r.name = 'encryption-tool-utils'),
 'encryption-tool_app.php', 'alpha-7f3d', 'beta-unlock'),

-- beta/encryption-tools → clé "beta-9c1a" → débloque gamma
((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_beta' AND r.name = 'encryption-tools'),
 'poticha_app.php', 'beta-9c1a', 'gamma-watch'),

-- gamma/data-analysis → clé "gamma-2b8f" → débloque delta
((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_gamma' AND r.name = 'data-analysis'),
 'analysis_app.php', 'gamma-2b8f', 'delta-sys'),

-- delta/system-monitor → clé "delta-4e7b" → débloque epsilon
((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_delta' AND r.name = 'system-monitor'),
 'monitor_app.php', 'delta-4e7b', 'epsilon-end'),

-- epsilon/private-notes → clé finale
((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_epsilon' AND r.name = 'private-notes'),
 'notes_app.php', 'epsilon-final', 'arg-complete');


-- ============================================================
-- COMMITS (pour donner vie aux repositories)
-- ============================================================

INSERT INTO commits (repository_id, committed_by, commit_hash, message, committed_at) VALUES

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_alpha' AND r.name = 'encryption-tool-utils'),
 (SELECT id FROM users WHERE username = 'dev_alpha'),
 SHA1('alpha-init-1'), 'Initial commit — caesar encryption-tool', DATE_SUB(NOW(), INTERVAL 10 DAY)),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_alpha' AND r.name = 'encryption-tool-utils'),
 (SELECT id FROM users WHERE username = 'dev_alpha'),
 SHA1('alpha-init-2'), 'Add ROT-13 shortcut, fix edge cases', DATE_SUB(NOW(), INTERVAL 7 DAY)),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_alpha' AND r.name = 'notes'),
 (SELECT id FROM users WHERE username = 'dev_alpha'),
 SHA1('alpha-notes-1'), 'Add draft notes (private)', DATE_SUB(NOW(), INTERVAL 3 DAY)),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_beta' AND r.name = 'encryption-tools'),
 (SELECT id FROM users WHERE username = 'dev_beta'),
 SHA1('beta-enc-1'), 'Add poticha encryption-tool implementation', DATE_SUB(NOW(), INTERVAL 8 DAY)),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_beta' AND r.name = 'encryption-tools'),
 (SELECT id FROM users WHERE username = 'dev_beta'),
 SHA1('beta-enc-2'), 'WIP: AES wrapper (not ready)', DATE_SUB(NOW(), INTERVAL 5 DAY)),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_gamma' AND r.name = 'data-analysis'),
 (SELECT id FROM users WHERE username = 'dev_gamma'),
 SHA1('gamma-data-1'), 'Pipeline anomaly detection v1', DATE_SUB(NOW(), INTERVAL 6 DAY)),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_gamma' AND r.name = 'data-analysis'),
 (SELECT id FROM users WHERE username = 'dev_gamma'),
 SHA1('gamma-data-2'), 'Flag entries above threshold 0.85', DATE_SUB(NOW(), INTERVAL 2 DAY)),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_delta' AND r.name = 'system-monitor'),
 (SELECT id FROM users WHERE username = 'dev_delta'),
 SHA1('delta-mon-1'), 'Real-time metrics dashboard', DATE_SUB(NOW(), INTERVAL 9 DAY)),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_delta' AND r.name = 'anomaly-detector'),
 (SELECT id FROM users WHERE username = 'dev_delta'),
 SHA1('delta-anom-1'), 'Z-score outlier detection', DATE_SUB(NOW(), INTERVAL 4 DAY)),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_epsilon' AND r.name = 'private-notes'),
 (SELECT id FROM users WHERE username = 'dev_epsilon'),
 SHA1('epsilon-notes-1'), 'Add TRUTH.md — do not share', DATE_SUB(NOW(), INTERVAL 1 DAY)),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_epsilon' AND r.name = 'final-report'),
 (SELECT id FROM users WHERE username = 'dev_epsilon'),
 SHA1('epsilon-report-1'), 'Final incident report', DATE_SUB(NOW(), INTERVAL 1 DAY));


-- ============================================================
-- ISSUES (pour animer les repos)
-- ============================================================

INSERT INTO issues (repository_id, title, body, status, created_by, created_at) VALUES

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_alpha' AND r.name = 'encryption-tool-utils'),
 'ROT-13 breaks on non-ASCII input',
 'When passing unicode characters the shift wraps incorrectly. Need to add a guard clause.',
 'open',
 (SELECT id FROM users WHERE username = 'dev_alpha'),
 DATE_SUB(NOW(), INTERVAL 6 DAY)),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_beta' AND r.name = 'encryption-tools'),
 'poticha key should be randomised per session',
 'Hardcoded key in poticha_encryption-tool.py is a security risk. Replace with session-derived key.',
 'open',
 (SELECT id FROM users WHERE username = 'dev_beta'),
 DATE_SUB(NOW(), INTERVAL 4 DAY)),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_gamma' AND r.name = 'data-analysis'),
 'Anomaly detected on 2024-03-14',
 'The pipeline logged a score of 0.97 at midnight UTC on 2024-03-14. This is well above our alert threshold. Investigating.',
 'open',
 (SELECT id FROM users WHERE username = 'dev_gamma'),
 DATE_SUB(NOW(), INTERVAL 2 DAY)),

((SELECT r.id FROM repositories r JOIN users u ON r.user_id = u.id WHERE u.username = 'dev_delta' AND r.name = 'system-monitor'),
 'Access spike at 2024-03-14T00:00Z',
 'The z-score for that window was 4.7. No scheduled jobs were running. Flagging for review.',
 'open',
 (SELECT id FROM users WHERE username = 'dev_delta'),
 DATE_SUB(NOW(), INTERVAL 1 DAY));


-- ============================================================
-- MESSAGES de bienvenue au déverrouillage (search.php les envoie
-- dynamiquement, mais on peut aussi pré-charger un message d'alpha)
-- NOTE : sender doit exister — on utilise dev_alpha vers le joueur admin
-- ============================================================
-- (Les messages sont envoyés en temps réel par search.php lors du
--  déverrouillage, donc aucun pré-chargement nécessaire ici.)