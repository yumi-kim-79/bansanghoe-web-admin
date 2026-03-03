<?php

include_once("_common.php");

if($type == "user"){

    $token_up = "UPDATE a_member SET
                    mb_token = '{$token}'
                    WHERE mb_id = '{$id}'";
    //echo $token_up;
    sql_query($token_up);
}else{

    $token_up = "UPDATE g5_member SET
                    mb_token = '{$token}'
                    WHERE mb_id = '{$id}'";
    sql_query($token_up);
}
?>