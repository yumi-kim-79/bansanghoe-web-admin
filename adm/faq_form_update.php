<?php
$sub_menu = "100300";
require_once "./_common.php";

$today = date("Y-m-d H:i:s");

if($w == "u"){

    $update_query = "UPDATE a_faq SET
                    category = '{$category}',
                    faq_title = '{$faq_title}',
                    faq_content = '{$faq_content}',
                    is_prior = '{$is_prior}'
                    WHERE faq_id = '{$faq_id}'";
    sql_query($update_query);
    
}else{

    
    $insert_query = "INSERT INTO a_faq SET
                    category = '{$category}',
                    faq_title = '{$faq_title}',
                    faq_content = '{$faq_content}',
                    is_prior = '{$is_prior}',
                    wid = '{$member['mb_id']}',
                    created_at = '{$today}'";
        
    //echo $insert_query.'<br>';
    //echo $insert_book.'<br>';
    //exit;
    sql_query($insert_query);
    $faq_id = sql_insert_id(); //팝업 idx

}

//exit;

if($w == 'u'){
    alert('FAQ가 수정되었습니다.');
}else{
    alert('FAQ가 등록되었습니다.', './faq_form.php?' . $qstr . '&amp;w=u&amp;faq_id=' . $faq_id);
}