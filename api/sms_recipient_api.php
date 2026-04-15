<?php
/**
 * SMS 수신자 전화번호 조회 API
 *
 * GET ?action=recipients&building_id=N              단지 전체
 * GET ?action=recipients&building_id=N&dong_id=M    동 필터
 */
require_once "../_common.php";

header('Content-Type: application/json; charset=utf-8');

try {

$action = $_REQUEST['action'] ?? '';

switch($action){

    case 'recipients':
        $building_id = $_GET['building_id'] ?? '';
        if(!$building_id){
            echo json_encode(['success' => false, 'msg' => 'building_id 필수']);
            exit;
        }

        $where = " WHERE is_del = 0 AND ho_status = 'Y' AND building_id = '{$building_id}' ";

        $dong_id = $_GET['dong_id'] ?? '';
        if($dong_id && $dong_id != '-1') $where .= " AND dong_id = '{$dong_id}' ";

        $sql = "SELECT ho_id, dong_id, ho_name, ho_tenant, ho_tenant_hp, ho_owner, ho_owner_hp
                FROM a_building_ho
                {$where}
                ORDER BY dong_id ASC, ho_name + 0 ASC";
        $res = sql_query($sql);

        $phone_list = [];
        $detail_list = [];

        while($row = sql_fetch_array($res)){
            $phone = trim($row['ho_tenant_hp']);
            if(!$phone) $phone = trim($row['ho_owner_hp']);
            $phone = preg_replace('/[\s\-]/', '', $phone);

            if(!$phone || strlen($phone) < 10) continue;

            $phone_list[] = $phone;
            $detail_list[] = [
                'ho_id' => (int)$row['ho_id'],
                'dong_id' => (int)$row['dong_id'],
                'ho_name' => $row['ho_name'],
                'name' => $row['ho_tenant'] ?: $row['ho_owner'],
                'phone' => $phone,
            ];
        }

        echo json_encode([
            'success' => true,
            'count' => count($detail_list),
            'phone_list' => array_values(array_unique($phone_list)),
            'detail_list' => $detail_list,
        ], JSON_UNESCAPED_UNICODE);
        break;

    default:
        echo json_encode(['success' => false, 'msg' => '알 수 없는 action: ' . $action]);
        break;
}

} catch(Exception $e) {
    echo json_encode(['success' => false, 'msg' => 'Error: ' . $e->getMessage()]);
}
