<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$conn = getDBConnection();

try {
    // Get all submissions
    $submissions = [];
    
    $query = "SELECT id, group_email, timestamp FROM submissions ORDER BY timestamp DESC";
    $result = $conn->query($query);
    
    while ($row = $result->fetch_assoc()) {
        $submission_id = $row['id'];
        
        // Get members for this submission
        $members_query = "SELECT name, year, department, role FROM group_members 
                         WHERE submission_id = ? ORDER BY member_order";
        $stmt = $conn->prepare($members_query);
        $stmt->bind_param("i", $submission_id);
        $stmt->execute();
        $members_result = $stmt->get_result();
        
        $members = [];
        while ($member = $members_result->fetch_assoc()) {
            $members[] = $member;
        }
        $stmt->close();
        
        // Get projects for this submission
        $projects_query = "SELECT title, description, project_number FROM project_ideas 
                          WHERE submission_id = ? ORDER BY project_number";
        $stmt = $conn->prepare($projects_query);
        $stmt->bind_param("i", $submission_id);
        $stmt->execute();
        $projects_result = $stmt->get_result();
        
        $projects = [];
        while ($project = $projects_result->fetch_assoc()) {
            $projects[] = [
                'title' => $project['title'],
                'description' => $project['description']
            ];
        }
        $stmt->close();
        
        // Get selection if exists
        $selection_query = "SELECT project_number, project_title FROM project_selections 
                           WHERE submission_id = ?";
        $stmt = $conn->prepare($selection_query);
        $stmt->bind_param("i", $submission_id);
        $stmt->execute();
        $selection_result = $stmt->get_result();
        
        $selection = null;
        if ($selection_row = $selection_result->fetch_assoc()) {
            $selection = [
                'index' => $selection_row['project_number'] - 1,
                'title' => $selection_row['project_title']
            ];
        }
        $stmt->close();
        
        $submissions[] = [
            'id' => $submission_id,
            'timestamp' => $row['timestamp'],
            'groupEmail' => $row['group_email'],
            'members' => $members,
            'projects' => $projects,
            'selection' => $selection
        ];
    }
    
    echo json_encode([
        'success' => true,
        'submissions' => $submissions
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error fetching submissions: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
