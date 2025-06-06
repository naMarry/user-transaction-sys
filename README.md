
# 🧾 User-Transaction System

A simple microservice-based system for managing users and their transactions. This is designed for learning and demonstration purposes, showing how to separate services (e.g., user service in PHP, transaction service in Node.js) and make them work together.

## 🚀 Tech Stack

- PHP (User Service)
- Node.js (Transaction Service)
- MySQL / MongoDB

🧱 Architecture
This project consists of two services:
- User Service (PHP): Handles user login, and profile management.
- Transaction Service (Node.js): Manages transactions linked to users( deposit, withdraw, and transaction history ).

## 🛠 How to Run

**User Service**
cd user-service
composer init
composer require firebase/php-jwt
composer require vlucas/phpdotenv

Create .env file
SECRET_KEY=your-secrete-key


**Transaction Service**
cd transaction-service
npm install
nodemon app

Create .env file
PORT=port
MONGO_URI=your_db_uri
