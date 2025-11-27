<?php
require_once 'config.php';

// JSON 입력 받기
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['fortune']) || !isset($input['type'])) {
    echo json_encode([
        'success' => false,
        'message' => '운세 데이터가 필요합니다.'
    ]);
    exit;
}

$fortune = $input['fortune'];
$fortuneType = $input['type'];

// API 키 확인 - API 키가 없으면 로컬 요약 사용
$useLocalSummary = !defined('CLAUDE_API_KEY') || CLAUDE_API_KEY === 'your-api-key-here';

// 운세 타입별 제목 설정
$typeNames = [
    'daily' => '오늘의 운세',
    'tarot' => '타로',
    'saju' => '사주',
    'omikuji' => '운세 뽑기'
];
$typeName = $typeNames[$fortuneType] ?? '운세';

// 운세 요약 생성
try {
    if ($useLocalSummary) {
        // 로컬 요약 사용 (무료)
        $summary = generateLocalSummary($fortune, $fortuneType, $typeName);
    } else {
        // Claude API 사용
        $fortuneText = convertFortuneToText($fortune, $fortuneType);
        $summary = callClaudeAPI($fortuneText, $typeName);
    }

    echo json_encode([
        'success' => true,
        'summary' => $summary,
        'method' => $useLocalSummary ? 'local' : 'ai'
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => '요약 생성 실패: ' . $e->getMessage()
    ]);
}

// 로컬 요약 생성 함수 (무료)
function generateLocalSummary($fortune, $type, $typeName) {
    $summary = '';
    $emoji = [
        'daily' => '☀️',
        'tarot' => '🃏',
        'saju' => '📿',
        'omikuji' => '🎋'
    ];

    $icon = $emoji[$type] ?? '✨';

    switch($type) {
        case 'tarot':
            // 타로: 첫 번째 카드와 종합 해석 요약
            if (isset($fortune['cards']) && is_array($fortune['cards']) && count($fortune['cards']) > 0) {
                $firstCard = $fortune['cards'][0];
                $summary .= "{$icon} {$firstCard['card_name']} 카드가 나왔습니다. ";

                // 카드 의미 첫 문장 추출
                $meaning = $firstCard['card_meaning'];
                $sentences = preg_split('/[.!?]\s+/', $meaning, 2);
                $summary .= trim($sentences[0]) . '. ';
            }

            if (isset($fortune['overall_interpretation'])) {
                // 종합 해석 첫 1-2문장 추출
                $interpretation = $fortune['overall_interpretation'];
                $sentences = preg_split('/[.!?]\s+/', $interpretation, 3);
                $summary .= trim($sentences[0]);
                if (isset($sentences[1])) {
                    $summary .= '. ' . trim($sentences[1]);
                }
            }
            break;

        case 'saju':
            // 사주: 띠, 오행, 운세 핵심 요약
            $parts = [];

            if (isset($fortune['zodiac_sign'])) {
                $parts[] = "{$fortune['zodiac_sign']}띠";
            }

            if (isset($fortune['element'])) {
                $parts[] = "{$fortune['element']}";
            }

            if (count($parts) > 0) {
                $summary .= "{$icon} " . implode(', ', $parts) . "입니다. ";
            }

            // 운세 텍스트 핵심 요약
            if (isset($fortune['fortune_text'])) {
                $sentences = preg_split('/[.!?]\s+/', $fortune['fortune_text'], 3);
                $summary .= trim($sentences[0]);
                if (isset($sentences[1]) && mb_strlen($sentences[1]) < 100) {
                    $summary .= '. ' . trim($sentences[1]);
                }
            }
            break;

        case 'omikuji':
            // 운세 뽑기: 길흉과 운세 요약
            if (isset($fortune['fortune_level'])) {
                $levelMap = [
                    '大吉' => '대길',
                    '中吉' => '중길',
                    '小吉' => '소길',
                    '吉' => '길',
                    '末吉' => '말길',
                    '半吉' => '반길',
                    '凶' => '흉',
                    '小凶' => '소흉',
                    '大凶' => '대흉'
                ];
                $level = $levelMap[$fortune['fortune_level']] ?? $fortune['fortune_level'];
                $summary .= "{$icon} {$level} 운세입니다. ";
            }

            if (isset($fortune['fortune_text'])) {
                $sentences = preg_split('/[.!?]\s+/', $fortune['fortune_text'], 3);
                $summary .= trim($sentences[0]);
                if (isset($sentences[1])) {
                    $summary .= '. ' . trim($sentences[1]);
                }
            }
            break;

        case 'daily':
            // 오늘의 운세: 별자리와 종합 운세 요약
            if (isset($fortune['zodiac_sign'])) {
                $summary .= "{$icon} {$fortune['zodiac_sign']} 운세입니다. ";
            }

            if (isset($fortune['overall'])) {
                $sentences = preg_split('/[.!?]\s+/', $fortune['overall'], 3);
                $summary .= trim($sentences[0]);
                if (isset($sentences[1])) {
                    $summary .= '. ' . trim($sentences[1]);
                }
            }

            // 행운의 아이템 추가
            if (isset($fortune['lucky_item'])) {
                $summary .= " 행운의 아이템은 {$fortune['lucky_item']}입니다.";
            }
            break;
    }

    // 요약이 비어있으면 기본 메시지
    if (empty(trim($summary))) {
        $summary = "{$icon} 오늘 하루도 좋은 일만 가득하시길 바랍니다!";
    }

    return trim($summary);
}

