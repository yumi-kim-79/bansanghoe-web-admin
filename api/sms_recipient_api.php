<?php
/**
 * SMS 수신자 전화번호 조회 API
 * GET ?action=recipients&building_id=N&dong_id=M(선택)
 */
header('Content-Type: application/json; charset=utf-8');

try {

    // 환경별 DB 자동 선택
    $host = 'localhost';
    $user = 'sm_user1';
    $pass = 'sm2025@@';

    if(strpos($_SERVER['HTTP_HOST'] ?? '', 'test.') !== false){
        $dbname = 'bansanghoe';
    } else {
        $dbname = 'sinbansang';
    }

    $conn = new mysqli($host, $user, $pass, $dbname);
    $conn->set_charset('utf8');

    if($conn->connect_error){
        echo json_encode(['success' => false, 'msg' => 'DB 연결 실패'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $action = $_GET['action'] ?? '';

    if($action != 'recipients'){
        echo json_encode(['success' => false, 'msg' => '알 수 없는 action'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $building_id = $conn->real_escape_string($_GET['building_id'] ?? '');
    if(!$building_id){
        echo json_encode(['success' => false, 'msg' => 'building_id 필수'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // 단지명 조회
    $brow = $conn->query("SELECT building_name FROM a_building WHERE building_id = '{$building_id}'")->fetch_assoc();
    $building_name = $brow['building_name'] ?? '';

    $where = "is_del = 0 AND building_id = '{$building_id}'";

    $dong_id = $conn->real_escape_string($_GET['dong_id'] ?? '');
    if($dong_id && $dong_id != '-1'){
        $where .= " AND dong_id = '{$dong_id}'";
    }

    $sql = "SELECT ho_id, dong_id, ho_name, ho_tenant, ho_tenant_hp, ho_owner, ho_owner_hp
            FROM a_building_ho
            WHERE {$where}
            ORDER BY dong_id ASC, ho_name + 0 ASC";
    $res = $conn->query($sql);

    $phone_list = [];
    $detail_list = [];

    if($res){
        while($row = $res->fetch_assoc()){
            $phone = trim($row['ho_tenant_hp'] ?? '');
            if(!$phone) $phone = trim($row['ho_owner_hp'] ?? '');
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
    }

    $conn->close();

    echo json_encode([
        'success' => true,
        'db' => $dbname,
        'building_name' => $building_name,
        'count' => count($detail_list),
        'phone_list' => array_values(array_unique($phone_list)),
        'detail_list' => $detail_list,
    ], JSON_UNESCAPED_UNICODE);

} catch(Exception $e) {
    echo json_encode(['success' => false, 'msg' => 'Error: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
