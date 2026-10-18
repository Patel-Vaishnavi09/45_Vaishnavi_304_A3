<?php

//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

//echo "Q2 PHP script started.<br>";

//require_once __DIR__ . "/../config/db.php";
//echo "Database connection included.<br>";
require_once __DIR__ . "/../config/db.php";

$xmlFile = __DIR__ . "/students.xml";

if (!file_exists($xmlFile)) {
    die("students.xml file not found.");
}

$xml = simplexml_load_file($xmlFile);

if ($xml === false) {
    die("Unable to read XML file.");
}

$stmt = $conn->prepare(
    "INSERT INTO students (name, email, course) VALUES (?, ?, ?)"
);

$count = 0;

foreach ($xml->student as $student) {
    $name = (string) $student->name;
    $email = (string) $student->email;
    $course = (string) $student->course;

    $stmt->bind_param("sss", $name, $email, $course);

    if ($stmt->execute()) {
        $count++;
    }
}

echo "Successfully imported $count students into MySQL.";

$stmt->close();
$conn->close();
