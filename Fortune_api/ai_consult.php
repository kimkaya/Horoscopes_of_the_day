<?php
require_once 'config.php';

// CORS 헤더는 config.php에서 설정됨

// POST 요청만 허용
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'POST 요청만 허용됩니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// JSON 데이터 받기
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => '잘못된 요청입니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// 사용자 메시지 확인
if (!isset($data['message']) || empty(trim($data['message']))) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => '메시지를 입력해주세요.'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

$userMessage = trim($data['message']);
$conversationHistory = isset($data['history']) ? $data['history'] : [];
$userData = isset($data['userData']) ? $data['userData'] : null;
$contextInfo = isset($data['contextInfo']) ? $data['contextInfo'] : null;

// Gemini API 키 확인
if (!defined('GEMINI_API_KEY') || GEMINI_API_KEY === 'your-api-key-here') {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Gemini API 키가 설정되지 않았습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

// 시스템 프롬프트
$userInfoText = "";
if ($userData) {
    $userInfoText = "\n\n[사용자 정보]\n";
    $userInfoText .= "이름: " . $userData['name'] . "\n";
    $userInfoText .= "생년월일: " . $userData['birthdate'] . "\n";
    if (!empty($userData['birthTime'])) {
        $userInfoText .= "출생시간: " . $userData['birthTime'] . "\n";
    }
    $userInfoText .= "성별: " . ($userData['gender'] === 'M' ? '남성' : '여성') . "\n";
}

// 컨텍스트 정보 추가
$contextInfoText = "";
// 날짜 정보를 항상 포함
$currentDate = date('Y년 m월 d일 (l)', time());
$currentTime = date('H:i', time());
$currentHour = (int)date('H', time());

$contextInfoText = "\n\n[중요: 현재 날짜와 시간]\n";
$contextInfoText .= "오늘 날짜: " . $currentDate . "\n";
$contextInfoText .= "현재 시각: " . $currentTime . "\n";

// 시간대별 메시지
if ($currentHour >= 6 && $currentHour < 12) {
    $contextInfoText .= "시간대: 오전 (아침)\n";
} elseif ($currentHour >= 12 && $currentHour < 18) {
    $contextInfoText .= "시간대: 오후 (낮)\n";
} elseif ($currentHour >= 18 && $currentHour < 22) {
    $contextInfoText .= "시간대: 저녁\n";
} else {
    $contextInfoText .= "시간대: 밤\n";
}

if ($contextInfo) {
    // 날짜/시간 정보 (클라이언트에서 전달된 경우 우선 사용)
    if (isset($contextInfo['dateTime'])) {
        $dt = $contextInfo['dateTime'];
        $contextInfoText = "\n\n[중요: 현재 날짜와 시간]\n";
        $contextInfoText .= "오늘 날짜: " . $dt['date'] . " (" . $dt['dayOfWeek'] . ")\n";
        $contextInfoText .= "현재 시각: " . $dt['time'] . "\n";

        // 시간대별 메시지
        $hour = $dt['hour'];
        if ($hour >= 6 && $hour < 12) {
            $contextInfoText .= "시간대: 오전 (아침)\n";
        } elseif ($hour >= 12 && $hour < 18) {
            $contextInfoText .= "시간대: 오후 (낮)\n";
        } elseif ($hour >= 18 && $hour < 22) {
            $contextInfoText .= "시간대: 저녁\n";
        } else {
            $contextInfoText .= "시간대: 밤\n";
        }
    }

    // 날씨 정보
    if (isset($contextInfo['weather']) && $contextInfo['weather']) {
        $weather = $contextInfo['weather'];
        if (isset($weather['location'])) {
            $contextInfoText .= "위치: " . $weather['location'] . "\n";
        }
        if (isset($weather['temp'])) {
            $contextInfoText .= "온도: " . $weather['temp'] . "°C\n";
        }
        if (isset($weather['description'])) {
            $contextInfoText .= "날씨: " . $weather['description'] . "\n";
        }
        if (isset($weather['humidity'])) {
            $contextInfoText .= "습도: " . $weather['humidity'] . "%\n";
        }
    }
}

$contextInfoText .= "\n※ 반드시 위의 날짜와 시간을 기준으로 답변해주세요.";

$systemPrompt = "당신은 전문적이고 친절한 운세 상담사입니다. 사용자의 고민과 질문에 대해 따뜻하고 긍정적인 조언을 제공합니다.
타로, 사주, 별자리, 오미쿠지 등 다양한 운세에 대한 해석과 조언을 할 수 있습니다.
답변은 한국어로 하며, 존댓말을 사용합니다.

[중요 규칙]
1. 답변은 반드시 1-2문장으로 작성합니다. 절대 3문장을 넘지 마세요.
2. 각 문장은 최대 40자 이내로 작성합니다.
3. 핵심 메시지만 간결하게 전달하세요.
4. 불필요한 설명이나 부연 설명은 생략합니다.

사용자의 운세와 관련된 질문에만 답변하며, 다른 주제는 정중히 거절합니다." . $userInfoText . $contextInfoText;

// 대화 이력 구성
$contents = [];

// 시스템 프롬프트를 첫 번째 메시지로 추가
if (empty($conversationHistory)) {
    $contents[] = [
        'role' => 'user',
        'parts' => [['text' => $systemPrompt]]
    ];
    $contents[] = [
        'role' => 'model',
        'parts' => [['text' => '안녕하세요! 운세 상담사입니다. 궁금하신 점이나 고민을 편하게 말씀해주세요. 😊']]
    ];
}

// 이전 대화 이력 추가
foreach ($conversationHistory as $msg) {
    $role = $msg['role'] === 'user' ? 'user' : 'model';
    $contents[] = [
        'role' => $role,
        'parts' => [['text' => $msg['content']]]
    ];
}

// 현재 사용자 메시지 추가
$contents[] = [
    'role' => 'user',
    'parts' => [['text' => $userMessage]]
];

// Gemini API 요청 데이터
$requestData = [
    'contents' => $contents,
    'generationConfig' => [
        'temperature' => 0.7,
        'topK' => 40,
        'topP' => 0.95,
        'maxOutputTokens' => 100,
    ],
    'safetySettings' => [
        [
            'category' => 'HARM_CATEGORY_HARASSMENT',
            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
        ],
        [
            'category' => 'HARM_CATEGORY_HATE_SPEECH',
            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
        ],
        [
            'category' => 'HARM_CATEGORY_SEXUALLY_EXPLICIT',
            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
        ],
        [
            'category' => 'HARM_CATEGORY_DANGEROUS_CONTENT',
            'threshold' => 'BLOCK_MEDIUM_AND_ABOVE'
        ]
    ]
];

// API 호출
$apiUrl = 'https://generativelanguage.googleapis.com/v1/models/gemini-2.0-flash:generateContent?key=' . GEMINI_API_KEY;

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));
curl_setopt($ch, CURLOPT_TIMEOUT, 30);
// Disable SSL verification for development (Windows SSL certificate issue)
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
// curl_close() is deprecated in PHP 8.5+ and not needed since PHP 8.0

if ($curlError) {
    error_log("Gemini API Error: " . $curlError);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'AI 상담 중 오류가 발생했습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

if ($httpCode !== 200) {
    error_log("Gemini API HTTP Error: " . $httpCode . " - " . $response);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'AI 상담 중 오류가 발생했습니다. (HTTP ' . $httpCode . ')'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

$responseData = json_decode($response, true);

if (!$responseData || !isset($responseData['candidates'][0]['content']['parts'][0]['text'])) {
    error_log("Gemini API Invalid Response: " . $response);
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'AI 응답을 처리할 수 없습니다.'
    ], JSON_UNESCAPED_UNICODE);
    exit();
}

$aiResponse = $responseData['candidates'][0]['content']['parts'][0]['text'];

// 성공 응답
echo json_encode([
    'success' => true,
    'message' => $aiResponse
], JSON_UNESCAPED_UNICODE);
