<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit(); }

$input = json_decode(file_get_contents('php://input'), true);
$submission_id = intval($input['submission_id'] ?? 0);
$mentor_id     = !empty($input['mentor_id']) ? intval($input['mentor_id']) : null;

if (!$submission_id) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid submission_id']); exit(); }

$conn = getDBConnection();

if ($mentor_id) {
    // Check if already assigned
    $chk = $conn->prepare("SELECT id FROM mentor_assignments WHERE submission_id = ?");
    $chk->bind_param("i", $submission_id);
    $chk->execute();
    $exists = $chk->get_result()->num_rows > 0;
    $chk->close();

    if ($exists) {
        $stmt = $conn->prepare("UPDATE mentor_assignments SET mentor_id = ?, assigned_at = NOW() WHERE submission_id = ?");
        $stmt->bind_param("ii", $mentor_id, $submission_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO mentor_assignments (submission_id, mentor_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $submission_id, $mentor_id);
    }
} else {
    // Unassign
    $stmt = $conn->prepare("DELETE FROM mentor_assignments WHERE submission_id = ?");
    $stmt->bind_param("i", $submission_id);
}

$ok = $stmt->execute();
$stmt->close();
$conn->close();

echo json_encode(['success' => $ok]);
?>
