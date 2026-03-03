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
    
    $sql_add = "";
    if($bill_check['building_id'] != $building_id){
        $sql_add = " and building_id = '{$building_id}', ";
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