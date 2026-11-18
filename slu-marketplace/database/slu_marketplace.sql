-- SLU Peer-to-Peer Textbook & Course Material Marketplace
-- Import via phpMyAdmin or: mysql -u root < slu_marketplace.sql

CREATE DATABASE IF NOT EXISTS slu_marketplace
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE slu_marketplace;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reports;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS listings;
DROP TABLE IF EXISTS listing_images;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  user_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  student_number VARCHAR(30) NOT NULL UNIQUE,
  full_name VARCHAR(120) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('student','admin') NOT NULL DEFAULT 'student',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE listings (
  listing_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  seller_id INT UNSIGNED NOT NULL,
  title VARCHAR(200) NOT NULL,
  description TEXT,
  category ENUM('Textbooks','Reviewers','Laboratory Manuals','Course Materials','Uniforms and Academic Attire','Other Academic Items') NOT NULL,
  department VARCHAR(80) DEFAULT NULL,
  course_code VARCHAR(40) DEFAULT NULL,
  edition VARCHAR(60) DEFAULT NULL,
  price DECIMAL(10,2) NOT NULL,
  `condition` ENUM('Brand New','Like New','Good','Fair') NOT NULL,
  status ENUM('available','reserved','sold','removed') NOT NULL DEFAULT 'available',
  image_path VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
  INDEX idx_listings_category (category),
  INDEX idx_listings_status (status),
  INDEX idx_listings_course (course_code),
  INDEX idx_listings_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE messages (
  message_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  sender_id INT UNSIGNED NOT NULL,
  receiver_id INT UNSIGNED NOT NULL,
  listing_id INT UNSIGNED NOT NULL,
  content TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (receiver_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE,
  INDEX idx_messages_thread (listing_id, sender_id, receiver_id),
  INDEX idx_messages_created (created_at)
) ENGINE=InnoDB;

CREATE TABLE transactions (
  transaction_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  listing_id INT UNSIGNED NOT NULL,
  buyer_id INT UNSIGNED NOT NULL,
  seller_id INT UNSIGNED NOT NULL,
  agreed_price DECIMAL(10,2) DEFAULT NULL,
  meetup_location ENUM('Main Campus','Maryheights Campus') DEFAULT NULL,
  status ENUM('pending','completed','cancelled') NOT NULL DEFAULT 'pending',
  completed_at TIMESTAMP NULL DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE,
  FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE,
  INDEX idx_tx_buyer (buyer_id),
  INDEX idx_tx_seller (seller_id),
  INDEX idx_tx_listing (listing_id)
) ENGINE=InnoDB;

CREATE TABLE reviews (
  review_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  transaction_id INT UNSIGNED NOT NULL,
  reviewer_id INT UNSIGNED NOT NULL,
  reviewed_user_id INT UNSIGNED NOT NULL,
  rating TINYINT UNSIGNED NOT NULL,
  feedback TEXT DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (reviewed_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  UNIQUE KEY uniq_review (transaction_id, reviewer_id),
  CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5)
) ENGINE=InnoDB;

CREATE TABLE reports (
  report_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  listing_id INT UNSIGNED NOT NULL,
  reporter_id INT UNSIGNED NOT NULL,
  reason TEXT NOT NULL,
  status ENUM('pending','resolved','dismissed') NOT NULL DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (listing_id) REFERENCES listings(listing_id) ON DELETE CASCADE,
  FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
  INDEX idx_reports_status (status)
) ENGINE=InnoDB;
