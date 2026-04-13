<?php
/**
 * 단지 설정 API
 *
 * GET  ?action=building_managers&building_id=N       단지별 담당자 목록
 * GET  ?action=building_managers_all                  전체 단지 담당자 목록
 * GET  ?action=building_settings&building_id=N       단지별 연체요율 조회
 * GET  ?action=building_settings_all                  전체 단지 연체요율 목록
 * POST ?action=update_building_settings               단지 연체요율 수정
 */
require_once "../_common.php";

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if($_SERVER['REQUEST_METHOD'] == 'OPTIONS'){
    http_response_code(200);
    exit;
}

$action = $_REQUEST['action'] ?? '';

switch($action){

    // ─── 담당자 조회 ───

    // 단지별 담당자 목록 (a_mng_building 활용)
    case 'building_managers':
        $building_id = $_GET['building_id'] ?? '';
        if(!$building_id){
            echo json_encode(['error' => true, 'msg' => 'building_id 필수']);
            exit;
        }

        $brow = sql_fetch("SELECT building_id, building_name FROM a_building WHERE building_id = '{$building_id}' AND is_del = 0");
        if(!$brow['building_id']){
            echo json_encode(['error' => true, 'msg' => '단지를 찾을 수 없습니다.']);
            exit;
        }

        $managers = _get_building_managers($building_id);

        echo json_encode([
            'building_id' => (int)$brow['building_id'],
            'building_name' => $brow['building_name'],
            'managers' => $managers,
        ], JSON_UNESCAPED_UNICODE);
        break;

    // 전체 단지 담당자 목록
    case 'building_managers_all':
        $bsql = "SELECT building_id, building_name FROM a_building WHERE is_del = 0 AND is_use = 1 ORDER BY building_name ASC";
        $bres = sql_query($bsql);

        $list = [];
        while($brow = sql_fetch_array($bres)){
            $list[] = [
                'building_id' => (int)$brow['building_id'],
                'building_name' => $brow['building_name'],
                'managers' => _get_building_managers($brow['building_id']),
            ];
        }

        echo json_encode($list, JSON_UNESCAPED_UNICODE);
        break;

    // ─── 연체요율 조회/수정 ───

    // 단지별 연체요율 조회
    case 'building_settings':
        $building_id = $_GET['building_id'] ?? '';
        if(!$building_id){
            echo json_encode(['error' => true, 'msg' => 'building_id 필수']);
            exit;
        }

        $row = sql_fetch("SELECT building_id, building_name, late_fee_rate, late_fee_base FROM a_building WHERE building_id = '{$building_id}' AND is_del = 0");

        if(!$row['building_id']){
            echo json_encode(['error' => true, 'msg' => '단지를 찾을 수 없습니다.']);
            exit;
        }

        echo json_encode([
            'building_id' => (int)$row['building_id'],
            'building_name' => $row['building_name'],
            'late_fee_rate' => (float)($row['late_fee_rate'] ?: 0),
            'late_fee_base' => $row['late_fee_base'] ?: '미납금액',
        ], JSON_UNESCAPED_UNICODE);
        break;

    // 전체 단지 연체요율 목록
    case 'building_settings_all':
        $sql = "SELECT building_id, building_name, late_fee_rate, late_fee_base FROM a_building WHERE is_del = 0 AND is_use = 1 ORDER BY building_name ASC";
        $res = sql_query($sql);

        $list = [];
        while($row = sql_fetch_array($res)){
            $list[] = [
                'building_id' => (int)$row['building_id'],
                'building_name' => $row['building_name'],
                'late_fee_rate' => (float)($row['late_fee_rate'] ?: 0),
                'late_fee_base' => $row['late_fee_base'] ?: '미납금액',
            ];
        }

        echo json_encode($list, JSON_UNESCAPED_UNICODE);
        break;

    // 단지 연체요율 수정 (담당자는 a_mng_building에서 관리)
    case 'update_building_settings':
        if($_SERVER['REQUEST_METHOD'] != 'POST'){
            echo json_encode(['error' => true, 'msg' => 'POST 요청만 허용']);
            exit;
        }

        $building_id = $_POST['building_id'] ?? '';
        if(!$building_id){
            echo json_encode(['error' => true, 'msg' => 'building_id 필수']);
            exit;
        }

        $chk = sql_fetch("SELECT building_id FROM a_building WHERE building_id = '{$building_id}' AND is_del = 0");
        if(!$chk['building_id']){
            echo json_encode(['error' => true, 'msg' => '단지를 찾을 수 없습니다.']);
            exit;
        }

        $late_fee_rate = max(0, min(100, (float)($_POST['late_fee_rate'] ?? 0)));
        $late_fee_base = $_POST['late_fee_base'] ?? '미납금액';
        if(!in_array($late_fee_base, ['미납금액', '당월금액'])) $late_fee_base = '미납금액';

        sql_query("UPDATE a_building SET
            late_fee_rate = '{$late_fee_rate}',
            late_fee_base = '{$late_fee_base}'
            WHERE building_id = '{$building_id}'");

        echo json_encode([
            'error' => false,
            'msg' => '저장되었습니다.',
            'building_id' => (int)$building_id,
        ], JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['error' => true, 'msg' => '알 수 없는 action: ' . $action]);
        break;
}


// ─── 내부 함수 ───

function _get_building_managers($building_id){
    $sql = "SELECT mng.mng_id, mng.mng_name, mng.mng_hp, mng.mng_email,
                   dept.md_name, grade.mg_name, mng.mng_certi
            FROM a_mng_building AS mb
            LEFT JOIN a_mng AS mng ON mb.mb_id = mng.mng_id
            LEFT JOIN a_mng_department AS dept ON mng.mng_department = dept.md_idx
            LEFT JOIN a_mng_grade AS grade ON mng.mng_grades = grade.mg_idx
            WHERE mb.building_id = '{$building_id}'
            AND mb.is_del = 0
            AND mng.mng_status = 1
            AND mng.is_del = 0
            ORDER BY dept.is_prior ASC, grade.is_prior DESC";
    $res = sql_query($sql);

    $managers = [];
    while($row = sql_fetch_array($res)){
        $managers[] = [
            'mng_id' => $row['mng_id'],
            'mng_name' => $row['mng_name'] ?: '',
            'mng_phone' => $row['mng_hp'] ?: '',
            'mng_email' => $row['mng_email'] ?: '',
            'department' => $row['md_name'] ?: '',
            'grade' => $row['mg_name'] ?: '',
            'certi' => $row['mng_certi'] ?: '',
        ];
    }
    return $managers;
}
