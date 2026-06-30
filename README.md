Markdown
# ⚡ AeroCart - Premium Multi-Vendor E-Commerce Platform

AeroCart is an ultra-premium, modern, and minimalist Multi-Vendor E-Commerce Marketplace designed for seamless buying and selling. Built with a robust **PHP/MySQL** backend and styled using a modern **Tailwind CSS** white theme with deep blue and glassmorphic accents, this platform provides a high-end corporate shopping experience.

---

## 🚀 Core Features

### 🛍️ 1. Multi-Vendor Architecture & Merchant Onboarding
* **Instant Account Upgrade:** Normal buyers can instantly transition into merchants using the database-driven **"Become a Seller"** workflow.
* **Dedicated Dashboards:** Separate fully-featured workspaces for both Customers and Sellers to manage workflows independently.

### 🔍 2. Fully Responsive Smart Search System
* Native desktop and mobile-optimized search query bars bound dynamically with `GET` parameters.
* Real-time search processing that filters database inventories on the fly without breaking UI responsiveness.

### 💬 3. Live Messaging & Chat System
* Built-in communication engine connecting buyers and sellers directly.
* Features automated **unread message counters** and real-time visual notification badges.

### 🛡️ 4. Advanced Admin Control Panel
* Secure operational moderation cockpit allowing global control over platform users, listings, and core system actions.

---

## 📊 Complete Database Schema & SQL Queries

```sql
-- 1. Create Database
CREATE DATABASE IF NOT EXISTS `aerocart` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `aerocart`;

-- 2. Users Table (Handles Buyers, Sellers, and Admins)
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('buyer', 'seller', 'admin') DEFAULT 'buyer',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. Products Table (Linked to Sellers)
CREATE TABLE IF NOT EXISTS `products` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `seller_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `image` VARCHAR(255) DEFAULT 'default_product.jpg',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`seller_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Live Chat Messages Table
CREATE TABLE IF NOT EXISTS `messages` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sender_id` INT NOT NULL,
  `receiver_id` INT NOT NULL,
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`sender_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`receiver_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Insert Sample Admin Account (Password: admin123)
INSERT INTO `users` (`name`, `email`, `password`, `role`) 
VALUES ('Wajahat Awan', 'admin@aerocart.com', '$2y$10$7rX7g8J9o1K2L3M4N5O6P7Q8R9S0T1U2V3W4X5Y6Z7a8b9c0d1e2f', 'admin')
ON DUPLICATE KEY UPDATE `id`=`id`;
📂 Project Structure Overview
Plaintext
aerocart/
│
├── assets/               # CSS, JavaScript, Images, and SVG graphics
├── includes/             # Reusable modular sections (header.php, footer.php)
├── config.php            # Secure core database configuration and credentials
├── index.php             # Main Landing Page and dynamic product loop
├── seller_dashboard.php  # Advanced analytics interface for merchants
└── messages.php          # Interactive live communication terminal
🔧 Installation & Local Setup
Move to Local Server Environment: Place the project folder into your local server environment directory (e.g., XAMPP/htdocs/aerocart).

Database Import: Run the SQL queries provided above in your phpMyAdmin.

Configuration Mapping: Update your local server database credentials inside the config.php file:

PHP
$conn = new mysqli("localhost", "YOUR_DB_USER", "YOUR_DB_PASSWORD", "aerocart");
Run Project: Open your browser and navigate to http://localhost/aerocart.

👨‍💻 Developer & Author
Lead Full-Stack Web Developer: Wajahat Awan

Specialization: Dynamic database-driven web applications, Custom POS frameworks, and High-End Professional UI/UX Engineering.

Available for Freelance: Open to custom development contracts and enterprise portfolio builds.

📄 License
This project is proprietary software built for portfolio representation. All rights reserved.
