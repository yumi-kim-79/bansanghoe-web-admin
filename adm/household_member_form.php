<?php
$sub_menu = "300110";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '추가';
}

$g5['title'] .= '세대 구성원 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;

$sql = "SELECT 
            ho.*, 
            post.post_name,
            building.building_name,
            dong.dong_name
        FROM a_building_ho as ho
        LEFT JOIN a_post_addr as post on ho.post_id = post.post_idx
        LEFT JOIN a_building as building on ho.building_id = building.building_id
        LEFT JOIN a_building_dong as dong on ho.dong_id = dong.dong_id
        WHERE ho.ho_id = {$ho_id}";
$row = sql_fetch($sql);

//echo $eightGradeYear;
$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

//세대관리
$household_sql = "SELECT * FROM a_building_household WHERE ho_id = '{$ho_id}' and is_del = 0";
$household_res = sql_query($household_sql);
$household_total = sql_num_rows($household_res);

//차량관리
$car_sql = "SELECT * FROM a_building_car WHERE ho_id = '{$ho_id}' and is_del = 0 ORDER BY car_id asc";
$car_res = sql_query($car_sql);
$car_total = sql_num_rows($car_res);

//입 퇴실 내역
$history_sql = "SELECT * FROM a_building_household_history WHERE ho_id = '{$ho_id}' ORDER BY history_idx desc";
$history_res = sql_query($history_sql);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql.'<br />';
    echo $household_sql.'<br>';
    echo $car_sql.'<br>';
    echo $history_sql.'<br>';

    //print_r2($row);
}

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>

