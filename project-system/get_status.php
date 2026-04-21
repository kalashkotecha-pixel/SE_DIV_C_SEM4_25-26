<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') { http_response_code(405); echo json_encode(['success'=>false,'message'=>'Method not allowed']); exit(); }

$email = trim($_GET['email'] ?? '');
if (!$email) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Email required']); exit(); }

$conn = getDBConnection();

// Get submission by email
$stmt = $conn->prepare("SELECT id, group_email, timestamp, status FROM submissions WHERE group_email = ? ORDER BY timestamp DESC LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    echo json_encode(['success'=>true,'submission'=>null]);
    $conn->close(); exit();
}

$sub = $result->fetch_assoc();
$sid = $sub['id'];

// Members
$stmt = $conn->prepare("SELECT name, year, department, role FROM group_members WHERE submission_id = ? ORDER BY member_order");
$stmt->bind_param("i", $sid); $stmt->execute();
$members = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Projects
$stmt = $conn->prepare("SELECT title, description, project_number FROM project_ideas WHERE submission_id = ? ORDER BY project_number");
$stmt->bind_param("i", $sid); $stmt->execute();
$projects = array_map(fn($p) => ['title'=>$p['title'],'description'=>$p['description']], $stmt->get_result()->fetch_all(MYSQLI_ASSOC));
$stmt->close();

// Selection
$stmt = $conn->prepare("SELECT project_number, project_title FROM project_selections WHERE submission_id = ?");
$stmt->bind_param("i", $sid); $stmt->execute();
$selRow = $stmt->get_result()->fetch_assoc();
$stmt->close();
$selection = $selRow ? ['index'=>$selRow['project_number']-1,'title'=>$selRow['project_title']] : null;

// Mentor assignment
$stmt = $conn->prepare("SELECT m.id, m.name, m.department, m.email FROM mentor_assignments ma JOIN mentors m ON ma.mentor_id = m.id WHERE ma.submission_id = ?");
$stmt->bind_param("i", $sid); $stmt->execute();
$mentorRow = $stmt->get_result()->fetch_assoc();
$stmt->close();
$conn->close();

echo json_encode([
    'success' => true,
    'submission' => [
        'id'         => $sid,
        'groupEmail' => $sub['group_email'],
        'timestamp'  => $sub['timestamp'],
        'status'     => $sub['status'] ?? 'pending',
        'members'    => $members,
        'projects'   => $projects,
        'selection'  => $selection,
        'mentor'     => $mentorRow ?: null
    ]
]);
?>
