-- ===================================================================
-- Smart Redirect Platform (SRP) - Complete Production Database
-- Version: 2.1.1
-- Date: 2025-11-27
-- ===================================================================
-- MASTER SCHEMA FILE - Execute on fresh database installation
-- This file contains ALL tables, indexes, views, events, and default data
-- ===================================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION';

-- ===================================================================
-- CORE TABLES
-- ===================================================================

-- -------------------------------------------------------------------
-- Table: users (Admin users)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `email` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login_at` INT UNSIGNED DEFAULT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    `updated_at` INT UNSIGNED NOT NULL,
    INDEX `idx_username` (`username`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Admin users for dashboard access';

-- -------------------------------------------------------------------
-- Table: settings (System settings - singleton)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
    `id` INT PRIMARY KEY DEFAULT 1,
    `system_on` TINYINT(1) NOT NULL DEFAULT 1 COMMENT 'System active status',
    `redirect_url` JSON DEFAULT NULL COMMENT 'Array of redirect URLs',
    `fallback_url` VARCHAR(1000) DEFAULT '/_meetups/' COMMENT 'Fallback when no rules match',
    `country_filter_mode` ENUM('all','whitelist','blacklist') NOT NULL DEFAULT 'all',
    `country_filter_list` TEXT DEFAULT NULL COMMENT 'Comma-separated country codes',
    `allowed_countries` JSON DEFAULT NULL,
    `disallowed_countries` JSON DEFAULT NULL,
    `postback_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `postback_url` VARCHAR(1000) DEFAULT NULL,
    `postback_always` TINYINT(1) NOT NULL DEFAULT 1,
    `default_payout` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `total_decision_a` INT UNSIGNED NOT NULL DEFAULT 0,
    `total_decision_b` INT UNSIGNED NOT NULL DEFAULT 0,
    `stats_reset_at` INT UNSIGNED DEFAULT NULL,
    `decision_a_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `decision_b_count` INT UNSIGNED NOT NULL DEFAULT 0,
    `last_reset_date` DATE DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` INT UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='System configuration (singleton row id=1)';

