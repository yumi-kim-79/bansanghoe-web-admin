<?php
/**
 * SMS 수신자 전화번호 조회 API
 *
 * GET ?action=recipients&building_id=N              단지 전체 입주민
 * GET ?action=recipients&building_id=N&dong_id=M    동 필터
 * POST ?action=recipients_selected  body: ho_ids=1,2,3  개별 세대 선택
 */
require_once "../_common.php";

header('Content-Type: application/json; charset=utf-8');

try {

$action = $_REQUEST['action'] ?? '';

switch($action){

    case 'recipients':
        $building_id = $_GET['building_id'] ?? '';
        if(!$building_id){
            echo json_encode(['error' => true, 'msg' => 'building_id 필수']);
            exit;
        }

        $where = " WHERE ho.is_del = 0 AND ho.ho_status = 'Y' AND ho.building_id = '{$building_id}' ";

        $dong_id = $_GET['dong_id'] ?? '';
        if($dong_id) $where .= " AND ho.dong_id = '{$dong_id}' ";

        $sql = "SELECT ho.ho_id, ho.ho_name, ho.ho_tenant, ho.ho_tenant_hp, ho.ho_owner, ho.ho_owner_hp,
                       dong.dong_name
                FROM a_building_ho AS ho
                LEFT JOIN a_building_dong AS dong ON ho.dong_id = dong.dong_id
                {$where}
                ORDER BY dong.dong_name + 0 ASC, ho.ho_name + 0 ASC";
        $res = sql_query($sql);

        $list = [];
        while($row = sql_fetch_array($res)){
            $phone = trim($row['ho_tenant_hp']);
            if(!$phone) $phone = trim($row['ho_owner_hp']);
            $phone = preg_replace('/[\s\-]/', '', $phone);

            if(!$phone || strlen($phone) < 10) continue;

            $list[] = [
                'ho_id' => (int)$row['ho_id'],
                'dong' => $row['dong_name'],
                'ho' => $row['ho_name'],
                'name' => $row['ho_tenant'] ?: $row['ho_owner'],
                'phone' => $phone,
            ];
        }

        echo json_encode(['error' => false, 'total' => count($list), 'list' => $list], JSON_UNESCAPED_UNICODE);
        break;

    case 'recipients_selected':
        $ho_ids_str = $_POST['ho_ids'] ?? '';
        if(!$ho_ids_str){
            echo json_encode(['error' => true, 'msg' => 'ho_ids 필수']);
            exit;
        }

        $ho_ids = array_filter(array_map('intval', explode(',', $ho_ids_str)));
        if(count($ho_ids) == 0){
            echo json_encode(['error' => true, 'msg' => '유효한 세대 없음']);
            exit;
        }

        $ids_in = implode(',', $ho_ids);
        $sql = "SELECT ho.ho_id, ho.ho_name, ho.ho_tenant, ho.ho_tenant_hp, ho.ho_owner, ho.ho_owner_hp,
                       dong.dong_name
                FROM a_building_ho AS ho
                LEFT JOIN a_building_dong AS dong ON ho.dong_id = dong.dong_id
                WHERE ho.ho_id IN ({$ids_in}) AND ho.is_del = 0
                ORDER BY dong.dong_name + 0 ASC, ho.ho_name + 0 ASC";
        $res = sql_query($sql);

        $phones = [];
        while($row = sql_fetch_array($res)){
            $phone = trim($row['ho_tenant_hp']);
            if(!$phone) $phone = trim($row['ho_owner_hp']);
            $phone = preg_replace('/[\s\-]/', '', $phone);
            if($phone && strlen($phone) >= 10) $phones[] = $phone;
        }

        echo json_encode(['error' => false, 'phones' => implode(',', $phones), 'count' => count($phones)], JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['error' => true, 'msg' => '알 수 없는 action']);
        break;
}

} catch(Exception $e) {
    echo json_encode(['error' => true, 'msg' => 'Error: ' . $e->getMessage()]);
}
