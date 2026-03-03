<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

//지역
$post_sql = "SELECT * FROM a_post_addr WHERE is_del = 0 ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

$mng_building = get_mng_building($member['mb_id']);

$mng_building_t = "'".implode("','", $mng_building)."'";


$buidling_sql = "SELECT building.*, post.post_name FROM a_building as building
                LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                WHERE building.is_del = 0 and building.is_use = 1 and building.building_id IN ({$mng_building_t})
                ORDER BY building.building_name asc, building.building_id DESC";
$building_res = sql_query($buidling_sql);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $buidling_sql.'<br>';
}
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="sch_box_wrap mgt10">
                <div class="ipt_flex">
                    <select name="post_id" id="post_id" class="bansang_sel sch_cate">
                        <option value="">지역 전체</option>
                        <?php while($post_row = sql_fetch_array($post_res)){?>
                        <option value="<?php echo $post_row['post_idx']; ?>"><?php echo $post_row['post_name']; ?></option>
                        <?php }?>
                    </select>
                    <div class="sch_ipt_box ipt_box ipt_flex ipt_box_ver2">
                        <input type="text" name="building_name" id="building_name" class="bansang_ipt ver4" placeholder="단지명을 입력하세요.">
                        <button type="button" onclick="meter_reading_list();" class="sch_button">
                            <img src="/images/sch_icons.svg" alt="">
                        </button>
                    </div>
                </div>
            </div>
            <div class="bbs_vote_notice ver2 mgt15">
                <div class="bbs_vote_notice_inner ver2">
                데이터 통신이 불안정한 경우, 검침 데이터가 저장되지 않을 수 있으니
                통신이 원활한 구역에서 저장하세요.
                </div>
            </div>
            <ul class="meter_reading_list">
                <?php foreach($building_res as $row){ ?>
                <li>
                    <a href="/meter_reading_info.php?building_id=<?php echo $row['building_id']; ?>">
                        <p class="meter_reading_area"><?php echo $row['post_name']; ?></p>
                        <p class="meter_reading_building"><?php echo $row['building_name']; ?></p>
                    </a>
                </li>
                <?php }?>
            </ul>
        </div>
        
    </div>
</div>
<script>

meter_reading_list();

function meter_reading_list(){

    let mb_id = "<?php echo $member['mb_id']; ?>";
    let post_id = $("#post_id option:selected").val();
    let building_name = $("#building_name").val();

    console.log(post_id, building_name);

    $.ajax({

    url : "/meter_reading_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "mb_id":mb_id, "post_id":post_id, "building_name":building_name}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".meter_reading_list").html(msg);
    }

    });
}

</script>
<?php
include_once(G5_PATH.'/tail.php');
?>