<form name="fhousehold" id="fhousehold" action="./household_member_form_update.php" onsubmit="return fhousehold_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="ho_id" value="<?php echo $row['ho_id']; ?>">
    <input type="hidden" name="ho_status2" value="<?php echo $row['ho_status']; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <div class="h2_frm_wraps">
            <h2 class="h2_frm">세대 구성원 정보</h2>
            <!-- <button type="submit" class="btn btn_04">입주자 정보 엑셀 업로드</button> -->
            <a href="./household_member_add_excel.php?ho_id=<?php echo $ho_id; ?>" onclick="return excelform(this.href);"  class="btn btn_04">세대 구성원 정보 엑셀 업로드</a>
        </div>
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
                    <th>지역</th>
                    <td>
                        <?php if($w == "u"){?>
                        <input type="hidden" name="post_id" value="<?php echo $row['post_id']; ?>">
                        <input type="text" name="post_name" id="post_name" class="bansang_ipt" value="<?php echo $row['post_name']; ?>" readonly>
                        <?php }else{ ?>
                            <select name="post_id" id="post_id" class="bansang_sel" required onchange="post_change();">
                                <option value="">선택</option>
                                <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                                <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($row['post_id'], $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
                            <?php }?>
                            </select>
                            <script>
                                function post_change(){
                                    var postSelect = document.getElementById("post_id");
                                    var postValue = postSelect.options[postSelect.selectedIndex].value;

                                    console.log('postValue', postValue);

                                    $.ajax({

                                    url : "./post_building_ajax.php", //ajax 통신할 파일
                                    type : "POST", // 형식
                                    data: { "post_id":postValue}, //파라미터 값
                                    success: function(msg){ //성공시 이벤트

                                        //console.log(msg);
                                        $("#building_id").html(msg);
                                    }

                                    });
                                }
                            </script>
                        <?php }?>
                    </td>
                    <th>단지</th>
                    <td>
                        <?php if($w == "u"){?>
                        <input type="hidden" name="building_id" value="<?php echo $row['building_id']; ?>">
                        <input type="text" name="building_name" id="building_name" class="bansang_ipt" value="<?php echo $row['building_name']; ?>" size="50" readonly>
                        <?php }else{ ?>
                        <?php
                        $sql_building = "SELECT * FROM a_building WHERE post_id = '{$row['post_id']}' and is_del = 0";
                        $res_building = sql_query($sql_building);
                        
                        ?>
                        <select name="building_id" id="building_id" class="bansang_sel" onchange="building_change();" required>
                            <option value=""><?php echo $w == 'u' ? '단지를' : '지역을'; ?> 선택해주세요.</option>
                            <?php
                            while($row_building = sql_fetch_array($res_building)){
                            ?>
                            <option value="<?php echo $row_building['building_id']?>" <?php echo get_selected($row['building_id'], $row_building['building_id']); ?>><?php echo $row_building['building_name'];?></option>
                            <?php }?>
                        </select>
                        <script>
                            function building_change(){
                                var buildingSelect = document.getElementById("building_id");
                                var buildingValue = buildingSelect.options[buildingSelect.selectedIndex].value;

                                console.log('buildingValue', buildingValue);

                                $.ajax({

                                url : "./building_dong_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "building_id":buildingValue}, //파라미터 값
                                success: function(msg){ //성공시 이벤트

                                    //console.log(msg);
                                    $("#dong_id").html(msg);
                                }

                                });
                            }
                        </script>
                        <?php }?>
                    </td>
                </tr>
                <tr>
                    <th>동</th>
                    <td>
                        <?php if($w == "u"){?>
                        <input type="hidden" name="dong_id" id="dong_id" class="bansang_ipt ver2" value="<?php echo $row['dong_id'];?>" required>
                        <input type="text" name="dong_name" id="dong_name" class="bansang_ipt" value="<?php echo $row['dong_name'].'동'; ?>" readonly>
                        <?php }else{ ?>
                            <select name="dong_id" id="dong_id" class="bansang_sel" required>
                                <option value=""><?php echo $w == 'u' ? '동을' : '단지를'; ?> 선택해주세요.</option>
                            </select>
                        <?php }?>
                    </td>
                    <th>호수</th>
                    <td>
                        <!-- <input type="text" name="ho_id" id="ho_id" class="bansang_ipt ver2" value="<?php echo $row['ho_id']; ?>"> -->
                        <input type="text" name="ho_name" id="ho_name" class="bansang_ipt <?php echo $w == "u" ? "" : "ver2"; ?>" value="<?php echo $row['ho_name'].'호'; ?>" <?php echo $w == "u" ? "readonly" : "";?> required>
                    </td>
                </tr>
                <tr>
                    <th>입주자</th>
                    <td>
                        <input type="text" name="ho_tenant" id="ho_tenant" class="bansang_ipt <?php echo $w == "u" ? "" : "ver2"; ?>" value="<?php echo $row['ho_status'] == 'Y' ? $row['ho_tenant'] : ''; ?>" <?php echo $w == "u" ? "readonly" : "";?>>
                    </td>
                    <th>입주일</th>
                    <td><input type="text" name="ho_tenant_at" id="ho_tenant_at" class="bansang_ipt <?php echo $w == "u" ? "" : "ver2"; ?>" value="<?php echo $row['ho_status'] == 'Y' ? $row['ho_tenant_at'] : ''; ?>" <?php echo $w == "u" ? "readonly" : "";?>></td>
                </tr>
                <tr>
                    <th>세대 구성원</th>
                    <td colspan="3">
                        <?php echo help("최대 5명 추가 가능합니다.");?>
                        <div class="house_hold_box_wrap">
                            <?php if($w == "u"){?>
                                <?php if($household_total > 0 && $row['ho_status'] == 'Y'){?>
                                    <?php for($i=0;$household_row = sql_fetch_array($household_res);$i++){?>
                                       
                                        <div class="house_hold_box_inner">
                                        <input type="hidden" name="hh_id[]" value="<?php echo $household_row['hh_id']; ?>">
                                            <div class="house_hold_box">
                                                <p class="hh_label">관계</p>
                                                <input type="text" name="hh_relationship[]" class="bansang_ipt ver2" value="<?php echo $household_row['hh_relationship']; ?>" >
                                            </div>
                                            <div class="house_hold_box">
                                                <p class="hh_label">이름</p>
                                                <input type="text" name="hh_name[]" class="bansang_ipt ver2" value="<?php echo $household_row['hh_name']; ?>" >
                                            </div>
                                            <div class="house_hold_box">
                                                <p class="hh_label">연락처</p>
                                                <input type="text" name="hh_hp[]" class="bansang_ipt ver2 phone" maxlength="13" value="<?php echo $household_row['hh_hp']; ?>" >
                                            </div>
                                            <?php if($i==0){?>
                                            <div class="house_hold_box">
                                                <button type="button" onclick="hh_add();" class="bansang_btns ver1">추가</button>
                                            </div>
                                            <?php }else{ ?>
                                            <div class="dong_del_box">
                                                <input type="checkbox" name="hh_del[<?php echo $i; ?>]" id="hh_del<?php echo $i + 1; ?>" value="1">
                                                <label for="hh_del<?php echo $i + 1; ?>">삭제</label>
                                            </div>
                                            <?php }?>
                                        </div>
                                    <?php }?>
                                <?php }else {?>
                                    <div class="house_hold_box_inner">
                                        <div class="house_hold_box">
                                            <p class="hh_label">관계</p>
                                            <input type="text" name="hh_relationship[]" class="bansang_ipt ver2">
                                        </div>
                                        <div class="house_hold_box">
                                            <p class="hh_label">이름</p>
                                            <input type="text" name="hh_name[]" class="bansang_ipt ver2">
                                        </div>
                                        <div class="house_hold_box">
                                            <p class="hh_label">연락처</p>
                                            <input type="text" name="hh_hp[]" class="bansang_ipt ver2 phone" maxlength="13">
                                        </div>
                                        <div class="house_hold_box">
                                            <button type="button" onclick="hh_add();" class="bansang_btns ver1">추가</button>
                                        </div>
                                    </div>
                                <?php }?>
                            <?php }else{ ?>
                            <div class="house_hold_box_inner">
                                <div class="house_hold_box">
                                    <p class="hh_label">관계</p>
                                    <input type="text" name="hh_relationship[]" class="bansang_ipt ver2">
                                </div>
                                <div class="house_hold_box">
                                    <p class="hh_label">이름</p>
                                    <input type="text" name="hh_name[]" class="bansang_ipt ver2">
                                </div>
                                <div class="house_hold_box">
                                    <p class="hh_label">연락처</p>
                                    <input type="text" name="hh_hp[]" class="bansang_ipt ver2 phone" maxlength="13">
                                </div>
                                <div class="house_hold_box">
                                    <button type="button" onclick="hh_add();" class="bansang_btns ver1">추가</button>
                                </div>
                            </div>
                            <?php }?>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="btn_fixed_top">
        <a href="./household_member_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <input type="submit" value="저장" class="btn_submit btn" accesskey='s'>
    </div>
