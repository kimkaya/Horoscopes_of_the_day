# Fortune Database Setup

## Installation

1. MySQL 서버에 접속
2. fortune_db.sql 실행하여 데이터베이스 및 테이블 생성
3. fortune_data.sql 실행하여 샘플 데이터 삽입

```bash
mysql -u root -p < fortune_db.sql
mysql -u root -p < fortune_data.sql
```

## Tables

- **fortune_history**: 운세 조회 기록 저장
- **daily_fortunes**: 오늘의 운세 메시지
- **tarot_cards**: 타로 카드 정보
- **saju_results**: 사주 운세 결과
- **omikuji_results**: 오미쿠지 운세 결과
