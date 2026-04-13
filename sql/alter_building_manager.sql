-- 단지(a_building) 테이블에 담당자/연체요율 컬럼 추가
-- 실행 대상: sinbansang (운영), bansanghoe (테스트)
-- 실행일: 2026-04-13

ALTER TABLE a_building
  ADD COLUMN manager_name VARCHAR(50) DEFAULT '' COMMENT '담당자명',
  ADD COLUMN manager_phone VARCHAR(20) DEFAULT '' COMMENT '담당자연락처',
  ADD COLUMN manager_email VARCHAR(100) DEFAULT '' COMMENT '담당자이메일',
  ADD COLUMN late_fee_rate DECIMAL(5,2) DEFAULT 0.00 COMMENT '연체요율(%)',
  ADD COLUMN late_fee_base VARCHAR(20) DEFAULT '미납금액' COMMENT '연체요율적용기준';
