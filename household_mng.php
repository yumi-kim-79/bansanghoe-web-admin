<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$building_sql = "SELECT building.*, post.post_name FROM a_building as building
                 LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                 WHERE building.building_id = '{$building_id}'";
$building_row = sql_fetch($building_sql);

//echo $building_sql;

$dong_sql = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' and is_del = 0 ORDER BY dong_id asc";
$dong_res = sql_query($dong_sql);
//echo $dong_sql;
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <p class="house_hold_tit"><?php echo $building_row['building_name']; ?></p>
            </div>
        </div>
        <div class="inner">
            <div class="house_hold_wrap">
                <div class="sch_box_wrap mgb20 sm_section sm_section1 sm_section3">
                    <div class="sch_box_select">
                        <select name="dong_id" id="dong_id" class="bansang_sel">
                            <option value="">동 전체</option>
                            <?php for($i=0;$dong_row = sql_fetch_array($dong_res);$i++){?>
                                <option value="<?php echo $dong_row['dong_id']; ?>"><?php echo $dong_row['dong_name']; ?>동</option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="ipt_box ipt_flex ipt_box_ver2 mgt10">
                        <input type="text" name="sch_text" id="sch_text" class="bansang_ipt ver4" placeholder="호수를 입력하세요.">
                        <button type="button" onclick="ho_list_ajax();" class="sch_button">
                            <img src="/images/sch_icons.svg" alt="">
                        </button>
                    </div>
                </div>
                <ul class="house_hold_list">
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
ho_list_ajax();

function ho_list_ajax(){

    let building_id = "<?php echo $building_id; ?>";
    let dong_id = $("#dong_id option:selected").val();
    let schText = $("#sch_text").val();

    $.ajax({

    url : "/household_mng_list_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: {'building_id':building_id, 'schText':schText, 'dong_id':dong_id}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);

        $(".house_hold_list").html(msg);
    }

    });
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>