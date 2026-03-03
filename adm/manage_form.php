<?php
$sub_menu = "300200";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '추가';
}

$g5['title'] .= '관리단 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;

$sql = "SELECT * FROM a_mng_team
        WHERE mt_id = {$mt_id}";
$row = sql_fetch($sql);

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

//단지
$build_sql = "SELECT * FROM a_building WHERE building_id = '{$row['build_id']}'";
$build_row = sql_fetch($build_sql);

//동
$dong_sql = "SELECT * FROM a_building_dong WHERE building_id = '{$row['build_id']}'";
$dong_res = sql_query($dong_sql);

//호수
$ho_sql = "SELECT * FROM a_building_ho WHERE building_id = '{$row['build_id']}' and dong_id = '{$row['dong_id']}' and ho_status = 'Y' ";
$ho_res = sql_query($ho_sql);

//직책
$grade_sql = "SELECT * FROM a_mng_team_grade WHERE is_del = 0 ORDER BY gr_id asc";
$grade_res = sql_query($grade_sql);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql.'<br>';
    echo $build_sql.'<br>';
    echo $dong_sql.'<br>';
    echo $ho_sql.'<br>';

    //print_r2($row);
}

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>

<form name="fmanage" id="fmanage" action="./manage_form_update.php" onsubmit="return fmanage_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="mt_id" value="<?php echo $row['mt_id']; ?>">
    <input type="hidden" name="mb_id" id="mb_id" value="<?php echo $row['mb_id']; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">관리단 정보</h2>
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
                    <th>구분</th>
                    <td colspan="3">
                        <!-- onchange="mt_type_change();" -->
                        <div class="mt_p" style="<?php echo $row['mt_type'] == 'OUT' ? '' : 'display:none;'; ?>">
                            <?php echo help("* 외부인의 경우 아이디는 연락처 / 비밀번호는 연락처 뒷자리 4자리로 설정 되며, 반상회 앱 로그인 가능합니다.\n(단 메뉴 이용에 대해 제한이 있습니다.)"); ?>
                        </div>
                        <select name="mt_type" id="mt_type" class="bansang_sel" onchange="mt_type_change();" required>
                            <option value="IN" <?php echo get_selected($row['mt_type'], 'IN'); ?>>입주민</option>
                            <option value="OUT" <?php echo get_selected($row['mt_type'], 'OUT'); ?>>외부인</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>지역</th>
                    <td colspan="3">
                        <select name="post_id" id="post_id" class="bansang_sel" onchange="post_change();" required>
                            <option value="">전체</option>
                            <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                                <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($row['post_id'], $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>단지</th>
                    <td colspan="3">
                        <div class="sch_box_wrap ">
                            <div class="sch_box_left">
                                <div class="sch_result_box">
                                    <!-- <button type="button">푸르지오</button>
                                    <button type="button">푸르지오2</button>
                                    <button type="button">푸르지오3</button>
                                    <button type="button">푸르지오3</button>
                                    <button type="button">푸르지오3</button>
                                    <button type="button">푸르지오3</button>
                                    <button type="button">푸르지오3</button> -->
                                </div>
                                <!-- 검색어를 입력해주세요. -->
                                <input type="text" name="building_sch" id="building_sch" class="bansang_ipt ver2" size="50" placeholder="단지명을 입력하세요.">
                            </div>
                            <!-- <div class="sch_box_right">
                                <button type="button" class="bansang_btns ver1" onclick="building_handler();">검색</button>
                            </div> -->
                        </div>
                        <input type="hidden" name="building_id" id="building_id" value="<?php echo $row['build_id']; ?>">
                        <input type="text" name="building_name" id="building_name" class="bansang_ipt ver2 mgt10" size="100" placeholder="선택한 단지가 보여집니다." readonly value="<?php echo $build_row['building_name']; ?>" required>
                       
                    </td>
                </tr>
                <tr class="tr_dongho" style="<?php echo $row['mt_type'] == 'OUT' ? 'display:none;' : '';?>">
                    <th>동</th>
                    <td>
                        <select name="dong_id" id="dong_id" class="bansang_sel" onchange="dong_change();" <?php echo $row['mt_type'] == 'OUT' ? '' : 'required'; ?>>
                            <option value="">선택</option>
                            <?php for($i=0;$dong_row = sql_fetch_array($dong_res);$i++){ ?>
                                <option value="<?php echo $dong_row['dong_id']; ?>" <?php echo get_selected($row['dong_id'], $dong_row['dong_id']); ?>><?php echo $dong_row['dong_name']; ?>동</option>
                            <?php }?>
                        </select>
                    </td>
                    <th>호수</th>
                    <td>
                        <select name="ho_id" id="ho_id" class="bansang_sel" onchange="ho_change();" <?php echo $row['mt_type'] == 'OUT' ? '' : 'required'; ?>>
                            <option value="">선택</option>
                            <?php for($i=0;$ho_row = sql_fetch_array($ho_res);$i++){?>
                                <option value="<?php echo $ho_row['ho_id']; ?>" <?php echo get_selected($row['ho_id'], $ho_row['ho_id']); ?>><?php echo $ho_row['ho_name']; ?>호</option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>이름</th>
                    <td>
                        <input type="text" name="ho_tenant" id="ho_tenant" class="bansang_ipt ver2" value="<?php echo $row['mt_name']; ?>" <?php echo $row['mt_type'] == 'IN' || $w == '' ? 'readonly' : '';?>  required>
                    </td>
                    <th>연락처</th>
                    <td>
                        <input type="tel" name="ho_tenant_hp" id="ho_tenant_hp" class="bansang_ipt ver2 phone" value="<?php echo $row['mt_hp']; ?>" maxlength="13" <?php echo $row['mt_type'] == 'IN' || $w == '' ? 'readonly' : '';?> required>
                    </td>
                </tr>
                <tr class="tr_out">
                   
                </tr>
                <tr>
                    <th>직책</th>
                    <td>
                        <select name="mt_grade" id="mt_grade" class="bansang_sel" required>
                            <option value="">선택</option>
                            <?php for($i=0;$grade_row = sql_fetch_array($grade_res);$i++){?>
                                <option value="<?php echo $grade_row['gr_id']?>" <?php echo get_selected($row['mt_grade'], $grade_row['gr_id']); ?>><?php echo $grade_row['gr_name']; ?></option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>메모</th>
                    <td colspan="3">
                        <textarea name="mt_memo" id="mt_memo" class="bansang_ipt ver2 full ta"><?php echo $row['mt_memo']; ?></textarea>
                    </td>
                </tr>
                
            </tbody>
        </table>
    </div>
    <div class="btn_fixed_top">
        <a href="./manage_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <input type="submit" value="저장" class="btn_submit btn btn_02" accesskey='s'>
    </div>
</form>

<script>
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


function mt_type_change(){
    var mtTypeSelect = document.getElementById("mt_type");
    var mtTypeValue = mtTypeSelect.options[mtTypeSelect.selectedIndex].value;

    // let html = `<th>비밀번호</th>
    //                 <td colspan="3"><input type="text" name="mb_password" id="mb_password" class="bansang_ipt ver2" value=""></td>`;
    $("#ho_tenant").val("");
    $("#ho_tenant_hp").val("");

    if(mtTypeValue == "OUT"){
        // $(".tr_out").html(html);
        $(".mt_p").show();
        $(".tr_dongho").css("display", "none");

        $("#dong_id").attr("required", false);
        $("#dong_id").attr("disabled", true);

        $("#ho_id").attr("required", false);
        $("#ho_id").attr("disabled", true);

        $("#ho_tenant").attr("readonly", false);
        $("#ho_tenant_hp").attr("readonly", false);
    }else{
        // $(".tr_out").html("");
        $(".mt_p").hide();
        $(".tr_dongho").css("display", "table-row");

        $("#dong_id").attr("required", true);
        $("#dong_id").attr("disabled", false);
        $("#ho_id").attr("required", true);
        $("#ho_id").attr("disabled", false);

        $("#ho_tenant").attr("readonly", true);
        $("#ho_tenant_hp").attr("readonly", true);
    }
}


function post_change(){
    var postIdSelect = document.getElementById("post_id");
    var postValue = postIdSelect.options[postIdSelect.selectedIndex].value;

    console.log(postValue);

    let html = `<option value="">선택</option>"`;


    let mt_type = $("#mt_type option:selected").val();
    let w = "<?php echo $w; ?>";

    //초기화
    if(w == "" && mt_type == "IN"){
        $("#ho_tenant").val("");
        $("#ho_tenant_hp").val("");
        $("#dong_id").html(html);
        $("#ho_id").html(html);
    }
    
    // $("#building_id").val("");
    // $("#building_name").val("");

    if(postValue != ""){
        $("#building_sch").addClass("ver2");
        $("#building_sch").attr("readonly", false);
        $("#building_sch").attr("placeholder", "단지명을 입력하세요.");
    }else{
        $("#building_sch").removeClass("ver2");
        $("#building_sch").attr("readonly", true);
        $("#building_sch").attr("placeholder", "지역을 먼저 선택하세요.");
    }
}


//단지 입력시 ajax
$(document).on("keyup", "#building_sch", function(){
    var post_id = $("#post_id option:selected").val();
    let sch_text = this.value;

    if(sch_text != ""){

        $(".sch_result_box").show();

        $.ajax({

        url : "./manage_form_sch_ajax.php", //ajax 통신할 파일
        type : "POST", // 형식
        data: { "building_name":sch_text, "post_id":post_id}, //파라미터 값
        success: function(msg){ //성공시 이벤트

            console.log('keyup',msg);
        
            $(".sch_result_box").html(msg); //.select_box2에 html로 나타내라..
        }

        });
    }else{
        $(".sch_result_box").html("");
    }
});

//검색버튼 클릭
function building_handler(){

    var post_id = $("#post_id option:selected").val();
    let building_text = $("#building_sch").val();

    if(post_id == ""){
        alert("지역을 먼저 선택하세요.");
        return false;
    }

    if(building_text == ""){
        alert("검색어를 입력해주세요.");
        return false;
    } 

    $.ajax({

    url : "./manage_form_sch_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "building_name":building_text, "post_id":post_id}, //파라미터 값
    success: function(msg){ //성공시 이벤트

        console.log(msg);
        $(".sch_result_box").html(msg);
        //$(".select_box2").html(msg); //.select_box2에 html로 나타내라..
    }

    });
}

//단지선택..
function building_select(id, name){
    //alert(id);

    $("#building_id").val(id);
    $("#building_name").val(name);

    let html = `<option value="">선택</option>"`;

    $.ajax({

    url : "./building_dong_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "building_id":id}, //파라미터 값
    success: function(msg){ //성공시 이벤트

        //console.log(msg);
        $("#dong_id").html(msg);
        $(".sch_result_box").hide();
        $("#building_sch").val("");

        //초기화
        $("#ho_tenant").val("");
        $("#ho_tenant_hp").val("");
        $("#ho_id").html(html);
    }

    });


    //building_post_ajax
    let sendData = {'building_id': id};

    $.ajax({
        type: "POST",
        url: "./building_post_ajax.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                alert(data.msg);
                return false;
            }else{
            
                $("#post_id").val(data.msg).change();
            
            }
        },
    });

}

//동 변경시
function dong_change(){
    var dongSelect = document.getElementById("dong_id");
    var dongValue = dongSelect.options[dongSelect.selectedIndex].value;

    //console.log('dongValue', dongValue);

    $.ajax({

    url : "./building_ho_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "dong_id":dongValue}, //파라미터 값
    success: function(msg){ //성공시 이벤트

        //console.log(msg);
        $("#ho_id").html(msg);

        $("#ho_tenant").val("");
        $("#ho_tenant_hp").val("");
    }

    });
}

//호수 변경시
function ho_change(){
    var hoSelect = document.getElementById("ho_id");
    var hoValue = hoSelect.options[hoSelect.selectedIndex].value;

    var mt_type = $("#mt_type option:selected").val();
    var info;

    //console.log('mt_type', mt_type);
    if(mt_type == "IN"){
        $.ajax({

        url : "./building_ho_info_ajax.php", //ajax 통신할 파일
        type : "POST", // 형식
        data: { "ho_id":hoValue}, //파라미터 값
        success: function(msg){ //성공시 이벤트

            console.log(msg.split("|"));
            info = msg.split("|");
            //$("#ho_id").html(msg);

            $("#mb_id").val(info[0]);
            $("#ho_tenant").val(info[1]);
            $("#ho_tenant_hp").val(info[2]);
        }

        });
    }
}



function fmanage_submit(f) {
    

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

