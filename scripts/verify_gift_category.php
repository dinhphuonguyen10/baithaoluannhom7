<?php
$root = dirname(__DIR__);
$source = file_get_contents($root . DIRECTORY_SEPARATOR . 'database' . DIRECTORY_SEPARATOR . 'baitaplon.sql');
$source = str_replace(["\r\n", "\r"], "\n", $source);
$start = strpos($source, "INSERT INTO categories (name, slug)\nVALUES ('Set quà tặng', 'set-qua-tang')");
$end = strpos($source, '-- SQL VIEWS', $start);

if ($start === false || $end === false) {
    fwrite(STDERR, "Gift category seed block not found.\n");
    exit(1);
}

$db = new mysqli('localhost', 'root', '', 'baitaplon');
if ($db->connect_error) {
    fwrite(STDERR, $db->connect_error . "\n");
    exit(1);
}
$db->set_charset('utf8mb4');

for ($run = 1; $run <= 2; $run++) {
    if (!$db->multi_query(substr($source, $start, $end - $start))) {
        fwrite(STDERR, $db->error . "\n");
        exit(1);
    }
    do {
        if ($result = $db->store_result()) {
            $result->free();
        }
    } while ($db->more_results() && $db->next_result());
    if ($db->errno) {
        fwrite(STDERR, $db->error . "\n");
        exit(1);
    }
}

$imagePatterns = [
    'bong-tai' => 'assets' . DIRECTORY_SEPARATOR . 'bôngtai' . DIRECTORY_SEPARATOR . 'bong*.jpg',
    'day-chuyen' => 'assets' . DIRECTORY_SEPARATOR . 'dây chuyền' . DIRECTORY_SEPARATOR . 'dâychuyen' . DIRECTORY_SEPARATOR . 'day*.jpg',
    'nhan' => 'assets' . DIRECTORY_SEPARATOR . 'nhẫn' . DIRECTORY_SEPARATOR . 'nhan*.png',
    'vong-tay' => 'assets' . DIRECTORY_SEPARATOR . 'vòng tay' . DIRECTORY_SEPARATOR . 'vong*.png',
    'set-qua-tang' => 'assets' . DIRECTORY_SEPARATOR . 'quà' . DIRECTORY_SEPARATOR . 'set*.jpg',
];
$productQuery = $db->prepare('SELECT p.image FROM products p JOIN categories c ON c.id = p.category_id WHERE c.slug = ?');

foreach ($imagePatterns as $slug => $pattern) {
    $files = glob($root . DIRECTORY_SEPARATOR . $pattern);
    if ($files === false || $files === []) {
        fwrite(STDERR, 'No product images found for category ' . $slug . ".\n");
        exit(1);
    }

    $expectedImages = [];
    foreach ($files as $file) {
        $expectedImages[] = str_replace(DIRECTORY_SEPARATOR, '/', substr($file, strlen($root) + 1));
    }

    $productQuery->bind_param('s', $slug);
    $productQuery->execute();
    $result = $productQuery->get_result();
    $productImages = [];
    while ($row = $result->fetch_assoc()) {
        $productImages[$row['image']] = true;
    }

    foreach ($expectedImages as $image) {
        if (!isset($productImages[$image])) {
            fwrite(STDERR, 'Missing product for image ' . $image . ' in category ' . $slug . ".\n");
            exit(1);
        }
    }

    echo $slug . ': ' . count($expectedImages) . ' / ' . count($expectedImages) . ' image products verified.' . PHP_EOL;
}

$productQuery->close();
echo "Catalog seed is repeatable; all category images have products.\n";
