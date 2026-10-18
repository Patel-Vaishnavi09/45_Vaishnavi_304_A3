
<?php
$directory = __DIR__ . "/files";

if (!is_dir($directory)) {
    die("Directory does not exist.");
}

$items = scandir($directory);

echo "<h2>Files in Directory</h2>";
echo "<ul>";

foreach ($items as $item) {
    if ($item === "." || $item === "..") {
        continue;
    }

    $path = $directory . DIRECTORY_SEPARATOR . $item;

    if (is_file($path)) {
        echo "<li>" . htmlspecialchars($item) . "</li>";
    }
}

echo "</ul>";
?>