-- -------------------------------------------------------------------
-- Table: clicks (Click tracking)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `clicks` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `click_id` VARCHAR(100) NOT NULL UNIQUE,
    `ts` INT UNSIGNED NOT NULL,
    `country_code` CHAR(2) NOT NULL DEFAULT 'XX',
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `device_type` VARCHAR(50) DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `referrer` VARCHAR(500) DEFAULT NULL,
    `user_lp` VARCHAR(100) DEFAULT NULL COMMENT 'Landing page identifier',
    `redirect_url` VARCHAR(1000) DEFAULT NULL,
    `final_url` VARCHAR(1000) DEFAULT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'pending',
    `postback_status` VARCHAR(50) DEFAULT NULL,
    `postback_payout` DECIMAL(10,2) DEFAULT 0.00,
    `postback_ts` INT UNSIGNED DEFAULT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    INDEX `idx_click_id` (`click_id`),
    INDEX `idx_ts` (`ts`),
    INDEX `idx_country_code` (`country_code`),
    INDEX `idx_status` (`status`),
    INDEX `idx_postback_status` (`postback_status`),
    INDEX `idx_postback_ts` (`postback_ts`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Click tracking with postback status';

-- -------------------------------------------------------------------
-- Table: traffic_logs (Decision API request logs)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `traffic_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ts` INT UNSIGNED NOT NULL,
    `ip` VARCHAR(45) NOT NULL,
    `ua` TEXT DEFAULT NULL COMMENT 'User Agent',
    `cid` VARCHAR(100) DEFAULT NULL COMMENT 'Click ID',
    `cc` CHAR(2) DEFAULT NULL COMMENT 'Country Code',
    `lp` VARCHAR(100) DEFAULT NULL COMMENT 'Landing Page',
    `decision` CHAR(1) DEFAULT NULL COMMENT 'A or B decision',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ts` (`ts`),
    INDEX `idx_ip` (`ip`),
    INDEX `idx_cc` (`cc`),
    INDEX `idx_decision` (`decision`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Decision API request logs';

-- -------------------------------------------------------------------
-- Table: postback_logs (Outgoing postback logs)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `postback_logs` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ts` INT UNSIGNED NOT NULL,
    `country_code` VARCHAR(10) NOT NULL,
    `traffic_type` VARCHAR(50) NOT NULL DEFAULT 'Unknown',
    `payout` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `postback_url` TEXT NOT NULL,
    `response_code` INT DEFAULT NULL,
    `response_body` TEXT DEFAULT NULL,
    `success` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ts` (`ts`),
    INDEX `idx_success` (`success`),
    INDEX `idx_country_code` (`country_code`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Outgoing postback attempt logs';

-- -------------------------------------------------------------------
-- Table: postback_received (Incoming postbacks from networks)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `postback_received` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ts` INT UNSIGNED NOT NULL,
    `status` VARCHAR(50) NOT NULL DEFAULT 'confirmed',
    `country_code` CHAR(2) NOT NULL DEFAULT 'XX',
    `traffic_type` VARCHAR(50) NOT NULL DEFAULT 'Unknown',
    `payout` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `click_id` VARCHAR(100) DEFAULT NULL,
    `network` VARCHAR(100) DEFAULT 'unknown',
    `signature_verified` TINYINT(1) NOT NULL DEFAULT 0,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `query_string` TEXT DEFAULT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_ts` (`ts`),
    INDEX `idx_status` (`status`),
    INDEX `idx_country_code` (`country_code`),
    INDEX `idx_click_id` (`click_id`),
    INDEX `idx_network` (`network`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Incoming postbacks from affiliate networks';

-- -------------------------------------------------------------------
-- Table: routing_rules (Smart routing configuration)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `routing_rules` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `rule_name` VARCHAR(100) NOT NULL,
    `country_code` CHAR(2) NOT NULL,
    `device_type` VARCHAR(50) NOT NULL,
    `user_lp` VARCHAR(100) DEFAULT NULL,
    `redirect_url` VARCHAR(1000) NOT NULL,
    `priority` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` INT UNSIGNED NOT NULL,
    `updated_at` INT UNSIGNED NOT NULL,
    INDEX `idx_country_device` (`country_code`, `device_type`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Country/device-based routing rules';

-- -------------------------------------------------------------------
-- Table: api_keys (API key management)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `api_keys` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `key_name` VARCHAR(100) NOT NULL,
    `api_key` VARCHAR(64) NOT NULL UNIQUE,
    `key_type` ENUM('internal','external') NOT NULL DEFAULT 'external',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `rate_limit` INT UNSIGNED DEFAULT 120 COMMENT 'Requests per minute',
    `created_at` INT UNSIGNED NOT NULL,
    `last_used_at` INT UNSIGNED DEFAULT NULL,
    INDEX `idx_api_key` (`api_key`),
    INDEX `idx_is_active` (`is_active`),
    INDEX `idx_key_type` (`key_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='API key management';

-- -------------------------------------------------------------------
-- Table: audit_log (Admin action audit trail)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `audit_log` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `action` VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) DEFAULT NULL,
    `entity_id` INT UNSIGNED DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` VARCHAR(255) DEFAULT NULL,
    `details` TEXT DEFAULT NULL,
    `created_at` INT UNSIGNED NOT NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Admin action audit trail';

-- -------------------------------------------------------------------
-- Table: env_config (Environment configuration sync)
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `env_config` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `config_key` VARCHAR(100) NOT NULL UNIQUE,
    `config_value` TEXT DEFAULT NULL,
    `config_type` ENUM('string','integer','boolean','json') NOT NULL DEFAULT 'string',
    `description` VARCHAR(255) DEFAULT NULL,
    `is_sensitive` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1=mask in UI',
    `is_editable` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0=read-only',
    `updated_at` INT UNSIGNED NOT NULL,
    `updated_by` INT UNSIGNED DEFAULT NULL,
    INDEX `idx_config_key` (`config_key`),
    INDEX `idx_is_sensitive` (`is_sensitive`),
    INDEX `idx_updated_at` (`updated_at`),
    CONSTRAINT `fk_config_user` FOREIGN KEY (`updated_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Environment configuration synchronized with .env';

-- ===================================================================
-- SECURITY TABLES
-- ===================================================================

-- -------------------------------------------------------------------
-- Table: postback_security_log
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `postback_security_log` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ts` INT UNSIGNED NOT NULL COMMENT 'Unix timestamp',
    `ip` VARCHAR(45) NOT NULL,
    `success` TINYINT(1) NOT NULL DEFAULT 0,
    `message` VARCHAR(255) NOT NULL,
    `data` TEXT DEFAULT NULL COMMENT 'Request data JSON',
    INDEX `idx_ts` (`ts`),
    INDEX `idx_ip` (`ip`),
    INDEX `idx_success` (`success`),
    INDEX `idx_ip_ts` (`ip`, `ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Postback security audit log';

-- -------------------------------------------------------------------
-- Table: rate_limit_tracking
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `rate_limit_tracking` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip` VARCHAR(45) NOT NULL,
    `endpoint` VARCHAR(100) NOT NULL,
    `attempts` INT UNSIGNED NOT NULL DEFAULT 1,
    `window_start` INT UNSIGNED NOT NULL,
    `window_end` INT UNSIGNED NOT NULL,
    `blocked_until` INT UNSIGNED DEFAULT NULL,
    UNIQUE KEY `idx_ip_endpoint_window` (`ip`, `endpoint`, `window_start`),
    INDEX `idx_window_end` (`window_end`),
    INDEX `idx_blocked_until` (`blocked_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='API rate limiting per IP/endpoint';

-- -------------------------------------------------------------------
-- Table: failed_login_attempts
-- -------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `failed_login_attempts` (
    `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `ip` VARCHAR(45) NOT NULL,
    `username` VARCHAR(100) NOT NULL,
    `user_agent` VARCHAR(500) DEFAULT NULL,
    `attempted_at` INT UNSIGNED NOT NULL,
    `reason` VARCHAR(100) DEFAULT NULL,
    INDEX `idx_ip` (`ip`),
    INDEX `idx_username` (`username`),
    INDEX `idx_attempted_at` (`attempted_at`),
    INDEX `idx_ip_attempted` (`ip`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Failed login attempts for security monitoring';

-- ===================================================================
-- VIEWS
-- ===================================================================

-- Security summary dashboard view
CREATE OR REPLACE VIEW `v_security_summary` AS
SELECT
    'postback_attempts' AS metric_type,
    COUNT(*) AS total_count,
    SUM(CASE WHEN success = 1 THEN 1 ELSE 0 END) AS success_count,
    SUM(CASE WHEN success = 0 THEN 1 ELSE 0 END) AS failure_count,
    COUNT(DISTINCT ip) AS unique_ips,
    MAX(ts) AS last_activity
FROM `postback_security_log`
WHERE `ts` > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR))

UNION ALL

SELECT
    'failed_logins' AS metric_type,
    COUNT(*) AS total_count,
    0 AS success_count,
    COUNT(*) AS failure_count,
    COUNT(DISTINCT ip) AS unique_ips,
    MAX(attempted_at) AS last_activity
FROM `failed_login_attempts`
WHERE `attempted_at` > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 24 HOUR))

UNION ALL

SELECT
    'rate_limits' AS metric_type,
    COUNT(*) AS total_count,
    SUM(CASE WHEN blocked_until IS NULL THEN 1 ELSE 0 END) AS success_count,
    SUM(CASE WHEN blocked_until IS NOT NULL THEN 1 ELSE 0 END) AS failure_count,
    COUNT(DISTINCT ip) AS unique_ips,
    MAX(window_start) AS last_activity
FROM `rate_limit_tracking`
WHERE `window_end` > UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 HOUR));

-- ===================================================================
-- SCHEDULED EVENTS
-- ===================================================================

DELIMITER $$

-- Cleanup old logs event (30 days retention)
DROP EVENT IF EXISTS `cleanup_old_logs`$$

CREATE EVENT IF NOT EXISTS `cleanup_old_logs`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
COMMENT 'Clean up logs older than 30 days'
DO
BEGIN
    -- Security logs (30 days)
    DELETE FROM `postback_security_log` WHERE `ts` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY));
    DELETE FROM `failed_login_attempts` WHERE `attempted_at` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY));

    -- Rate limit records (1 day)
    DELETE FROM `rate_limit_tracking` WHERE `window_end` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 1 DAY));

    -- Traffic logs (7 days)
    DELETE FROM `traffic_logs` WHERE `ts` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 7 DAY));

    -- Postback logs (30 days)
    DELETE FROM `postback_logs` WHERE `ts` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY));
    DELETE FROM `postback_received` WHERE `ts` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 30 DAY));

    -- Audit log (90 days)
    DELETE FROM `audit_log` WHERE `created_at` < UNIX_TIMESTAMP(DATE_SUB(NOW(), INTERVAL 90 DAY));
