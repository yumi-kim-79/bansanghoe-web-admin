<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

$counts = array_count_values($bill_name); // 각 값의 개수를 계산
$hasDuplicates = max($counts) > 1; // 하나라도 2개 이상이면 중복 있음

if($hasDuplicates) alert('중복된 계산서 종류가 존재합니다.');

for($i=0;$i<count($bill_name);$i++){
    
    $sql_add = "";
    if($bt_idx[$i] != ""){
        $sql_add = " and bt_idx != '{$bt_idx[$i]}' ";
    }
   
    $industrt_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_company_bill_type WHERE bill_name = '{$bill_name[$i]}' {$sql_add}");

    $cnts = $i + 1;
    if($industrt_confirm['cnt'] > 0) alert($cnts.'번째 항목에 중복된 계산서 종류가 입력되었습니다.');

    //사용여부
  
    $is_use_val = ${'b_is_use'.$cnts};

    //echo $is_use_val.'<Br>';

    if($bt_idx[$i] != ""){
        $insert_query = "UPDATE a_company_bill_type SET
                        bill_name = '{$bill_name[$i]}',
                        is_use = '{$is_use_val}'
                        WHERE bt_idx = '{$bt_idx[$i]}'";
    }else{

        $insert_query = "INSERT INTO a_company_bill_type SET
                        bill_name = '{$bill_name[$i]}',
                        is_fixed = 0,
                        is_use = '{$is_use_val}',
                        created_at = '{$today}'";
    }
    
    //echo $insert_query.'<br>';

    sql_query($insert_query);
}

//exit;
alert('계산서 종류 추가 및 수정이 완료되었습니다.');