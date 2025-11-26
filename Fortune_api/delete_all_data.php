<?php
require_once 'config.php';

$response = [
    'success' => false,
    'message' => ''
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'POST 요청만 허용됩니다.';
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
    // Start transaction
    $conn->beginTransaction();

    // Delete all data from fortune_history table
    $stmt = $conn->prepare("DELETE FROM fortune_history");
    $stmt->execute();
    $deletedRows = $stmt->rowCount();

    // Commit transaction
    $conn->commit();

    $response['success'] = true;
    $response['message'] = "모든 데이터가 성공적으로 삭제되었습니다. (삭제된 행: {$deletedRows}개)";

} catch (PDOException $e) {
    // Rollback on error
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = '데이터 삭제 중 오류가 발생했습니다: ' . $e->getMessage();
    error_log("Delete All Data API Error: " . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
