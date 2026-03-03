<?php
$sub_menu = "900100";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '등록';
}

$g5['title'] .= '담당자 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;


$sql = "SELECT * FROM a_mng
        WHERE mng_id = '{$mng_id}'";
$row = sql_fetch($sql);

//부서
$sql_depart = "SELECT * FROM a_mng_department WHERE is_del = 0 ORDER BY is_prior asc, md_idx desc";
$depart_res = sql_query($sql_depart);

//직급
$sql_gr = "SELECT * FROM a_mng_grade WHERE is_del = 0 ORDER BY is_prior asc, mg_idx desc";
$gr_res = sql_query($sql_gr);


if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql.'<br>';
    echo $sql_depart.'<br>';
    echo $sql_gr.'<br>';
    //print_r2($row);
}
?>

<form name="fmanager" id="fmanager" action="./sm_manager_form_update.php" onsubmit="return fmanager_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <!-- <input type="hidden" name="mng_id" value="<?php echo $row['mng_id']; ?>"> -->

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">담당자 정보</h2>
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <tr>
                    <th>부서</th>
                    <td >
                        <select name="mng_department" id="mng_department" class="bansang_sel" required>
                            <option value="">선택</option>
                            <?php for($i=0;$depart_row = sql_fetch_array($depart_res);$i++){?>
                                <option value="<?php echo $depart_row['md_idx']; ?>" <?php echo get_selected($row['mng_department'], $depart_row['md_idx']); ?>><?php echo $depart_row['md_name']; ?></option>
                            <?php }?>
                        </select>
                    </td>
                    <th>직급</th>
                    <td >
                        <select name="mng_grades" id="mng_grades" class="bansang_sel" required>
                            <option value="">선택</option>
                            <?php for($i=0;$gr_row = sql_fetch_array($gr_res);$i++){?>
                                <option value="<?php echo $gr_row['mg_idx']; ?>" <?php echo get_selected($row['mng_grades'], $gr_row['mg_idx']); ?>><?php echo $gr_row['mg_name']; ?></option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>등급</th>
                    <td colspan="3">
                        <select name="mng_certi" id="mng_certi" class="bansang_sel" required>
                            <option value="">선택</option>
                            <option value="A" <?php echo get_selected($row['mng_certi'], 'A'); ?>>A</option>
                            <option value="B" <?php echo get_selected($row['mng_certi'], 'B'); ?>>B</option>
                            <option value="C" <?php echo get_selected($row['mng_certi'], 'C'); ?>>C</option>
                            <option value="D" <?php echo get_selected($row['mng_certi'], 'D'); ?>>D</option>
                        </select>
                    </td>
                </tr>
               <tr>
                    <th>담당자 명</th>
                    <td>
                        <input type="text" name="mng_name" class="bansang_ipt ver2" value="<?php echo $row['mng_name']; ?>" required>
                    </td>
                    <th>담당자 연락처</th>
                    <td>
                        <input type="tel" name="mng_hp" class="bansang_ipt ver2 phone" value="<?php echo $row['mng_hp']; ?>" maxlength="13"  required>
                    </td>
               </tr>
               <tr>
                    <th>아이디</th>
                    <td>
                        <?php echo $w == '' ? help('아이디는 영문만 사용 가능합니다.') : '';?>
                        <input type="hidden" name="id_check" id="id_check" value="<?php echo $w == "u" ? "1" : "";?>">
                        <div class="ipt_box flex_ver">
                            <input type="text" name="mng_id" id="mng_id" class="bansang_ipt ver2" value="<?php echo $row['mng_id']; ?>" <?php echo $w == "u" ? "readonly" : ""; ?> oninput="idOnInput(this)" required>
                            <?php if($w == ""){?>
                            <button type="button" onclick="idValidCheck();" class="bansang_btns ver1">중복확인</button>
                            <?php }?>
                        </div>
                    </td>
                    <th>비밀번호</th>
                    <td><input type="password" name="mng_password" class="bansang_ipt ver2" <?php echo $w == "u" ? "" : "required"?>></td>
               </tr>
               <tr>
                    <th>입사일</th>
                    <td><input type="text" name="joined_at" class="bansang_ipt ver2 ipt_date" value="<?php echo $row['joined_at']; ?>" required></td>
                    <th>상태</th>
                    <td>
                        <select name="mng_status" id="mng_status" class="bansang_sel" required>
                            <option value="1" <?php echo get_selected($row['mng_status'], "1"); ?>>승인</option>
                            <?php if($w == "u"){?>
                            <option value="2" <?php echo get_selected($row['mng_status'], "2"); ?>>퇴사</option>
                            <?php }?>
                        </select>
                    </td>
               </tr>
            </tbody>
        </table>
    </div>
    <?php if($w == "u" && $row['mng_status'] == 1){

    $mng_building_yes = "SELECT * FROM a_mng_building WHERE is_del = 0 and mb_id = '{$mng_id}'";
    $mng_building_yes_res = sql_query($mng_building_yes);

    $mng_building_yes_arr = array();

    while($mng_building_yes_row = sql_fetch_array($mng_building_yes_res)){
        array_push($mng_building_yes_arr, $mng_building_yes_row['building_id']);
    }


    $mng_building_yes_arr_t = "'".implode("','", $mng_building_yes_arr)."'";
    //print_r2($mng_building_yes_arr);

    $no_mng_building2 = "SELECT * FROM a_building WHERE is_del = 0 and building_id NOT IN ($mng_building_yes_arr_t) ORDER BY building_id desc";

    //echo $no_mng_building2;
    $no_mng_res2 = sql_query($no_mng_building2);

    $no_mng_building_arr = array();

    while($no_mng_row2 = sql_fetch_array($no_mng_res2)){
        array_push($no_mng_building_arr, $no_mng_row2['building_id']);
    }
        
    //관리중이지 않은 단지
    $no_mng_building = "SELECT * FROM a_building WHERE is_del = 0 and is_use = 1 and building_id NOT IN ($mng_building_yes_arr_t) ORDER BY building_id desc";
    // echo $no_mng_building.'<br>';
    $no_mng_res = sql_query($no_mng_building);

    if($_SERVER['REMOTE_ADDR'] == '59.16.155.80'){
        // print_r2($mng_building_yes_arr);
        // echo $mng_building_yes_arr_t.'<br>';

        // echo $no_mng_building;
    }


    //----------
    $mng_building2 = "SELECT * FROM a_mng_building WHERE is_del = 0 and mb_id = '{$mng_id}'";
    $mng_res2 = sql_query($mng_building2);

    $mng_building_arr = array();

    while($mng_row2 = sql_fetch_array($mng_res2)){
        array_push($mng_building_arr, $mng_row2['building_id']);
    }

    //관리중인 단지
    $mng_building = "SELECT mng_build.*, building.building_name, building.is_use, building.building_addr FROM 
                    a_mng_building as mng_build
                    left join a_building as building on mng_build.building_id = building.building_id
                    WHERE mng_build.is_del = 0 and mng_build.mb_id = '{$mng_id}' and building.is_use = 1 ORDER BY building.building_name asc";
    //echo $mng_building;
    $mng_res = sql_query($mng_building);
    ?>
    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm">담당 단지 설정</h2>
        <div class="mng_building_wrap">
            <div class="mng_building_box">
                <div class="mng_build_label">관리중이지 않은 단지</div>
                <input type="hidden" name="select_bf_chk" id="select_bf_chk" value="<?php echo implode(",", $no_mng_building_arr); ?>">
                <div class="mng_building_box_wrap mgt10">
                    <div class="mng_building_box_hd">
                        <div class="mng_building_sch_box">
                            <div class="mng_sch_ipt">
                                <input type="text" name="bf_mng_building_sch" id="bf_mng_building_sch" class="bansang_ipt ver2" placeholder="검색어를 입력해주세요.">
                                <button type="button" onclick="bf_sch_handler();" class="bansang_btns ver4">검색</button>
                            </div>
                            <script>
                                function bf_sch_handler(){
                                    let schText = $("#bf_mng_building_sch").val();

                                    //console.log('schText::', schText);

                                    $.ajax({

                                    url : "./bf_mnf_building_sch.php", //ajax 통신할 파일
                                    type : "POST", // 형식
                                    data: { "mng_id":"<?php echo $mng_id; ?>", "schText":schText}, //파라미터 값
                                    success: function(msg){ //성공시 이벤트

                                        //console.log(msg);
                                        $(".mng_building_list2_bf").html(msg);
                                        // $(".select_box2").html(msg); //.select_box2에 html로 나타내라..
                                    }

                                    });
                                }
                            </script>
                        </div>
                        <div class="mng_building_add">
                            <button type="button" onclick="bf_building_add();" class="bansang_btns ver1">추가</button>
                        </div>
                    </div>
                    <div class="mng_building_list">
                        <div class="mng_building_list_box_wrap">
                            <div class="mng_building_list_box1 mng_building_list_box1_tit">
                                <input type="checkbox" name="bf_mng_chk_all" id="bf_mng_chk_all" value="1">
                            </div>
                            <div class="mng_building_list_box2">
                                <div class="mng_building_list_box mng_building_list_box_tit">단지명</div>
                                <div class="mng_building_list_box mng_building_list_box_tit">주소</div>
                            </div>
                        </div>
                        <div class="mng_building_list2 mng_building_list2_bf">
                            <?php for($i=0;$no_mng_row = sql_fetch_array($no_mng_res);$i++){?>
                            <div class="mng_building_list_box_wrap">
                                <div class="mng_building_list_box1">
                                    <input type="checkbox" name="bf_mng_chk" id="bf_mng_chk<?php echo $i + 1; ?>" class="bf_mng_chk" value="<?php echo $no_mng_row['building_id']; ?>">
                                </div>
                                <div class="mng_building_list_box2">
                                    <div class="mng_building_list_box">
                                        <label for="bf_mng_chk<?php echo $i + 1;?>">
                                        <?php echo $no_mng_row['building_name']; ?>
                                        </label>
                                    </div>
                                    <div class="mng_building_list_box"><?php echo $no_mng_row['building_addr']; ?></div>
                                </div>
                            </div>
                            <?php }?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mng_building_box">
                <div class="mng_build_label">관리중인 단지</div>
                <input type="hidden" name="select_af_chk" id="select_af_chk" value="<?php echo implode(",", $mng_building_arr); ?>">
                <div class="mng_building_box_wrap mgt10">
                    <div class="mng_building_box_hd">
                        <div class="mng_building_sch_box">
                            <div class="mng_sch_ipt">
                                <input type="text" name="af_mng_building_sch" id="af_mng_building_sch" class="bansang_ipt ver2" placeholder="검색어를 입력해주세요.">
                                <button type="button" onclick="af_sch_handler();" class="bansang_btns ver4">검색</button>
                            </div>
                            <script>
                                function af_sch_handler(){
                                    let schText = $("#af_mng_building_sch").val();

                                    //console.log('schText::', schText);

                                    $.ajax({

                                    url : "./af_mnf_building_sch.php", //ajax 통신할 파일
                                    type : "POST", // 형식
                                    data: { "mng_id":"<?php echo $mng_id; ?>", "schText":schText}, //파라미터 값
                                    success: function(msg){ //성공시 이벤트

                                        //console.log(msg);
                                        $(".mng_building_list2_af").html(msg);
                                        // $(".select_box2").html(msg); //.select_box2에 html로 나타내라..
                                    }

                                    });
                                }
                            </script>
                        </div>
                        <div class="mng_building_add">
                            <button type="button" onclick="af_mng_handler();" class="bansang_btns ver2">삭제</button>
                        </div>
                    </div>
                    <div class="mng_building_list">
                        <div class="mng_building_list_box_wrap">
                            <div class="mng_building_list_box1 mng_building_list_box1_tit">
                                <input type="checkbox" name="af_mng_chk_all" id="af_mng_chk_all">
                            </div>
                            <div class="mng_building_list_box2">
                                <div class="mng_building_list_box mng_building_list_box_tit">단지명</div>
                                <div class="mng_building_list_box mng_building_list_box_tit">주소</div>
                            </div>
                        </div>
                        <div class="mng_building_list2 mng_building_list2_af">
                            <?php for($i=0;$mng_row = sql_fetch_array($mng_res);$i++){?>
                            <div class="mng_building_list_box_wrap">
                                <div class="mng_building_list_box1">
                                    <input type="checkbox" name="af_mng_chk" id="af_mng_chk<?php echo $i + 1;?>" class="af_mng_chk" value="<?php echo $mng_row['building_id']; ?>">
                                </div>
                                <div class="mng_building_list_box2">
                                    <div class="mng_building_list_box">
                                        <label for="af_mng_chk<?php echo $i + 1;?>">
                                        <?php echo $mng_row['building_name']; ?>
                                        </label>
                                    </div>
                                    <div class="mng_building_list_box"><?php echo $mng_row['building_addr']; ?></div>
                                </div>
                            </div>
                            <?php }?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php }?>
    <div class="btn_fixed_top">
        <a href="./sm_manager.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <input type="submit" value="저장" class="btn_submit btn btn_02" accesskey='s'>
    </div>
</form>


<script>
$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d"});
});