</form>

<script>
$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});

function excelform(url) // 회원 엑셀 업로드를 위하여 추가
{ 

    var opt = "width=600,height=450,left=10,top=10"; 

    window.open(url, "win_excel", opt); 

    return false; 

}

//세대관리 추가
function hh_add(){
    let html = `<div class="house_hold_box_inner">
                    <div class="house_hold_box">
                        <p class="hh_label">관계</p>
                        <input type="text" name="hh_relationship[]" class="bansang_ipt ver2" required>
                    </div>
                    <div class="house_hold_box">
                        <p class="hh_label">이름</p>
                        <input type="text" name="hh_name[]" class="bansang_ipt ver2" required>
                    </div>
                    <div class="house_hold_box">
                        <p class="hh_label">연락처</p>
                        <input type="text" name="hh_hp[]" class="bansang_ipt ver2 phone" maxlength="13" required>
                    </div>
                    <div class="house_hold_box">
                        <button type="button" onclick="hh_remove(this);" class="bansang_btns ver2">삭제</button>
                    </div>
                </div>`;

    let endLength = $(".house_hold_box_inner").length;

    if(endLength >= 5){
        alert("세대구성원은 5명까지 추가 가능합니다.");
    }else{
        $(".house_hold_box_wrap").append(html);
    }
}

//세데관리 삭제
function hh_remove(ele){
    ele.closest('.house_hold_box_inner').remove();
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



//차량 추가
function car_add(){
    let html = `<div class="car_box_inner">
                    <div class="car_box">
                        <p class="hh_label">차종</p>
                        <input type="text" name="car_type[]" class="bansang_ipt ver2" required>
                    </div>
                    <div class="car_box">
                        <p class="hh_label">차량번호</p>
                        <input type="text" name="car_name[]" class="bansang_ipt ver2" required>
                    </div>
                    <div class="car_box">
                        <button type="button" onclick="car_remove(this);" class="bansang_btns ver2">삭제</button>
                    </div>
                </div>`;

    let endLength = $(".car_box_inner").length;

    if(endLength >= 3){
        alert("차량등록은 3대까지 추가 가능합니다.");
    }else{
        $(".car_box_wrap").append(html);
    }
}

//차량 삭제
function car_remove(ele){
    ele.closest('.car_box_inner').remove();
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

function fhousehold_submit(f) {

    //소유자 매매일 날짜 형식 검사
    if(!checkValidDate(f.ho_owner_sale_date.value)){
        alert("소유자 매매일을 날짜 형식에 맞게 입력해주세요.");
        f.ho_owner_sale_date.focus();
        return false;
    }

    //입주일 날짜 형식 검사
    if(f.ho_status.value == "Y"){
        if(!checkValidDate(f.ho_tenant_at.value)){
            alert("입주일을 날짜 형식에 맞게 입력해주세요.");
            f.ho_tenant_at.focus();
            return false;
        }
    }

    //퇴실상태에서는 변경 불가능
    if(f.w.value == "u"){
        if(f.ho_status.value == 'N' && f.ho_status2.value == 'N'){
            alert("퇴실 상태에서 세대관리 수정은 불가능합니다.");
            return false;
        }
    }
    
    //입주에서 퇴실처리로 변경시 컨펌창
    if(f.ho_status.value == 'N' && f.ho_status2.value == 'Y'){

        if (!confirm("퇴실 처리 하시겠습니까?\n입주자 및 세대구성원, 차량정보도 함께 삭제됩니다.")) {
            return false;
        }
    }

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

