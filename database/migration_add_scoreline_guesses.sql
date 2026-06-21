-- Migration: Add scoreline guess bonus feature
-- Run this against your existing database to add the new tables and default settings.

CREATE TABLE IF NOT EXISTS league_scoreline_guesses (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    participant_id INT UNSIGNED NOT NULL,
    match_id INT UNSIGNED NOT NULL,
    home_score TINYINT UNSIGNED NOT NULL,
    away_score TINYINT UNSIGNED NOT NULL,
    is_correct TINYINT(1) NULL DEFAULT NULL,
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_scoreline_guess_participant_match (participant_id, match_id),
    KEY idx_scoreline_guess_match_id (match_id),
    CONSTRAINT fk_scoreline_guesses_participant FOREIGN KEY (participant_id) REFERENCES league_participants (id) ON DELETE CASCADE,
    CONSTRAINT fk_scoreline_guesses_match FOREIGN KEY (match_id) REFERENCES league_matches (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS league_settings (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL,
    setting_value VARCHAR(500) NOT NULL DEFAULT '',
    created_at DATETIME NOT NULL,
    updated_at DATETIME NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uniq_league_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default bonus settings (ignore if already exist)
INSERT IGNORE INTO league_settings (setting_key, setting_value, created_at, updated_at)
VALUES
    ('bonus_enabled', '1', NOW(), NOW()),
    ('bonus_position_threshold', '6', NOW(), NOW()),
    ('bonus_points_per_guess', '5', NOW(), NOW());