//관리중이지 않은 단지 전체선택
$("#bf_mng_chk_all").click(function() {
	if($("#bf_mng_chk_all").is(":checked")){
		$(".bf_mng_chk").prop("checked", true);
	}else{
		$(".bf_mng_chk").prop("checked", false);
	}
	$(".bf_mng_chk").change();
});
$(".bf_mng_chk").click(function() {
	var total = $(".bf_mng_chk").length;
	var checked = $(".bf_mng_chk:checked").length;

	if(total != checked) $("#bf_mng_chk_all").prop("checked", false);
	else $("#bf_mng_chk_all").prop("checked", true); 
});

//관리중인 단지로 추가
function bf_building_add(){

    $("#bf_mng_building_sch").val("");

    var bf_mng_chk_arr = [];

    $("input[name=bf_mng_chk]:checked").each(function(){
        var chk = $(this).val();

        bf_mng_chk_arr.push(chk);
    });


    //단지 선택안하면 에러
    if(bf_mng_chk_arr == ""){
        alert("추가하실 단지를 하나이상 선택해주세요.");
        return false;
    }

    console.log('관리중인 단지로 추가할 단지 idx', bf_mng_chk_arr);


    let filter_bidx;
    for(var i=0;i<bf_mng_chk_arr.length;i++){

        let select_bf_chk = $("#select_bf_chk").val(); //관리중으로 추가된 idx
        let select_bf_chk_t = select_bf_chk.split(","); //배열로 변경

        filter_bidx = select_bf_chk_t.filter((element) => element !== bf_mng_chk_arr[i]);

        $("#select_bf_chk").val(filter_bidx.join(","));
    }

    if($("#select_af_chk").val() == ""){
        $("#select_af_chk").val(bf_mng_chk_arr.join(","));
    }else{
        $("#select_af_chk").val($("#select_af_chk").val() + "," + bf_mng_chk_arr.join(","));
    }

    let select_af_chk = $("#select_af_chk").val();

    //관리중인 단지로 추가
    $.ajax({

    url : "./sm_manager_bulding_add.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "select_chk":select_af_chk, "mb_id":"<?php echo $mng_id; ?>"}, //파라미터 값
    success: function(msg){ //성공시 이벤트

        //console.log(msg);
        $(".mng_building_list2_af").html(msg);
        // $(".select_box2").html(msg); //.select_box2에 html로 나타내라..
    }

    });


    //관리중이지 않은 단지에서 삭제
     $.ajax({

        url : "./sm_manager_bulding_remove.php", //ajax 통신할 파일
        type : "POST", // 형식
        data: { "select_chk":select_af_chk}, //파라미터 값
        success: function(msg){ //성공시 이벤트

            //console.log(msg);
            $(".mng_building_list2_bf").html(msg);
            // $(".select_box2").html(msg); //.select_box2에 html로 나타내라..
        }

    });
}


