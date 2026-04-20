<?php
$conn = new mysqli("localhost", "root", "", "anorrldb");

$key = trim($_POST["key"] ?? "");

$stmt = $conn->prepare("SELECT access_key FROM accesskeys WHERE access_key = ?");
$stmt->bind_param("s", $key);
$stmt->execute();
$result = $stmt->get_result();

if ($result->fetch_assoc()) {
    $delete = $conn->prepare("DELETE FROM accesskeys WHERE access_key = ?");
    $delete->bind_param("s", $key);
    $delete->execute();
    $delete->close();

    echo "OK";
} else {
    echo "INVALID";
}

$stmt->close();
$conn->close();
