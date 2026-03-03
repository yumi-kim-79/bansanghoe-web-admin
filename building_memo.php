<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$building_sql = "SELECT building.*, post.post_name FROM a_building as building
                 LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                 WHERE building.building_id = '{$building_id}'";
$building_row = sql_fetch($building_sql);

$my_building_sql = "SELECT * FROM a_mng_building WHERE mb_id = '{$member['mb_id']}'";
$my_building_res = sql_query($my_building_sql);

$my_building_arr = array();

while($my_building_row = sql_fetch_array($my_building_res)){
    array_push($my_building_arr, $my_building_row['building_id']);
}

//print_r2($my_building_arr);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <p class="mng_title"><?php echo $building_row['building_name']; ?></p>
            </div>
        </div>
        <div class="build_memo_cont">
            <div class="inner">
                <div class="comple_answers mgt20">
                    <p class="regi_list_title">메모</p>
                    <textarea name="build_memo" id="build_memo" class="bansang_ipt ver2 ta ta2"><?php echo $building_row['building_memo']; ?></textarea>
                </div>
            </div>
        </div>
       
        <div class="fix_btn_back_box"></div>
        <div class="fix_btn_box ver3">
            <button type="button" onclick="memoUpdate();" class="fix_btn on" id="fix_btn" >저장</button>
        </div>
        <!-- <?php if(in_array($building_id, $my_building_arr)){?>
        <?php }?> -->
    </div>
</div>
<script>

function memoUpdate(){

    let mb_id = "<?php echo $member['mb_id']; ?>";
    let building_id = "<?php echo $building_id; ?>";
    let build_memo = $("#build_memo").val();

    let sendData = {'mb_id': mb_id, 'building_id':building_id, 'build_memo':build_memo};

    $("#fix_btn").attr("disabled", true);

    $.ajax({
        type: "POST",
        url: "/building_memo_update.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                showToast(data.msg);
                //$(".btn_submit").attr('disabled', false);
                $("#fix_btn").attr("disabled", false);
                return false;
            }else{
                showToast(data.msg);
               
                setTimeout(() => {
                    window.location.reload();
                }, 700);
            }
        },
    });

}

</script>
<?php
include_once(G5_PATH.'/tail.php');
?>