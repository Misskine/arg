-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : lun. 05 jan. 2026 à 10:30
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `gitrepo`
--

-- --------------------------------------------------------

--
-- Structure de la table `arg_apps`
--

CREATE TABLE `arg_apps` (
  `id` int(11) NOT NULL,
  `repository_id` int(11) NOT NULL,
  `app_filename` varchar(255) NOT NULL,
  `secret_key` varchar(100) DEFAULT NULL,
  `unlock_code` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `arg_apps`
--

INSERT INTO `arg_apps` (`id`, `repository_id`, `app_filename`, `secret_key`, `unlock_code`, `is_active`) VALUES
(1, 1, 'encryption-tool.php', 'alpha-secure-456', 'beta-unlock-123', 1),
(2, 4, 'security-scanner.php', 'beta-access-789', 'gamma-unlock-456', 1),
(3, 7, 'data-analyzer.php', 'gamma-watch-012', 'delta-unlock-789', 1),
(4, 10, 'system-monitor.php', 'delta-log-345', 'epsilon-unlock-012', 1),
(5, 13, 'final-puzzle.php', 'epsilon-final-678', 'complete-arg-999', 1);

-- --------------------------------------------------------

--
-- Structure de la table `comments`
--

CREATE TABLE `comments` (
  `id` int(11) NOT NULL,
  `issue_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `commits`
--

CREATE TABLE `commits` (
  `id` int(11) NOT NULL,
  `repository_id` int(11) NOT NULL,
  `committed_by` int(11) NOT NULL,
  `commit_hash` varchar(40) NOT NULL,
  `message` text NOT NULL,
  `committed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `found_secrets`
--

CREATE TABLE `found_secrets` (
  `id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `repository_id` int(11) NOT NULL,
  `secret_key` varchar(100) DEFAULT NULL,
  `found_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `issues`
--

CREATE TABLE `issues` (
  `id` int(11) NOT NULL,
  `repository_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `status` enum('open','closed') DEFAULT 'open',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `issues`
--

INSERT INTO `issues` (`id`, `repository_id`, `title`, `body`, `status`, `created_by`, `created_at`, `closed_at`) VALUES
(1, 1, 'Encryption bug found', 'The XOR function has a vulnerability when key contains null bytes.', 'open', 1, '2026-01-03 22:50:18', NULL),
(2, 4, 'Security audit failed', 'Found unauthorized access patterns in logs.', 'open', 2, '2026-01-03 22:50:18', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `is_system_message` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messages`
--

INSERT INTO `messages` (`id`, `sender_id`, `recipient_id`, `subject`, `body`, `is_read`, `is_system_message`, `sent_at`) VALUES
(1, 1, 7, 'Something\'s not right...', 'Hello,\r\n\r\nI don\'t usually reach out to new members, but I need to share something with you.\r\n\r\nI\'ve been working on cryptographic tools in my **project-cipher** repository, and I\'ve noticed some very concerning patterns. Someone has been accessing my systems without permission.\r\n\r\nI\'m not sure who I can trust anymore, but I\'m leaving clues in my code. If you want to understand what\'s happening here, you should start by investigating my repositories.\r\n\r\n**Here\'s what I know:**\r\n- Unauthorized access logs show activity I didn\'t authorize\r\n- Strange patterns appearing in my cipher outputs\r\n- References to someone called \"beta\" in my system\r\n\r\nCheck out my **project-cipher** repository. There\'s a live demo application - the answers might be hidden in the encryption patterns.\r\n\r\nI\'ve also documented everything in my **personal-notes** repository. Read my journal entries carefully.\r\n\r\nBe careful. Something bigger is going on here.\r\n\r\nStay safe,\r\ndev_alpha', 1, 0, '2026-01-03 22:57:51');

-- --------------------------------------------------------

--
-- Structure de la table `player_progress`
--

CREATE TABLE `player_progress` (
  `id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `unlocked_user_id` int(11) NOT NULL,
  `unlocked_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `player_progress`
--

INSERT INTO `player_progress` (`id`, `player_id`, `unlocked_user_id`, `unlocked_at`) VALUES
(1, 7, 1, '2026-01-03 22:57:51');

-- --------------------------------------------------------

--
-- Structure de la table `repositories`
--

CREATE TABLE `repositories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `is_private` tinyint(1) DEFAULT 0,
  `stargazers_count` int(11) DEFAULT 0,
  `forks_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `repositories`
--

INSERT INTO `repositories` (`id`, `user_id`, `name`, `description`, `is_private`, `stargazers_count`, `forks_count`, `created_at`, `updated_at`) VALUES
(1, 1, 'project-cipher', 'Cryptography tools and experiments', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(2, 1, 'personal-notes', 'Personal notes and journal entries', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(3, 1, 'web-tools', 'Collection of web utilities', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(4, 2, 'encryption-tools', 'Advanced encryption utilities', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(5, 2, 'security-audit', 'Security audit logs and reports', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(6, 2, 'api-gateway', 'API gateway implementation', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(7, 3, 'data-analysis', 'Data analysis scripts and tools', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(8, 3, 'pattern-detector', 'Pattern detection algorithms', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(9, 3, 'statistics-toolkit', 'Statistical analysis toolkit', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(10, 4, 'system-monitor', 'System monitoring tools', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(11, 4, 'network-tools', 'Network analysis utilities', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(12, 4, 'infrastructure', 'Infrastructure as code', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(13, 5, 'private-notes', 'Private notes and documentation', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(14, 5, 'final-report', 'Final investigation report', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18'),
(15, 5, 'legacy-code', 'Legacy code archive', 0, 0, 0, '2026-01-03 22:50:18', '2026-01-03 22:50:18');

-- --------------------------------------------------------

--
-- Structure de la table `repository_files`
--

CREATE TABLE `repository_files` (
  `id` int(11) NOT NULL,
  `repository_id` int(11) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `filepath` varchar(500) DEFAULT NULL,
  `content` longtext DEFAULT NULL,
  `language` varchar(50) DEFAULT NULL,
  `size_bytes` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `repository_files`
--

INSERT INTO `repository_files` (`id`, `repository_id`, `filename`, `filepath`, `content`, `language`, `size_bytes`, `created_at`) VALUES
(1, 1, 'encrypt.php', 'src', '<?php\r\n// XOR encryption function\r\nfunction xor_encrypt($text, $key) {\r\n    $result = \"\";\r\n    for ($i = 0; $i < strlen($text); $i++) {\r\n        $result .= $text[$i] ^ $key[$i % strlen($key)];\r\n    }\r\n    return base64_encode($result);\r\n}\r\n\r\n// Secret key found in logs: alpha-secure-456\r\n// Use this in the encryption tool\r\n?>', 'php', NULL, '2026-01-03 22:50:18'),
(2, 1, 'readme.md', '', '# Project Cipher\r\n## Secret Notes\r\nI suspect someone is monitoring my work. The key \"beta-unlock-123\" might be important.\r\nCheck the encryption tool for more clues.', 'markdown', NULL, '2026-01-03 22:50:18'),
(3, 4, 'scanner.php', 'tools', '<?php\r\n// Security scanner\r\n// Found suspicious activity from user \"gamma\"\r\n// Secret key: beta-access-789\r\n// Unlock code for next level: gamma-unlock-456\r\n?>', 'php', NULL, '2026-01-03 22:50:18'),
(4, 7, 'analyze.py', '', '# Data analysis script\r\n# Pattern detected: delta system accessed at midnight\r\n# Key: gamma-watch-012\r\n# Next: delta-unlock-789\r\nimport pandas as pd', 'python', NULL, '2026-01-03 22:50:18');

-- --------------------------------------------------------

--
-- Structure de la table `stars`
--

CREATE TABLE `stars` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `repository_id` int(11) NOT NULL,
  `starred_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `avatar_url` varchar(255) DEFAULT 'https://github.com/identicons/{username}.png',
  `bio` text DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `is_arg_character` tinyint(1) DEFAULT 0,
  `unlock_order` int(11) DEFAULT 0,
  `is_locked` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `avatar_url`, `bio`, `location`, `website`, `is_arg_character`, `unlock_order`, `is_locked`, `created_at`) VALUES
(1, 'dev_alpha', 'alpha@gitrepo.dev', '$2y$10$locked', 'https://github.com/identicons/{username}.png', 'Cryptography enthusiast. Something feels off about my last project...', NULL, NULL, 1, 1, 1, '2026-01-03 22:50:18'),
(2, 'dev_beta', 'beta@gitrepo.dev', '$2y$10$locked', 'https://github.com/identicons/{username}.png', 'Security researcher. I found something strange in alpha\'s code.', NULL, NULL, 1, 2, 1, '2026-01-03 22:50:18'),
(3, 'dev_gamma', 'gamma@gitrepo.dev', '$2y$10$locked', 'https://github.com/identicons/{username}.png', 'Data scientist. Patterns are emerging in the logs.', NULL, NULL, 1, 3, 1, '2026-01-03 22:50:18'),
(4, 'dev_delta', 'delta@gitrepo.dev', '$2y$10$locked', 'https://github.com/identicons/{username}.png', 'System architect. The infrastructure has been compromised.', NULL, NULL, 1, 4, 1, '2026-01-03 22:50:18'),
(5, 'dev_epsilon', 'epsilon@gitrepo.dev', '$2y$10$locked', 'https://github.com/identicons/{username}.png', 'Lead developer. I know what happened, but I can\'t tell anyone.', NULL, NULL, 1, 5, 1, '2026-01-03 22:50:18'),
(6, 'testplayer', 'player@test.com', '$2y$10$TestPasswordHash', 'https://github.com/identicons/{username}.png', NULL, NULL, NULL, 0, 0, 0, '2026-01-03 22:50:18'),
(7, 'perso1', 'marie@fnac.fr', '$2y$10$2djVtpyh0H8Q5vItfyrJbee6cXRw9szSWziiPHkB4sskcekmAhghu', 'https://github.com/identicons/{username}.png', NULL, NULL, NULL, 0, 0, 0, '2026-01-03 22:57:51');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `arg_apps`
--
ALTER TABLE `arg_apps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `repository_id` (`repository_id`);

--
-- Index pour la table `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `issue_id` (`issue_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `commits`
--
ALTER TABLE `commits`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `commit_hash` (`commit_hash`),
  ADD KEY `repository_id` (`repository_id`),
  ADD KEY `committed_by` (`committed_by`);

--
-- Index pour la table `found_secrets`
--
ALTER TABLE `found_secrets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_found` (`player_id`,`repository_id`),
  ADD KEY `repository_id` (`repository_id`);

--
-- Index pour la table `issues`
--
ALTER TABLE `issues`
  ADD PRIMARY KEY (`id`),
  ADD KEY `repository_id` (`repository_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `recipient_id` (`recipient_id`);

--
-- Index pour la table `player_progress`
--
ALTER TABLE `player_progress`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_unlock` (`player_id`,`unlocked_user_id`),
  ADD KEY `unlocked_user_id` (`unlocked_user_id`);

--
-- Index pour la table `repositories`
--
ALTER TABLE `repositories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_repo` (`user_id`,`name`);

--
-- Index pour la table `repository_files`
--
ALTER TABLE `repository_files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `repository_id` (`repository_id`);

--
-- Index pour la table `stars`
--
ALTER TABLE `stars`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_star` (`user_id`,`repository_id`),
  ADD KEY `repository_id` (`repository_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `arg_apps`
--
ALTER TABLE `arg_apps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `commits`
--
ALTER TABLE `commits`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `found_secrets`
--
ALTER TABLE `found_secrets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `issues`
--
ALTER TABLE `issues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `player_progress`
--
ALTER TABLE `player_progress`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `repositories`
--
ALTER TABLE `repositories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT pour la table `repository_files`
--
ALTER TABLE `repository_files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `stars`
--
ALTER TABLE `stars`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `arg_apps`
--
ALTER TABLE `arg_apps`
  ADD CONSTRAINT `arg_apps_ibfk_1` FOREIGN KEY (`repository_id`) REFERENCES `repositories` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`issue_id`) REFERENCES `issues` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `commits`
--
ALTER TABLE `commits`
  ADD CONSTRAINT `commits_ibfk_1` FOREIGN KEY (`repository_id`) REFERENCES `repositories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `commits_ibfk_2` FOREIGN KEY (`committed_by`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `found_secrets`
--
ALTER TABLE `found_secrets`
  ADD CONSTRAINT `found_secrets_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `found_secrets_ibfk_2` FOREIGN KEY (`repository_id`) REFERENCES `repositories` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `issues`
--
ALTER TABLE `issues`
  ADD CONSTRAINT `issues_ibfk_1` FOREIGN KEY (`repository_id`) REFERENCES `repositories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `issues_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`recipient_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `player_progress`
--
ALTER TABLE `player_progress`
  ADD CONSTRAINT `player_progress_ibfk_1` FOREIGN KEY (`player_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `player_progress_ibfk_2` FOREIGN KEY (`unlocked_user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `repositories`
--
ALTER TABLE `repositories`
  ADD CONSTRAINT `repositories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `repository_files`
--
ALTER TABLE `repository_files`
  ADD CONSTRAINT `repository_files_ibfk_1` FOREIGN KEY (`repository_id`) REFERENCES `repositories` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `stars`
--
ALTER TABLE `stars`
  ADD CONSTRAINT `stars_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stars_ibfk_2` FOREIGN KEY (`repository_id`) REFERENCES `repositories` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
