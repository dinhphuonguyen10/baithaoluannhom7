<?php
require __DIR__ . '/../config/db.php';
$r = $conn->query('DESCRIBE users');
while ($row = $r->fetch_assoc()) {
    echo $row['Field'] . ' | ' . $row['Type'] . ' | NULL=' . $row['Null'] . ' | Default=' . $row['Default'] . "\n";
}