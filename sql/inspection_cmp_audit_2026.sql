-- 점검일지 inspection_cmp 오염 감사 (2026년 전체)
-- 실행 환경: 운영 sinbansang DB 또는 테스트 bansanghoe DB
-- 실행 시점: UPDATE 보정 전 사전 시뮬레이션용
-- 목적:
--   1) 점검 기록의 inspection_cmp 가 가리키는 a_contract 가 비정상(해지/삭제/만료)인 행을 추출
--   2) 각 행에 대해 created_at 시점 기준의 활성 계약 후보를 suggested_correction 으로 제시
--   3) suggested_correction 이 NULL 이면 보정 불가 (운영팀 수동 검토 필요)
--
-- 안전: 본 파일은 SELECT 만 포함. UPDATE 는 별도 승인 후 수동 실행.

SELECT
    i.inspection_idx,
    b.building_name,
    ind.industry_name,
    i.inspection_cmp,
    mc.company_name                                 AS saved_company,
    i.inspection_year,
    i.inspection_month,
    i.inspection_status,
    i.created_at,
    ct.ct_sdate,
    ct.ct_edate,
    ct.ct_status,
    ct.is_del                                       AS ct_is_del,
    -- created_at 시점에 활성이었던 계약 후보 (보정 가능한 경우)
    (SELECT CONCAT(mc2.company_name, ' (idx=', ct2.company_idx, ')')
     FROM a_contract ct2
     LEFT JOIN a_manage_company mc2 ON ct2.company_idx = mc2.company_idx
     WHERE ct2.building_id  = i.building_id
       AND ct2.industry_idx = i.inspection_category
       AND ct2.is_del   = 0
       AND ct2.is_temp  = 0
       AND ct2.ct_status = 0
       AND ct2.ct_sdate <= DATE(i.created_at)
       AND ct2.ct_edate >= DATE(i.created_at)
     ORDER BY ct2.ct_sdate DESC, ct2.ct_idx DESC
     LIMIT 1
    )                                               AS suggested_correction,
    -- 동일 후보의 company_idx 만 별도 컬럼 (UPDATE 시 사용)
    (SELECT ct2.company_idx
     FROM a_contract ct2
     WHERE ct2.building_id  = i.building_id
       AND ct2.industry_idx = i.inspection_category
       AND ct2.is_del   = 0
       AND ct2.is_temp  = 0
       AND ct2.ct_status = 0
       AND ct2.ct_sdate <= DATE(i.created_at)
       AND ct2.ct_edate >= DATE(i.created_at)
     ORDER BY ct2.ct_sdate DESC, ct2.ct_idx DESC
     LIMIT 1
    )                                               AS suggested_company_idx
FROM a_inspection i
LEFT JOIN a_building       b   ON i.building_id        = b.building_id
LEFT JOIN a_industry_list  ind ON i.inspection_category = ind.industry_idx
LEFT JOIN a_manage_company mc  ON i.inspection_cmp     = mc.company_idx
LEFT JOIN a_contract       ct  ON ct.building_id  = i.building_id
                              AND ct.industry_idx = i.inspection_category
                              AND ct.company_idx  = i.inspection_cmp
WHERE i.is_del = 0
  AND i.inspection_year = '2026'
  AND (ct.ct_status != 0 OR ct.is_del = 1 OR ct.ct_idx IS NULL)
ORDER BY i.created_at DESC;

-- 요약 통계 (별도 실행)
-- SELECT
--     COUNT(*)                                                         AS total_polluted,
--     SUM(CASE WHEN suggested_company_idx IS NOT NULL THEN 1 ELSE 0 END) AS correctable,
--     SUM(CASE WHEN suggested_company_idx IS NULL     THEN 1 ELSE 0 END) AS uncorrectable
-- FROM ( ... 위 쿼리를 서브쿼리로 ... ) AS audit;
