<?php
require_once "./_common.php";

$faq_sql = "SELECT faq.*, faq_cate.fc_name FROM a_faq as faq
            LEFT JOIN a_faq_category as faq_cate ON faq.category = faq_cate.fc_code
            WHERE faq.category = '{$fc_code}' and faq.is_del = 0 
            ORDER BY faq.is_prior asc, faq.faq_id desc";
$faq_res = sql_query($faq_sql);

for($i=0;$faq_row = sql_fetch_array($faq_res);$i++){
?>
<div class="faq_info_box">
    <div class="faq_info_question">
        <div class="faq_span"><?php echo $faq_row['fc_name'];?></div>
        <div class="faq_question"><?php echo $faq_row['faq_title'];?></div>
    </div>
    <div class="faq_info_answer mgt10">
    <?php echo $faq_row['faq_content']; ?>
    </div>
</div>
<?php }?>
<?php if($i==0){?>
<div class="faq_empty_box">
    등록된 FAQ가 없습니다.
</div>
<?php }?>