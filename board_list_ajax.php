<?php
require_once "./_common.php";

$code_sql = "";
if($code != 'all'){
    $code_sql = " and bbs_code = '{$code}'";
}

$bbs_sql = "SELECT * FROM a_bbs WHERE is_view = 1 and is_del = 0 {$code_sql} ORDER BY bbs_idx desc";
// echo $bbs_sql;
$bbs_res = sql_query($bbs_sql);

for($i=0;$bbs_row = sql_fetch_array($bbs_res);$i++){

    $cate = sql_fetch("SELECT * FROM a_bbs_setting WHERE bbs_code = '{$bbs_row['bbs_code']}'");
?>
<a href="/board_info.php?bbs_idx=<?php echo $bbs_row['bbs_idx']; ?>&tabCode=<?php echo $code;?>&tabIdx=<?php echo $index;?>" class="content_box ver3">
    <div class="content_box_ct ver2">
        <div class="content_box_ct1">
            <?php echo date("Y.m.d", strtotime($bbs_row['created_at']));?> 
            <div class="content_box_ct_wrtier">
                <?php echo $cate['bbs_title']; ?>
            </div>
            <div class="content_box_ct_wrtier">
                <!-- 현장팀/팀장/홍길동 -->
                <?php if($bbs_row['wid'] == "admin"){?>
                    <?php echo "신반상회"; ?>
                <?php }else{ 
                      $bbs_mng = get_manger($bbs_row['wid']);
                    ?>
                    <?php echo $bbs_mng['md_name'].'/'.$bbs_mng['mg_name'].'/'.$bbs_mng['mng_name'];?>
                <?php }?>
            </div> 
        </div>
        <div class="content_box_ct2">
            <?php echo $bbs_row['bbs_title'];?>
        </div>
    </div>
</a>
<?php }?>
<?php if($i == 0){?>
<div class="faq_empty_box">
    등록된 게시글이 없습니다.
</div>
<?php }?>