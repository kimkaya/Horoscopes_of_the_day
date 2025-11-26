<?php
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo "GET 요청만 허용됩니다.";
    exit();
}

$conn = getDBConnection();
if (!$conn) {
    http_response_code(500);
    echo "데이터베이스 연결 실패";
    exit();
}

try {
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
    $history = $stmt->fetchAll();

    // Set headers for Excel download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=fortune_history_' . date('Y-m-d_H-i-s') . '.csv');

    // Create file pointer connected to output stream
    $output = fopen('php://output', 'w');

    // Add BOM for UTF-8
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Add column headers
    fputcsv($output, ['ID', '운세 타입', '이름', '생년월일', '성별', '결과', '일시']);

    // Add data rows
    foreach ($history as $row) {
        $fortuneTypeMap = [
            'daily' => '오늘의 운세',
            'tarot' => '타로',
            'saju' => '사주',
            'omikuji' => '오미쿠지'
        ];

        $genderMap = [
            'male' => '남성',
            'female' => '여성',
            'other' => '기타'
        ];

        fputcsv($output, [
            $row['id'],
            $fortuneTypeMap[$row['fortune_type']] ?? $row['fortune_type'],
            $row['user_name'],
            $row['user_birthdate'] ?? '-',
            $row['user_gender'] ? ($genderMap[$row['user_gender']] ?? $row['user_gender']) : '-',
            $row['result_text'],
            $row['created_at']
        ]);
    }

    fclose($output);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Export Excel Error: " . $e->getMessage());
    echo "오류가 발생했습니다.";
}
