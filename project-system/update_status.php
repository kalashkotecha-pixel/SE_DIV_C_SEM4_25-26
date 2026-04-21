<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit(); }

$input  = json_decode(file_get_contents('php://input'), true);
$id     = intval($input['submission_id'] ?? 0);
$status = $input['status'] ?? '';

if (!$id || !in_array($status, ['pending','approved','rejected'])) {
    http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid input']); exit();
}

$conn = getDBConnection();
$stmt = $conn->prepare("UPDATE submissions SET status = ? WHERE id = ?");
$stmt->bind_param("si", $status, $id);
$ok = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => $ok]);
?>
