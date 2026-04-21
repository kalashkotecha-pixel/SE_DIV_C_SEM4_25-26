<?php
/**
 * register.php
 * Handles registration for Student, Admin, and Coordinator roles.
 *
 * REQUIRED TABLES:
 *   students     (id, email, name, password_hash, created_at)
 *   admins       (id, username, password_hash, created_at)
 *   coordinators (id, username, department, password_hash, created_at)
 *
 * COORDINATOR TABLE SQL (run once):
 * ──────────────────────────────────────────────────────────────
 *   CREATE TABLE IF NOT EXISTS coordinators (
 *       id INT AUTO_INCREMENT PRIMARY KEY,
 *       username VARCHAR(100) NOT NULL UNIQUE,
 *       password_hash VARCHAR(255) NOT NULL,
 *       department VARCHAR(200) NOT NULL,
 *       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 *   );
 * ──────────────────────────────────────────────────────────────
 */

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['role']) || !isset($input['password'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit();
}

$role     = $input['role'];
$password = $input['password'];

if (strlen($password) < 6) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 6 characters']);
    exit();
}

$password_hash = password_hash($password, PASSWORD_BCRYPT);

// ─── STUDENT REGISTRATION ────────────────────────────────────────────────────
if ($role === 'student') {
    $email = trim($input['email'] ?? '');
    $name  = trim($input['name']  ?? '');

    if (empty($email)) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Email is required']); exit(); }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Invalid email address']); exit(); }

    $conn = getDBConnection();
    $chk  = $conn->prepare("SELECT id FROM students WHERE email = ? LIMIT 1");
    $chk->bind_param("s", $email); $chk->execute();
    $exists = $chk->get_result()->num_rows > 0; $chk->close();

    if ($exists) { http_response_code(409); echo json_encode(['success'=>false,'message'=>'An account with this email already exists']); $conn->close(); exit(); }

    $stmt = $conn->prepare("INSERT INTO students (email, name, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $email, $name, $password_hash);
    $ok = $stmt->execute(); $new_id = $conn->insert_id;
    $stmt->close(); $conn->close();

    if ($ok) echo json_encode(['success'=>true,'message'=>'Student account created successfully','user'=>['id'=>$new_id,'email'=>$email,'name'=>$name]]);
    else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create account. Please try again.']); }
    exit();
}

// ─── ADMIN REGISTRATION ──────────────────────────────────────────────────────
if ($role === 'admin') {
    $username = trim($input['username'] ?? '');

    if (empty($username))          { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Username is required']); exit(); }
    if (strlen($username) < 3)     { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Username must be at least 3 characters']); exit(); }

    $conn = getDBConnection();
    $chk  = $conn->prepare("SELECT id FROM admins WHERE username = ? LIMIT 1");
    $chk->bind_param("s", $username); $chk->execute();
    $exists = $chk->get_result()->num_rows > 0; $chk->close();

    if ($exists) { http_response_code(409); echo json_encode(['success'=>false,'message'=>'This username is already taken']); $conn->close(); exit(); }

    $stmt = $conn->prepare("INSERT INTO admins (username, password_hash) VALUES (?, ?)");
    $stmt->bind_param("ss", $username, $password_hash);
    $ok = $stmt->execute(); $new_id = $conn->insert_id;
    $stmt->close(); $conn->close();

    if ($ok) echo json_encode(['success'=>true,'message'=>'Admin account created successfully','user'=>['id'=>$new_id,'username'=>$username]]);
    else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create account. Please try again.']); }
    exit();
}

// ─── COORDINATOR REGISTRATION ─────────────────────────────────────────────────
if ($role === 'coordinator') {
    $username   = trim($input['username']   ?? '');
    $department = trim($input['department'] ?? '');

    if (empty($username))      { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Username is required']); exit(); }
    if (strlen($username) < 3) { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Username must be at least 3 characters']); exit(); }
    if (empty($department))    { http_response_code(400); echo json_encode(['success'=>false,'message'=>'Department is required']); exit(); }

    $conn = getDBConnection();
    $chk  = $conn->prepare("SELECT id FROM coordinators WHERE username = ? LIMIT 1");
    $chk->bind_param("s", $username); $chk->execute();
    $exists = $chk->get_result()->num_rows > 0; $chk->close();

    if ($exists) { http_response_code(409); echo json_encode(['success'=>false,'message'=>'This username is already taken']); $conn->close(); exit(); }

    $stmt = $conn->prepare("INSERT INTO coordinators (username, department, password_hash) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $department, $password_hash);
    $ok = $stmt->execute(); $new_id = $conn->insert_id;
    $stmt->close(); $conn->close();

    if ($ok) echo json_encode(['success'=>true,'message'=>'Coordinator account created successfully','user'=>['id'=>$new_id,'username'=>$username,'department'=>$department]]);
    else { http_response_code(500); echo json_encode(['success'=>false,'message'=>'Failed to create account. Please try again.']); }
    exit();
}

// Unknown role
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unknown role. Use "student", "admin", or "coordinator".']);
?>
