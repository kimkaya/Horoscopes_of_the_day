<?php
require_once 'config.php';

// 띠 계산 함수
function getZodiacAnimal($birthdate) {
    $year = (int)date('Y', strtotime($birthdate));
    $zodiacAnimals = ['쥐띠', '소띠', '호랑이띠', '토끼띠', '용띠', '뱀띠',
                     '말띠', '양띠', '원숭이띠', '닭띠', '개띠', '돼지띠'];
    $zodiacIndex = ($year - 4) % 12;
    if ($zodiacIndex < 0) $zodiacIndex += 12;
    return $zodiacAnimals[$zodiacIndex];
}

// 별자리 계산 함수
function getStarSign($birthdate) {
    $date = strtotime($birthdate);
    $month = (int)date('n', $date);
    $day = (int)date('j', $date);

    $zodiacSigns = [
        ['name' => '물병자리', 'start_month' => 1, 'start_day' => 20, 'end_month' => 2, 'end_day' => 18],
        ['name' => '물고기자리', 'start_month' => 2, 'start_day' => 19, 'end_month' => 3, 'end_day' => 20],
        ['name' => '양자리', 'start_month' => 3, 'start_day' => 21, 'end_month' => 4, 'end_day' => 19],
        ['name' => '황소자리', 'start_month' => 4, 'start_day' => 20, 'end_month' => 5, 'end_day' => 20],
        ['name' => '쌍둥이자리', 'start_month' => 5, 'start_day' => 21, 'end_month' => 6, 'end_day' => 21],
        ['name' => '게자리', 'start_month' => 6, 'start_day' => 22, 'end_month' => 7, 'end_day' => 22],
        ['name' => '사자자리', 'start_month' => 7, 'start_day' => 23, 'end_month' => 8, 'end_day' => 22],
        ['name' => '처녀자리', 'start_month' => 8, 'start_day' => 23, 'end_month' => 9, 'end_day' => 22],
        ['name' => '천칭자리', 'start_month' => 9, 'start_day' => 23, 'end_month' => 10, 'end_day' => 22],
        ['name' => '전갈자리', 'start_month' => 10, 'start_day' => 23, 'end_month' => 11, 'end_day' => 22],
        ['name' => '사수자리', 'start_month' => 11, 'start_day' => 23, 'end_month' => 12, 'end_day' => 21],
        ['name' => '염소자리', 'start_month' => 12, 'start_day' => 22, 'end_month' => 1, 'end_day' => 19]
    ];

    foreach ($zodiacSigns as $sign) {
        if ($sign['start_month'] == $sign['end_month']) {
            if ($month == $sign['start_month'] && $day >= $sign['start_day'] && $day <= $sign['end_day']) {
                return $sign['name'];
            }
        } else {
            if (($month == $sign['start_month'] && $day >= $sign['start_day']) ||
                ($month == $sign['end_month'] && $day <= $sign['end_day'])) {
                return $sign['name'];
            }
        }
    }

    return null;
}

