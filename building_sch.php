<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$post_sql = "SELECT * FROM a_post_addr WHERE is_del = 0 ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);
?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<div id="wrappers" class="bgs">
    <div class="wrap_container">
        <div class="inner">
            <div class="building_sch_wrapper">
                <div class="inspection_sch_box">
                    <div class="inspection_form_box">
                        <div class="inspection_sch_label">지역</div>
                        <select name="post_idx" id="post_idx" class="bansang_sel">
                            <option value="">지역전체</option>
                            <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                                <option value="<?php echo $post_row['post_idx'];?>"><?php echo $post_row['post_name']; ?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="inspection_form_box">
                        <div class="inspection_sch_label">단지</div>
                        <input type= "text" name="sch_name" id="sch_name" class="bansang_ipt ver2" placeholder="단지명을 입력하세요.">
                    </div>
                    <div class="inspection_form_box">
                        <div class="inspection_sch_label">운영여부</div>
                        <div class="inspection_radio_wrap">
                            <div class="inspection_radio_boxs">
                                <input type="radio" name="is_use" id="is_use1" value="1" checked>
                                <label for="is_use1">운영</label>
                            </div>
                            <div class="inspection_radio_boxs">
                                <input type="radio" name="is_use" id="is_use2" value="0">
                                <label for="is_use2">해지</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="inspection_form_button mgt10">
                    <button type="button" onclick="building_list_ajax();">검색</button>
                </div>
            </div>
            <div class="mng_building_wrap">
                <div class="mng_building_top_wrap">
                    <div class="mng_buinding_chk_wrap">
                        <input type="checkbox" name="mng_building_chk" id="mng_building_chk">
                        <label for="mng_building_chk">담당 단지만 보기</label>
                    </div>
                    <div class="mng_building_sort_wrap">
                        <div class="build_sort_box">
                            <input type="radio" name="sorts" id="sort_up" class="sort_up" value="up" checked>
                            <label for="sort_up">가나다순 <i></i></label>
                        </div>
                        <div class="build_sort_box">
                            <input type="radio" name="sorts" id="sort_down" class="sort_down" value="down">
                            <label for="sort_down">가나다순 <i></i></label>
                        </div>
                    </div>
                </div>
                <ul class="mng_building_list mgt20">
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
building_list_ajax();

function building_list_ajax(mng_chk_val = '', is_use = '1', sortVal = 'up'){

    console.log('mng_chk_val',mng_chk_val);

    let mng_chk = mng_chk_val ? "1" : "0";
    let mb_id = "<?php echo $member['mb_id']; ?>";
    let post_idx = $("#post_idx option:selected").val();
    let schText = $("#sch_name").val();
    sortVal = $('input[name=sorts]:checked').val();
    is_use = $('input[name=is_use]:checked').val();

    $.ajax({

    url : "/building_sch_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: {'post_idx':post_idx, 'schText':schText, 'mng_chk':mng_chk, 'mb_id':mb_id, 'sortVal':sortVal, 'is_use':is_use}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);

        $(".mng_building_list").html(msg);
    }

    });
}

$("#mng_building_chk").click(function(){
    console.log($("#mng_building_chk").is(":checked"));

    let mng_chk = $("#mng_building_chk").is(":checked");

    building_list_ajax(mng_chk);
})

$("input[name='sorts']").change(function(){
    let sortVal = $('input[name=sorts]:checked').val();
    let mng_chk = $("#mng_building_chk").is(":checked");

    building_list_ajax(mng_chk, sortVal);
});

</script>
<?php
include_once(G5_PATH.'/tail.php');
?>