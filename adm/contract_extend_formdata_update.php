<?php
require_once './_common.php';

if($ct_idx == '') alert('잘못된 접근입니다.');
//print_r2($_POST);
$today = date("Y-m-d H:i:s");

// print_r2($_POST);

//계약내역 추가
$insert_ct_history_query = "INSERT INTO a_contract_list_history SET
                            ct_idx = '{$ct_idx}',
                            ct_sdate = '{$extend_sdate}',
                            ct_edate = '{$extend_edate}',
                            ct_price = '{$extend_price}',
                            mb_id = '{$member['mb_id']}',
                            created_at = '{$today}'";
// echo $insert_ct_history_query.'<br>';
sql_query($insert_ct_history_query);
$ct_hidx = sql_insert_id(); //계약 히스토리 idx

//비용 변경 내용 히스토리  
$insert_ct_price_query = "INSERT INTO a_contract_list_price_history SET
                            ct_idx = '{$ct_idx}',
                            ct_hidx = '{$ct_hidx}',
                            ct_price = '{$extend_price}',
                            psdate = '{$extend_sdate}',
                            mb_id = '{$member['mb_id']}',
                            created_at = '{$today}'";
// echo $insert_ct_price_query.'<br>';
sql_query($insert_ct_price_query);


alert('계약 연장이 완료되었습니다.');