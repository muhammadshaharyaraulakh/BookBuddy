<?php
require_once __DIR__ . '/config/config.php';

$categoryNames = [
    'History',
    'Cloud Computing',
    'Biographies',
    'Religious',
    'Programming',
    'Philosophy'
];

$categoryMap = [];

$checkCatStmt = $connection->prepare("SELECT id, title FROM categories WHERE title = :title LIMIT 1");
$insertCatStmt = $connection->prepare("INSERT INTO categories (title) VALUES (:title)");

foreach ($categoryNames as $catName) {
    $checkCatStmt->execute([':title' => $catName]);
    $existing = $checkCatStmt->fetch(PDO::FETCH_OBJ);
    if ($existing) {
        $categoryMap[$catName] = (int)$existing->id;
    } else {
        $insertCatStmt->execute([':title' => $catName]);
        $categoryMap[$catName] = (int)$connection->lastInsertId();
    }
}

$books = [
    [
        'title' => 'Clean Code',
        'author' => 'Robert C Martin',
        'isbn' => '9780132350884',
        'publishDate' => '2008-08-01',
        'publisher' => 'Prentice Hall',
        'price' => 45,
        'stock' => 35,
        'category' => 'Programming',
        'image' => 'clean_code.webp',
        'desc1' => 'Even bad code can function but if code is not clean it can bring a development organization to its knees. Every year countless hours and significant resources are lost because of poorly written code. Clean Code is divided into three parts including patterns of writing clean code and case studies of increasing complexity.',
        'desc2' => 'This book is a must read for any developer software engineer project manager team lead or systems analyst with an interest in producing better code. You will learn how to tell the difference between good code and bad code and how to transform bad code into good code.'
    ],
    [
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
        'desc2' => 'Covered topics range from personal responsibility and career development to architectural techniques for keeping your code flexible and easy to adapt and reuse. Learn how to write flexible dynamic and adaptable code while avoiding common pitfalls.'
    ],
    [
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
        'desc2' => 'The 23 patterns contained here allow designers to create more flexible elegant and ultimately reusable designs without having to rediscover the design solutions themselves. Includes practical examples in modern object oriented languages.'
    ],
    [
        'title' => 'Refactoring',
        'author' => 'Martin Fowler',
        'isbn' => '9780201485677',
        'publishDate' => '1999-07-08',
        'publisher' => 'Addison Wesley',
        'price' => 48,
        'stock' => 30,
        'category' => 'Programming',
        'image' => 'refactoring.webp',
        'desc1' => 'Refactoring is a controlled technique for improving the design of an existing code base. Its essence is applying a series of small behavior preserving transformations each of which too small to be worth doing but the cumulative effect is radical.',
        'desc2' => 'Martin Fowler shows you where opportunities for refactoring typically occur and how to make code easier to understand and cheaper to modify without introducing new defects into existing applications.'
    ],
    [
        'title' => 'Cloud Security and Privacy',
        'author' => 'Tim Mather and Subra Kumaraswamy',
        'isbn' => '9780596802769',
        'publishDate' => '2009-09-04',
        'publisher' => 'OReilly Media',
        'price' => 40,
        'stock' => 28,
        'category' => 'Cloud Computing',
        'image' => 'cloud_security_privacy.webp',
        'desc1' => 'You may regard cloud computing as an ideal way for your company to control IT costs but do you know how cloud security impacts your company and your customers. This book provides a comprehensive overview of cloud computing risks and security challenges.',
        'desc2' => 'Learn about data security privacy concerns regulatory compliance and architecture considerations when adopting public or private cloud infrastructure across enterprise environments.'
    ],
    [
        'title' => 'Distributed and Cloud Computing',
        'author' => 'Kai Hwang and Geoffrey Fox',
        'isbn' => '9780123858801',
        'publishDate' => '2011-12-18',
        'publisher' => 'Morgan Kaufmann',
        'price' => 65,
        'stock' => 20,
        'category' => 'Cloud Computing',
        'image' => 'distributed_cloud_computing.webp',
        'desc1' => 'From parallel processing to the Internet of Things this book delivers comprehensive coverage of modern distributed and cloud computing systems including virtualization clusters and data centers.',
        'desc2' => 'Provides in depth insights into scalable architectures service models MapReduce computing frameworks and foundational principles of cloud network design.'
    ],
    [
        'title' => 'Cloud Native Patterns',
        'author' => 'Cornelia Davis',
        'isbn' => '9781617294297',
        'publishDate' => '2019-05-18',
        'publisher' => 'Manning Publications',
        'price' => 52,
        'stock' => 32,
        'category' => 'Cloud Computing',
        'image' => 'cloud_native_patterns.webp',
        'desc1' => 'Cloud Native Patterns teaches you how to build resilient cloud software that scales effortlessly and tolerates inevitable infrastructure failures through proven software architecture practices.',
        'desc2' => 'Explore modern techniques like microservices event driven architectures 12 factor app guidelines and zero downtime deployment strategies in production environments.'
    ],
    [
        'title' => 'Cloud Application Architectures',
        'author' => 'George Reese',
        'isbn' => '9780596156367',
        'publishDate' => '2009-04-20',
        'publisher' => 'OReilly Media',
        'price' => 38,
        'stock' => 22,
        'category' => 'Cloud Computing',
        'image' => 'cloud_app_architectures.webp',
        'desc1' => 'Building applications for the cloud requires a different mindset. This guide examines how cloud computing changes infrastructure economics and technical requirements for application development.',
        'desc2' => 'Learn practical tips for scaling disaster recovery security and migration strategies using Amazon Web Services and other cloud platforms.'
    ],
    [
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
        'desc2' => 'Through aphorisms and essays Nietzsche explores the will to power master and slave morality and the psychology behind philosophical assertions.'
    ],
    [
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
        'desc2' => 'Recorded as notes to himself while on military campaign the work is a timeless guide to Stoic philosophy inner fortitude duty and tranquility in the face of chaos.'
    ],
    [
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
        'desc2' => 'Kant investigates the limits and scope of human knowledge synthesizing rationalism and empiricism into a revolutionary transcendental idealism.'
    ],
    [
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
        'desc2' => 'Featuring the famous Allegory of the Cave and reflections on philosopher kings the text continues to stimulate lively debate across ethics government and education.'
    ],
    [
        'title' => 'Sapiens A Brief History of Humankind',
        'author' => 'Yuval Noah Harari',
        'isbn' => '9780062316097',
        'publishDate' => '2015-02-10',
        'publisher' => 'Harper',
        'price' => 30,
        'stock' => 55,
        'category' => 'History',
        'image' => 'sapiens.webp',
        'desc1' => 'One hundred thousand years ago at least six different species of humans inhabited Earth. Yet today there is only one Homo sapiens. What happened to the others and what may happen to us.',
        'desc2' => 'Dr Yuval Noah Harari spans the whole of human history from the very first humans to walk the earth to the radical and sometimes devastating breakthroughs of the Cognitive Agricultural and Scientific Revolutions.'
    ],
    [
        'title' => 'Guns Germs and Steel',
        'author' => 'Jared Diamond',
        'isbn' => '9780393317558',
        'publishDate' => '1999-04-17',
        'publisher' => 'W W Norton Company',
        'price' => 28,
        'stock' => 35,
        'category' => 'History',
        'image' => 'guns_germs_steel.webp',
        'desc1' => 'Jared Diamonds landmark work argues that geographical and environmental factors shaped the modern world rather than biological differences among human populations.',
        'desc2' => 'A winner of the Pulitzer Prize this gripping account illuminates how Eurasian civilizations conquered other continents and developed technologies through environmental fortune.'
    ],
    [
        'title' => 'A History of the World in 6 Glasses',
        'author' => 'Tom Standage',
        'isbn' => '9780802715524',
        'publishDate' => '2006-05-16',
        'publisher' => 'Walker Books',
        'price' => 24,
        'stock' => 40,
        'category' => 'History',
        'image' => 'history_world_6_glasses.webp',
        'desc1' => 'Throughout human history certain drinks have done much more than quench thirst. Tom Standage demonstrates how beer wine spirits coffee tea and Coca Cola shaped civilizations.',
        'desc2' => 'From Mesopotamia and classical Greece to colonial expansion and modern capitalism each beverage serves as a unique lens into pivotal eras of world history.'
    ],
    [
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
        'desc2' => 'Walter Isaacson has written a riveting story of the roller coaster life and searingly intense personality of a creative entrepreneur whose passion for perfection revolutionized six industries.'
    ],
    [
        'title' => 'Leonardo da Vinci',
        'author' => 'Walter Isaacson',
        'isbn' => '9781501139154',
        'publishDate' => '2017-10-17',
        'publisher' => 'Simon Schuster',
        'price' => 36,
        'stock' => 30,
        'category' => 'Biographies',
        'image' => 'leonardo_da_vinci.webp',
        'desc1' => 'Leonardo da Vinci was historys most creative genius. Walter Isaacson weaves together da Vincis art science and curiosity using thousands of pages from his personal notebooks.',
        'desc2' => 'From the Mona Lisa and The Last Supper to studies of anatomy optics birds and hydraulics discover how a passion for observation unlocked boundless imagination.'
    ],
    [
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
        'desc2' => 'An illegitimate orphan from the Caribbean Hamilton rose to become George Washingtons chief aide first Treasury Secretary and architect of the modern American economic system.'
    ],
    [
        'title' => 'The World Religions',
        'author' => 'Huston Smith',
        'isbn' => '9780061660184',
        'publishDate' => '2009-03-10',
        'publisher' => 'HarperOne',
        'price' => 26,
        'stock' => 42,
        'category' => 'Religious',
        'image' => 'world_religions.webp',
        'desc1' => 'Huston Smiths masterwork is the essential introduction to the great faiths of humanity explaining the inner teachings and spiritual traditions of Hinduism Buddhism Confucianism Daoism Judaism Christianity and Islam.',
        'desc2' => 'Written with warmth eloquence and deep philosophical insight this definitive guide highlights the enduring wisdom and shared spiritual heritage uniting humankind across cultures.'
    ],
    [
        'title' => 'A History of God',
        'author' => 'Karen Armstrong',
        'isbn' => '9780345384560',
        'publishDate' => '1994-08-09',
        'publisher' => 'Ballantine Books',
        'price' => 25,
        'stock' => 38,
        'category' => 'Religious',
        'image' => 'history_of_god.webp',
        'desc1' => 'A provocative and informative exploration of how Judaism Christianity and Islam have conceived and experienced the Divine through four thousand years of history.',
        'desc2' => 'Karen Armstrong tracks the evolution of monotheism from Abraham to the modern era investigating how philosophical and social transformations continuously redefined theological understanding.'
    ],
    [
        'title' => 'The Varieties of Religious Experience',
        'author' => 'William James',
        'isbn' => '9780140390346',
        'publishDate' => '1982-12-09',
        'publisher' => 'Penguin Classics',
        'price' => 24,
        'stock' => 28,
        'category' => 'Religious',
        'image' => 'varieties_religious_experience.webp',
        'desc1' => 'Standing as a landmark in psychological and philosophical thought William James examines personal religious phenomena including conversion mysticism and saintliness.',
        'desc2' => 'Based on his renowned Gifford Lectures James explores how individual spiritual feelings and actions form the primary foundation of religious belief rather than institutional dogmas.'
    ]
];

