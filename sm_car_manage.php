<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$building_sql = "SELECT building.*, post.post_name FROM a_building as building
                 LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                 WHERE building.building_id = '{$building_id}'";
$building_row = sql_fetch($building_sql);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <p class="mng_title_sub"><?php echo $building_row['post_name']; ?></p>
                <p class="mng_title"><?php echo $building_row['building_name']; ?></p>
            </div>
        </div>
        <div class="car_content">
            <div class="inner">
                <ul class="tab_lnb">
                    <li class="tab01 on" onclick="tab_handler('1', 'car')">차량관리</li>
                    <li class="tab02" onclick="tab_handler('2', 'visit_car')">방문 차량 관리</li>
                </ul>
                <div class="sch_box_wrap mgt20 mgb10">
                    <div class="date_inputs sm_section sm_section2">
                        <input type="text" name="sch_date" id="sch_date" class="bansang_ipt ipt_date ver2 mgb10">
                    </div>
                    <div class="ipt_box ipt_flex ipt_box_ver2">
                        <select name="sch_type" id="sch_type" class="bansang_sel" onchange="schTypeChange();">
                            <option value="car_name">차량번호</option>
                            <option value="car_type">차종</option>
                        </select>
                        <script>
                            function schTypeChange(){
                                var schTypeSelect = document.getElementById("sch_type");
                                var schTypeValue = schTypeSelect.options[schTypeSelect.selectedIndex].value;

                                if(schTypeValue == "car_name"){
                                    $("#sch_text").attr("placeholder", "차량번호를 입력하세요.");
                                }else{
                                    $("#sch_text").attr("placeholder", "차종을 입력하세요.");
                                }
                            }
                        </script>
                        <div class="ipt_box ipt_box2 ipt_flex ipt_box_ver2">
                            <input type="text" name="sch_text" id="sch_text" class="bansang_ipt" placeholder="차량번호를 입력하세요.">
                            <button type="button" onclick="schHandler();" class="sch_button">
                                <img src="/images/sch_icons.svg" alt="">
                            </button>
                        </div>
                    </div>
                </div>
                <ul class="house_hold_list ver2 sm_section sm_section1">
                </ul>
                <div class="mng_cont_wrap mgt20 sm_section sm_section2">
                    <div class="mng_cont_box_wrap">
                        <div class="mng_cont_box">
                            <div class="mng_cont_label">방문 동/호수</div>
                            <div class="mng_cont_infos">1001동 1001호</div>
                        </div>
                        <div class="mng_cont_box">
                            <div class="mng_cont_label">차량번호</div>
                            <div class="mng_cont_infos">123가 1234</div>
                        </div>
                        <div class="mng_cont_box tel">
                            <div class="mng_cont_label">방문자 연락처</div>
                            <div class="mng_cont_infos tel">
                                010-1234-5678
                                <a href="tel:010-1111-1111" class="tel_btn"><i><img src="/images/phone_icons.svg" alt=""></i>전화걸기</a>
                            </div>
                        </div>
                        <div class="mng_cont_box">
                            <div class="mng_cont_label">방문기간</div>
                            <div class="mng_cont_infos">2024.10.11 ~ 2024.10.11</div>
                        </div>
                        <div class="mng_cont_box">
                            <div class="mng_cont_label">출차</div>
                            <div class="mng_cont_infos">-</div>
                        </div>
                    </div>
                    <div class="mng_cont_box_wrap">
                        <div class="mng_cont_box">
                            <div class="mng_cont_label">방문 동/호수</div>
                            <div class="mng_cont_infos">1001동 1001호</div>
                        </div>
                        <div class="mng_cont_box">
                            <div class="mng_cont_label">차량번호</div>
                            <div class="mng_cont_infos">123가 1234</div>
                        </div>
                        <div class="mng_cont_box tel">
                            <div class="mng_cont_label">방문자 연락처</div>
                            <div class="mng_cont_infos tel">
                                010-1234-5678
                                <a href="tel:010-1111-1111" class="tel_btn"><i><img src="/images/phone_icons.svg" alt=""></i>전화걸기</a>
                            </div>
                        </div>
                        <div class="mng_cont_box">
                            <div class="mng_cont_label">방문기간</div>
                            <div class="mng_cont_infos">2024.10.11 ~ 2024.10.11</div>
                        </div>
                        <div class="mng_cont_box">
                            <div class="mng_cont_label">출차</div>
                            <div class="mng_cont_infos">-</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";
let tabCode = "<?php echo $tabCode ?? 'car'; ?>"; //검색을 위해 코드명 저장
tab_handler(tabIdx, tabCode);

function tab_handler(index, code){
    tabCode = code;

    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    $(".sm_section").hide();
    $(".sm_section" + index).show();

    let building_id = "<?php echo $building_id; ?>";

    $.ajax({

    url : "/sm_car_manage_list_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: {'code':tabCode, 'building_id':building_id}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);

        if(tabCode == 'car'){
            $(".house_hold_list").html(msg);
        }else{
            $(".mng_cont_wrap").html(msg);
        }
    }

    });
}

//날짜형식
function checkValidDate(value) {
	var result = true;
	try {
	    var date = value.split("-");
	    var y = parseInt(date[0], 10),
	        m = parseInt(date[1], 10),
	        d = parseInt(date[2], 10);
	    
	    var dateRegex = /^(?=\d)(?:(?:31(?!.(?:0?[2469]|11))|(?:30|29)(?!.0?2)|29(?=.0?2.(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(?:\x20|$))|(?:2[0-8]|1\d|0?[1-9]))([-.\/])(?:1[012]|0?[1-9])\1(?:1[6-9]|[2-9]\d)?\d\d(?:(?=\x20\d)\x20|$))?(((0?[1-9]|1[012])(:[0-5]\d){0,2}(\x20[AP]M))|([01]\d|2[0-3])(:[0-5]\d){1,2})?$/;
	    result = dateRegex.test(d+'-'+m+'-'+y);
	} catch (err) {
		result = false;
	}    
    return result;
}

function schHandler(){

    let building_id = "<?php echo $building_id; ?>";
    let sch_date = $("#sch_date").val();
    let sch_type = $("#sch_type option:selected").val();
    let sch_text = $("#sch_text").val();

    //console.log('sch_text', sch_text);

    if(sch_date != ""){
        if(!checkValidDate(sch_date)){
            showToast("날짜를 형식에 맞게 입력하세요.");
            return false;
        }
    }

    $.ajax({

    url : "/sm_car_manage_list_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: {'code':tabCode, 'building_id':building_id, "sch_date":sch_date, "sch_type":sch_type, "sch_text":sch_text}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);

        if(tabCode == 'car'){
            $(".house_hold_list").html(msg);
        }else{
            $(".mng_cont_wrap").html(msg);
        }
    }

    });
}

$(function(){
    $("#sch_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>