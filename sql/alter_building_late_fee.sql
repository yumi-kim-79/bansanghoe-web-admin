-- a_building 테이블에 연체요율 컬럼만 추가
-- 담당자는 기존 a_mng_building 테이블 활용
-- 실행 대상: sinbansang (운영), bansanghoe (테스트)

ALTER TABLE a_building
  ADD COLUMN IF NOT EXISTS late_fee_rate DECIMAL(5,2) DEFAULT 0.00 COMMENT '연체요율(%)',
  ADD COLUMN IF NOT EXISTS late_fee_base VARCHAR(20) DEFAULT '미납금액' COMMENT '연체요율적용기준';
