CREATE DATABASE lms_db;
USE lms_db;

-- Users table
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    membership_status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin
INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@booksy.com', '$2y$10$examplehashedpassword', 'admin');  -- Replace with actual hash for 'admin12345'

-- Authors table
CREATE TABLE authors (
    author_id INT AUTO_INCREMENT PRIMARY KEY,
    author_name VARCHAR(255) NOT NULL
);

-- Categories table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

-- Books table
CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NOT NULL,
    category_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    isbn VARCHAR(20) UNIQUE NOT NULL,
    status ENUM('available', 'loaned') DEFAULT 'available',
    FOREIGN KEY (author_id) REFERENCES authors(author_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

-- Loans table
CREATE TABLE loans (
    loan_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    checkout_date DATE NOT NULL,
    return_date DATE NULL,
    status ENUM('active', 'returned') DEFAULT 'active',
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Sample data (optional, for testing)
INSERT INTO authors (author_name) VALUES ('F. Scott Fitzgerald'), ('George Orwell');
INSERT INTO categories (name) VALUES ('Fiction'), ('Dystopian');
INSERT INTO books (author_id, category_id, title, isbn) VALUES (1, 1, 'The Great Gatsby', '1234567890'), (2, 2, '1984', '0987654321');
INSERT INTO users (name, email, password, role) VALUES ('John Doe', 'user@example.com', '$2y$10$examplehashedpassword', 'user');  -- Replace with actual hash