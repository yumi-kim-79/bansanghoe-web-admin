<?php
require_once "../_common.php";

header('Content-Type: application/json');

$today = date("Y-m-d H:i:s");

$action = $_POST['action'] ?? '';
$ho_id = $_POST['ho_id'] ?? '';

if(!$ho_id){
    echo json_encode(['result' => false, 'msg' => '잘못된 요청입니다.']);
    exit;
}

switch($action){

    case 'save_billing_key':
        $billing_key = $_POST['billing_key'] ?? '';
        $card_company = $_POST['card_company'] ?? '';
        $card_number = $_POST['card_number'] ?? '';

        if(!$billing_key){
            echo json_encode(['result' => false, 'msg' => '빌링키가 없습니다.']);
            exit;
        }

        // 기존 카드 soft delete
        sql_query("UPDATE a_billing_card SET is_del = 1, deleted_at = '{$today}' WHERE ho_id = '{$ho_id}' AND is_del = 0");

        // 새 카드 등록
        sql_query("INSERT INTO a_billing_card SET
            ho_id = '{$ho_id}',
            billing_key = '{$billing_key}',
            card_company = '{$card_company}',
            card_number = '{$card_number}',
            created_at = '{$today}'");

        echo json_encode(['result' => true, 'msg' => '카드가 등록되었습니다.']);
        break;

    case 'delete_card':
        sql_query("UPDATE a_billing_card SET is_del = 1, deleted_at = '{$today}' WHERE ho_id = '{$ho_id}' AND is_del = 0");

        echo json_encode(['result' => true, 'msg' => '카드가 삭제되었습니다.']);
        break;

    default:
        echo json_encode(['result' => false, 'msg' => '알 수 없는 요청입니다.']);
        break;
}
