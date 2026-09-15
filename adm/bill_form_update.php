<?php
require_once "./_common.php";

ini_set('memory_limit','-1');
ini_set('max_execution_time', 0);
set_time_limit(0);

$today = date("Y-m-d H:i:s");
$ip_info = $_SERVER['REMOTE_ADDR'];

// print_r2($_POST);
// exit;




// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){

//     echo $excel_type.'<br>';
//     //단지 고지서 저장
//     $insert_bill = "INSERT INTO a_bill SET
//                             post_id = '{$post_id}',
//                             building_id = '{$building_id}',
//                             bill_year = '{$bill_year}',
//                             bill_month = '{$bill_month}',
//                             bill_due_date = '{$bill_due_date}',
//                             vt_add = '{$vt_add}',
//                             wid = '{$member['mb_id']}',
//                             created_at = '{$today}'";
//     echo $insert_bill.'<br>';
//     exit;
// }

if($w == "u"){

    $bill_check = sql_fetch("SELECT * FROM a_bill WHERE bill_id = '{$bill_id}'");
    
    // ★ [고지서 2026-09] 단지 변경이 저장되지 않던 문제
    //
    //  기존 코드는 SET 절에 'and' 를 붙이고 있었다.
    //      $sql_add = " and building_id = '{$building_id}', ";
    //      → "UPDATE a_bill SET and building_id = '..', bill_year = '..' ..."  ← 문법 오류
    //  그래서 단지를 바꾸면 쿼리 전체가 실패해 년/월·납부기한까지 아무것도 저장되지 않았다.
    //  단지를 그대로 두면 $sql_add 가 비어 있어 정상 동작했기 때문에 눈에 띄지 않았다.
    $sql_add = "";
    $building_changed = false;

    if($building_id != '' && $bill_check['building_id'] != $building_id){

        // 예약발행(R) / 발행(Y) 상태에서는 단지를 바꿀 수 없다
        if($bill_check['is_submit'] == 'R' || $bill_check['is_submit'] == 'Y'){
            alert('발행 또는 예약발행된 고지서는 단지를 변경할 수 없습니다.\\n발행 취소 후 다시 시도해 주세요.');
        }

        // 지역(post_id)은 화면에서 온 값이 아니라 단지 정보에서 다시 읽는다
        $b_info = sql_fetch("SELECT building_id, post_id FROM a_building WHERE building_id = '{$building_id}'");
        if(!$b_info || !isset($b_info['building_id'])){
            alert('선택한 단지 정보를 찾을 수 없습니다.');
        }

        // 바꾸려는 단지에 같은 년/월 고지서가 이미 있으면 막는다
        $dup = sql_fetch("SELECT COUNT(*) as cnt FROM a_bill
                          WHERE building_id = '{$building_id}'
                            and bill_year = '{$bill_year}' and bill_month = '{$bill_month}'
                            and is_del = 0 and bill_id != '{$bill_id}'");
        if($dup['cnt'] > 0){
            alert('선택한 단지에 해당 년/월 고지서가 이미 있습니다.');
        }

        $sql_add = " building_id = '{$building_id}', post_id = '{$b_info['post_id']}', ";
        $building_changed = true;
    }

    // 고지서 수정
    $update_bill = "UPDATE a_bill SET
                    {$sql_add}
                    bill_year = '{$bill_year}',
                    bill_month = '{$bill_month}',
                    bill_due_date = '{$bill_due_date}',
                    vt_add = '{$vt_add}',
                    updated_at = '{$today}'
                    WHERE bill_id = '{$bill_id}'";
    if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
        // print_r2($_POST);
        // echo $update_bill.'<br>';
        // exit;
    }
    sql_query($update_bill);

    // 단지가 바뀌었는데 이번 저장에 새 명세서가 함께 오지 않았다면,
    //  이전 단지 기준으로 만들어진 명세서 내역(동/호)은 새 단지와 맞지 않으므로 지운다.
    //  (새 명세서가 함께 온 경우엔 아래 groupedCnt 분기에서 지우고 다시 넣는다)
    $has_new_items = (isset($groupedCnt) && $groupedCnt > 0);
    if($building_changed && !$has_new_items){
        sql_query("DELETE FROM a_bill_item WHERE bill_id = '{$bill_id}'");
    }

    if($groupedCnt > 0){
        //이전내역 삭제
        $delete_bill_item = "DELETE FROM a_bill_item WHERE bill_id = '{$bill_id}'";
        sql_query($delete_bill_item);

        //단지별 동수로 반복
        for($i=0;$i<$groupedCnt;$i++){

            $tagName = ${'row_data'.$i}; //동별 데이터

            $firstArr = array(); //첫번째 값 담을 배열
            $modArr = array(); //나머지 값 담을 배열
            foreach($tagName as $key => $row){
                $values = explode('|', $row);

                // 첫 번째 값 추출
                $firstValue = array_shift($values); 
                array_push($firstArr, str_replace(" ", "", $firstValue));

                // 첫 번째 값만 삭제된 나머지 배열
                $remainingValues = $values; 
                array_push($modArr, $remainingValues);
            }

            //덮어쓰기
            foreach($firstArr as $key => $row){

                $options = implode("|", $modArr[$key]);
                
                $insert_bill_item = "INSERT INTO a_bill_item SET
                                    bill_id = '{$bill_id}',
                                    dong_name = '{$groupKey[$i]}',
                                    bi_name = '{$row}',
                                    bi_option = '{$options}',
                                    created_at = '{$today}'";
                // echo $insert_bill_item.'<br>';
                sql_query($insert_bill_item);
            
            }
        }
    }
    

}else{
    $vt_add = $excel_type == 'yes' ? 1 : 0;

    //단지 고지서 저장
    $insert_bill = "INSERT INTO a_bill SET
                            post_id = '{$post_id}',
                            building_id = '{$building_id}',
                            bill_year = '{$bill_year}',
                            bill_month = '{$bill_month}',
                            bill_due_date = '{$bill_due_date}',
                            vt_add = '{$vt_add}',
                            wid = '{$member['mb_id']}',
                            created_at = '{$today}'";
    // echo $insert_bill.'<br>';
    sql_query($insert_bill);
    $bill_id = sql_insert_id(); //호수 idx
    // echo '<br>-----------------<br>';

    //단지별 동수로 반복
    for($i=0;$i<$groupedCnt;$i++){

        $tagName = ${'row_data'.$i}; //동별 데이터

        $firstArr = array(); //첫번째 값 담을 배열
        $modArr = array(); //나머지 값 담을 배열
        foreach($tagName as $key => $row){
            $values = explode('|', $row);

            // 첫 번째 값 추출
            $firstValue = array_shift($values); 
            array_push($firstArr, str_replace(" ", "", $firstValue));

            // 첫 번째 값만 삭제된 나머지 배열
            $remainingValues = $values; 
            array_push($modArr, $remainingValues);
        }


        foreach($firstArr as $key => $row){

            $options = implode("|", $modArr[$key]);
            
            $insert_bill_item = "INSERT INTO a_bill_item SET
                                bill_id = '{$bill_id}',
                                dong_name = '{$groupKey[$i]}',
                                bi_name = '{$row}',
                                bi_option = '{$options}',
                                created_at = '{$today}'";
            // echo $insert_bill_item.'<br>';
            sql_query($insert_bill_item);
        }

        // echo '<br>-----------------<br>';
    }


    //엑셀 파일 저장
    if($file_name != ''){

        $confirm_bill_file = sql_fetch("SELECT COUNT(*) as cnt FROM a_bill_file WHERE bill_id = '{$bill_id}'");

        if($confirm_bill_file['cnt'] > 0){
            $insert_bill_file = "UPDATE a_bill_file SET
                                file_name = '{$file_name}',
                                created_at = '{$today}'
                                WHERE bill_id = '{$bill_id}'";
            // echo $insert_bill_file.'<br>';
            sql_query($insert_bill_file);

        }else{
            $insert_bill_file = "INSERT INTO a_bill_file SET
                                bill_id = '{$bill_id}',
                                file_name = '{$file_name}',
                                created_at = '{$today}'";
            // echo $insert_bill_file.'<br>';
            sql_query($insert_bill_file);
        }
        
    }

}
//print_r2($_POST);

// exit;

if($w == 'u'){
    alert('고지서가 수정되었습니다.');
}else{
    alert('고지서가 저장되었습니다.', './bill_form.php?'. $qstr . '&amp;w=u&amp;bill_id=' . $bill_id);
}