<?php
/**
 * BookBuddy Complete Database Seeder & Schema Initializer
 * Designed for local development and zero-config Railway/Cloud deployment.
 */
require_once __DIR__ . "/config/config.php";

// Section 1: Ensure Database Schema Exists
$schemaStatements = [
    "CREATE TABLE IF NOT EXISTS user (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullname VARCHAR(50) NOT NULL,
        username VARCHAR(50) NOT NULL UNIQUE,
        email VARCHAR(150) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        profileImage VARCHAR(255) DEFAULT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(50) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS book (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        author VARCHAR(255) NOT NULL,
        ISBN VARCHAR(20) DEFAULT NULL,
        publishDate DATE DEFAULT NULL,
        Publisher VARCHAR(255) NOT NULL,
        Original_Price INT NOT NULL,
        Discount_Percentage INT DEFAULT 0,
        Discount_Price INT AS (
            ROUND(Original_Price * (100 - IFNULL(Discount_Percentage, 0)) / 100)
        ) STORED,
        Stock INT NOT NULL DEFAULT 0,
        description_para_1 TEXT NOT NULL,
        description_para_2 TEXT DEFAULT NULL,
        coverImage VARCHAR(255) NOT NULL,
        category_id INT DEFAULT NULL,
        totalSales INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS deals (
        id INT AUTO_INCREMENT PRIMARY KEY,
        book_id INT NOT NULL,
        discount_percentage INT NOT NULL,
        start_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        end_time TIMESTAMP GENERATED ALWAYS AS (DATE_ADD(start_time, INTERVAL 24 HOUR)),
        status ENUM('active', 'processing', 'expired') DEFAULT 'active',
        lock_token VARCHAR(64) DEFAULT NULL,
        processed_at DATETIME DEFAULT NULL,
        FOREIGN KEY (book_id) REFERENCES book(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS reviews (
        id INT AUTO_INCREMENT PRIMARY KEY,
        body TEXT NOT NULL,
        book_id INT NOT NULL,
        user_id INT NOT NULL,
        ratings INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_book_review (user_id, book_id),
        FOREIGN KEY (book_id) REFERENCES book(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS cart (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY unique_user_cart_item (user_id, book_id),
        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
        FOREIGN KEY (book_id) REFERENCES book(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS user_address (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        province VARCHAR(50) NOT NULL,
        district VARCHAR(100) DEFAULT NULL,
        city VARCHAR(100) NOT NULL,
        postcode VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        contact VARCHAR(20) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_amount DECIMAL(10, 2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash on Delivery',
        payment_status ENUM('pending', 'paid', 'completed', 'failed') NOT NULL DEFAULT 'pending',
        order_status ENUM('processing', 'shipped', 'delivered', 'cancelled', 'returned') NOT NULL DEFAULT 'processing',
        shipping_address_id INT DEFAULT NULL,
        shipping_address_text TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES user(id) ON DELETE CASCADE,
        FOREIGN KEY (shipping_address_id) REFERENCES user_address(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",

    "CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT NOT NULL,
        book_id INT DEFAULT NULL,
        book_title VARCHAR(255) DEFAULT NULL,
        quantity INT NOT NULL,
        price_at_purchase DECIMAL(10, 2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
        FOREIGN KEY (book_id) REFERENCES book(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($schemaStatements as $sql) {
    $connection->exec($sql);
}

// Section 2: Seed Default Admin & Customer Accounts
$defaultUsers = [
    [
        "fullname" => "AliAulakh",
        "username" => "aliraza1",
        "email" => "muhammadshaharyaraulakh@gmail.com",
        "password" => '$2y$12$p0pKKnA9gSsgmvbHE.4A5enFVLSd5VH9o4SXU1lvV.XYuEpI.UB2y',
        "role" => "admin"
    ],
    [
        "fullname" => "AliRazaJutt",
        "username" => "aliraza123",
        "email" => "shaharyaraulakh@gmail.com",
        "password" => '$2y$12$2sH3eGv0YyE0Hl2u9rZ3jO6o8P6sD4p2Q1l0W8m7K5j4H3g2F1e0a',
        "role" => "user"
    ]
];

$userCheckStmt = $connection->prepare("SELECT id FROM user WHERE username = :username OR email = :email LIMIT 1");
$userInsertStmt = $connection->prepare("
    INSERT INTO user (fullname, username, email, password, role) 
    VALUES (:fullname, :username, :email, :password, :role)
");

foreach ($defaultUsers as $u) {
    $userCheckStmt->execute([":username" => $u["username"], ":email" => $u["email"]]);
    if (!$userCheckStmt->fetch()) {
        $userInsertStmt->execute([
            ":fullname" => $u["fullname"],
            ":username" => $u["username"],
            ":email" => $u["email"],
            ":password" => $u["password"],
            ":role" => $u["role"]
        ]);
    }
}

// Section 3: Seed Active Categories
$categoryNames = [
    "Biographies",
    "Religious",
    "Programming",
    "Philosophy",
    "Fiction",
    "DevOps"
];

$categoryMap = [];
$checkCatStmt = $connection->prepare("SELECT id, title FROM categories WHERE title = :title LIMIT 1");
$insertCatStmt = $connection->prepare("INSERT INTO categories (title) VALUES (:title)");

foreach ($categoryNames as $catName) {
    $checkCatStmt->execute([":title" => $catName]);
    $existing = $checkCatStmt->fetch(PDO::FETCH_OBJ);
    if ($existing) {
        $categoryMap[$catName] = (int)$existing->id;
    } else {
        $insertCatStmt->execute([":title" => $catName]);
        $categoryMap[$catName] = (int)$connection->lastInsertId();
    }
}

// Section 4: Seed All 61 Curated Books
$books = array (
  0 => 
  array (
    'title' => 'The Pragmatic Programmer',
    'author' => 'Andrew Hunt and David Thomas',
    'isbn' => '9780201616224',
    'publishDate' => '1999-10-30',
    'publisher' => 'Addison Wesley',
    'price' => 50,
    'stock' => 40,
    'category' => 'Programming',
    'image' => 'pragmatic_programmer.webp',
    'desc1' => 'The Pragmatic Programmer cuts through the increasing specialization and technicalities of modern software development to examine the core process taking a requirement and producing working maintainable code that delights users.',
    'desc2' => 'Covered topics range from personal responsibility and career development to architectural techniques for keeping your code flexible and easy to adapt and reuse. Learn how to write flexible dynamic and adaptable code while avoiding common pitfalls.',
  ),
  1 => 
  array (
    'title' => 'Design Patterns',
    'author' => 'Erich Gamma and Richard Helm',
    'isbn' => '9780201633610',
    'publishDate' => '1994-11-10',
    'publisher' => 'Addison Wesley',
    'price' => 55,
    'stock' => 25,
    'category' => 'Programming',
    'image' => 'design_patterns.webp',
    'desc1' => 'Capturing a wealth of experience about the design of object oriented software four top notch designers present a catalog of simple and succinct solutions to commonly occurring design problems.',
    'desc2' => 'The 23 patterns contained here allow designers to create more flexible elegant and ultimately reusable designs without having to rediscover the design solutions themselves. Includes practical examples in modern object oriented languages.',
  ),
  2 => 
  array (
    'title' => 'Refactoring',
    'author' => 'Martin Fowler',
    'isbn' => '9780201485677',
    'publishDate' => '1999-07-08',
    'publisher' => 'Addison Wesley',
    'price' => 48,
    'stock' => 34,
    'category' => 'Programming',
    'image' => 'refactoring.webp',
    'desc1' => 'Refactoring is a controlled technique for improving the design of an existing code base. Its essence is applying a series of small behavior preserving transformations each of which too small to be worth doing but the cumulative effect is radical.',
    'desc2' => 'Martin Fowler shows you where opportunities for refactoring typically occur and how to make code easier to understand and cheaper to modify without introducing new defects into existing applications.',
  ),
  3 => 
  array (
    'title' => 'Beyond Good and Evil',
    'author' => 'Friedrich Nietzsche',
    'isbn' => '9780140449235',
    'publishDate' => '2003-01-30',
    'publisher' => 'Penguin Classics',
    'price' => 20,
    'stock' => 45,
    'category' => 'Philosophy',
    'image' => 'beyond_good_and_evil.webp',
    'desc1' => 'Beyond Good and Evil confirmed Nietzsches position as the towering European philosopher of his age. The work dramatically rejects traditional Western thought and moral systems.',
    'desc2' => 'Through aphorisms and essays Nietzsche explores the will to power master and slave morality and the psychology behind philosophical assertions.',
  ),
  4 => 
  array (
    'title' => 'Meditations',
    'author' => 'Marcus Aurelius',
    'isbn' => '9780140449334',
    'publishDate' => '2006-04-27',
    'publisher' => 'Penguin Classics',
    'price' => 18,
    'stock' => 60,
    'category' => 'Philosophy',
    'image' => 'meditations.webp',
    'desc1' => 'Marcus Aurelius was Emperor of Rome between 161 and 180 and was considered the last of the Five Good Emperors. Meditations represents his personal reflections and private journal.',
    'desc2' => 'Recorded as notes to himself while on military campaign the work is a timeless guide to Stoic philosophy inner fortitude duty and tranquility in the face of chaos.',
  ),
  5 => 
  array (
    'title' => 'Critique of Pure Reason',
    'author' => 'Immanuel Kant',
    'isbn' => '9780521657297',
    'publishDate' => '1999-01-28',
    'publisher' => 'Cambridge University Press',
    'price' => 35,
    'stock' => 18,
    'category' => 'Philosophy',
    'image' => 'critique_of_pure_reason.webp',
    'desc1' => 'Kants Critique of Pure Reason is widely regarded as one of the most influential works in the history of Western philosophy challenging traditional metaphysics and epistemology.',
    'desc2' => 'Kant investigates the limits and scope of human knowledge synthesizing rationalism and empiricism into a revolutionary transcendental idealism.',
  ),
  6 => 
  array (
    'title' => 'The Republic',
    'author' => 'Plato',
    'isbn' => '9780140455113',
    'publishDate' => '2007-05-31',
    'publisher' => 'Penguin Classics',
    'price' => 22,
    'stock' => 50,
    'category' => 'Philosophy',
    'image' => 'the_republic.webp',
    'desc1' => 'Platos Republic is a cornerstone of political thought and philosophy framed as a Socratic dialogue exploring justice the ideal city state and human nature.',
    'desc2' => 'Featuring the famous Allegory of the Cave and reflections on philosopher kings the text continues to stimulate lively debate across ethics government and education.',
  ),
  7 => 
  array (
    'title' => 'Steve Jobs',
    'author' => 'Walter Isaacson',
    'isbn' => '9781451648539',
    'publishDate' => '2011-10-24',
    'publisher' => 'Simon Schuster',
    'price' => 35,
    'stock' => 65,
    'category' => 'Biographies',
    'image' => 'steve_jobs.webp',
    'desc1' => 'Based on more than forty interviews with Steve Jobs conducted over two years as well as interviews with more than a hundred family members friends adversaries and colleagues.',
    'desc2' => 'Walter Isaacson has written a riveting story of the roller coaster life and searingly intense personality of a creative entrepreneur whose passion for perfection revolutionized six industries.',
  ),
  8 => 
  array (
    'title' => 'Leonardo da Vinci',
    'author' => 'Walter Isaacson',
    'isbn' => '9781501139154',
    'publishDate' => '2017-10-17',
    'publisher' => 'Simon Schuster',
    'price' => 36,
    'stock' => 17,
    'category' => 'Biographies',
    'image' => 'leonardo_da_vinci.webp',
    'desc1' => 'Leonardo da Vinci was historys most creative genius. Walter Isaacson weaves together da Vincis art science and curiosity using thousands of pages from his personal notebooks.',
    'desc2' => 'From the Mona Lisa and The Last Supper to studies of anatomy optics birds and hydraulics discover how a passion for observation unlocked boundless imagination.',
  ),
  9 => 
  array (
    'title' => 'Alexander Hamilton',
    'author' => 'Ron Chernow',
    'isbn' => '9780143034759',
    'publishDate' => '2005-03-29',
    'publisher' => 'Penguin Books',
    'price' => 32,
    'stock' => 25,
    'category' => 'Biographies',
    'image' => 'alexander_hamilton.webp',
    'desc1' => 'Ron Chernows monumental biography of Alexander Hamilton inspired the Broadway musical sensation and reveals the vibrant legacy of Americas foremost founding financial visionary.',
    'desc2' => 'An illegitimate orphan from the Caribbean Hamilton rose to become George Washingtons chief aide first Treasury Secretary and architect of the modern American economic system.',
  ),
  10 => 
  array (
    'title' => 'The World Religions',
    'author' => 'Huston Smith',
    'isbn' => '9780061660184',
    'publishDate' => '2009-03-10',
    'publisher' => 'HarperOne',
    'price' => 26,
    'stock' => 35,
    'category' => 'Religious',
    'image' => 'world_religions.webp',
    'desc1' => 'Huston Smiths masterwork is the essential introduction to the great faiths of humanity explaining the inner teachings and spiritual traditions of Hinduism Buddhism Confucianism Daoism Judaism Christianity and Islam.',
    'desc2' => 'Written with warmth eloquence and deep philosophical insight this definitive guide highlights the enduring wisdom and shared spiritual heritage uniting humankind across cultures.',
  ),
  11 => 
  array (
    'title' => 'Clean Code by Shaharyar',
    'author' => 'Maurice Lombard',
    'isbn' => '1234567812567',
    'publishDate' => '2025-07-17',
    'publisher' => 'Stars Publishers',
    'price' => 45,
    'stock' => 60,
    'category' => 'Programming',
    'image' => '1788873284_CleanCodebyShaharyar.jpg',
    'desc1' => 'Even bad code can function but if code is not clean it can bring a development organization to its knees. Every year countless hours and significant resources are lost because of poorly written code. Clean Code is divided into three parts including patterns of writing clean code and case studies of increasing complexity.',
    'desc2' => 'This book is a must read for any developer software engineer project manager team lead or systems analyst with an interest in producing better code. You will learn how to tell the difference between good code and bad code and how to transform bad code into good code.',
  ),
  12 => 
  array (
    'title' => 'The Great Gatsby',
    'author' => 'F. Scott Fitzgerald',
    'isbn' => '9780743273565',
    'publishDate' => '1925-04-10',
    'publisher' => 'Scribner',
    'price' => 24,
    'stock' => 45,
    'category' => 'Fiction',
    'image' => 'the_great_gatsby.webp',
    'desc1' => 'Set in the Jazz Age on Long Island, the novel depicts narrator Nick Carraway\'s interactions with mysterious millionaire Jay Gatsby and Gatsby\'s obsession to reunite with his former lover, Daisy Buchanan. A quintessential reflection on the American Dream.',
    'desc2' => 'Fitzgerald\'s masterpiece explores themes of decadence, idealism, social upheaval, and excess, creating a portrait of the Roaring Twenties that has been described as a cautionary tale regarding the American Dream.',
  ),
  13 => 
  array (
    'title' => 'To Kill a Mockingbird',
    'author' => 'Harper Lee',
    'isbn' => '9780060935467',
    'publishDate' => '1960-07-11',
    'publisher' => 'J.B. Lippincott & Co.',
    'price' => 28,
    'stock' => 38,
    'category' => 'Fiction',
    'image' => 'to_kill_a_mockingbird.webp',
    'desc1' => 'The unforgettable novel of a childhood in a sleepy Southern town and the crisis of conscience that rocked it. Compassionate, dramatic, and deeply moving, To Kill a Mockingbird takes readers to the roots of human behavior.',
    'desc2' => 'Through the eyes of Scout Finch, the book addresses racial injustice and the destruction of innocence with poignant wisdom, winning the Pulitzer Prize and becoming a global classic.',
  ),
  14 => 
  array (
    'title' => '1984',
    'author' => 'George Orwell',
    'isbn' => '9780451524935',
    'publishDate' => '1949-06-08',
    'publisher' => 'Secker & Warburg',
    'price' => 22,
    'stock' => 50,
    'category' => 'Fiction',
    'image' => '1984.webp',
    'desc1' => 'Winston Smith toes the Party line, rewriting history to satisfy the Ministry of Truth. With each lie he writes, he grows to hate the Party that yearns for power for its own sake and persecutes individualism.',
    'desc2' => 'Orwell’s chilling prophecy about the future remains ever-relevant, examining totalitarian surveillance, doublethink, and the power of truth in a controlled society.',
  ),
  15 => 
  array (
    'title' => 'Pride and Prejudice',
    'author' => 'Jane Austen',
    'isbn' => '9780141439518',
    'publishDate' => '1813-01-28',
    'publisher' => 'T. Egerton',
    'price' => 20,
    'stock' => 42,
    'category' => 'Fiction',
    'image' => 'pride_and_prejudice.webp',
    'desc1' => 'The romantic clash between the opinionated Elizabeth Bennet and her proud beau, Mr. Darcy, is a splendid performance of civilized sparring, social commentary, and emotional discovery.',
    'desc2' => 'Jane Austen\'s radiant wit and acute perception of human foibles shine in this classic of 19th-century British literature, showing how love conquers class and stubbornness.',
  ),
  16 => 
  array (
    'title' => 'The Catcher in the Rye',
    'author' => 'J.D. Salinger',
    'isbn' => '9780316769488',
    'publishDate' => '1951-07-16',
    'publisher' => 'Little, Brown and Company',
    'price' => 26,
    'stock' => 30,
    'category' => 'Fiction',
    'image' => 'the_catcher_in_the_rye.webp',
    'desc1' => 'The hero-narrator of The Catcher in the Rye is an ancient sixteen-year-old, a native New Yorker named Holden Caulfield. Through his distinctive voice, Salinger captures the angst of adolescence and the search for identity.',
    'desc2' => 'Holden\'s search for authenticity in an adult world filled with phoniness has struck a chord with generations of readers across the world.',
  ),
  17 => 
  array (
    'title' => 'Brave New World',
    'author' => 'Aldous Huxley',
    'isbn' => '9780060850524',
    'publishDate' => '1932-01-01',
    'publisher' => 'Chatto & Windus',
    'price' => 25,
    'stock' => 35,
    'category' => 'Fiction',
    'image' => 'brave_new_world.webp',
    'desc1' => 'Aldous Huxley\'s profoundly important classic of world literature is a searching vision of an unequal, technologically advanced future where humans are genetically bred and conditioned.',
    'desc2' => 'A darkly satiric vision of a utopian world where technological wizardry, psychological conditioning, and mindless consumerism eliminate sorrow at the expense of human soul and freedom.',
  ),
  18 => 
  array (
    'title' => 'The Hobbit',
    'author' => 'J.R.R. Tolkien',
    'isbn' => '9780547928227',
    'publishDate' => '1937-09-21',
    'publisher' => 'George Allen & Unwin',
    'price' => 30,
    'stock' => 48,
    'category' => 'Fiction',
    'image' => 'the_hobbit.webp',
    'desc1' => 'Bilbo Baggins is a hobbit who enjoys a comfortable, unambitious life. But his contentment is disturbed when Gandalf the wizard and a company of thirteen dwarves arrive on his doorstep to embark on a quest.',
    'desc2' => 'An epic adventure through Middle-earth facing trolls, goblins, giant spiders, and Smaug the dragon, The Hobbit is the foundational classic of modern fantasy.',
  ),
  19 => 
  array (
    'title' => 'Fahrenheit 451',
    'author' => 'Ray Bradbury',
    'isbn' => '9781451673319',
    'publishDate' => '1953-10-19',
    'publisher' => 'Ballantine Books',
    'price' => 23,
    'stock' => 40,
    'category' => 'Fiction',
    'image' => 'fahrenheit_451.webp',
    'desc1' => 'Guy Montag is a fireman. His job is to destroy the most illegal of all commodities: the printed book, along with the houses in which they are hidden. Montag never questions the destruction until he meets a young eccentric.',
    'desc2' => 'Ray Bradbury’s brilliant, poetic masterpiece warns against censorship and television-driven apathy, celebrating the redemptive power of the written word.',
  ),
  20 => 
  array (
    'title' => 'Crime and Punishment',
    'author' => 'Fyodor Dostoevsky',
    'isbn' => '9780140449136',
    'publishDate' => '1866-01-01',
    'publisher' => 'The Russian Messenger',
    'price' => 27,
    'stock' => 32,
    'category' => 'Fiction',
    'image' => 'crime_and_punishment.webp',
    'desc1' => 'Raskolnikov, a destitute and desperate former student, wanders through the slums of St Petersburg and commits a random murder without remorse. He imagines himself to be an extraordinary man above moral laws.',
    'desc2' => 'As the police detective draws closer, Raskolnikov finds his guilt growing into a psychological battlefield between arrogance, despair, and spiritual redemption.',
  ),
  21 => 
  array (
    'title' => 'The Alchemist',
    'author' => 'Paulo Coelho',
    'isbn' => '9780062315007',
    'publishDate' => '1988-01-01',
    'publisher' => 'HarperTorch',
    'price' => 22,
    'stock' => 55,
    'category' => 'Fiction',
    'image' => 'the_alchemist.webp',
    'desc1' => 'Paulo Coelho\'s masterpiece tells the mystical story of Santiago, an Andalusian shepherd boy who yearns to travel in search of a worldly treasure. His quest leads him to treasures within.',
    'desc2' => 'The story of the treasures Santiago finds along the way teaches us, as only a few stories have done, about the essential wisdom of listening to our hearts and following our dreams.',
  ),
  22 => 
  array (
    'title' => 'The Phoenix Project',
    'author' => 'Gene Kim, Kevin Behr, George Spafford',
    'isbn' => '9781942788294',
    'publishDate' => '2013-01-10',
    'publisher' => 'IT Revolution Press',
    'price' => 42,
    'stock' => 35,
    'category' => 'DevOps',
    'image' => 'the_phoenix_project.webp',
    'desc1' => 'Bill, an IT manager at Parts Unlimited, has been tasked with the impossible: rescue an over-budget, delayed mission-critical project before the company faces bankruptcy. A gripping novel for tech leaders.',
    'desc2' => 'Demonstrating how DevOps methodologies like the Three Ways and lean manufacturing principles revolutionize corporate IT, this book is essential reading for software engineers and managers.',
  ),
  23 => 
  array (
    'title' => 'The DevOps Handbook',
    'author' => 'Gene Kim, Jez Humble, Patrick Debois, John Willis',
    'isbn' => '9781942788003',
    'publishDate' => '2016-10-06',
    'publisher' => 'IT Revolution Press',
    'price' => 48,
    'stock' => 28,
    'category' => 'DevOps',
    'image' => 'the_devops_handbook.webp',
    'desc1' => 'For decades, technology leaders have struggled to balance agility, reliability, and security. The DevOps Handbook shows leaders how to replicate world-class stability and high deployment cadence.',
    'desc2' => 'Packed with case studies from Amazon, Netflix, and Google, it outlines practical steps for CI/CD, telemetry, automated testing, and blameless organizational culture.',
  ),
  24 => 
  array (
    'title' => 'Accelerate',
    'author' => 'Nicole Forsgren, Jez Humble, Gene Kim',
    'isbn' => '9781942788331',
    'publishDate' => '2018-03-27',
    'publisher' => 'IT Revolution Press',
    'price' => 38,
    'stock' => 40,
    'category' => 'DevOps',
    'image' => 'accelerate.webp',
    'desc1' => 'How can we apply technology to drive business value? Accelerate answers this question using four years of rigorous DORA research spanning thousands of engineering organizations worldwide.',
    'desc2' => 'Learn the four key metrics of software delivery performance and how culture, automation, and leadership directly impact company profitability and engineer happiness.',
  ),
  25 => 
  array (
    'title' => 'Site Reliability Engineering',
    'author' => 'Betsy Beyer, Chris Jones, Jennifer Petoff, Niall Murphy',
    'isbn' => '9781491929124',
    'publishDate' => '2016-04-16',
    'publisher' => 'O\'Reilly Media',
    'price' => 55,
    'stock' => 25,
    'category' => 'DevOps',
    'image' => 'site_reliability_engineering.webp',
    'desc1' => 'Google’s pioneering Site Reliability Engineering team reveals how their commitment to treating operations as a software engineering problem enables massive internet-scale systems to run seamlessly.',
    'desc2' => 'Covers service level objectives (SLOs), error budgets, incident response, elimination of toil, and distributed tracing in modern cloud environments.',
  ),
  26 => 
  array (
    'title' => 'Continuous Delivery',
    'author' => 'Jez Humble and David Farley',
    'isbn' => '9780321601919',
    'publishDate' => '2010-07-27',
    'publisher' => 'Addison-Wesley Professional',
    'price' => 52,
    'stock' => 30,
    'category' => 'DevOps',
    'image' => 'continuous_delivery.webp',
    'desc1' => 'Getting software released to users is often a painful, risky, and time-consuming process. This groundbreaking book sets out the principles and technical practices that enable rapid, incremental delivery.',
    'desc2' => 'Explore deployment pipelines, automated infrastructure management, configuration versioning, and zero-downtime release strategies.',
  ),
  27 => 
  array (
    'title' => 'Kubernetes: Up and Running',
    'author' => 'Kelsey Hightower, Brendan Burns, Joe Beda',
    'isbn' => '9781492046530',
    'publishDate' => '2019-09-17',
    'publisher' => 'O\'Reilly Media',
    'price' => 46,
    'stock' => 35,
    'category' => 'DevOps',
    'image' => 'kubernetes_up_and_running.webp',
    'desc1' => 'Written by co-creators of Kubernetes and industry leaders, this guide teaches you how to build, deploy, and maintain cloud-native applications on production-grade container clusters.',
    'desc2' => 'Hands-on walk-throughs explain Pods, Deployments, ReplicaSets, Services, Ingress controllers, daemon sets, and automated rollouts with practical clarity.',
  ),
  28 => 
  array (
    'title' => 'Docker Deep Dive',
    'author' => 'Nigel Poulton',
    'isbn' => '9781521822807',
    'publishDate' => '2017-06-25',
    'publisher' => 'Independently Published',
    'price' => 36,
    'stock' => 45,
    'category' => 'DevOps',
    'image' => 'docker_deep_dive.webp',
    'desc1' => 'Recognized by Docker captains and developers as the master guide to containerization. Breaks down Linux namespaces, cgroups, UnionFS, and OCI image layers into simple, intuitive concepts.',
    'desc2' => 'Master multi-stage Dockerfiles, image optimization, rootless containers, secure registries, and container networking for real production workflows.',
  ),
  29 => 
  array (
    'title' => 'Infrastructure as Code',
    'author' => 'Kief Morris',
    'isbn' => '9781491924402',
    'publishDate' => '2016-06-09',
    'publisher' => 'O\'Reilly Media',
    'price' => 44,
    'stock' => 32,
    'category' => 'DevOps',
    'image' => 'infrastructure_as_code.webp',
    'desc1' => 'Virtualization, cloud, and containers have made it easy to provision server components rapidly. This book shows how to model and manage modern dynamic cloud infrastructure as software code.',
    'desc2' => 'Learn architectural patterns for immutable servers, declarative orchestration, automated configuration tests, and serverless infrastructure scaling.',
  ),
  30 => 
  array (
    'title' => 'Terraform: Up & Running',
    'author' => 'Yevgeniy Brikman',
    'isbn' => '9781492046905',
    'publishDate' => '2019-10-08',
    'publisher' => 'O\'Reilly Media',
    'price' => 45,
    'stock' => 30,
    'category' => 'DevOps',
    'image' => 'terraform_up_and_running.webp',
    'desc1' => 'Terraform has become a key player in the DevOps world for deploying code to AWS, Azure, Google Cloud, and multi-cloud architectures. This book shows how to write clean, reusable HCL code.',
    'desc2' => 'Understand state management, remote backends, module design, zero-downtime server deployments, and automated testing for cloud resources.',
  ),
  31 => 
  array (
    'title' => 'Building Microservices',
    'author' => 'Sam Newman',
    'isbn' => '9781491950357',
    'publishDate' => '2015-02-20',
    'publisher' => 'O\'Reilly Media',
    'price' => 50,
    'stock' => 29,
    'category' => 'DevOps',
    'image' => 'building_microservices.webp',
    'desc1' => 'Distributed systems have become the standard for modern software engineering. Newman provides a comprehensive foundation for designing, establishing, and managing microservice architectures.',
    'desc2' => 'Covers service integration, breaking monolithic databases, asynchronous messaging, service discovery, security tokens, and distributed observability.',
  ),
  32 => 
  array (
    'title' => 'Clean Architecture',
    'author' => 'Robert C. Martin',
    'isbn' => '9780134494166',
    'publishDate' => '2017-09-10',
    'publisher' => 'Prentice Hall',
    'price' => 52,
    'stock' => 35,
    'category' => 'Programming',
    'image' => 'clean_architecture.webp',
    'desc1' => 'Building upon the successes of Clean Code, Uncle Bob Martin reveals the universal rules of software architecture, separation of concerns, and boundary discipline that stand the test of time.',
    'desc2' => 'Learn SOLID principles applied to components, boundaries between business rules and databases, and how to create architectures that are testable and resilient to change.',
  ),
  33 => 
  array (
    'title' => 'Effective Java',
    'author' => 'Joshua Bloch',
    'isbn' => '9780134685991',
    'publishDate' => '2017-12-27',
    'publisher' => 'Addison-Wesley Professional',
    'price' => 54,
    'stock' => 40,
    'category' => 'Programming',
    'image' => 'effective_java.webp',
    'desc1' => 'The definitive guide to Java best practices. Joshua Bloch, primary architect of Java Collections and core libraries, provides best-in-class advice on language idioms, concurrency, and API design.',
    'desc2' => 'Covers generics, lambdas, streams, immutability, serialization, and defensive programming with clear code examples that every software professional should master.',
  ),
  34 => 
  array (
    'title' => 'JavaScript: The Good Parts',
    'author' => 'Douglas Crockford',
    'isbn' => '9780596517748',
    'publishDate' => '2008-05-01',
    'publisher' => 'O\'Reilly Media',
    'price' => 32,
    'stock' => 45,
    'category' => 'Programming',
    'image' => 'javascript_good_parts.webp',
    'desc1' => 'Most programming languages contain good and bad parts, but JavaScript has more than its share of bad. Douglas Crockford scrapes away the bad features to expose the truly elegant core of the language.',
    'desc2' => 'An indispensable handbook focusing on functions, loose typing, dynamic objects, and expressive object literals that transformed modern web engineering.',
  ),
  35 => 
  array (
    'title' => 'You Don\'t Know JS: Scope & Closures',
    'author' => 'Kyle Simpson',
    'isbn' => '9781449335588',
    'publishDate' => '2014-03-24',
    'publisher' => 'O\'Reilly Media',
    'price' => 28,
    'stock' => 50,
    'category' => 'Programming',
    'image' => 'ydkjs_scope_closures.webp',
    'desc1' => 'No matter how much experience you have with JavaScript, odds are you don’t fully understand the language. This book dives into lexical scope, function declarations, hoisting, and closures.',
    'desc2' => 'Dispelling myths and giving you precise mental models of JavaScript engines, this concise title elevates your coding competence and debugging precision.',
  ),
  36 => 
  array (
    'title' => 'Fluent Python',
    'author' => 'Luciano Ramalho',
    'isbn' => '9781491946008',
    'publishDate' => '2015-08-20',
    'publisher' => 'O\'Reilly Media',
    'price' => 58,
    'stock' => 26,
    'category' => 'Programming',
    'image' => 'fluent_python.webp',
    'desc1' => 'Python’s simplicity lets you become productive quickly, but this often means you aren’t using everything it has to offer. Ramalho guides you through Python’s unique data model, special methods, and idioms.',
    'desc2' => 'Master iterators, generators, coroutines, context managers, decorators, and meta-programming to write expressive, idiomatic, and highly efficient Python code.',
  ),
  37 => 
  array (
    'title' => 'Introduction to Algorithms',
    'author' => 'Thomas Cormen, Charles Leiserson, Ronald Rivest, Clifford Stein',
    'isbn' => '9780262033848',
    'publishDate' => '2009-07-31',
    'publisher' => 'MIT Press',
    'price' => 68,
    'stock' => 20,
    'category' => 'Programming',
    'image' => 'intro_to_algorithms.webp',
    'desc1' => 'Globally known as \'CLRS\', this is the quintessential textbook on computer algorithms. It covers a broad range of algorithms in depth, yet makes their design and analysis accessible to all levels of readers.',
    'desc2' => 'From sorting and dynamic programming to graph traversal, flow networks, and NP-completeness, CLRS is the indispensable bible for software engineers.',
  ),
  38 => 
  array (
    'title' => 'Cracking the Coding Interview',
    'author' => 'Gayle Laakmann McDowell',
    'isbn' => '9780984782857',
    'publishDate' => '2015-07-01',
    'publisher' => 'CareerCup',
    'price' => 40,
    'stock' => 55,
    'category' => 'Programming',
    'image' => 'cracking_coding_interview.webp',
    'desc1' => 'Now in its 6th edition, Cracking the Coding Interview gives you the interview preparation you need to get the top software developer jobs at companies like Google, Apple, and Microsoft.',
    'desc2' => 'Features 189 programming questions and solutions, from big-O complexity and hash tables to system design and behavioral interviewing questions.',
  ),
  39 => 
  array (
    'title' => 'Designing Data-Intensive Applications',
    'author' => 'Martin Kleppmann',
    'isbn' => '9781449373320',
    'publishDate' => '2017-03-16',
    'publisher' => 'O\'Reilly Media',
    'price' => 56,
    'stock' => 34,
    'category' => 'Programming',
    'image' => 'designing_data_intensive_applications.webp',
    'desc1' => 'Data is at the center of many challenges in system design today. This modern masterpiece navigates the diverse landscape of databases, stream processing, batch engines, and distributed transactions.',
    'desc2' => 'Kleppmann explores replication, partitioning, consistency models, Paxos, Raft, and event streams, providing software architects with clarity on complex distributed tradeoffs.',
  ),
  40 => 
  array (
    'title' => 'Einstein: His Life and Universe',
    'author' => 'Walter Isaacson',
    'isbn' => '9780743264730',
    'publishDate' => '2007-04-10',
    'publisher' => 'Simon & Schuster',
    'price' => 36,
    'stock' => 30,
    'category' => 'Biographies',
    'image' => 'einstein_life_universe.webp',
    'desc1' => 'Based on newly released personal letters, Isaacson explores how an imaginative, insolent patent clerk revolutionized physics and unraveled the mysteries of space, time, and gravity.',
    'desc2' => 'A riveting biography that reveals how Einstein\'s scientific genius was intimately linked to his rebel character, curiosity, and steadfast humanism.',
  ),
  41 => 
  array (
    'title' => 'Elon Musk',
    'author' => 'Walter Isaacson',
    'isbn' => '9781982181284',
    'publishDate' => '2023-09-12',
    'publisher' => 'Simon & Schuster',
    'price' => 38,
    'stock' => 40,
    'category' => 'Biographies',
    'image' => 'elon_musk.webp',
    'desc1' => 'The astonishingly intimate story of the most fascinating and controversial innovator of our era—a rule-breaking visionary who helped lead the world into the era of electric vehicles, private space exploration, and artificial intelligence.',
    'desc2' => 'For two years, Isaacson shadowed Musk, attended his meetings, walked his factories with him, and spent hours interviewing him, his family, friends, coworkers, and adversaries.',
  ),
  42 => 
  array (
    'title' => 'Becoming',
    'author' => 'Michelle Obama',
    'isbn' => '9781524763138',
    'publishDate' => '2018-11-13',
    'publisher' => 'Crown',
    'price' => 34,
    'stock' => 45,
    'category' => 'Biographies',
    'image' => 'becoming_michelle_obama.webp',
    'desc1' => 'In a life filled with meaning and accomplishment, Michelle Obama has emerged as one of the most iconic and compelling women of our era. A deeply personal reckoning of a woman of soul and substance.',
    'desc2' => 'Warm, wise, and revelatory, Becoming is the memoir of a woman who has steadily defied expectations and whose story inspires others to do the same.',
  ),
  43 => 
  array (
    'title' => 'Long Walk to Freedom',
    'author' => 'Nelson Mandela',
    'isbn' => '9780316548182',
    'publishDate' => '1994-11-01',
    'publisher' => 'Little, Brown and Company',
    'price' => 32,
    'stock' => 28,
    'category' => 'Biographies',
    'image' => 'long_walk_to_freedom.webp',
    'desc1' => 'Nelson Mandela is one of the great moral and political leaders of our time: an international hero whose lifelong dedication to the fight against racial oppression in South Africa won him the Nobel Peace Prize.',
    'desc2' => 'Long Walk to Freedom tells the exhilarating story of Mandela\'s youth, his 27 years in prison, and his triumph in forging a democratic South Africa.',
  ),
  44 => 
  array (
    'title' => 'The Diary of a Young Girl',
    'author' => 'Anne Frank',
    'isbn' => '9780553296983',
    'publishDate' => '1947-06-25',
    'publisher' => 'Bantam',
    'price' => 20,
    'stock' => 50,
    'category' => 'Biographies',
    'image' => 'diary_of_young_girl.webp',
    'desc1' => 'Discovered in the attic in which she spent the last years of her life, Anne Frank’s remarkable diary has since become a world classic—a powerful reminder of the horrors of war and an eloquent testament to the human spirit.',
    'desc2' => 'A timeless masterpiece that chronicles a young girl\'s growth, aspirations, and courage while hiding from Nazi persecution in occupied Amsterdam.',
  ),
  45 => 
  array (
    'title' => 'Shoe Dog',
    'author' => 'Phil Knight',
    'isbn' => '9781501135910',
    'publishDate' => '2016-04-26',
    'publisher' => 'Scribner',
    'price' => 30,
    'stock' => 38,
    'category' => 'Biographies',
    'image' => 'shoe_dog.webp',
    'desc1' => 'Nike founder and board chairman Phil Knight shares the inside story of the company’s early days as an intrepid start-up and its evolution into one of the world’s most iconic, game-changing, and profitable brands.',
    'desc2' => 'Candid, humble, and entertaining, Shoe Dog is an honest memoir on resilience, risk-taking, and following the crazy ideas that redefine industries.',
  ),
  46 => 
  array (
    'title' => 'Churchill: A Life',
    'author' => 'Martin Gilbert',
    'isbn' => '9780805023961',
    'publishDate' => '1991-10-15',
    'publisher' => 'Henry Holt and Co.',
    'price' => 35,
    'stock' => 24,
    'category' => 'Biographies',
    'image' => 'churchill_a_life.webp',
    'desc1' => 'The master historian Martin Gilbert delivers the definitive one-volume biography of Sir Winston Churchill, tracing his extraordinary journey from Victorian schoolboy to World War II savior of Britain.',
    'desc2' => 'Drawing on official archives and personal correspondence, Gilbert reveals Churchill’s formidable rhetoric, military daring, and towering political resolve.',
  ),
  47 => 
  array (
    'title' => 'Benjamin Franklin: An American Life',
    'author' => 'Walter Isaacson',
    'isbn' => '9780684807614',
    'publishDate' => '2003-07-01',
    'publisher' => 'Simon & Schuster',
    'price' => 33,
    'stock' => 26,
    'category' => 'Biographies',
    'image' => 'benjamin_franklin.webp',
    'desc1' => 'In this authoritative biography, Walter Isaacson chronicles the life of America’s most delightful founding father, who was also its most brilliant scientist, inventor, diplomat, and writer.',
    'desc2' => 'Isaacson shows how Franklin’s practical wisdom, wit, and commitment to middle-class values helped define the character of democratic society.',
  ),
  48 => 
  array (
    'title' => 'Thus Spoke Zarathustra',
    'author' => 'Friedrich Nietzsche',
    'isbn' => '9780140441186',
    'publishDate' => '1883-01-01',
    'publisher' => 'Penguin Classics',
    'price' => 22,
    'stock' => 36,
    'category' => 'Philosophy',
    'image' => 'thus_spoke_zarathustra.webp',
    'desc1' => 'Nietzsche’s poetic masterpiece introduces the concepts of the Übermensch, the Will to Power, and the Eternal Recurrence of the same, spoken through the prophet Zarathustra descending from his mountain.',
    'desc2' => 'A foundational philosophical work written in soaring lyrical prose that challenges conventional morality and summons humanity to transcend itself.',
  ),
  49 => 
  array (
    'title' => 'Being and Time',
    'author' => 'Martin Heidegger',
    'isbn' => '9780674064256',
    'publishDate' => '1927-01-01',
    'publisher' => 'State University of New York Press',
    'price' => 35,
    'stock' => 20,
    'category' => 'Philosophy',
    'image' => 'being_and_time.webp',
    'desc1' => 'Being and Time revolutionized 20th-century European philosophy. Heidegger’s radical existential ontology analyzes \'Dasein\'—human existence situated within temporality, care, anxiety, and being-in-the-world.',
    'desc2' => 'Essential reading for continental philosophy, existentialism, and hermeneutics, profoundly impacting thinkers from Sartre and Merleau-Ponty to Derrida.',
  ),
  50 => 
  array (
    'title' => 'The Myth of Sisyphus',
    'author' => 'Albert Camus',
    'isbn' => '9780679733737',
    'publishDate' => '1942-01-01',
    'publisher' => 'Vintage',
    'price' => 24,
    'stock' => 40,
    'category' => 'Philosophy',
    'image' => 'myth_of_sisyphus.webp',
    'desc1' => '\'There is but one truly serious philosophical problem, and that is suicide.\' With this opening line, Camus confronts the absurd: the human desire for meaning in a silent, indifferent universe.',
    'desc2' => 'Camus concludes that one must imagine Sisyphus happy—affirming conscious rebellion, freedom, and passion in the face of the absurd.',
  ),
  51 => 
  array (
    'title' => 'Nicomachean Ethics',
    'author' => 'Aristotle',
    'isbn' => '9780140449495',
    'publishDate' => '1953-01-01',
    'publisher' => 'Penguin Classics',
    'price' => 20,
    'stock' => 35,
    'category' => 'Philosophy',
    'image' => 'nicomachean_ethics.webp',
    'desc1' => 'Aristotle’s treatise on moral philosophy examines how humans can achieve \'eudaimonia\'—flourishing, happiness, and moral excellence through cultivated virtue and rational action.',
    'desc2' => 'Introducing the famous Doctrine of the Mean (virtue as the balance between deficiency and excess), this work forms the backbone of Western ethical theory.',
  ),
  52 => 
  array (
    'title' => 'Letters from a Stoic',
    'author' => 'Seneca',
    'isbn' => '9780140442106',
    'publishDate' => '1969-01-01',
    'publisher' => 'Penguin Classics',
    'price' => 22,
    'stock' => 45,
    'category' => 'Philosophy',
    'image' => 'letters_from_a_stoic.webp',
    'desc1' => 'The Roman statesman and philosopher Seneca offers pragmatic, humane advice on grief, wealth, anger, friendship, and time management in letters to his friend Lucilius.',
    'desc2' => 'A profound guide to cultivating tranquility, endurance, and mindfulness, Seneca’s letters remain one of the most accessible masterpieces of Stoic philosophy.',
  ),
  53 => 
  array (
    'title' => 'Tao Te Ching',
    'author' => 'Lao Tzu',
    'isbn' => '9780140441313',
    'publishDate' => '1963-01-01',
    'publisher' => 'Penguin Classics',
    'price' => 18,
    'stock' => 50,
    'category' => 'Philosophy',
    'image' => 'tao_te_ching.webp',
    'desc1' => 'The primary classic of Taoism, the Tao Te Ching consists of 81 short, poetic verses teaching the Way (\'Tao\') of effortless action (\'Wu Wei\'), natural harmony, and humble leadership.',
    'desc2' => 'Simultaneously mystical and intensely practical, this ancient Chinese text provides solace and serene perspective to readers across centuries.',
  ),
  54 => 
  array (
    'title' => 'Man\'s Search for Meaning',
    'author' => 'Viktor E. Frankl',
    'isbn' => '9780807014295',
    'publishDate' => '1946-01-01',
    'publisher' => 'Beacon Press',
    'price' => 25,
    'stock' => 48,
    'category' => 'Philosophy',
    'image' => 'mans_search_for_meaning.webp',
    'desc1' => 'Psychiatrist Viktor Frankl’s memoir of surviving Auschwitz and Dachau describes how finding personal purpose and dignity enabled individuals to endure unimaginable suffering.',
    'desc2' => 'Frankl’s theory of logotherapy argues that our primary human drive is not pleasure or power, but the discovery and pursuit of meaning.',
  ),
  55 => 
  array (
    'title' => 'The Quran: English Translation',
    'author' => 'M.A.S. Abdel Haleem',
    'isbn' => '9780199535958',
    'publishDate' => '2004-05-13',
    'publisher' => 'Oxford University Press',
    'price' => 26,
    'stock' => 60,
    'category' => 'Religious',
    'image' => 'the_quran_oxford.webp',
    'desc1' => 'The Quran is the supreme authority in Islam, respected by over a billion people worldwide. Abdel Haleem’s modern English translation reproduces the rhythm, beauty, and solemn force of the original Arabic.',
    'desc2' => 'Includes detailed introductory notes, historical context, and surah commentaries that make this sacred text accessible and illuminating for all readers.',
  ),
  56 => 
  array (
    'title' => 'The Prophet',
    'author' => 'Kahlil Gibran',
    'isbn' => '9780394404288',
    'publishDate' => '1923-09-01',
    'publisher' => 'Alfred A. Knopf',
    'price' => 20,
    'stock' => 55,
    'category' => 'Religious',
    'image' => 'the_prophet_gibran.webp',
    'desc1' => 'Kahlil Gibran’s masterpiece of mystical prose poetry consists of 28 poetic essays spoken by the prophet Almustafa on topics such as love, marriage, children, giving, eating, work, joy, and freedom.',
    'desc2' => 'Translated into more than 100 languages, The Prophet continues to inspire spiritual seekers across cultures with its universal warmth and transcendent lyrical beauty.',
  ),
  57 => 
  array (
    'title' => 'A History of God',
    'author' => 'Karen Armstrong',
    'isbn' => '9780345384560',
    'publishDate' => '1993-01-01',
    'publisher' => 'Ballantine Books',
    'price' => 28,
    'stock' => 32,
    'category' => 'Religious',
    'image' => 'history_of_god.webp',
    'desc1' => 'A former nun and one of the world\'s foremost commentators on religious affairs, Karen Armstrong traces how humanity has understood the divine across 4,000 years of Judaism, Christianity, and Islam.',
    'desc2' => 'A tour-de-force of comparative religion examining Abrahamic faith traditions from monotheism’s origins to modern philosophical and secular movements.',
  ),
  58 => 
  array (
    'title' => 'Mere Christianity',
    'author' => 'C.S. Lewis',
    'isbn' => '9780060652920',
    'publishDate' => '1952-01-01',
    'publisher' => 'HarperOne',
    'price' => 24,
    'stock' => 42,
    'category' => 'Religious',
    'image' => 'mere_christianity.webp',
    'desc1' => 'Originating as a series of BBC radio broadcasts during the dark days of World War II, C.S. Lewis’s classic provides a rational defense of basic Christian ethics and spiritual philosophy.',
    'desc2' => 'Lewis applies clarity, logic, and common-sense analogies to universal moral questions, creating an enduring masterpiece of theological literature.',
  ),
  59 => 
  array (
    'title' => 'Muhammad: His Life Based on the Earliest Sources',
    'author' => 'Martin Lings',
    'isbn' => '9781594771538',
    'publishDate' => '1983-01-01',
    'publisher' => 'Inner Traditions',
    'price' => 32,
    'stock' => 38,
    'category' => 'Religious',
    'image' => 'muhammad_martin_lings.webp',
    'desc1' => 'Martin Lings\' biography of the Prophet Muhammad is internationally acclaimed as the finest and most comprehensive biography in English, grounded faithfully in 8th- and 9th-century Arabic sources.',
    'desc2' => 'With spellbinding narrative prose and spiritual reverence, Lings recounts the life, trials, revelations, and triumph of the founder of Islam.',
  ),
  60 => 
  array (
    'title' => 'The Tibetan Book of Living and Dying',
    'author' => 'Sogyal Rinpoche',
    'isbn' => '9780062508348',
    'publishDate' => '1992-09-16',
    'publisher' => 'HarperOne',
    'price' => 28,
    'stock' => 25,
    'category' => 'Religious',
    'image' => 'tibetan_book_living_dying.webp',
    'desc1' => 'A spiritual classic from one of the foremost Tibetan Buddhist teachers of our time. Presents the teachings of Tibetan Buddhism on the nature of mind, karma, rebirth, compassion, and caring for the dying.',
    'desc2' => 'Clear, practical, and inspiring, this work introduces meditation and spiritual guidance for meeting death and transforming daily existence.',
  ),
);

$checkBookStmt = $connection->prepare("SELECT id FROM book WHERE (ISBN = :isbn AND ISBN != '') OR title = :title LIMIT 1");
$insertBookStmt = $connection->prepare("
    INSERT INTO book (title, author, ISBN, publishDate, Publisher, Original_Price, Stock, description_para_1, description_para_2, coverImage, category_id)
    VALUES (:title, :author, :isbn, :publish_date, :publisher, :price, :stock, :desc1, :desc2, :image, :category_id)
");
$updateBookStmt = $connection->prepare("
    UPDATE book 
    SET title = :title, author = :author, publishDate = :publish_date, Publisher = :publisher,
        Original_Price = :price, Stock = :stock, description_para_1 = :desc1, description_para_2 = :desc2,
        coverImage = :image, category_id = :category_id
    WHERE id = :id
");

$insertedCount = 0;
$updatedCount = 0;

foreach ($books as $b) {
    $catId = $categoryMap[$b["category"]] ?? null;
    if (!$catId) {
        continue;
    }

    $checkBookStmt->execute([":isbn" => $b["isbn"], ":title" => $b["title"]]);
    $existingBook = $checkBookStmt->fetch(PDO::FETCH_OBJ);

    if ($existingBook) {
        $updateBookStmt->execute([
            ":id" => $existingBook->id,
            ":title" => $b["title"],
            ":author" => $b["author"],
            ":publish_date" => $b["publishDate"],
            ":publisher" => $b["publisher"],
            ":price" => (int)$b["price"],
            ":stock" => (int)$b["stock"],
            ":desc1" => $b["desc1"],
            ":desc2" => $b["desc2"],
            ":image" => $b["image"],
            ":category_id" => (int)$catId
        ]);
        $updatedCount++;
    } else {
        $insertBookStmt->execute([
            ":title" => $b["title"],
            ":author" => $b["author"],
            ":isbn" => $b["isbn"],
            ":publish_date" => $b["publishDate"],
            ":publisher" => $b["publisher"],
            ":price" => (int)$b["price"],
            ":stock" => (int)$b["stock"],
            ":desc1" => $b["desc1"],
            ":desc2" => $b["desc2"],
            ":image" => $b["image"],
            ":category_id" => (int)$catId
        ]);
        $insertedCount++;
    }
}

// Section 5: Clean Expired Deals & Ensure One Active Daily Deal
$connection->exec("DELETE FROM deals WHERE status = 'expired'");
$activeDealCheck = $connection->query("SELECT id FROM deals WHERE status = 'active' AND end_time > NOW() LIMIT 1")->fetch();

if (!$activeDealCheck) {
    $sampleBook = $connection->query("SELECT id FROM book WHERE Stock > 10 ORDER BY id ASC LIMIT 1")->fetch(PDO::FETCH_OBJ);
    if ($sampleBook) {
        $createDeal = $connection->prepare("
            INSERT INTO deals (book_id, discount_percentage, start_time, status)
            VALUES (:bid, 50, NOW(), 'active')
        ");
        $createDeal->execute([":bid" => $sampleBook->id]);
    }
}

// Output Response
if (php_sapi_name() === "cli") {
    echo "=== BookBuddy Seeder Finished ===" . PHP_EOL;
    echo "Tables initialized: 9" . PHP_EOL;
    echo "Categories seeded: " . count($categoryNames) . PHP_EOL;
    echo "Books processed: " . count($books) . " (Inserted: $insertedCount, Updated: $updatedCount)" . PHP_EOL;
    echo "Admin user ready: aliraza1 (muhammadshaharyaraulakh@gmail.com)" . PHP_EOL;
    echo "Active deals verified." . PHP_EOL;
} else {
    header("Content-Type: application/json");
    echo json_encode([
        "status" => "success",
        "message" => "BookBuddy schema and seed completed successfully!",
        "tables" => 9,
        "categories" => count($categoryNames),
        "books_total" => count($books),
        "inserted" => $insertedCount,
        "updated" => $updatedCount
    ]);
}