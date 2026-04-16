CREATE TABLE IF NOT EXISTS a_sms_history (
    sh_id INT AUTO_INCREMENT PRIMARY KEY,
    sh_building_id INT COMMENT '단지 ID',
    sh_sender VARCHAR(20) COMMENT '발신번호',
    sh_recipients TEXT COMMENT '수신번호 JSON 배열',
    sh_message TEXT COMMENT '문자 내용',
    sh_total_count INT DEFAULT 0 COMMENT '총 발송 건수',
    sh_success_count INT DEFAULT 0 COMMENT '성공 건수',
    sh_fail_count INT DEFAULT 0 COMMENT '실패 건수',
    sh_cost INT DEFAULT 0 COMMENT '비용 (원)',
    sh_api_response TEXT COMMENT 'API 응답 JSON',
    sh_created_at DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '발송 일시',
    sh_created_by VARCHAR(50) COMMENT '발송자 ID',
    INDEX idx_building (sh_building_id),
    INDEX idx_created_at (sh_created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='SMS 발송 이력';