//관리중인 단지 전체선택
$("#af_mng_chk_all").click(function() {
	if($("#af_mng_chk_all").is(":checked")){
		$(".af_mng_chk").prop("checked", true);
	}else{
		$(".af_mng_chk").prop("checked", false);
	}
	$(".af_mng_chk").change();
});
$(".af_mng_chk").click(function() {
	var total = $(".af_mng_chk").length;
	var checked = $(".af_mng_chk:checked").length;

	if(total != checked) $("#af_mng_chk_all").prop("checked", false);
	else $("#af_mng_chk_all").prop("checked", true); 
});


function af_mng_handler(){

    $("#af_mng_building_sch").val("");

    var af_mng_chk_arr = [];

    $("input[name=af_mng_chk]:checked").each(function(){
        var chk = $(this).val();

        af_mng_chk_arr.push(chk);
    })

    if(af_mng_chk_arr == ""){
        alert("삭제하실 단지를 하나이상 선택해주세요.");
        return false;
    }

    console.log('관리중이지 않은 단지로 이동', af_mng_chk_arr);
    

    let filter_bidx;
    for(var i=0;i<af_mng_chk_arr.length;i++){

        let select_af_chk = $("#select_af_chk").val(); //관리중으로 추가된 idx
        let select_af_chk_t = select_af_chk.split(","); //배열로 변경

        filter_bidx = select_af_chk_t.filter((element) => element !== af_mng_chk_arr[i]);

        $("#select_af_chk").val(filter_bidx.join(","));
    }

    let af_mng_chk_arr_t;
    if($("#select_bf_chk").val() == ""){
        af_mng_chk_arr_t = af_mng_chk_arr.join(",");
    }else{
        af_mng_chk_arr_t = $("#select_bf_chk").val() + "," + af_mng_chk_arr.join(",");
    }

    $("#select_bf_chk").val(af_mng_chk_arr_t);

    console.log('af_mng_chk_arr_t', af_mng_chk_arr_t);
   
    $.ajax({

    url : "./sm_no_manager_bulding_add.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "select_chk":af_mng_chk_arr_t}, //파라미터 값
    success: function(msg){ //성공시 이벤트

        //console.log(msg);
        $(".mng_building_list2_bf").html(msg);
        // $(".select_box2").html(msg); //.select_box2에 html로 나타내라..
    }

    });

    let select_af_chk_val = $("#select_af_chk").val();
    $.ajax({

    url : "./sm_no_manager_bulding_remove.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "mng_id":"<?php echo $mng_id; ?>", "select_af_chk":select_af_chk_val}, //파라미터 값
    success: function(msg){ //성공시 이벤트

        //console.log(msg);
        $(".mng_building_list2_af").html(msg);
        // $(".select_box2").html(msg); //.select_box2에 html로 나타내라..
    }

    });
}

