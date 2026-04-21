<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

// Validate input
if (!$input || !isset($input['submission_id']) || !isset($input['project_index']) || !isset($input['project_title'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$conn = getDBConnection();

try {
    $submission_id = intval($input['submission_id']);
    $project_number = intval($input['project_index']) + 1;
    $project_title = $input['project_title'];
    
    // Check if selection already exists
    $check_stmt = $conn->prepare("SELECT id FROM project_selections WHERE submission_id = ?");
    $check_stmt->bind_param("i", $submission_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Update existing selection
        $stmt = $conn->prepare("UPDATE project_selections SET project_number = ?, project_title = ?, selected_at = NOW() WHERE submission_id = ?");
        $stmt->bind_param("isi", $project_number, $project_title, $submission_id);
    } else {
        // Insert new selection
        $stmt = $conn->prepare("INSERT INTO project_selections (submission_id, project_number, project_title) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $submission_id, $project_number, $project_title);
    }
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Project selected successfully'
        ]);
    } else {
        throw new Exception("Failed to save selection");
    }
    
    $stmt->close();
    $check_stmt->close();
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error saving selection: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