// 별자리 궁합 데이터 (간단 버전)
function getStarSignCompatibility($sign1, $sign2) {
    // 별자리 궁합 점수 매트릭스 (간단 버전)
    $compatibilityScores = [
        '물병자리' => ['물병자리' => 85, '물고기자리' => 70, '양자리' => 75, '황소자리' => 60, '쌍둥이자리' => 95, '게자리' => 55, '사자자리' => 80, '처녀자리' => 65, '천칭자리' => 90, '전갈자리' => 60, '사수자리' => 85, '염소자리' => 55],
        '물고기자리' => ['물고기자리' => 80, '양자리' => 65, '황소자리' => 90, '쌍둥이자리' => 60, '게자리' => 95, '사자자리' => 70, '처녀자리' => 85, '천칭자리' => 65, '전갈자리' => 95, '사수자리' => 70, '염소자리' => 85, '물병자리' => 70],
        '양자리' => ['양자리' => 75, '황소자리' => 60, '쌍둥이자리' => 85, '게자리' => 55, '사자자리' => 95, '처녀자리' => 60, '천칭자리' => 80, '전갈자리' => 70, '사수자리' => 95, '염소자리' => 60, '물병자리' => 75, '물고기자리' => 65],
        '황소자리' => ['황소자리' => 85, '쌍둥이자리' => 60, '게자리' => 90, '사자자리' => 65, '처녀자리' => 95, '천칭자리' => 70, '전갈자리' => 85, '사수자리' => 60, '염소자리' => 95, '물병자리' => 60, '물고기자리' => 90, '양자리' => 60],
        '쌍둥이자리' => ['쌍둥이자리' => 85, '게자리' => 60, '사자자리' => 90, '처녀자리' => 70, '천칭자리' => 95, '전갈자리' => 55, '사수자리' => 85, '염소자리' => 55, '물병자리' => 95, '물고기자리' => 60, '양자리' => 85, '황소자리' => 60],
        '게자리' => ['게자리' => 90, '사자자리' => 70, '처녀자리' => 85, '천칭자리' => 65, '전갈자리' => 95, '사수자리' => 60, '염소자리' => 85, '물병자리' => 55, '물고기자리' => 95, '양자리' => 55, '황소자리' => 90, '쌍둥이자리' => 60],
        '사자자리' => ['사자자리' => 80, '처녀자리' => 70, '천칭자리' => 90, '전갈자리' => 65, '사수자리' => 95, '염소자리' => 60, '물병자리' => 80, '물고기자리' => 70, '양자리' => 95, '황소자리' => 65, '쌍둥이자리' => 90, '게자리' => 70],
        '처녀자리' => ['처녀자리' => 85, '천칭자리' => 75, '전갈자리' => 85, '사수자리' => 65, '염소자리' => 95, '물병자리' => 65, '물고기자리' => 85, '양자리' => 60, '황소자리' => 95, '쌍둥이자리' => 70, '게자리' => 85, '사자자리' => 70],
        '천칭자리' => ['천칭자리' => 85, '전갈자리' => 70, '사수자리' => 85, '염소자리' => 65, '물병자리' => 90, '물고기자리' => 65, '양자리' => 80, '황소자리' => 70, '쌍둥이자리' => 95, '게자리' => 65, '사자자리' => 90, '처녀자리' => 75],
        '전갈자리' => ['전갈자리' => 85, '사수자리' => 70, '염소자리' => 90, '물병자리' => 60, '물고기자리' => 95, '양자리' => 70, '황소자리' => 85, '쌍둥이자리' => 55, '게자리' => 95, '사자자리' => 65, '처녀자리' => 85, '천칭자리' => 70],
        '사수자리' => ['사수자리' => 85, '염소자리' => 60, '물병자리' => 85, '물고기자리' => 70, '양자리' => 95, '황소자리' => 60, '쌍둥이자리' => 85, '게자리' => 60, '사자자리' => 95, '처녀자리' => 65, '천칭자리' => 85, '전갈자리' => 70],
        '염소자리' => ['염소자리' => 85, '물병자리' => 55, '물고기자리' => 85, '양자리' => 60, '황소자리' => 95, '쌍둥이자리' => 55, '게자리' => 85, '사자자리' => 60, '처녀자리' => 95, '천칭자리' => 65, '전갈자리' => 90, '사수자리' => 60]
    ];

    $score = $compatibilityScores[$sign1][$sign2] ?? 70;

    $level = '보통';
    $description = '';

    if ($score >= 90) {
        $level = '천생연분';
        $description = "{$sign1}와 {$sign2}는 천생연분입니다! 서로를 완벽하게 이해하고 보완하는 최고의 궁합이에요. 함께라면 어떤 어려움도 이겨낼 수 있습니다.";
    } elseif ($score >= 75) {
        $level = '매우좋음';
        $description = "{$sign1}와 {$sign2}는 매우 좋은 궁합입니다. 서로의 장점을 끌어내고 행복한 관계를 만들어갈 수 있어요.";
    } elseif ($score >= 60) {
        $level = '좋음';
        $description = "{$sign1}와 {$sign2}는 좋은 궁합입니다. 서로를 이해하고 배려한다면 아름다운 관계를 만들 수 있어요.";
    } elseif ($score >= 50) {
        $level = '보통';
        $description = "{$sign1}와 {$sign2}는 평범한 궁합입니다. 노력과 이해를 통해 좋은 관계를 만들어갈 수 있어요.";
    } else {
        $level = '주의';
        $description = "{$sign1}와 {$sign2}는 차이가 있는 궁합입니다. 하지만 서로의 다름을 존중한다면 더욱 특별한 관계가 될 수 있어요.";
    }

    return [
        'score' => $score,
        'level' => $level,
        'description' => $description
    ];
}