$checkBookStmt = $connection->prepare("SELECT id FROM book WHERE ISBN = :isbn OR title = :title LIMIT 1");

$insertBookStmt = $connection->prepare("
    INSERT INTO book 
    (title, author, ISBN, publishDate, Publisher, Original_Price, Discount_Percentage, Stock, description_para_1, description_para_2, coverImage, category_id, totalSales)
    VALUES
    (:title, :author, :isbn, :publish_date, :publisher, :price, 0, :stock, :desc1, :desc2, :image, :category_id, 0)
");

$updateBookStmt = $connection->prepare("
    UPDATE book 
    SET title = :title,
        author = :author,
        publishDate = :publish_date,
        Publisher = :publisher,
        Original_Price = :price,
        Discount_Percentage = 0,
        Stock = :stock,
        description_para_1 = :desc1,
        description_para_2 = :desc2,
        coverImage = :image,
        category_id = :category_id
    WHERE id = :id
");

$insertedCount = 0;
$updatedCount = 0;

foreach ($books as $b) {
    if (!preg_match('/^[a-zA-Z0-9 ]+$/', $b['title'])) {
        throw new Exception("Invalid title format: " . $b['title']);
    }
    if (!preg_match('/^[a-zA-Z0-9 ]+$/', $b['publisher'])) {
        throw new Exception("Invalid publisher format: " . $b['publisher']);
    }
    if (!ctype_digit($b['isbn']) || strlen($b['isbn']) !== 13) {
        throw new Exception("Invalid ISBN format: " . $b['isbn']);
    }
    if ($b['price'] < 0 || $b['stock'] < 0) {
        throw new Exception("Price and stock must be non-negative");
    }

    $catId = $categoryMap[$b['category']] ?? null;
    if (!$catId) {
        throw new Exception("Missing category ID for " . $b['category']);
    }

    $checkBookStmt->execute([':isbn' => $b['isbn'], ':title' => $b['title']]);
    $existingBook = $checkBookStmt->fetch(PDO::FETCH_OBJ);

    if ($existingBook) {
        $updateBookStmt->execute([
            ':id' => $existingBook->id,
            ':title' => $b['title'],
            ':author' => $b['author'],
            ':publish_date' => $b['publishDate'],
            ':publisher' => $b['publisher'],
            ':price' => (int)$b['price'],
            ':stock' => (int)$b['stock'],
            ':desc1' => $b['desc1'],
            ':desc2' => $b['desc2'],
            ':image' => $b['image'],
            ':category_id' => (int)$catId
        ]);
        $updatedCount++;
    } else {
        $insertBookStmt->execute([
            ':title' => $b['title'],
            ':author' => $b['author'],
            ':isbn' => $b['isbn'],
            ':publish_date' => $b['publishDate'],
            ':publisher' => $b['publisher'],
            ':price' => (int)$b['price'],
            ':stock' => (int)$b['stock'],
            ':desc1' => $b['desc1'],
            ':desc2' => $b['desc2'],
            ':image' => $b['image'],
            ':category_id' => (int)$catId
        ]);
        $insertedCount++;
    }
}

if (php_sapi_name() === 'cli') {
    echo "Seed completed successfully. Inserted: " . $insertedCount . ", Updated: " . $updatedCount . ", Total: " . count($books) . PHP_EOL;
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success',
        'inserted' => $insertedCount,
        'updated' => $updatedCount,
        'total' => count($books)
    ]);
}
