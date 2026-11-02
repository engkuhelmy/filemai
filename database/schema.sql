-- Filemai.my - MariaDB Database Schema

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'creator', 'viewer') DEFAULT 'viewer',
    subscription_status ENUM('free', 'premium') DEFAULT 'free',
    subscription_expires_at TIMESTAMP NULL,
    avatar_url VARCHAR(255) NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS dramas (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    creator_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    synopsis TEXT NULL,
    poster_url VARCHAR(255) NOT NULL,
    banner_url VARCHAR(255) NULL,
    genre VARCHAR(100) DEFAULT 'Romance',
    type ENUM('series', 'movie') DEFAULT 'series',
    status ENUM('pending', 'published', 'rejected') DEFAULT 'pending',
    views_count BIGINT UNSIGNED DEFAULT 0,
    featured BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (creator_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS episodes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    drama_id BIGINT UNSIGNED NOT NULL,
    episode_number INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    video_source ENUM('youtube', 'direct') NOT NULL DEFAULT 'youtube',
    youtube_video_id VARCHAR(20) NULL,
    video_url VARCHAR(500) NULL,
    duration INT NULL DEFAULT 60,
    views_count BIGINT UNSIGNED DEFAULT 0,
    is_free_preview BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_drama_episode (drama_id, episode_number),
    FOREIGN KEY (drama_id) REFERENCES dramas(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS ads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    video_url VARCHAR(500) NOT NULL,
    duration_seconds INT DEFAULT 15,
    click_url VARCHAR(500) NULL,
    sponsor_name VARCHAR(255) NULL,
    is_active BOOLEAN DEFAULT TRUE,
    views_count BIGINT UNSIGNED DEFAULT 0,
    clicks_count BIGINT UNSIGNED DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    amount DECIMAL(8,2) NOT NULL DEFAULT 5.00,
    payment_channel VARCHAR(50) NOT NULL,
    transaction_ref VARCHAR(255) UNIQUE NOT NULL,
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
