<?php
require __DIR__ . "/config/config.php";

echo "Seeding database...\n";

// 1. Users
$password = password_hash("password123", PASSWORD_BCRYPT);
$connection->exec("INSERT INTO user (fullname, username, email, password, role, profileImage) VALUES 
('Admin User', 'admin', 'admin@example.com', '$password', 'admin', 'teenager-student-girl-yellow-pointing-finger-side-copy.png'),
('Standard User', 'user', 'user@example.com', '$password', 'user', 'teenager-student-girl-yellow-pointing-finger-side-copy.png')");
echo "Added 1 admin and 1 user.\n";

// 2. Categories
$connection->exec("INSERT INTO categories (title) VALUES 
('Featured Books'), 
('Programming'), 
('Sci-Fi & Fantasy'), 
('Business & Economics'), 
('Biography')");
echo "Added categories.\n";

// Fetch category IDs
$catStmt = $connection->query("SELECT id FROM categories");
$categoryIds = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// 3. Books
$books = [
    ['The Pragmatic Programmer', 'David Thomas', '9780135957059', 'Addison-Wesley', 45, 10],
    ['Clean Code', 'Robert C. Martin', '9780132350884', 'Prentice Hall', 50, 0],
    ['Dune', 'Frank Herbert', '9780441172719', 'Chilton Books', 20, 15],
    ['Foundation', 'Isaac Asimov', '9780553293357', 'Gnome Press', 15, 0],
    ['Thinking, Fast and Slow', 'Daniel Kahneman', '9780374533557', 'Farrar, Straus and Giroux', 30, 20],
    ['Atomic Habits', 'James Clear', '9780735211292', 'Avery', 25, 0],
    ['Steve Jobs', 'Walter Isaacson', '9781451648539', 'Simon & Schuster', 35, 5],
    ['Elon Musk', 'Ashlee Vance', '9780062301239', 'Ecco', 28, 0],
    ['Design Patterns', 'Erich Gamma', '9780201633610', 'Addison-Wesley', 55, 25],
    ['Refactoring', 'Martin Fowler', '9780134757599', 'Addison-Wesley', 60, 0],
    ['The Martian', 'Andy Weir', '9780553418026', 'Crown Publishing', 18, 10],
    ['Neuromancer', 'William Gibson', '9780441569595', 'Ace', 16, 0],
    ['Zero to One', 'Peter Thiel', '9780804139298', 'Crown Business', 22, 10],
    ['The Lean Startup', 'Eric Ries', '9780307887894', 'Crown Business', 24, 0],
    ['Shoe Dog', 'Phil Knight', '9781501135927', 'Simon & Schuster', 26, 15],
    ['Sapiens', 'Yuval Noah Harari', '9780062316097', 'Harper', 28, 0],
    ['1984', 'George Orwell', '9780451524935', 'Secker & Warburg', 12, 0],
    ['Brave New World', 'Aldous Huxley', '9780060850524', 'Chatto & Windus', 14, 5],
    ['Code Complete', 'Steve McConnell', '9780735619678', 'Microsoft Press', 65, 30],
    ['Mythical Man-Month', 'Frederick P. Brooks Jr.', '9780201835953', 'Addison-Wesley', 40, 0],
    ['Ender\'s Game', 'Orson Scott Card', '9780812550702', 'Tor Books', 17, 10],
    ['Snow Crash', 'Neal Stephenson', '9780553380958', 'Bantam', 19, 0],
    ['Rich Dad Poor Dad', 'Robert T. Kiyosaki', '9781612680194', 'Plata Publishing', 21, 5],
    ['Freakonomics', 'Steven D. Levitt', '9780060731335', 'William Morrow', 23, 0],
    ['Becoming', 'Michelle Obama', '9781524763138', 'Crown Publishing', 30, 20],
];

$stmt = $connection->prepare("INSERT INTO book (title, author, ISBN, publishDate, Publisher, Original_Price, Discount_Percentage, Stock, description_para_1, description_para_2, coverImage, category_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$bookIds = [];
foreach ($books as $index => $book) {
    $catId = $categoryIds[array_rand($categoryIds)];
    $stmt->execute([
        $book[0], // title
        $book[1], // author
        $book[2], // ISBN
        '2022-01-01', // publishDate
        $book[3], // Publisher
        $book[4], // Original_Price
        $book[5], // Discount_Percentage
        100, // Stock
        'This is the first paragraph of the description for ' . $book[0],
        'This is the second paragraph for more details.',
        'placeholder.jpg', // coverImage (make sure this exists or it will just be broken)
        $catId
    ]);
    $bookIds[] = $connection->lastInsertId();
}
echo "Added 25 books.\n";

// 4. Deals
$dealStmt = $connection->prepare("INSERT INTO deals (book_id, discount_percentage, start_date, duration_days) VALUES (?, ?, ?, ?)");
for ($i = 0; $i < 5; $i++) {
    $bookId = $bookIds[array_rand($bookIds)];
    // Ensure book is not already in a deal
    $check = $connection->prepare("SELECT id FROM deals WHERE book_id = ?");
    $check->execute([$bookId]);
    if (!$check->fetch()) {
        $dealStmt->execute([$bookId, 25, date('Y-m-d'), 7]);
    }
}
echo "Added deals.\n";

echo "Database seeded successfully!\n";
