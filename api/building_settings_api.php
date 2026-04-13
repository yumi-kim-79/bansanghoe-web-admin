<?php
/**
 * 단지 담당자/연체요율 API
 *
 * GET  ?action=building_settings&building_id=N       단지별 조회
 * GET  ?action=building_settings_all                  전체 단지 목록
 * POST ?action=update_building_settings               단지 설정 수정
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

    // 단지별 담당자/연체요율 조회
    case 'building_settings':
        $building_id = $_GET['building_id'] ?? '';
        if(!$building_id){
            echo json_encode(['error' => true, 'msg' => 'building_id 필수']);
            exit;
        }

        $row = sql_fetch("SELECT building_id, building_name, manager_name, manager_phone, manager_email, late_fee_rate, late_fee_base FROM a_building WHERE building_id = '{$building_id}' AND is_del = 0");

        if(!$row['building_id']){
            echo json_encode(['error' => true, 'msg' => '단지를 찾을 수 없습니다.']);
            exit;
        }

        echo json_encode([
            'building_id' => (int)$row['building_id'],
            'building_name' => $row['building_name'],
            'manager_name' => $row['manager_name'] ?: '',
            'manager_phone' => $row['manager_phone'] ?: '',
            'manager_email' => $row['manager_email'] ?: '',
            'late_fee_rate' => (float)($row['late_fee_rate'] ?: 0),
            'late_fee_base' => $row['late_fee_base'] ?: '미납금액',
        ], JSON_UNESCAPED_UNICODE);
        break;

    // 전체 단지 담당자/연체요율 목록
    case 'building_settings_all':
        $sql = "SELECT building_id, building_name, manager_name, manager_phone, manager_email, late_fee_rate, late_fee_base FROM a_building WHERE is_del = 0 AND is_use = 1 ORDER BY building_name ASC";
        $res = sql_query($sql);

        $list = [];
        while($row = sql_fetch_array($res)){
            $list[] = [
                'building_id' => (int)$row['building_id'],
                'building_name' => $row['building_name'],
                'manager_name' => $row['manager_name'] ?: '',
                'manager_phone' => $row['manager_phone'] ?: '',
                'manager_email' => $row['manager_email'] ?: '',
                'late_fee_rate' => (float)($row['late_fee_rate'] ?: 0),
                'late_fee_base' => $row['late_fee_base'] ?: '미납금액',
            ];
        }

        echo json_encode($list, JSON_UNESCAPED_UNICODE);
        break;

    // 단지 담당자/연체요율 수정
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

        // 단지 존재 확인
        $chk = sql_fetch("SELECT building_id FROM a_building WHERE building_id = '{$building_id}' AND is_del = 0");
        if(!$chk['building_id']){
            echo json_encode(['error' => true, 'msg' => '단지를 찾을 수 없습니다.']);
            exit;
        }

        $manager_name = $_POST['manager_name'] ?? '';
        $manager_phone = $_POST['manager_phone'] ?? '';
        $manager_email = $_POST['manager_email'] ?? '';
        $late_fee_rate = $_POST['late_fee_rate'] ?? '0';
        $late_fee_base = $_POST['late_fee_base'] ?? '미납금액';

        // 연체요율 범위 검증
        $late_fee_rate = max(0, min(100, (float)$late_fee_rate));
        // 적용기준 검증
        if(!in_array($late_fee_base, ['미납금액', '당월금액'])){
            $late_fee_base = '미납금액';
        }

        $today = date("Y-m-d H:i:s");

        sql_query("UPDATE a_building SET
            manager_name = '{$manager_name}',
            manager_phone = '{$manager_phone}',
            manager_email = '{$manager_email}',
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
