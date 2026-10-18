
<?php
$url = "http://localhost:3000/api/students";

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);

$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if ($response === false) {
    die("API error: " . curl_error($ch));
}

curl_close($ch);

if ($status !== 200) {
    die("API returned HTTP status: " . $status);
}

$students = json_decode($response, true);

if (!is_array($students)) {
    die("Invalid JSON response.");
}

echo "<h2>Students from Express API</h2>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>ID</th><th>Name</th><th>Course</th></tr>";

foreach ($students as $student) {
    echo "<tr>";
    echo "<td>" . (int) $student["id"] . "</td>";
    echo "<td>" . htmlspecialchars($student["name"]) . "</td>";
    echo "<td>" . htmlspecialchars($student["course"]) . "</td>";
    echo "</tr>";
}

echo "</table>";
?>