END$$

DELIMITER ;

-- ===================================================================
-- DEFAULT DATA
-- ===================================================================

-- Default admin user
-- Username: admin
-- Password: password123 (CHANGE IMMEDIATELY!)
INSERT INTO `users` (`username`, `password_hash`, `email`, `is_active`, `created_at`, `updated_at`)
VALUES ('admin', '$2y$10$6pysXhhlGWYaw0NixOtHg.ZcRCAjjs49rBGI.42LxxopSFB7pSECy', 'admin@trackng.app', 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `updated_at` = UNIX_TIMESTAMP();

-- Default system settings
INSERT INTO `settings` (`id`, `system_on`, `postback_enabled`, `postback_always`, `default_payout`, `updated_at`)
VALUES (1, 1, 1, 1, 0.00, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `updated_at` = UNIX_TIMESTAMP();

-- Sample routing rules
INSERT INTO `routing_rules` (`rule_name`, `country_code`, `device_type`, `redirect_url`, `priority`, `is_active`, `created_at`, `updated_at`)
VALUES
    ('US Desktop', 'US', 'desktop', 'https://example.com/us-desktop', 10, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    ('US Mobile', 'US', 'mobile', 'https://example.com/us-mobile', 10, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    ('Global Desktop', 'XX', 'desktop', 'https://example.com/global', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()),
    ('Global Mobile', 'XX', 'mobile', 'https://example.com/global', 1, 1, UNIX_TIMESTAMP(), UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `updated_at` = UNIX_TIMESTAMP();

-- Default API keys (REPLACE WITH GENERATED KEYS!)
INSERT INTO `api_keys` (`key_name`, `api_key`, `key_type`, `is_active`, `rate_limit`, `created_at`)
VALUES
    ('Internal API', 'internal_key_32_chars_here_change_me', 'internal', 1, 300, UNIX_TIMESTAMP()),
    ('External API', 'external_key_32_chars_here_change_me', 'external', 1, 120, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `last_used_at` = NULL;

-- Environment configuration defaults
INSERT INTO `env_config` (`config_key`, `config_value`, `config_type`, `description`, `is_sensitive`, `is_editable`, `updated_at`) VALUES
-- Database
('DB_HOST', 'localhost', 'string', 'Database hostname', 0, 1, UNIX_TIMESTAMP()),
('DB_NAME', '', 'string', 'Database name', 0, 1, UNIX_TIMESTAMP()),
('DB_USER', '', 'string', 'Database username', 0, 1, UNIX_TIMESTAMP()),
('DB_PASS', '', 'string', 'Database password', 1, 1, UNIX_TIMESTAMP()),
('DB_PORT', '3306', 'integer', 'Database port', 0, 1, UNIX_TIMESTAMP()),
('DB_CHARSET', 'utf8mb4', 'string', 'Database charset', 0, 1, UNIX_TIMESTAMP()),

-- Application
('APP_NAME', 'Smart Redirect Platform', 'string', 'Application name', 0, 1, UNIX_TIMESTAMP()),
('APP_URL', 'https://trackng.app', 'string', 'Brand domain URL', 0, 1, UNIX_TIMESTAMP()),
('APP_PANEL_URL', 'https://panel.trackng.app', 'string', 'Admin panel URL', 0, 1, UNIX_TIMESTAMP()),
('APP_DEBUG', 'false', 'boolean', 'Debug mode', 0, 1, UNIX_TIMESTAMP()),
('APP_ENV', 'production', 'string', 'Environment', 0, 1, UNIX_TIMESTAMP()),
('SRP_ENV', 'production', 'string', 'SRP Environment', 0, 1, UNIX_TIMESTAMP()),

-- Tracking Domain
('TRACKING_PRIMARY_DOMAIN', 'qvtrk.com', 'string', 'Primary tracking domain', 0, 1, UNIX_TIMESTAMP()),
('TRACKING_REDIRECT_URL', 'https://t.qvtrk.com', 'string', 'Redirect service URL', 0, 1, UNIX_TIMESTAMP()),
('TRACKING_DECISION_API', 'https://api.qvtrk.com', 'string', 'Decision API URL', 0, 1, UNIX_TIMESTAMP()),
('TRACKING_POSTBACK_URL', 'https://postback.qvtrk.com', 'string', 'Postback receiver URL', 0, 1, UNIX_TIMESTAMP()),

-- API Keys (sensitive)
('API_KEY_INTERNAL', '', 'string', 'Internal API key', 1, 1, UNIX_TIMESTAMP()),
('API_KEY_EXTERNAL', '', 'string', 'External API key', 1, 1, UNIX_TIMESTAMP()),

-- Session
('SESSION_LIFETIME', '7200', 'integer', 'Session lifetime (seconds)', 0, 1, UNIX_TIMESTAMP()),
('SESSION_NAME', 'srp_session', 'string', 'Session cookie name', 0, 1, UNIX_TIMESTAMP()),
('SESSION_SECURE', 'true', 'boolean', 'Secure cookies (HTTPS)', 0, 1, UNIX_TIMESTAMP()),

-- Rate Limiting
('TRACKING_RATE_LIMIT_MAX', '120', 'integer', 'Max tracking requests/min', 0, 1, UNIX_TIMESTAMP()),
('TRACKING_RATE_LIMIT_WINDOW', '60', 'integer', 'Rate limit window (s)', 0, 1, UNIX_TIMESTAMP()),
('PANEL_RATE_LIMIT_MAX', '300', 'integer', 'Max panel requests/min', 0, 1, UNIX_TIMESTAMP()),
('PANEL_RATE_LIMIT_WINDOW', '60', 'integer', 'Rate limit window (s)', 0, 1, UNIX_TIMESTAMP()),
('RATE_LIMIT_TRACKING_ENABLED', 'true', 'boolean', 'Enable DB rate limiting', 0, 1, UNIX_TIMESTAMP()),

-- Security
('TRUST_CF_HEADERS', 'true', 'boolean', 'Trust Cloudflare headers', 0, 1, UNIX_TIMESTAMP()),
('TRUST_PROXY_HEADERS', 'false', 'boolean', 'Trust X-Forwarded-For', 0, 1, UNIX_TIMESTAMP()),

-- Logging
('LOG_LEVEL', 'warning', 'string', 'Log level', 0, 1, UNIX_TIMESTAMP()),
('LOG_PATH', '/home/username/logs/app.log', 'string', 'Log file path', 0, 1, UNIX_TIMESTAMP()),
('ERROR_LOG_PATH', '/home/username/logs/error.log', 'string', 'Error log path', 0, 1, UNIX_TIMESTAMP()),

-- Postback Security
('POSTBACK_HMAC_SECRET', '', 'string', 'HMAC secret for signatures', 1, 1, UNIX_TIMESTAMP()),
('POSTBACK_REQUIRE_API_KEY', 'true', 'boolean', 'Require API key', 0, 1, UNIX_TIMESTAMP()),
('POSTBACK_API_KEY', '', 'string', 'Postback API key', 1, 1, UNIX_TIMESTAMP()),
('POSTBACK_FORWARD_ENABLED', 'false', 'boolean', 'Enable forwarding', 0, 1, UNIX_TIMESTAMP()),
('POSTBACK_FORWARD_URL', '', 'string', 'Forward URL template', 0, 1, UNIX_TIMESTAMP()),

-- VPN Detection
('VPN_CHECK_URL', 'https://blackbox.ipinfo.app/lookup/', 'string', 'VPN check API URL', 0, 1, UNIX_TIMESTAMP()),
('VPN_CHECK_TIMEOUT', '2', 'integer', 'VPN check timeout (s)', 0, 1, UNIX_TIMESTAMP()),
('TRACKING_ENABLE_VPN_CHECK', 'true', 'boolean', 'Enable VPN detection', 0, 1, UNIX_TIMESTAMP()),

-- Features
('TRACKING_ENABLE_GEO_FILTER', 'true', 'boolean', 'Enable geo filtering', 0, 1, UNIX_TIMESTAMP()),
('TRACKING_ENABLE_DEVICE_FILTER', 'true', 'boolean', 'Enable device filter', 0, 1, UNIX_TIMESTAMP()),
('TRACKING_ENABLE_AUTO_MUTE', 'true', 'boolean', 'Enable auto-mute', 0, 1, UNIX_TIMESTAMP())
ON DUPLICATE KEY UPDATE `description` = VALUES(`description`);

-- ===================================================================
-- FINAL SETUP
-- ===================================================================

SET FOREIGN_KEY_CHECKS = 1;

-- Enable event scheduler (run as MySQL root if needed)
-- SET GLOBAL event_scheduler = ON;

-- ===================================================================
-- END OF SCHEMA
-- ===================================================================
