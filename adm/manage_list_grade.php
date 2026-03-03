<?php
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

$arr = array();

for($i=0;$i<count($grade);$i++){

    if($gr_id[$i] == ""){
        $confirm_grade = sql_fetch("SELECT COUNT(*) as cnt FROM a_mng_team_grade WHERE gr_name = '{$grade[$i]}' and is_del = 0");


        if($confirm_grade['cnt'] > 0) die(result_data(false, ($i+1).'번째 직책명이 중복됩니다.', []));
    }else{
        $confirm_grade = sql_fetch("SELECT COUNT(*) as cnt FROM a_mng_team_grade WHERE gr_name = '{$grade[$i]}' and is_del = 0 and gr_id != '{$gr_id[$i]}'");


        if($confirm_grade['cnt'] > 0) die(result_data(false, ($i+1).'번째 직책명이 중복됩니다.', []));
    }
}

for($i=0;$i<count($grade);$i++){

    if($gr_id[$i] != ""){

        $del_sql = "";

        if($mng_grade_del[$i]){

            $del_sql = " ,
                        is_del = 1,
                        deleted_at = '{$today}' ";
        }

        $update_query = "UPDATE a_mng_team_grade SET
                            gr_name = '{$grade[$i]}'
                            {$del_sql}
                            WHERE gr_id = '{$gr_id[$i]}'";
        sql_query($update_query);
        //array_push($arr, $update_query);
        
    }else{
        if($grade[$i] != ""){
            $insert_query = "INSERT INTO a_mng_team_grade SET
                        gr_name = '{$grade[$i]}',
                        created_at = '{$today}'";

            //array_push($arr, $insert_query);
            sql_query($insert_query);
        }
    }

    
}

echo result_data(true, "관리단 직책이 추가 및 변경되었습니다.", []);

//die(result_data(false, $arr, []));
?>