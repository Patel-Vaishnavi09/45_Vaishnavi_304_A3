
<?php

require_once __DIR__ . "/../config/db.php";

$result = $conn->query(
    "SELECT id, name, email, course FROM students ORDER BY id"
);

$xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
$xml .= "<students>\n";

while ($row = $result->fetch_assoc()) {
    $xml .= "  <student>\n";

    foreach ($row as $key => $value) {
        $safeValue = htmlspecialchars(
            (string) $value,
            ENT_XML1 | ENT_QUOTES,
            "UTF-8"
        );

        $xml .= "    <$key>$safeValue</$key>\n";
    }

    $xml .= "  </student>\n";
}

$xml .= "</students>\n";

$outputFile = __DIR__ . "/students.xml";

if (file_put_contents($outputFile, $xml) !== false) {
    echo "Students exported successfully to students.xml";
} else {
    echo "Unable to write XML file. Check folder permissions.";
}

$conn->close();
?>