$response = [
    'success' => false,
    'message' => '',
    'compatibility' => null
];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'POST 요청만 허용됩니다.';
    echo json_encode($response, JSON_UNESCAPED_UNICODE);
    exit();
}

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
if (empty($data['name1']) || empty($data['birthdate1']) ||
    empty($data['name2']) || empty($data['birthdate2'])) {
    $response['message'] = '필수 정보가 누락되었습니다.';
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
    $name1 = htmlspecialchars($data['name1'], ENT_QUOTES, 'UTF-8');
    $birthdate1 = $data['birthdate1'];
    $name2 = htmlspecialchars($data['name2'], ENT_QUOTES, 'UTF-8');
    $birthdate2 = $data['birthdate2'];

    // 띠 계산
    $zodiac1 = getZodiacAnimal($birthdate1);
    $zodiac2 = getZodiacAnimal($birthdate2);

    // 별자리 계산
    $starSign1 = getStarSign($birthdate1);
    $starSign2 = getStarSign($birthdate2);

    // 띠 궁합 조회
    $stmt = $conn->prepare("
        SELECT * FROM zodiac_compatibility
        WHERE (zodiac1 = ? AND zodiac2 = ?) OR (zodiac1 = ? AND zodiac2 = ?)
        LIMIT 1
    ");
    $stmt->execute([$zodiac1, $zodiac2, $zodiac2, $zodiac1]);
    $zodiacCompat = $stmt->fetch();

    // 띠 궁합이 없으면 기본값
    if (!$zodiacCompat) {
        $zodiacCompat = [
            'compatibility_score' => 70,
            'compatibility_level' => '보통',
            'description' => "{$zodiac1}와 {$zodiac2}의 궁합입니다. 서로를 이해하고 배려한다면 좋은 관계를 만들 수 있습니다.",
            'advice' => '서로의 차이를 인정하고 존중하세요.'
        ];
    }

    // 별자리 궁합 계산
    $starCompat = getStarSignCompatibility($starSign1, $starSign2);

    // 종합 점수 계산 (띠 60% + 별자리 40%)
    $totalScore = round($zodiacCompat['compatibility_score'] * 0.6 + $starCompat['score'] * 0.4);

    $overallLevel = '보통';
    if ($totalScore >= 90) {
        $overallLevel = '천생연분';
    } elseif ($totalScore >= 75) {
        $overallLevel = '매우좋음';
    } elseif ($totalScore >= 60) {
        $overallLevel = '좋음';
    } elseif ($totalScore >= 50) {
        $overallLevel = '보통';
    } else {
        $overallLevel = '주의';
    }

    $compatibility = [
        'person1' => [
            'name' => $name1,
            'birthdate' => $birthdate1,
            'zodiac' => $zodiac1,
            'star_sign' => $starSign1
        ],
        'person2' => [
            'name' => $name2,
            'birthdate' => $birthdate2,
            'zodiac' => $zodiac2,
            'star_sign' => $starSign2
        ],
        'zodiac_compatibility' => [
            'score' => $zodiacCompat['compatibility_score'],
            'level' => $zodiacCompat['compatibility_level'],
            'description' => $zodiacCompat['description'],
            'advice' => $zodiacCompat['advice'] ?? ''
        ],
        'star_sign_compatibility' => [
            'score' => $starCompat['score'],
            'level' => $starCompat['level'],
            'description' => $starCompat['description']
        ],
        'overall' => [
            'score' => $totalScore,
            'level' => $overallLevel,
            'summary' => "{$name1}님과 {$name2}님의 궁합 점수는 100점 만점에 {$totalScore}점입니다!"
        ]
    ];

    // 결과 텍스트 생성
    $resultText = sprintf(
        "【궁합 결과】\n%s (%s, %s) ♥ %s (%s, %s)\n\n종합 점수: %d점 (%s)\n\n띠 궁합: %d점 (%s)\n%s\n\n별자리 궁합: %d점 (%s)\n%s",
        $name1, $zodiac1, $starSign1,
        $name2, $zodiac2, $starSign2,
        $totalScore, $overallLevel,
        $zodiacCompat['compatibility_score'], $zodiacCompat['compatibility_level'], $zodiacCompat['description'],
        $starCompat['score'], $starCompat['level'], $starCompat['description']
    );

    // Save to history
    $stmt = $conn->prepare("
        INSERT INTO fortune_history (fortune_type, user_name, user_birthdate, result_text)
        VALUES ('compatibility', ?, ?, ?)
    ");
    $combinedNames = $name1 . ' & ' . $name2;
    $combinedBirthdates = $birthdate1 . ' & ' . $birthdate2;
    $stmt->execute([$combinedNames, $combinedBirthdates, $resultText]);

    $response['success'] = true;
    $response['message'] = '궁합을 성공적으로 가져왔습니다.';
    $response['compatibility'] = $compatibility;

} catch (PDOException $e) {
    $response['message'] = '오류가 발생했습니다: ' . $e->getMessage();
    error_log("Compatibility API Error: " . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
