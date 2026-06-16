-- 연차신청서 종료일 직접 선택 작업 (Phase 3 커밋 A)
-- 적용 대상: 운영 sinbansang + 테스트 (test.smtm2017.com 은 현재 운영 DB 사용 중)
-- 컬럼 타입: 기존 hp_date / holiday_date 와 동일하게 DATE NULL DEFAULT NULL
-- legacy 데이터(종료일 없음)는 NULL → 표시 로직에서 자동계산 fallback

ALTER TABLE a_holiday_person
  ADD COLUMN hp_edate DATE NULL DEFAULT NULL AFTER hp_date;

ALTER TABLE a_sign_off
  ADD COLUMN holiday_edate DATE NULL DEFAULT NULL AFTER holiday_date;
