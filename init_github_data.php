<?php
// init_github_data.php
include 'config.php';

echo "<pre>Creating GitHub-like data for 5 disappeared developers...\n\n";

try {
    // 1. Créer 5 utilisateurs (développeurs disparus)
    $developers = [
        [
            'username' => 'perso1',
            'email' => '1@example.com',
            'full_name' => 'personnage 1',
            'role' => '...',
            'bio' => '...',
            'disappearance_date' => '2023-12-12'
        ],
        [
            'username' => 'perso2',
            'email' => '2@example.com',
            'full_name' => 'personnage 2',
            'role' => '...',
            'bio' => '...',
            'disappearance_date' => '2023-12-18'
        ],
        [
            'username' => 'perso3',
            'email' => '3@example.com',
            'full_name' => 'personnage 3',
            'role' => '...',
            'bio' => '...',
            'disappearance_date' => '2023-12-24'
        ],
        [
            'username' => 'perso4',
            'email' => '4@example.com',
            'full_name' => 'personnage 4',
            'role' => '...',
            'bio' => '  ...',
            'disappearance_date' => '2023-12-30'
        ],
        [
            'username' => 'perso5',
            'email' => '5@example.com',
            'full_name' => 'personnage 5',
            'role' => ' ...',
            'bio' => '  ...',
            'disappearance_date' => '2024-01-05'
        ]
    ];
    
    $developer_ids = [];
    foreach ($developers as $dev) {
        // Vérifier si l'utilisateur existe déjà
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$dev['username']]);
        
        if (!$stmt->fetch()) {
            $password_hash = password_hash('password123', PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$dev['username'], $dev['email'], $password_hash]);
            $user_id = $pdo->lastInsertId();
            echo "✓ Created user: {$dev['username']}\n";
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
            $stmt->execute([$dev['username']]);
            $user_id = $stmt->fetch()['id'];
            echo "✓ User exists: {$dev['username']}\n";
        }
        
        $developer_ids[$dev['username']] = $user_id;
    }
    
    // 2. Créer des repositories pour chaque développeur
    $repositories = [
        [
            'name' => 'secure-chat',
            'description' => 'End-to-end encrypted messaging application',
            'owner' => 'alexchen',
            'files' => [
                [
                    'filename' => 'server.js',
                    'content' => '// Secure Chat Server
const express = require("express");
const crypto = require("crypto");

const app = express();
app.use(express.json());

// In-memory message store (not for production!)
let messages = [];

// Generate key pair for new users
function generateKeyPair() {
    return crypto.generateKeyPairSync("rsa", {
        modulusLength: 2048,
        publicKeyEncoding: { type: "spki", format: "pem" },
        privateKeyEncoding: { type: "pkcs8", format: "pem" }
    });
}

// Encrypt message with recipient\'s public key
function encryptMessage(message, publicKey) {
    const buffer = Buffer.from(message, "utf8");
    const encrypted = crypto.publicEncrypt(publicKey, buffer);
    return encrypted.toString("base64");
}

// TODO: Implement message queue system
// NOTE: Need to fix vulnerability in key exchange - check with Marcus

console.log("Secure Chat Server running on port 3000");',
                    'language' => 'javascript',
                    'suspicious' => true
                ],
                [
                    'filename' => 'README.md',
                    'content' => '# Secure Chat Application

A real-time messaging application with end-to-end encryption.

## Features
- AES-256 encryption for messages
- RSA key exchange
- Message persistence
- User authentication

## Security Notes
The current implementation has a potential vulnerability in the key exchange protocol. Need to review with the security team.

## Installation
```bash
npm install
npm start
```',
                    'language' => 'markdown',
                    'suspicious' => false
                ]
            ],
            'commits' => [
                ['Initial commit', '2023-11-15 10:30:00'],
                ['Added encryption module', '2023-11-20 14:22:00'],
                ['Fixed key exchange bug', '2023-12-10 23:45:00'],
                ['Emergency security patch', '2023-12-11 00:15:00']
            ]
        ],
        [
            'name' => 'anomaly-detector',
            'description' => 'Machine learning system for detecting unusual patterns in data streams',
            'owner' => 'sarahm',
            'files' => [
                [
                    'filename' => 'detector.py',
                    'content' => '# Anomaly Detection System
import numpy as np
from sklearn.ensemble import IsolationForest
import pandas as pd

class AnomalyDetector:
    def __init__(self):
        self.model = IsolationForest(contamination=0.1, random_state=42)
        self.is_trained = False
        
    def train(self, data):
        """Train the model on normal data"""
        print("Training anomaly detector...")
        self.model.fit(data)
        self.is_trained = True
        print("Training complete")
        
        # Log anomaly: training data contains unexpected patterns
        # TODO: Investigate data source - some entries seem artificially generated
        
    def predict(self, data):
        """Predict anomalies in new data"""
        if not self.is_trained:
            raise Exception("Model not trained")
            
        predictions = self.model.predict(data)
        return predictions == -1

# Main execution
if __name__ == "__main__":
    detector = AnomalyDetector()
    
    # Load training data
    # WARNING: Data file location changed - check config.ini
    data = pd.read_csv("data/training_set.csv")
    
    detector.train(data.values)
    
    print("System ready for anomaly detection")',
                    'language' => 'python',
                    'suspicious' => true
                ]
            ],
            'commits' => [
                ['Initial ML implementation', '2023-11-10 09:15:00'],
                ['Added data preprocessing', '2023-11-25 16:40:00'],
                ['Fixed false positive rate', '2023-12-15 11:20:00'],
                ['Updated training dataset', '2023-12-17 22:30:00']
            ]
        ]
    ];
    
    // Créer les repositories et les fichiers
    foreach ($repositories as $repo_data) {
        $owner_id = $developer_ids[$repo_data['owner']];
        
        // Créer le repository
        $stmt = $pdo->prepare("INSERT INTO repositories (name, description, user_id) VALUES (?, ?, ?)");
        $stmt->execute([$repo_data['name'], $repo_data['description'], $owner_id]);
        $repo_id = $pdo->lastInsertId();
        
        echo "✓ Created repository: {$repo_data['name']}\n";
        
        // Créer les fichiers
        foreach ($repo_data['files'] as $file) {
            $stmt = $pdo->prepare("
                INSERT INTO repository_files (repository_id, filename, content, language, is_suspicious) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$repo_id, $file['filename'], $file['content'], $file['language'], $file['suspicious']]);
        }
        
        // Créer les commits
        foreach ($repo_data['commits'] as $commit) {
            $hash = bin2hex(random_bytes(8));
            $stmt = $pdo->prepare("
                INSERT INTO commits (repository_id, commit_hash, message, committed_at) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$repo_id, $hash, $commit[0], $commit[1]]);
        }
        
        // Créer la victime correspondante
        $dev_key = array_search($repo_data['owner'], array_column($developers, 'username'));
        if ($dev_key !== false) {
            $dev = $developers[$dev_key];
            $stmt = $pdo->prepare("
                INSERT INTO victims (name, role, disappearance_date, bio, repository_id) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$dev['full_name'], $dev['role'], $dev['disappearance_date'], $dev['bio'], $repo_id]);
            
            // Lier le repository à la victime
            $stmt = $pdo->prepare("UPDATE repositories SET victim_id = ? WHERE id = ?");
            $stmt->execute([$pdo->lastInsertId(), $repo_id]);
        }
    }
    
    echo "\n✅ Data initialization complete!\n";
    echo "You can now:\n";
    echo "1. Go to index.php\n";
    echo "2. Sign up for a new account\n";
    echo "3. Explore the repositories of disappeared developers\n";
    echo "4. Look for clues in their code\n";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
