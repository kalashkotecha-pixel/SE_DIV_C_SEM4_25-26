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
if (!$input || !isset($input['groupEmail']) || !isset($input['members']) || !isset($input['projects'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit();
}

$conn = getDBConnection();

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Insert submission
    $stmt = $conn->prepare("INSERT INTO submissions (group_email) VALUES (?)");
    $stmt->bind_param("s", $input['groupEmail']);
    
    if (!$stmt->execute()) {
        throw new Exception("Failed to insert submission");
    }
    
    $submission_id = $conn->insert_id;
    $stmt->close();
    
    // Insert group members
    $stmt = $conn->prepare("INSERT INTO group_members (submission_id, name, year, department, role, member_order) VALUES (?, ?, ?, ?, ?, ?)");
    
    foreach ($input['members'] as $index => $member) {
        $stmt->bind_param(
            "issssi",
            $submission_id,
            $member['name'],
            $member['year'],
            $member['department'],
            $member['role'],
            $index
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert member");
        }
    }
    $stmt->close();
    
    // Insert project ideas
    $stmt = $conn->prepare("INSERT INTO project_ideas (submission_id, title, description, project_number) VALUES (?, ?, ?, ?)");
    
    foreach ($input['projects'] as $index => $project) {
        $project_number = $index + 1;
        $stmt->bind_param(
            "issi",
            $submission_id,
            $project['title'],
            $project['description'],
            $project_number
        );
        
        if (!$stmt->execute()) {
            throw new Exception("Failed to insert project");
        }
    }
    $stmt->close();
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Submission saved successfully',
        'submission_id' => $submission_id
    ]);
    
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error saving submission: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
