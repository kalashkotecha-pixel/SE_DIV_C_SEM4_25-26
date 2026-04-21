<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Return all mentors
    $conn = getDBConnection();
    $result = $conn->query("SELECT * FROM mentors ORDER BY name ASC");
    $mentors = [];
    while ($row = $result->fetch_assoc()) $mentors[] = $row;
    echo json_encode(['success' => true, 'mentors' => $mentors]);
    $conn->close();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? '';
    $conn = getDBConnection();

    if ($action === 'add') {
        $name  = trim($input['name'] ?? '');
        $dept  = trim($input['department'] ?? '');
        $email = trim($input['email'] ?? '');
        if (!$name || !$dept) { echo json_encode(['success'=>false,'message'=>'Name and department required']); exit(); }
        $stmt = $conn->prepare("INSERT INTO mentors (name, department, email) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $dept, $email);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok, 'id' => $conn->insert_id]);
        $stmt->close();

    } elseif ($action === 'delete') {
        $id = intval($input['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM mentors WHERE id = ?");
        $stmt->bind_param("i", $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => $ok]);
        $stmt->close();
    } else {
        echo json_encode(['success'=>false,'message'=>'Unknown action']);
    }
    $conn->close();
    exit();
}
?>
