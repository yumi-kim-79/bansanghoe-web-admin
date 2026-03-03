<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');

$buidling_info = sql_fetch("SELECT * FROM a_building WHERE building_id = '{$building_id}'");

$dong_sql = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' and is_del = 0 ORDER BY dong_name asc, dong_id desc";
$dong_res = sql_query($dong_sql);

$bfYear = date("Y", strtotime("-1 year"));
$nowYear = $selectYear == "" ? date("Y") : $selectYear;
$nowMonnth = $selectMonth == "" ? date("n") : $selectMonth;
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="insepecton_form_t parking_sc parking_sc1">
            <div class="inner">
                <p class="inspection_txt"><?php echo $buidling_info['building_name']; ?></p>
            </div>
        </div>
        
        <div class="inner">
            <div class="meter_reading_form_box">
                <div class="form_select_box">
                    <select name="dong_select" id="dong_select" class="bansang_sel">
                        <option value="">동 전체</option>
                        <?php while($dong_row = sql_fetch_array($dong_res)){?>
                        <option value="<?php echo $dong_row['dong_id']; ?>"><?php echo $dong_row['dong_name'].'동'; ?></option>
                        <?php }?>
                    </select>
                </div>
                <div class="form_select_box flex_ver">
                    <div class="form_select_box_wrap ver2">
                        <select name="mr_year" id="mr_year" class="bansang_sel">
                            <option value="">선택하세요.</option>
                            <?php for($i=$bfYear;$i<=$nowYear;$i++){?>
                                <option value="<?php echo $i;?>" <?php echo get_selected($nowYear, $i); ?>><?php echo $i;?></option>
                            <?php }?>
                        </select>
                        <select name="mr_month" id="mr_month" class="bansang_sel">
                            <option value="">선택하세요.</option>
                            <?php for($i=1;$i<=12;$i++){?>
                            <option value="<?php echo $i; ?>" <?php echo get_selected($nowMonnth, $i); ?>><?php echo $i; ?>월</option>
                            <?php }?>
                        </select>
                    </div>
                    <button type="button" onclick="meter_sch_handler();" class="sch_button">
                        <img src="/images/sch_icons.svg" alt="">
                    </button>
                </div>
                <div style="height:10px;"></div>
                <ul class="tab_lnb">
                    <li class="tab01 on" onclick="tab_handler('1', 'electro')">전기</li>
                    <li class="tab02" onclick="tab_handler('2', 'water')">수도</li>
                </ul>
                <div class="meter_table_box_wrap">

                </div>
            </div>
        </div>
        <div class="fix_btn_back_box"></div>
        <div class="fix_btn_box ver3">
            <button type="button" onclick="meter_save();" class="fix_btn on" id="fix_btn" >저장</button>
        </div>
    </div>
</div>
<script>

function bindInputFocusEvent() {
    const inputs = document.querySelectorAll('input[type="text"], input[type="tel"], input:not([type])');

    inputs.forEach(input => {
        input.addEventListener('focus', function () {
            const el = this;
            setTimeout(() => {
                const length = el.value.length;
                el.setSelectionRange(length, length);
            }, 0);
        });
    });
}

let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";
let tabCode = "<?php echo $tabCode ?? 'electro';?>";
tab_handler(tabIdx, tabCode);

function tab_handler(index, code){
    tabIdx = index;
    tabCode = code;

    console.log('gggg');

    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    let dong_id = $("#dong_select option:selected").val();
    let mr_year = $("#mr_year option:selected").val();
    let mr_month = $("#mr_month option:selected").val();

    $.ajax({

    url : "/meter_reading_info_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "type":tabCode, "dong_id":dong_id, "mr_year":mr_year, "mr_month":mr_month, "building_id":"<?php echo $building_id; ?>", "mr_department":"<?php echo $mng_info['mng_department'];?>", "wid":"<?php echo $mng_info['mng_id']; ?>"}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        // console.log(msg);
        $(".meter_table_box_wrap").html(msg);

        bindInputFocusEvent();
    }

    });
}

function meter_save(){
    var formData = $("#meter_frm").serialize();

    var selectYear = $("#mr_year option:selected").val();
    var selectMonth = $("#mr_month option:selected").val();
    var building_id = "<?php echo $building_id; ?>";

    $.ajax({
        type: "POST",
        url: "/meter_reading_info_update.php",
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            // console.log('data:::', data);

            if(data.result == false) { 
                showToast(data.msg);
                return false;
            }else{
                showToast(data.msg);

                if(data.data == 'water'){
                    setTimeout(() => {
                        window.location.replace('/meter_reading_info.php?building_id=' + building_id + '&tabIdx=2&tabCode=' + data.data + '&selectYear=' + selectYear + "&selectMonth=" + selectMonth);
                    }, 400)
                }else{
                    setTimeout(() => {
                        window.location.replace('/meter_reading_info.php?building_id=' + building_id + '&tabIdx=1&tabCode=' + data.data + '&selectYear=' + selectYear + "&selectMonth=" + selectMonth);
                    }, 400);
                }

              
                // popOpen('success_pop');
                // //$("#id_chk").val(1);
            }
        },
    });
}

function meter_sch_handler(){
    let code = tabCode;

    console.log(code);
    let dong_id = $("#dong_select option:selected").val();
    let mr_year = $("#mr_year option:selected").val();
    let mr_month = $("#mr_month option:selected").val();

    $.ajax({

        url : "/meter_reading_info_ajax.php", //ajax 통신할 파일
        type : "POST", // 형식
        data: { "type":code, "dong_id":dong_id, "mr_year":mr_year, "mr_month":mr_month, "building_id":"<?php echo $building_id; ?>", "mr_department":"<?php echo $mng_info['mng_department'];?>", "wid":"<?php echo $mng_info['mng_id']; ?>"}, //파라미터 값
        success: function(msg){ //성공시 이벤트
            // console.log(msg);
            $(".meter_table_box_wrap").html(msg);

            bindInputFocusEvent();
        }

    });
}

$(function(){
    //minDate:"0d" 
    $("#meter_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "0d"});
});
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>