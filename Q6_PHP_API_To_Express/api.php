
<?php
header("Content-Type: application/json; charset=UTF-8");

$students = [
    ["id" => 1, "name" => "Rahul Patel", "course" => "BCA"],
    ["id" => 2, "name" => "Priya Shah", "course" => "MSc ICT"],
    ["id" => 3, "name" => "Amit Joshi", "course" => "BSc IT"]
];

echo json_encode($students, JSON_PRETTY_PRINT);
?>
