<?php
require_once "./_common.php";

$sql_where = "";
if($industry_idx != ""){
    $sql_where = " and insp.inspection_category = '{$industry_idx}' ";
}

$inspection_sql = "SELECT insp.*, indus.industry_name, indus.indutry_icon FROM a_inspection as insp
                   LEFT JOIN a_industry_list as indus on insp.inspection_category = indus.industry_idx
                   WHERE insp.is_del = 0 and insp.inspection_status = 'Y' and building_id = '{$building_id}' and insp.created_at >= '{$ho_tenant_at_de}' {$sql_where} ORDER BY insp.inspection_idx desc";

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    echo $inspection_sql;
}
$inspection_res = sql_query($inspection_sql);

for($i=0;$inspection_row = sql_fetch_array($inspection_res);$i++){

    $indutry_icon_img = $inspection_row['indutry_icon'] != '' ? $inspection_row['indutry_icon'] : 'more_icon_sm.svg';

    $approval_at = $inspection_row['approval_at'] != '' ? date("Y.m.d", strtotime($inspection_row['approval_at'])) : date("Y.m.d", strtotime($inspection_row['created_at']));
?>
<a href="/inspection_info.php?inspection_idx=<?php echo $inspection_row['inspection_idx']; ?>" class="content_box">
    <div class="content_box_icons content_box_icons2">
        <img src="/images/<?php echo $indutry_icon_img; ?>" alt="">
    </div>
    <div class="content_box_ct">
        <div class="content_box_ct1">
            <span><?php echo $inspection_row['industry_name']; ?></span> <?php echo $approval_at; ?>
        </div>
        <div class="content_box_ct2">
            <?php echo $inspection_row['inspection_title']; ?>
        </div>
    </div>
</a>
<?php }?>
<?php if($i==0){?>
<div class="complain_empty">등록된 점검일지가 없습니다.</div>
<?php }?>