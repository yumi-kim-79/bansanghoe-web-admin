-- noti8: 점검일지 알림 카테고리 (매니저 전용)
-- 사용처: inspection_form_update.php (단지 매니저에게 점검일지 작성 통보)
-- 실행: 운영 sinbansang DB / 테스트 bansanghoe DB 양쪽
-- 기본값 1 (ON) — 기존 매니저는 점검일지 알림 수신

ALTER TABLE a_member  ADD COLUMN noti8 tinyint(2) NOT NULL DEFAULT 1;
ALTER TABLE g5_member ADD COLUMN noti8 tinyint(2) NOT NULL DEFAULT 1;
