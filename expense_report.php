<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

$expense_sql = "SELECT * FROM a_expense_report WHERE is_del = 0 and building_id = '{$user_building['building_id']}' and dong_id IN ('{$user_building['dong_id']}', '-1') and ex_status = 'E' and created_at >= '{$ho_tenant_at_de}' ORDER BY ex_id desc";
// echo $expense_sql;
$expense_res = sql_query($expense_sql);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    // echo $expense_sql.'<br>';
}
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="content_box_wrap">
                <?php for($i=0;$expense_row = sql_fetch_array($expense_res);$i++){?>
                <a href="/expense_report_info.php?ex_id=<?php echo $expense_row['ex_id'];?>" class="content_box">
                    <div class="content_box_icons">
                        <img src="/images/report_icons.svg" alt="">
                    </div>
                    <div class="content_box_ct">
                        <div class="content_box_ct1">
                            <?php echo date("Y.m.d", strtotime($expense_row['created_at']));?>
                        </div>
                        <div class="content_box_ct2">
                            <?php echo $expense_row['ex_title']; ?>
                        </div>
                    </div>
                </a>
                <?php }?>
                <?php if($i==0){?>
                    <div class="complain_empty">등록된 품의서가 없습니다.</div>
                <?php }?>
            </div>
        </div>
    </div>
</div>
<?php
include_once(G5_PATH.'/tail.php');
?>