// Ajouter plus de fichiers aux repositories existants
$additional_files = [
    [
        'repo_name' => 'secure-chat',
        'files' => [
            [
                'filename' => 'encryption.js',
                'content' => '// Encryption module - SECURITY ISSUE FOUND!
function encryptMessage(message, key) {
    // WARNING: Using weak encryption algorithm
    // TODO: Upgrade to AES-256
    let result = "";
    for (let i = 0; i < message.length; i++) {
        result += String.fromCharCode(message.charCodeAt(i) ^ key.charCodeAt(i % key.length));
    }
    return btoa(result);
}

function decryptMessage(encrypted, key) {
    let decoded = atob(encrypted);
    let result = "";
    for (let i = 0; i < decoded.length; i++) {
        result += String.fromCharCode(decoded.charCodeAt(i) ^ key.charCodeAt(i % key.length));
    }
    return result;
}

// Note to self: Marcus suggested using bcrypt instead
// But deadline is tomorrow...',
                'language' => 'javascript',
                'suspicious' => true
            ],
            [
                'filename' => 'config.ini',
                'content' => '; Configuration file
[server]
host = localhost
port = 3000

[database]
; Database connection - MOVED TO EXTERNAL FILE
; See secrets.txt for credentials (temporary solution)

[security]
encryption_key = "weak_key_123"
session_timeout = 3600

; TODO: Remove debug mode before production
debug = true',
                'language' => 'ini',
                'suspicious' => true
            ]
        ]
    ],
    [
        'repo_name' => 'anomaly-detector',
        'files' => [
            [
                'filename' => 'data_loader.py',
                'content' => '# Data loading module
import pandas as pd
import numpy as np

def load_sensitive_data(filepath):
    """Load financial transaction data"""
    data = pd.read_csv(filepath)
    
    # Debug: print first few suspicious transactions
    suspicious = data[data["amount"] > 10000]
    if len(suspicious) > 0:
        print(f"Found {len(suspicious)} suspicious transactions")
        # TODO: Remove this debug output before production
        print(suspicious[["account_id", "amount", "destination"]].head())
    
    # ANOMALY: Some transactions have negative IDs
    # Investigate with Sarah next week
    negative_ids = data[data["account_id"] < 0]
    if len(negative_ids) > 0:
        print("WARNING: Negative account IDs detected!")
    
    return data

# Temporary backdoor for debugging - REMOVE BEFORE DEPLOYMENT
def get_all_transactions():
    return pd.read_csv("/var/data/all_transactions.csv")',
                'language' => 'python',
                'suspicious' => true
            ]
        ]
    ]
];

foreach ($additional_files as $repo_files) {
    // Trouver le repository
    $stmt = $pdo->prepare("SELECT id FROM repositories WHERE name = ?");
    $stmt->execute([$repo_files['repo_name']]);
    $repo = $stmt->fetch();
    
    if ($repo) {
        foreach ($repo_files['files'] as $file) {
            $stmt = $pdo->prepare("
                INSERT INTO repository_files (repository_id, filename, content, language, is_suspicious) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$repo['id'], $file['filename'], $file['content'], $file['language'], $file['suspicious']]);
        }
    }
}
?>