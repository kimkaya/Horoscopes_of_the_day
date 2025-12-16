<?php
require_once 'config.php';

// 별자리 계산 함수
function getZodiacSign($birthdate) {
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

$response = [
    'success' => false,
    'message' => '',
    'fortune' => null
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
if (empty($data['type']) || empty($data['name'])) {
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
    // Start transaction
    $conn->beginTransaction();

    $fortuneType = $data['type'];
    $name = htmlspecialchars($data['name'], ENT_QUOTES, 'UTF-8');
    $birthdate = isset($data['birthdate']) ? $data['birthdate'] : null;
    $gender = isset($data['gender']) ? $data['gender'] : null;

    $fortune = null;
    $resultText = '';

    switch ($fortuneType) {
        case 'daily':
            // 생년월일 기반 별자리 운세
            if ($birthdate) {
                $zodiacSign = getZodiacSign($birthdate);

                if ($zodiacSign) {
                    // 별자리별 운세 생성
                    $fortunes = [
                        '물병자리' => [
                            'fortune_text' => '오늘은 새로운 아이디어가 샘솟는 날입니다. 창의적인 생각을 실천에 옮겨보세요. 주변 사람들과의 소통이 즐거운 하루가 될 것입니다.',
                            'lucky_item' => '노트북',
                            'lucky_color' => '청록색',
                            'lucky_number' => 11
                        ],
                        '물고기자리' => [
                            'fortune_text' => '감성이 풍부해지는 하루입니다. 예술적인 활동이나 명상을 통해 내면을 돌아보는 시간을 가져보세요. 직관력이 높아져 중요한 결정을 내리기 좋은 날입니다.',
                            'lucky_item' => '향초',
                            'lucky_color' => '보라색',
                            'lucky_number' => 7
                        ],
                        '양자리' => [
                            'fortune_text' => '활력이 넘치는 하루입니다. 새로운 도전을 시작하기 좋은 날이에요. 운동이나 야외 활동을 통해 에너지를 발산하면 더욱 좋은 결과를 얻을 수 있습니다.',
                            'lucky_item' => '운동화',
                            'lucky_color' => '빨간색',
                            'lucky_number' => 9
                        ],
                        '황소자리' => [
                            'fortune_text' => '안정적인 하루가 예상됩니다. 계획했던 일들을 차근차근 진행하세요. 금전운이 좋아 재테크나 저축 계획을 세우기 좋은 날입니다.',
                            'lucky_item' => '지갑',
                            'lucky_color' => '초록색',
                            'lucky_number' => 6
                        ],
                        '쌍둥이자리' => [
                            'fortune_text' => '소통의 날입니다. 새로운 사람들을 만나거나 오랜 친구와 연락하기 좋은 하루예요. SNS 활동이나 네트워킹이 긍정적인 결과를 가져올 것입니다.',
                            'lucky_item' => '스마트폰',
                            'lucky_color' => '노란색',
                            'lucky_number' => 5
                        ],
                        '게자리' => [
                            'fortune_text' => '가족과 가까운 사람들에게 관심을 기울이세요. 집에서 편안한 시간을 보내거나 요리를 하면 마음의 평화를 얻을 수 있습니다. 감정적인 유대감이 깊어지는 날입니다.',
                            'lucky_item' => '쿠션',
                            'lucky_color' => '은색',
                            'lucky_number' => 2
                        ],
                        '사자자리' => [
                            'fortune_text' => '당신의 매력이 빛나는 날입니다. 자신감을 가지고 중요한 프레젠테이션이나 미팅에 임하세요. 리더십을 발휘할 기회가 찾아올 것입니다.',
                            'lucky_item' => '선글라스',
                            'lucky_color' => '금색',
                            'lucky_number' => 1
                        ],
                        '처녀자리' => [
                            'fortune_text' => '세심한 주의가 필요한 날입니다. 업무나 공부에 집중하면 좋은 성과를 얻을 수 있어요. 건강 관리에도 신경 쓰면 더욱 좋습니다.',
                            'lucky_item' => '체크리스트',
                            'lucky_color' => '베이지색',
                            'lucky_number' => 5
                        ],
                        '천칭자리' => [
                            'fortune_text' => '균형과 조화가 중요한 날입니다. 결정을 내리기 전에 충분히 고민하세요. 예술이나 미적 감각을 활용하는 활동이 행운을 가져다줍니다.',
                            'lucky_item' => '악세사리',
                            'lucky_color' => '핑크색',
                            'lucky_number' => 6
                        ],
                        '전갈자리' => [
                            'fortune_text' => '깊이 있는 통찰력이 발휘되는 하루입니다. 중요한 비밀이나 정보를 알게 될 수 있어요. 집중력이 높아져 연구나 조사 활동에 좋은 날입니다.',
                            'lucky_item' => '다이어리',
                            'lucky_color' => '검은색',
                            'lucky_number' => 8
                        ],
                        '사수자리' => [
                            'fortune_text' => '모험과 탐험의 날입니다. 새로운 장소를 방문하거나 배움의 기회를 찾아보세요. 긍정적인 마인드가 행운을 불러올 것입니다.',
                            'lucky_item' => '여행 가방',
                            'lucky_color' => '남색',
                            'lucky_number' => 3
                        ],
                        '염소자리' => [
                            'fortune_text' => '목표를 향한 꾸준한 노력이 빛을 발하는 날입니다. 장기적인 계획을 세우거나 실행에 옮기기 좋은 하루예요. 인내심을 가지고 임하세요.',
                            'lucky_item' => '만년필',
                            'lucky_color' => '갈색',
                            'lucky_number' => 8
                        ]
                    ];

                    $fortuneData = $fortunes[$zodiacSign];
                    $fortune = [
                        'zodiac_sign' => $zodiacSign,
                        'fortune_text' => $fortuneData['fortune_text'],
                        'lucky_item' => $fortuneData['lucky_item'],
                        'lucky_color' => $fortuneData['lucky_color'],
                        'lucky_number' => $fortuneData['lucky_number'],
                        'overall' => $fortuneData['fortune_text'],
                        'advice' => '오늘 하루도 행복하게 보내세요!'
                    ];

                    $resultText = sprintf(
                        "별자리: %s\n오늘의 운세: %s\n행운의 아이템: %s\n행운의 색상: %s\n행운의 숫자: %d",
                        $zodiacSign,
                        $fortuneData['fortune_text'],
                        $fortuneData['lucky_item'],
                        $fortuneData['lucky_color'],
                        $fortuneData['lucky_number']
                    );
                } else {
                    if ($conn->inTransaction()) {
                        $conn->rollBack();
                    }
                    $response['message'] = '별자리를 계산할 수 없습니다.';
                    echo json_encode($response, JSON_UNESCAPED_UNICODE);
                    exit();
                }
            } else {
                if ($conn->inTransaction()) {
                    $conn->rollBack();
                }
                $response['message'] = '생년월일 정보가 필요합니다.';
                echo json_encode($response, JSON_UNESCAPED_UNICODE);
                exit();
            }
            break;

        case 'tarot':
            // Get 3 random tarot cards
            $stmt = $conn->prepare("SELECT * FROM tarot_cards ORDER BY RAND() LIMIT 3");
            $stmt->execute();
            $cards = $stmt->fetchAll();

            if ($cards && count($cards) === 3) {
                $cardPositions = ['과거', '현재', '미래'];
                $cardDetails = [];
                $resultTextParts = [];

                // Process each card
                foreach ($cards as $index => $card) {
                    // Randomly decide if reversed
                    $isReversed = rand(0, 1);
                    $meaning = $isReversed ? $card['reversed_meaning'] : $card['card_meaning'];

                    $cardDetails[] = [
                        'position' => $cardPositions[$index],
                        'card_name' => $card['card_name'],
                        'card_meaning' => $meaning,
                        'is_reversed' => $isReversed
                    ];

                    $resultTextParts[] = sprintf(
                        "[%s] %s\n%s",
                        $cardPositions[$index],
                        $card['card_name'],
                        $meaning
                    );
                }

                // Generate overall interpretation based on all 3 cards
                $overallInterpretation = "과거의 " . $cards[0]['card_name'] . "는 당신이 지나온 길을 보여줍니다. " .
                                        "현재의 " . $cards[1]['card_name'] . "는 지금 당신의 상황을 나타냅니다. " .
                                        "미래의 " . $cards[2]['card_name'] . "는 앞으로 다가올 일들을 예시합니다. " .
                                        "이 세 장의 카드가 말하는 것은, 당신이 과거의 경험을 바탕으로 현재를 살아가고 있으며, " .
                                        "그 연장선상에서 미래가 펼쳐질 것이라는 메시지입니다.";

                $fortune = [
                    'cards' => $cardDetails,
                    'overall_interpretation' => $overallInterpretation,
                    'question' => isset($data['question']) ? $data['question'] : ''
                ];

                $resultText = implode("\n\n", $resultTextParts) . "\n\n[종합 해석]\n" . $overallInterpretation;
            }
            break;

        case 'saju':
            // Calculate zodiac based on birth year
            if ($birthdate) {
                $year = (int)date('Y', strtotime($birthdate));
                $zodiacAnimals = ['쥐띠', '소띠', '호랑이띠', '토끼띠', '용띠', '뱀띠',
                                 '말띠', '양띠', '원숭이띠', '닭띠', '개띠', '돼지띠'];
                $zodiacIndex = ($year - 4) % 12;
                if ($zodiacIndex < 0) $zodiacIndex += 12;
                $zodiacSign = $zodiacAnimals[$zodiacIndex];

                // Get saju result for zodiac
                $stmt = $conn->prepare("SELECT * FROM saju_results WHERE zodiac_sign = ? LIMIT 1");
                $stmt->execute([$zodiacSign]);
                $fortune = $stmt->fetch();

                if ($fortune) {
                    $resultText = sprintf(
                        "띠: %s\n오행: %s\n성격: %s\n운세: %s\n재물운: %s\n애정운: %s",
                        $fortune['zodiac_sign'],
                        $fortune['element'],
                        $fortune['personality_text'],
                        $fortune['fortune_text'],
                        $fortune['career_text'],
                        $fortune['love_text']
                    );
                }
            }
            break;

        case 'omikuji':
            // Get random omikuji result
            $stmt = $conn->prepare("SELECT * FROM omikuji_results ORDER BY RAND() LIMIT 1");
            $stmt->execute();
            $fortune = $stmt->fetch();

            if ($fortune) {
                $resultText = sprintf(
                    "길흉: %s\n운세: %s\n소원: %s\n건강: %s\n학업/사업: %s\n여행: %s",
                    $fortune['fortune_level'],
                    $fortune['fortune_text'],
                    $fortune['wish_text'],
                    $fortune['health_text'],
                    $fortune['business_text'],
                    $fortune['travel_text']
                );
            }
            break;

        default:
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            $response['message'] = '잘못된 운세 타입입니다.';
            echo json_encode($response, JSON_UNESCAPED_UNICODE);
            exit();
    }

    if (!$fortune) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $response['message'] = '운세 데이터를 찾을 수 없습니다.';
        echo json_encode($response, JSON_UNESCAPED_UNICODE);
        exit();
    }

    // Save to history
    $stmt = $conn->prepare("
        INSERT INTO fortune_history (fortune_type, user_name, user_birthdate, user_gender, result_text)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        $fortuneType,
        $name,
        $birthdate,
        $gender,
        $resultText
    ]);

    // Commit transaction
    $conn->commit();

    $response['success'] = true;
    $response['message'] = '운세를 성공적으로 가져왔습니다.';
    $response['fortune'] = $fortune;

} catch (PDOException $e) {
    // Rollback on error
    if ($conn && $conn->inTransaction()) {
        $conn->rollBack();
    }
    $response['message'] = '오류가 발생했습니다: ' . $e->getMessage();
    error_log("Fortune API Error: " . $e->getMessage());
}

echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
