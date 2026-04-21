<?php
/**
 * login.php
 * Handles authentication for Student, Admin, and Coordinator roles.
 *
 * COORDINATOR SETUP SQL:
 * ─────────────────────────────────────────────────────────────────
 *   CREATE TABLE IF NOT EXISTS coordinators (
 *       id INT AUTO_INCREMENT PRIMARY KEY,
 *       username VARCHAR(100) NOT NULL UNIQUE,
 *       password_hash VARCHAR(255) NOT NULL,
 *       department VARCHAR(200) NOT NULL,
 *       created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 *   );
 *
 *   -- Example coordinator: username=coord_cs / password=Coord@123 / dept=Computer Engineering
 *   INSERT INTO coordinators (username, password_hash, department)
 *   VALUES ('coord_cs',
 *       '$2y$12$Y5K3bZQ1oV8p2M4xL9eN7.Hg8MfRiN6ZkJq2W0dCsVtP3uXaIbOym',
 *       'Computer Engineering');
 * ─────────────────────────────────────────────────────────────────
 */

require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['role'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$role = $input['role'];

// ─── STUDENT LOGIN ───────────────────────────────────────────────────────────
if ($role === 'student') {
    $email    = trim($input['email']    ?? '');
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit();
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, name, password_hash FROM students WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        $stmt->close(); $conn->close(); exit();
    }

    $row = $result->fetch_assoc();
    $stmt->close(); $conn->close();

    if (!password_verify($password, $row['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
        exit();
    }

    echo json_encode([
        'success' => true,
        'role'    => 'student',
        'user'    => ['id' => $row['id'], 'name' => $row['name'], 'email' => $email]
    ]);
    exit();
}

// ─── ADMIN LOGIN ─────────────────────────────────────────────────────────────
if ($role === 'admin') {
    $username = trim($input['username'] ?? '');
    $password = $input['password']      ?? '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password are required']);
        exit();
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, password_hash FROM admins WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        $stmt->close(); $conn->close(); exit();
    }

    $row = $result->fetch_assoc();
    $stmt->close(); $conn->close();

    if (!password_verify($password, $row['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit();
    }

    echo json_encode([
        'success' => true,
        'role'    => 'admin',
        'user'    => ['id' => $row['id'], 'username' => $username]
    ]);
    exit();
}

// ─── COORDINATOR LOGIN ────────────────────────────────────────────────────────
if ($role === 'coordinator') {
    $username = trim($input['username'] ?? '');
    $password = $input['password']      ?? '';

    if (empty($username) || empty($password)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Username and password are required']);
        exit();
    }

    $conn = getDBConnection();
    $stmt = $conn->prepare("SELECT id, password_hash, department FROM coordinators WHERE username = ? LIMIT 1");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        $stmt->close(); $conn->close(); exit();
    }

    $row = $result->fetch_assoc();
    $stmt->close(); $conn->close();

    if (!password_verify($password, $row['password_hash'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
        exit();
    }

    echo json_encode([
        'success' => true,
        'role'    => 'coordinator',
        'user'    => [
            'id'         => $row['id'],
            'username'   => $username,
            'department' => $row['department']
        ]
    ]);
    exit();
}

// Unknown role
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Unknown role']);
