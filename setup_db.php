<?php
require 'db.php';

// SQL to create the 'books' table
$sqlBooks = "
CREATE TABLE IF NOT EXISTS books (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    year_published INT,
    number_of_copies INT DEFAULT 1,
    genre VARCHAR(100),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

// SQL to create the 'register' table
$sqlRegister = "
CREATE TABLE IF NOT EXISTS register (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
";

try {
    // Execute the SQL statements
    $pdo->exec($sqlBooks);
    echo "Table 'books' created successfully.<br>";
    
    $pdo->exec($sqlRegister);
    echo "Table 'register' created successfully.<br>";

    // Additional tables for a Library System
    $sqlCategories = "
    CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
    ";
    $pdo->exec($sqlCategories);
    echo "Table 'categories' created successfully.<br>";

    $sqlBorrowedBooks = "
    CREATE TABLE IF NOT EXISTS borrowed_books (
        id INT AUTO_INCREMENT PRIMARY KEY,
        book_id INT NOT NULL,
        user_id INT NOT NULL,
        borrow_date DATE NOT NULL,
        return_date DATE,
        status ENUM('borrowed', 'returned', 'overdue') DEFAULT 'borrowed',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (book_id) REFERENCES books(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES register(id) ON DELETE CASCADE
    );
    ";
    $pdo->exec($sqlBorrowedBooks);
    echo "Table 'borrowed_books' created successfully.<br>";
    
} catch (\PDOException $e) {
    die("Error creating tables: " . $e->getMessage());
}
?>
