<?php
// Scan database for unused columns
$pdo = new PDO(
    'mysql:host=localhost;dbname=sisalmox',
    'root',
    ''
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$query = "SELECT TABLE_NAME, COLUMN_NAME, COLUMN_TYPE, IS_NULLABLE, COLUMN_KEY FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() ORDER BY TABLE_NAME, ORDINAL_POSITION";
$stmt = $pdo->query($query);
$schema = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $table = $row['TABLE_NAME'];
    if (!isset($schema[$table])) {
        $schema[$table] = [];
    }
    $schema[$table][] = $row;
}

echo json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
?>
