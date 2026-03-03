<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

$counts = array_count_values($pt_name); // 각 값의 개수를 계산
$hasDuplicates = max($counts) > 1; // 하나라도 2개 이상이면 중복 있음

if($hasDuplicates) alert('중복된 지급방식이 존재합니다.');

for($i=0;$i<count($pt_name);$i++){
    
    $sql_add = "";
    if($pt_idx[$i] != ""){
        $sql_add = " and pt_idx != '{$pt_idx[$i]}' ";
    }
   
    $industrt_confirm = sql_fetch("SELECT COUNT(*) as cnt FROM a_payment_type WHERE pt_name = '{$pt_name[$i]}' {$sql_add}");

       
    if($industrt_confirm['cnt'] > 0) alert($cnts.'번째 항목에 중복된 지급방식이 입력되었습니다.');

    //사용여부
    $cnts = $i + 1;
    $is_use_val = ${'is_use'.$cnts};

    //echo $is_use_val.'<Br>';

    if($pt_idx[$i] != ""){
        $insert_query = "UPDATE a_payment_type SET
                        pt_name = '{$pt_name[$i]}',
                        is_use = '{$is_use_val}'
                        WHERE pt_idx = '{$pt_idx[$i]}'";
    }else{

        $insert_query = "INSERT INTO a_payment_type SET
                        pt_name = '{$pt_name[$i]}',
                        is_fixed = 0,
                        is_use = '{$is_use_val}',
                        created_at = '{$today}'";
    }
    
    //echo $insert_query.'<br>';

    sql_query($insert_query);
}

alert('지급방식 추가 및 수정이 완료되었습니다.');