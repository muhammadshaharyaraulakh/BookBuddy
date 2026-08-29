USE BookBuddy;
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(50) NOT NULL, 
    username VARCHAR(50) NOT NULL,     
    email VARCHAR(150) NOT NULL UNIQUE, 
    password VARCHAR(255) NOT NULL,     
    profileImage VARCHAR(255) DEFAULT NULL,   
    role ENUM('user','admin') DEFAULT 'user', 
    subscribed ENUM('subscribed','unsubscribed') DEFAULT 'unsubscribed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



CREATE TABLE book (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255) NOT NULL,
    ISBN VARCHAR(20) DEFAULT NULL,
    publishDate DATE,
    Publisher VARCHAR(255) NOT NULL,
    Original_Price INT NOT NULL,
    Discount_Percentage INT,
    Discount_Price INT 
    AS (ROUND(Original_Price * (100 - IFNULL(Discount_Percentage,0)) / 100)) 
    STORED,
    Stock INT NOT NULL,
    description_para_1 TEXT NOT NULL,
    description_para_2 TEXT,
    coverImage VARCHAR(255) NOT NULL,
    category_id INT,
    totalSales INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);


CREATE TABLE deals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    discount_percentage INT NOT NULL,
    start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_time TIMESTAMP GENERATED ALWAYS AS (DATE_ADD(start_time, INTERVAL 24 HOUR)),
    status ENUM('active', 'expired') DEFAULT 'active',
    FOREIGN KEY (book_id) REFERENCES book(id) ON DELETE CASCADE
);


CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    body TEXT NOT NULL,
    book_id INT NOT NULL,
    user_id INT NOT NULL,
    ratings INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (book_id) REFERENCES book(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
);





CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES book(id) ON DELETE CASCADE
);


CREATE TABLE user_address (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    province VARCHAR(50) NOT NULL,
    city VARCHAR(100) NOT NULL,
    postcode VARCHAR(20) NOT NULL,
    address TEXT NOT NULL,
    contact VARCHAR(11) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
        REFERENCES user(id)
        ON DELETE CASCADE
);

CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    order_status ENUM('processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'processing',
    shipping_address_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
    FOREIGN KEY (shipping_address_id) REFERENCES user_address(id) ON DELETE SET NULL
);

CREATE TABLE order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    book_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES book(id) ON DELETE CASCADE
);
