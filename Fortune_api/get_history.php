<?php
require_once 'config.php';

$response = [
    'success' => false,
    'message' => '',
    'history' => [],
    'total' => 0
];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response['message'] = 'GET 요청만 허용됩니다.';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    $response['message'] = '데이터베이스 연결 실패';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

try {
    // Get total count
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM fortune_history");
    $stmt->execute();
    $countResult = $stmt->fetch();
    $response['total'] = $countResult['total'];

    // Get all history
    $stmt = $conn->prepare("
        SELECT
            id,
            fortune_type,
            user_name,
            user_birthdate,
            user_gender,
            result_text,
            created_at
        FROM fortune_history
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $response['history'] = $stmt->fetchAll();

    $response['success'] = true;
    $response['message'] = '히스토리를 성공적으로 가져왔습니다.';

} catch (PDOException $e) {
    $response['message'] = '오류가 발생했습니다: ' . $e->getMessage();
    error_log("History API Error: " . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