function idOnInput(e){
    //e.value = e.value.replace(/[^a-z0-9]/gi, '')
    e.value = e.value.replace(/[^a-z0-9]/g, '');

}


$(document).on("keyup", ".phone", function(){
    var value = this.value.replace(/[^0-9]/g, "");

  // 길이에 따라 하이픈 삽입
  if (value.length <= 3) {
    // 3자리까지는 아무것도 하지 않음
    this.value = value;
  } else if (value.length <= 7) {
    // 4자리까지는 '010-XXXX' 형태
    this.value = value.replace(/(\d{3})(\d{0,4})/, "$1-$2");
  } else if (value.length <= 11) {
    // 11자리까지는 '010-XXXX-YYYY' 형태
    this.value = value.replace(/(\d{3})(\d{4})(\d{0,4})/, "$1-$2-$3");
  } else {
    // 11자리를 초과하는 경우는 잘라서 처리
    this.value = value
      .substring(0, 11)
      .replace(/(\d{3})(\d{4})(\d{0,4})/, "$1-$2-$3");
  }
});

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

// $(document).on("keyup", "#mng_id", function(){
//     $("#id_check").val("");
//     //$("#id_check").removeClass('actives');
// });
$("#mng_id").on("change keyup paste", function() {
    var currentVal = $(this).val();
    console.log(currentVal);

    $("#mng_id").removeClass('actives');
    $("#id_check").val("");
});

function idValidCheck(){

    let id = $("#mng_id").val();

    if(id == "") alert("아이디를 입력해주세요.");
    
    let sendData = {'mb_id': id};

    $.ajax({
        type: "POST",
        url: "./sm_manager.id.check.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                alert(data.msg);
                //$(".btn_submit").attr('disabled', false);
                $("#mng_id").removeClass('actives');
                $("#id_check").val("");
                return false;
            }else{
                alert(data.msg);
                $("#mng_id").addClass('actives');
                $("#id_check").val(1);
               
            }
        },
    });

    
}

function fmanager_submit(f) {
   
    if(!checkValidDate(f.joined_at.value)){
        alert("입사일을 날짜 형식에 맞게 입력해주세요.");
        f.joined_at.focus();
        return false;
    }

    if(f.w.value == ""){
        if(f.id_check.value != '1'){
            alert('아이디 중복확인을 해주세요.');
            return false;
        }
    }
    

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

