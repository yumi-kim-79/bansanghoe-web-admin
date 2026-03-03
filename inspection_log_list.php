<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

//단지
$building_sql = "SELECT * FROM a_building WHERE is_del = 0 and is_use = 1 ORDER BY building_name asc, building_id desc";
$building_res = sql_query($building_sql);

//관리단지
$mng_building_list = "SELECT mgb.*, building.building_name, building.is_use FROM a_mng_building as mgb
                        LEFT JOIN a_building as building on mgb.building_id = building.building_id
                        WHERE mgb.is_del = 0 and mgb.mb_id = '{$member['mb_id']}' and building.is_use = 1 ORDER BY building.building_name asc, mgb.building_id asc";
// echo $mng_building_list.'<br>';
$mng_building_res = sql_query($mng_building_list);

//업종
$industry_sql = "SELECT * FROM a_industry_list WHERE is_del = 0 and is_use = 1 ORDER BY is_fixed desc, industry_idx asc";
$industry_res = sql_query($industry_sql);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <div class="inspection_sch_box">
                    <div class="inspection_form_box">
                        <div class="inspection_sch_label">단지</div>
                        <!-- <select name="building_id" id="building_id" class="bansang_sel">
                            <option value="">단지를 선택하세요.</option>
                            <?php while($building_row = sql_fetch_array($mng_building_res)){?>
                                <option value="<?php echo $building_row['building_id']; ?>"><?php echo $building_row['building_name']; ?></option>
                            <?php }?>
                        </select> -->
                        <input type= "text" name="building_name" id="building_name" class="bansang_ipt ver2" placeholder="단지명을 입력하세요." value="">
                    </div>
                    <div class="inspection_form_box">
                        <div class="inspection_sch_label">업종</div>
                        <select name="industry_idx" id="industry_idx" class="bansang_sel">
                            <option value="">전체</option>
                            <?php while($industry_row = sql_fetch_array($industry_res)){?>
                                <option value="<?php echo $industry_row['industry_idx'];?>"><?php echo $industry_row['industry_name']; ?></option>
                            <?php }?>
                        </select>
                    </div>
                    <div class="inspection_form_box">
                        <div class="inspection_sch_label">업체</div>
                        <input type= "text" name="company_name" id="company_name" class="bansang_ipt ver2" placeholder="업체명을 입력하세요.">
                    </div>
                </div>
                <div class="inspection_form_button mgt10">
                    <button type="button" onclick="sch_handler();">검색</button>
                </div>
            </div> 
        </div>
        <!--
         fire_icons_sm 소방
         parking_tower_icons 주차타워
         mng_icons 관리소장
         elector_icon_sm 전기
         elevetor_icons 승강기
         car_lift_sm 카리프트
         clean_icons_sm 청소
         disinfection_icons_sm 소독
         repair_icon_sm 기계설비
         more_icon_sm 기타
        -->
        <div class="inspection_cont">
            <div class="inner">
                <ul class="tab_lnb">
                    <li class="tab01 on" onclick="tab_handler('1', 'all')">전체</li>
                    <li class="tab02" onclick="tab_handler('2', 'N')">승인대기</li>
                    <li class="tab03" onclick="tab_handler('3', 'R')">재요청</li>
                    <li class="tab04" onclick="tab_handler('4', 'Y')">승인</li>
                    <li class="tab05" onclick="tab_handler('5', 'H')">보류</li>
                </ul>
                <ul class="inspection_cont_list">
                </ul>
            </div>
        </div>
    </div>
</div>
<script>
let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";
let tabCode = "<?php echo $tabCode ?? 'all'; ?>";

tab_handler(tabIdx, tabCode);

function tab_handler(index, code){
    tabIdx = index;
    tabCode = code;

    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    //let building_id = $("#building_id option:selected").val();
    let building_name = $("#building_name").val();
    let industry_idx = $("#industry_idx option:selected").val();
    let company_name = $("#company_name").val();

    $.ajax({

    url : "/inspection_log_list_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "code":tabCode, "building_name":building_name, "industry_idx":industry_idx, "company_name":company_name}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".inspection_cont_list").html(msg);
    }

    });
}

function sch_handler(){
    //let building_id = $("#building_id option:selected").val();
    let building_name = $("#building_name").val();
    let industry_idx = $("#industry_idx option:selected").val();
    let company_name = $("#company_name").val();

    $.ajax({

    url : "/inspection_log_list_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "code":tabCode, "building_name":building_name, "industry_idx":industry_idx, "company_name":company_name}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".inspection_cont_list").html(msg);
    }

    });
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>