<?php
include_once('./_common.php');
include_once(G5_PATH.'/head.php');

// print_r2($user_building['building_name']);
$ct_industry = "SELECT ct.* FROM a_contract as ct
                LEFT JOIN a_industry_list as industry ON ct.industry_idx = industry.industry_idx
                WHERE ct.building_id = '{$user_building['building_id']}' and ct.ct_status = 0 and ct.is_temp = 0 ORDER BY industry_idx desc";
// echo $ct_industry;
$ct_history_res = sql_query($ct_industry);


$industry_sql = "SELECT * FROM a_industry_list WHERE is_del = 0 and is_use = 1 ORDER BY is_fixed desc, industry_idx asc";
$industry_res = sql_query($industry_sql);
?>
<!-- <?php while($industry_row = sql_fetch_array($industry_res)){
                        ?>
                        <option value="<?php echo $industry_row['industry_idx']; ?>"><?php echo $industry_row['industry_name']; ?></option>
                    <?php }?> -->
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="sch_select">
                <select name="industry_idx" id="industry_idx" class="bansang_sel ver2" onchange="inspection_list_handler();">
                    <option value="">전체</option>
                    <?php while($industry_row = sql_fetch_array($ct_history_res)){
                        ?>
                        <option value="<?php echo $industry_row['industry_idx']; ?>"><?php echo $industry_row['industry_name']; ?></option>
                    <?php }?>
                </select>
            </div>
            <div class="content_box_wrap">
            </div>
        </div>
    </div>
</div>
<script>

inspection_list_handler();
function inspection_list_handler(){

    let building_id = "<?php echo $user_building['building_id'];?>"; //단지
    let industry_idx = $("#industry_idx option:selected").val();

    let ho_tenant_at_de = "<?php echo $user_building['ho_tenant_at']; ?>";

    $.ajax({

    url : "/inspection_lists_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "building_id":building_id, "industry_idx":industry_idx, "ho_tenant_at_de":ho_tenant_at_de}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".content_box_wrap").html(msg);
    }

    });
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>