// 운세 데이터를 텍스트로 변환하는 함수
function convertFortuneToText($fortune, $type) {
    $text = '';

    switch($type) {
        case 'tarot':
            if (isset($fortune['cards']) && is_array($fortune['cards'])) {
                foreach ($fortune['cards'] as $card) {
                    $text .= $card['position'] . ': ' . $card['card_name'] . "\n";
                    $text .= $card['card_meaning'] . "\n\n";
                }
            }
            if (isset($fortune['overall_interpretation'])) {
                $text .= "종합 해석: " . $fortune['overall_interpretation'];
            }
            break;

        case 'saju':
            if (isset($fortune['zodiac_sign'])) {
                $text .= "띠: " . $fortune['zodiac_sign'] . "\n";
            }
            if (isset($fortune['element'])) {
                $text .= "오행: " . $fortune['element'] . "\n";
            }
            if (isset($fortune['personality_text'])) {
                $text .= "성격: " . $fortune['personality_text'] . "\n";
            }
            if (isset($fortune['fortune_text'])) {
                $text .= "운세: " . $fortune['fortune_text'] . "\n";
            }
            if (isset($fortune['career_text'])) {
                $text .= "재물운: " . $fortune['career_text'] . "\n";
            }
            if (isset($fortune['love_text'])) {
                $text .= "애정운: " . $fortune['love_text'];
            }
            break;

        case 'omikuji':
            if (isset($fortune['fortune_level'])) {
                $text .= "길흉: " . $fortune['fortune_level'] . "\n";
            }
            if (isset($fortune['fortune_text'])) {
                $text .= "운세: " . $fortune['fortune_text'] . "\n";
            }
            if (isset($fortune['wish_text'])) {
                $text .= "소원: " . $fortune['wish_text'] . "\n";
            }
            if (isset($fortune['health_text'])) {
                $text .= "건강: " . $fortune['health_text'] . "\n";
            }
            if (isset($fortune['business_text'])) {
                $text .= "학업/사업: " . $fortune['business_text'] . "\n";
            }
            if (isset($fortune['travel_text'])) {
                $text .= "여행: " . $fortune['travel_text'];
            }
            break;

        case 'daily':
            if (isset($fortune['zodiac_sign'])) {
                $text .= "별자리: " . $fortune['zodiac_sign'] . "\n";
            }
            if (isset($fortune['overall'])) {
                $text .= "종합 운세: " . $fortune['overall'] . "\n";
            }
            if (isset($fortune['lucky_item'])) {
                $text .= "행운의 아이템: " . $fortune['lucky_item'] . "\n";
            }
            if (isset($fortune['lucky_color'])) {
                $text .= "행운의 색상: " . $fortune['lucky_color'] . "\n";
            }
            if (isset($fortune['lucky_number'])) {
                $text .= "행운의 숫자: " . $fortune['lucky_number'] . "\n";
            }
            if (isset($fortune['advice'])) {
                $text .= "조언: " . $fortune['advice'];
            }
            break;
    }

    return $text;
}

// Claude API 호출 함수
function callClaudeAPI($fortuneText, $typeName) {
    $prompt = "다음은 {$typeName} 결과입니다. 이 운세를 2-3문장으로 핵심만 간단명료하게 요약해주세요. 긍정적이고 친근한 톤으로 작성해주세요.\n\n{$fortuneText}";

    $data = [
        'model' => CLAUDE_MODEL,
        'max_tokens' => 300,
        'messages' => [
            [
                'role' => 'user',
                'content' => $prompt
            ]
        ]
    ];

    $ch = curl_init(CLAUDE_API_URL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'x-api-key: ' . CLAUDE_API_KEY,
        'anthropic-version: 2023-06-01'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception("API 호출 실패: " . $error);
    }

    curl_close($ch);

    if ($httpCode !== 200) {
        throw new Exception("API 오류 (HTTP {$httpCode}): " . $response);
    }

    $result = json_decode($response, true);

    if (!$result || !isset($result['content'][0]['text'])) {
        throw new Exception("응답 파싱 실패");
    }

    return $result['content'][0]['text'];
}
