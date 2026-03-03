<?php
$sub_menu = "400100";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '등록';
}

$mng_infos = get_manger($member['mb_id']);

$g5['title'] .= '품의서 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;

$sql = "SELECT expense.*, post.post_name, building.building_name, dong.dong_name FROM a_expense_report as expense
        LEFT JOIN a_post_addr as post on expense.post_id = post.post_idx
        LEFT JOIN a_building as building on expense.building_id = building.building_id
        LEFT JOIN a_building_dong as dong on expense.dong_id = dong.dong_id
        WHERE expense.ex_id = {$ex_id}";
$row = sql_fetch($sql);

$verCl = "ver2";
$readonlys = "";

if($row['ex_status'] != 'N' && $w == 'u'){
    $readonlys = "readonly";
    $verCl = "";
}

$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

//부서
$sql_depart = "SELECT * FROM a_mng_department WHERE is_del = 0 ORDER BY is_prior asc, md_idx desc";
$depart_res = sql_query($sql_depart);

if($row['dong_id'] != '-1'){
    $dong_sqls = "and mng_t.dong_id = '{$row['dong_id']}' ";
}

$mng_sql = "SELECT mng_t.*, mng_g.gr_name, dong.dong_name, ho.ho_name FROM a_mng_team as mng_t
            LEFT JOIN a_mng_team_grade as mng_g on mng_t.mt_grade = mng_g.gr_id
            LEFT JOIN a_building_dong as dong on mng_t.dong_id = dong.dong_id
            LEFT JOIN a_building_ho as ho on mng_t.ho_id = ho.ho_id
            WHERE mng_t.post_id = '{$row['post_id']}' and mng_t.build_id = '{$row['building_id']}'   {$dong_sqls} and mng_t.is_del = 0 ORDER BY dong_name + 1 asc, ho_name + 1 asc, mt_id desc ";
// echo $mng_sql.'<br>';
$mng_res = sql_query($mng_sql);
$mng_res2 = sql_query($mng_sql);
$mng_res3 = sql_query($mng_sql);

$file_sql = "SELECT * FROM g5_board_file WHERE bo_table = 'expense' and wr_id = {$ex_id} and bf_file != '' order by bf_no asc";
$file_res = sql_query($file_sql);

if($member['mb_level'] == 9) $mng_info = get_manger($member['mb_id']);

//print_r2($mng_info);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql.'<br>';
    echo $mng_sql.'<br>';
    echo $file_sql.'<br>';
    //print_r2($row);
}

$disabled = $member['mb_level'] == '9' ? 'disabled' : '';

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>

<form name="fexpense" id="fexpense" action="./student_form_update.php" onsubmit="return fexpense_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="ex_id" id="ex_id" value="<?php echo $ex_id; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">품의서 정보</h2>
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
                    <td colspan="3">
                        <?php if($w == "u"){?>
                        <input type="hidden" name="post_id" id="post_id" value="<?php echo $row['post_id']; ?>">
                        <input type="text" name="post_name" id="post_name" class="bansang_ipt" value="<?php echo $row['post_name']; ?>" readonly>
                        <?php }else{ ?>
                            <select name="post_id" id="post_id" class="bansang_sel" required onchange="post_change();">
                                <option value="">전체</option>
                                <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                                <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($row['post_id'], $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
                            <?php }?>
                            </select>
                            <script>
                                function post_change(){
                                    var postSelect = document.getElementById("post_id");
                                    var postValue = postSelect.options[postSelect.selectedIndex].value;

                                    console.log('postValue', postValue);
                                    
                                    if(postValue != ""){
                                    }else{
                                        $("#building_id").val("");
                                        $("#building_name").val("");
                                    }
                                }
                            </script>
                        <?php }?>
                    </td>
                </tr>
                <tr>
                    <th>단지</th>
                    <td colspan="3">
                        <div class="sch_box_wrap ">
                            <div class="sch_box_left">
                                <div class="sch_result_box">
                                </div>
                                <!-- 검색어를 입력해주세요. -->
                                <input type="text" name="building_sch" id="building_sch" class="bansang_ipt ver2" size="50" placeholder="단지명을 입력하세요." >
                            </div>
                           
                        </div>
                        <input type="hidden" name="building_id" id="building_id" value="<?php echo $row['building_id']; ?>">
                        <input type="text" name="building_name" id="building_name" class="bansang_ipt ver2 mgt10" size="100" placeholder="선택한 단지가 보여집니다." readonly value="<?php echo $row['building_name']; ?>" required>
                       <script>
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

                        //단지선택..
                        function building_select(id, name){
                            //alert(id);

                            $("#building_id").val(id);
                            $("#building_name").val(name);

                            let html = `<option value="">선택</option>"`;

                            $.ajax({

                            url : "./building_dong_ajax.php", //ajax 통신할 파일
                            type : "POST", // 형식
                            data: { "building_id":id, 'all':'Y'}, //파라미터 값
                            success: function(msg){ //성공시 이벤트

                                //console.log(msg);
                                $("#dong_id").html(msg);

                                $(".sch_result_box").hide();
                                $("#building_sch").val("");
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

                       </script>
                    </td>
                </tr>
                <tr>
                    <th>동</th>
                    <td colspan="3">
                        <?php if($w == "u"){?>
                        <input type="hidden" name="dong_id" id="dong_id" class="bansang_ipt ver2" value="<?php echo $row['dong_id'];?>">
                        <input type="text" name="dong_name" id="dong_name" class="bansang_ipt" value="<?php echo $row['dong_id'] == '-1' ? '전체' : $row['dong_name'].'동'; ?>" readonly>
                        <?php }else{ ?>
                            <select name="dong_id" id="dong_id" class="bansang_sel" onchange="dong_change();" required>
                                <option value=""><?php echo $w == 'u' ? '동을' : '단지를'; ?> 선택해주세요.</option>
                            </select>
                            <script>
                                function dong_change(){
                                var dongSelect = document.getElementById("dong_id");
                                var dongValue = dongSelect.options[dongSelect.selectedIndex].value;

                                console.log('dongValue', dongValue);

                                let post_id = $("#post_id option:selected").val();
                                let building_id = $("#building_id").val();

                                $.ajax({

                                url : "./expense_approver_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "post_id":post_id, "build_id":building_id, "dong_id":dongValue}, //파라미터 값
                                success: function(msg){ //성공시 이벤트

                                   // console.log(msg);
                                    $("#ex_approver1").html(msg);
                                    $("#ex_approver2").html(msg);
                                    $("#ex_approver3").html(msg);
                                }

                                });
                            }
                            </script>
                        <?php }?>
                        
                    </td>
                </tr>
                <tr>
                    <th>부서</th>
                    <td>
                       
                        <input type="hidden" name="ex_department" id="ex_department" value="<?php echo $w == 'u' ? $row['ex_department'] : $mng_info['mng_department'];?>">
                        <input type="text" name="department_name" class="bansang_ipt" readonly value="<?php echo $w == 'u' ? get_department_name($row['ex_department']) : $mng_info['md_name'];?>">
                    </td>
                    <th>직급</th>
                    <td><input type="text" name="ex_grade" id="ex_grade" class="bansang_ipt" value="<?php echo $w == "u" ? $row['ex_grade'] : $mng_info['mg_name']; ?>" readonly></td>
                    
                </tr>
                <tr>
                    <th>작성자</th>
                    <td colspan="3"><input type="text" name="ex_name" id="ex_name" class="bansang_ipt" value="<?php echo $w == "u" ? $row['ex_name'] : $member['mb_name']; ?>" readonly></td>
                </tr>
                <tr>
                    <th>제목</th>
                    <td colspan="3">
                        <input type="text" name="ex_title" id="ex_title" class="bansang_ipt <?php echo $verCl; ?>" size="100" value="<?php echo $row['ex_title']; ?>" <?php echo $readonlys; ?>>
                    </td>
                </tr>
                <?php if($w == "u"){?>
                <tr>
                    <th>상태</th>
                    <td>
                        <?php
                        switch($row['ex_status']){
                            case "N":
                                $status_r = "승인대기";
                                break;
                            case "P":
                                $status_r = "승인중";
                                break;
                            case "E":
                                $status_r = "승인완료";
                                break;
                        }
                        ?>
                        <input type="text" name="ex_status_r" id="ex_status_r" class="bansang_ipt" value="<?php echo $status_r; ?>" readonly>
                    </td>
                </tr>
                <?php }?>
                <tr>
                    <th>
                        관리단 결재자1 (최초 결재자)
                    </th>
                    <td colspan='3'>
                        <?php if($row['ex_approver1'] != '' && $row['ex_apprval1_chk']){
                            $sign_off_mng_user1 = get_user($row['ex_approver1']);
                            $approval_info1 = get_mng_team($row['ex_approver1']);
                        ?>
                            <input type="hidden" name="ex_approver1" value="<?php echo $row['ex_approver1'];?>" class="bansang_ipt" readonly>
                            <input type="text" name="ex_approver_user1" value="<?php echo $approval_info1['mt_name'].' '.$approval_info1['gr_name'];?>" class="bansang_ipt" readonly>
                        <?php }else{ ?>
                        <select name="ex_approver1" id="ex_approver1" class="bansang_sel" required <?php echo $readonlys; ?>>
                            <option value="">관리단 선택</option>
                            <?php 
                            for($i=0;$mng_row = sql_fetch_array($mng_res);$i++){
                            
                                if($mng_row['mt_type'] == 'OUT'){
                                    $approval1_info = "외부인 ".$mng_row['mt_name']." ".$mng_row['gr_name'];
                                }else{
                                    $approval1_info = $mng_row['dong_name']."동 ".$mng_row['ho_name']."호 ".$mng_row['mt_name']." ".$mng_row['gr_name'];
                                }


                                ?>
                                <option value="<?php echo $mng_row['mb_id']; ?>" <?php echo get_selected($mng_row['mb_id'], $row['ex_approver1']); ?>><?php echo $approval1_info; ?></option>
                            <?php }?>
                        </select>
                        <?php }?>
                    </td>
                </tr>
                <tr>
                    <th>관리단 결재자2 (중간 결재자)</th>
                    <td colspan='3'>
                        <?php if($row['ex_approver2'] != '' && $row['ex_apprval2_chk']){
                            $sign_off_mng_user2 = get_user($row['ex_approver2']);
                            $approval_info2 = get_mng_team($row['ex_approver2']);
                        ?>
                            <input type="hidden" name="ex_approver2" value="<?php echo $row['ex_approver2'];?>" class="bansang_ipt" readonly>
                            <input type="text" name="ex_approver_user2" value="<?php echo $approval_info2['mt_name'].' '.$approval_info2['gr_name'];?>" class="bansang_ipt" readonly>
                        <?php }else{ ?>
                        <select name="ex_approver2" id="ex_approver2" class="bansang_sel" <?php echo $readonlys; ?>>
                            <option value="">관리단 선택</option>
                            <?php 
                            for($i=0;$mng_row = sql_fetch_array($mng_res2);$i++){
                                if($mng_row['mt_type'] == 'OUT'){
                                    $approval2_info = "외부인 ".$mng_row['mt_name']." ".$mng_row['gr_name'];
                                }else{
                                    $approval2_info = $mng_row['dong_name']."동 ".$mng_row['ho_name']."호 ".$mng_row['mt_name']." ".$mng_row['gr_name'];
                                }
                            ?>
                                <option value="<?php echo $mng_row['mb_id']; ?>" <?php echo get_selected($mng_row['mb_id'], $row['ex_approver2']); ?>><?php echo $approval2_info; ?></option>
                            <?php }?>
                        </select>
                        <?php }?>
                    </td>
                </tr>
                <tr>
                    <th>관리단 결재자3 (최종 결재자)</th>
                    <td colspan='3'>
                        <?php if($row['ex_approver3'] != '' && $row['ex_apprval3_chk']){
                            $sign_off_mng_user3 = get_user($row['ex_approver3']);
                            $approval_info3 = get_mng_team($row['ex_approver3']);
                        ?>
                            <input type="hidden" name="ex_approver3" value="<?php echo $row['ex_approver3'];?>" class="bansang_ipt" readonly>
                            <input type="text" name="ex_approver_user3" value="<?php echo $approval_info3['mt_name'].' '.$approval_info3['gr_name'];?>" class="bansang_ipt" readonly tabindex="-1">
                             
                        <?php }else{ ?>
                        <select name="ex_approver3" id="ex_approver3" class="bansang_sel" <?php echo $readonlys; ?>>
                            <option value="">관리단 선택</option>
                            <?php 
                            for($i=0;$mng_row = sql_fetch_array($mng_res3);$i++){
                                if($mng_row['mt_type'] == 'OUT'){
                                    $approval3_info = "외부인 ".$mng_row['mt_name']." ".$mng_row['gr_name'];
                                }else{
                                    $approval3_info = $mng_row['dong_name']."동 ".$mng_row['ho_name']."호 ".$mng_row['mt_name']." ".$mng_row['gr_name'];
                                }    
                            ?>
                                <option value="<?php echo $mng_row['mb_id']; ?>" <?php echo get_selected($mng_row['mb_id'], $row['ex_approver3']); ?>><?php echo $approval3_info; ?></option>
                            <?php }?>
                        </select>
                        <?php }?>
                    </td>
                </tr>
                <?php if($w == 'u' && $row['ex_status'] != 'E'){?>
                    <tr>
                        <th>즉시승인</th>
                        <td colspan='3'>
                            <p class="red mgb10">
                            즉시승인 기능은 결재자가 반상회앱을 통해 "결재 서명" 불가한 경우에 사용하기 바라며,<br>
                            즉시승인 적용 시 "서면 승인 완료" 된 것으로 입주민에게 공지됩니다.
                            </p>
                            <button type="button" onclick="direct_submit();" class="btn btn_03">즉시승인</button>
                            <script>

                                function direct_submit(){

                                    if(!confirm("품의서 즉시 승인 처리 하시겠습니까?\n승인 처리시 반상회 앱에 노출 됩니다.")) return false;

                                    console.log('즉시승인처리');

                                    let ex_id = "<?php echo $ex_id; ?>";

                                    var formData = new FormData();
                                    formData.append('ex_id', ex_id);

                                    $.ajax({
                                        type: "POST",
                                        url: "./expense_direct_submit.php",
                                        data: formData,
                                        cache: false,
                                        async: false,
                                        dataType: "json",
                                        contentType: false,
                                        processData: false,
                                        success: function(data) {
                                            console.log('data:::', data);
                                            if(data.result == false) { 
                                                alert(data.msg);
                                                //$(".btn_submit").attr('disabled', false);
                                                return false;
                                            }else{
                                                alert(data.msg);

                                                setTimeout(() => {
                                                    window.location.reload();
                                                }, 700);
                                            }
                                        },
                                        error:function(e){
                                            alert(e);
                                        }
                                    });
                                }
                            </script>
                        </td>
                    </tr>
                <?php }?>
                <tr>
                    <th>사진 첨부</th>
                    <td colspan='3'>
                        <?php echo help("사진 첨부는 8장까지 등록 가능합니다.");?>
                        <div class="ipt_box">
                            <div class="img_upload_wrap">
                                <?php if($row['ex_status'] == 'N' || $w == ''){?>
                                <div class="img_upload_box ver1">
                                    <input type="file" name="img_up[]" id="img_up" onchange="addFile(this);" multiple accept="image/*">
                                    <label for="img_up">
                                        <img src="/images/file_plus.svg" alt="">
                                    </label>
                                </div>
                                <?php }?>
                                <?php if($w == "u" && $file_res){?>
                                    <?php for($i=0;$file_row = sql_fetch_array($file_res);$i++){?>
                                        <div class="img_upload_box_wrapper8">
                                            <div class="img_upload_box ver4 filebox">
                                                <input type="file" name="img_up<?php echo $i + 1;?>" id="img_up<?php echo $i + 1;?>" accept="image/*" onchange="fileUp(this, 'img_up<?php echo $i + 1;?>', <?php echo $i; ?>, 'before')">
                                                
                                                <img src="/data/file/expense/<?php echo $file_row['bf_file']; ?>" class="img_up<?php echo $i + 1;?>" alt="" onclick="bigSize('/data/file/expense/<?php echo $file_row['bf_file']; ?>')">
                                       
                                                <?php if($row['ex_status'] == 'N'){?>
                                                <div class="file_del">
                                                    <input type="checkbox" name="ex_file_del[<?php echo $file_row['bf_no'];?>]" id="ex_file_del<?php echo $i+1;?>" class="ex_file_del" value="1">
                                                    <label for="ex_file_del<?php echo $i+1;?>">삭제</label>
                                                </div>
                                                <?php }?>
                                            </div>
                                            <?php if($row['ex_status'] == 'N'){?>
                                            <label class="img_labels" for="img_up<?php echo $i + 1;?>">
                                            이미지 첨부
                                            </label>
                                            <?php }?>
                                        </div>
                                    <?php }?>
                                <?php }?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>기타사항</th>
                    <td colspan='3'>
                        <?php echo editor_html('ex_content', get_text(html_purifier($row['ex_content']), 0)); ?>
                    </td>
                </tr>
                <?php if($w == 'u' && $row['ex_status'] != 'N'){?>
                    <tr>
                        <th>
                            관리단 결재자 1 (최초결재자)
                            <?php 
                            $approval_info1 = get_mng_team($row['ex_approver1']);
                            
                            $approval_text1 = get_dong($approval_info1['dong_id'])['dong_name'].' '.$approval_info1['gr_name'];
                            ?>
                            <p><?php echo '-'.$approval_text1; ?></p>
                        </th>
                        <td colspan='3'>
                            <?php
                            //서명이미지
                            $sql_sign_off_img = "SELECT soi.*, sig.fil_name FROM a_expense_report_sign as soi
                            LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                            WHERE soi.is_cancel = 0 and soi.ex_id = '{$ex_id}' and apprval_type = 'apprval1'";

                            $sign_img_row = sql_fetch($sql_sign_off_img);
                            ?>
                            <?php if($sign_img_row){?>
                            <div class="mng_sign_img_box">
                                <img src="/data/file/approval_expense/<?php echo $sign_img_row['fil_name']; ?>" alt="" class="mgt10">
                            </div>
                            <?php }else{ ?>
                                <?php if($row['ex_status_d'] == 'Y'){?>
                                    <!-- <?php echo $row['ex_status_d_at']; ?> -->
                                     서면 서명 완료
                                <?php }?>
                            <?php }?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            관리단 결재자 2 (중간 결재자)
                            <?php 
                            $approval_info2 = get_mng_team($row['ex_approver2']);
                            $approval_text2 = get_dong($approval_info2['dong_id'])['dong_name'].' '.$approval_info2['gr_name'];
                            ?>
                            <p><?php echo '-'.$approval_text2; ?></p>        
                        </th>
                        <td colspan='3'>
                            <?php
                            //서명이미지
                            $sql_sign_off_img2 = "SELECT soi.*, sig.fil_name FROM a_expense_report_sign as soi
                            LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                            WHERE soi.is_cancel = 0 and soi.ex_id = '{$ex_id}' and apprval_type = 'apprval2'";

                            // echo $sql_sign_off_img;
                            $sign_img_row2 = sql_fetch($sql_sign_off_img2);
                            ?>
                             <?php if($sign_img_row2){?>
                            <div class="mng_sign_img_box">
                                <img src="/data/file/approval_expense/<?php echo $sign_img_row2['fil_name']; ?>" alt="" class="mgt10">
                            </div>
                            <?php }else{ ?>
                                <?php if($row['ex_status_d'] == 'Y'){?>
                                    <!-- <?php echo $row['ex_status_d_at']; ?> -->
                                     서면 서명 완료
                                <?php }?>
                            <?php }?>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            관리단 결재자 3 (최종 결재자)
                            <?php 
                            $approval_info3 = get_mng_team($row['ex_approver3']);
                            $approval_text3 = get_dong($approval_info3['dong_id'])['dong_name'].' '.$approval_info3['gr_name'];
                            ?>
                            <p><?php echo '-'.$approval_text3; ?></p>  
                        </th>
                        <td colspan='3'>
                            <?php
                            //서명이미지
                            $sql_sign_off_img3 = "SELECT soi.*, sig.fil_name FROM a_expense_report_sign as soi
                            LEFT JOIN a_signature as sig ON soi.sg_idx = sig.sg_idx
                            WHERE soi.is_cancel = 0 and soi.ex_id = '{$ex_id}' and apprval_type = 'apprval3'";

                            // echo $sql_sign_off_img;
                            $sign_img_row3 = sql_fetch($sql_sign_off_img3);
                            ?>
                            <?php if($sign_img_row3){?>
                            <div class="mng_sign_img_box">
                                <img src="/data/file/approval_expense/<?php echo $sign_img_row3['fil_name']; ?>" alt="" class="mgt10">
                            </div>
                            <?php }else{ ?>
                                <?php if($row['ex_status_d'] == 'Y'){?>
                                    <!-- <?php echo $row['ex_status_d_at']; ?> -->
                                     서면 서명 완료
                                <?php }?>
                            <?php }?>
                        </td>
                    </tr>
                <?php }?>
            </tbody>
        </table>
    </div>
    <?php if($w == "u" && $row['ex_status'] != 'N'){
    
    $sql_depart2 = "SELECT * FROM a_mng_department WHERE is_del = 0 ORDER BY is_prior asc, md_idx desc";
    $depart_res2 = sql_query($sql_depart2);
    ?>
    <div class="tbl_frm01 tbl_wrap">
        <div class="h2_frm_wraps">
            <h2 class="h2_frm">시행자</h2>
            <button type="button" onclick="enforce_change();" class="btn btn_03">시행자 변경</button>
        </div>
        <table>
            <tbody>
                <tr>
                    <th>부서</th>
                    <td>
                        <select name="enforce_deaprt" id="enforce_deaprt" class="bansang_sel" onchange="en_department_change();" required>
                            <option value="">선택하세요.</option>
                            <?php for($i=0;$depart_row = sql_fetch_array($depart_res2);$i++){?>
                                <option value="<?php echo $depart_row['md_idx']; ?>" <?php echo get_selected($row['enforce_deaprt'], $depart_row['md_idx']); ?>><?php echo $depart_row['md_name']; ?></option>
                            <?php }?>
                        </select>
                        <script>
                            function en_department_change(){
                                var departmentSelect = document.getElementById("enforce_deaprt");
                                var departmentValue = departmentSelect.options[departmentSelect.selectedIndex].value;

                                console.log('departmentValue', departmentValue);

                                $("#enforce_id").html("<option value=''>선택하세요.</opiton>")

                                $.ajax({

                                url : "./expense_grade_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "department":departmentValue}, //파라미터 값
                                success: function(msg){ //성공시 이벤트

                                    console.log(msg);
                                   $("#enforce_grade").html(msg);
                                }

                                });
                            }
                        </script>
                    </td>
                </tr>
                <tr>
                    <th>직급</th>
                    <td>
                        <?php
                        $sql = "SELECT mng.*, grade.mg_name FROM a_mng as mng
                        LEFT JOIN a_mng_grade as grade on mng.mng_grades = grade.mg_idx
                        WHERE mng.mng_department = '{$row['enforce_deaprt']}' GROUP BY mng.mng_grades ORDER BY mng.mng_grades desc, mng.mng_idx desc";
                        $res = sql_query($sql);

                        ?>
                        <select name="enforce_grade" id="enforce_grade" class="bansang_sel" onchange="en_grade_change();" required>
                            <option value="">선택하세요.</option>
                            <?php for($i=0;$gr_row = sql_fetch_array($res);$i++){?>
                            <option value="<?php echo $gr_row['mng_grades']; ?>" <?php echo get_selected($row['enforce_grade'], $gr_row['mng_grades']); ?>><?php echo $gr_row['mg_name']; ?></option>
                            <?php }?>
                        </select>
                        <script>
                            function en_grade_change(){
                                var gradeSelect = document.getElementById("enforce_grade");
                                var gradeValue = gradeSelect.options[gradeSelect.selectedIndex].value;

                                let departValue = $("#enforce_deaprt option:selected").val();

                                $.ajax({

                                url : "./enforce_name_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "gradeValue":gradeValue, "departValue":departValue}, //파라미터 값
                                success: function(msg){ //성공시 이벤트

                                    //console.log(msg);
                                    $("#enforce_id").html(msg);
                                }

                                });
                            }
                        </script>
                    </td>
                </tr>
                <tr>
                    <th>시행자</th>
                    <td>
                        <?php
                        $sql = "SELECT mng.*, grade.mg_name FROM a_mng as mng
                        LEFT JOIN a_mng_grade as grade on mng.mng_grades = grade.mg_idx
                        WHERE mng.mng_department = '{$row['enforce_deaprt']}' and mng.mng_grades = '{$row['enforce_grade']}' ORDER BY mng.mng_grades desc, mng.mng_idx desc";
                        //echo $sql;
                        $res = sql_query($sql);
                        ?>
                        <select name="enforce_id" id="enforce_id" class="bansang_sel" required>
                            <option value="">선택하세요.</option>
                            <?php for($i=0;$ei_row = sql_fetch_array($res);$i++){?>
                            <option value="<?php echo $ei_row['mng_id']; ?>" <?php echo get_selected($row['enforce_id'], $ei_row['mng_id']); ?>><?php echo $ei_row['mng_name'].' '.$ei_row['mg_name']; ?></option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php }?>
    <div class="btn_fixed_top">
        <a href="./expense_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <?php if($w == 'u'){?>
            <button type="button" onclick="print_info('<?php echo $ex_id;?>');" class="btn btn_02">인쇄</button>
        <?php }?>

        <!-- 등급 A, B, 작성자만 승인대기, 승인 중일 때 삭제가능 -->
        <?php if($w == 'u' && $row['ex_status'] != 'E' && ($mng_infos['mng_certi'] == 'A' || $mng_infos['mng_certi'] == 'B' || $mng_info['mng_id'] == $row['wid'])){?>
            <button type="button" onclick="expense_del();" class="btn btn_01">삭제</button>
        <?php }?>

        <!-- 결재상태가 승인대기때 수정가능, 아직 등록전 일 때 -->
        <?php if($row['ex_status'] == 'N' || $w == ''){?>
            <?php if($mng_infos['mng_certi'] == 'A' || $mng_infos['mng_certi'] == 'B' || $mng_info['mng_id'] == $row['wid'] || $w == ''){?>
           
            <!-- 등급 A, B, 작성자만 승인대기중일 때 수정가능 -->
            <button type="button" onclick="expense_submit();" class="btn btn_03"><?php echo $w == 'u' ? '수정' : '저장';?></button>
            <?php }?>
        <?php }?>
    </div>
</form>

<!-- 이미지 팝업 -->
<div id="big_size_pop">
    <div class="od_cancel_inner"></div>
	<button type="button" class="big_size_pop_x" onclick="bigSizeOff();">
		<span></span>
		<span></span>
	</button>
	<div class="od_cancel_cont">
		<img src="" id="big_img" alt="확대 보기">
	</div>
</div>

<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>품의서를 등록 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>

<script>
function print_info(ex_id) // 회원 엑셀 업로드를 위하여 추가
{ 

    var opt = "width=810,height=1200,left=10,top=10"; 
    var url = "./expense_print_sample.php?ex_id=" + ex_id;

    window.open(url, "win_news", opt); 

    return false; 

}


function bigSize(url){
	const windowHeight = window.innerHeight;
	$("#big_size_pop .od_cancel_cont").css("height", `${windowHeight}px`);
	$("#big_img").attr("src", url);
	$("#big_size_pop").show();
}

function bigSizeOff(){
	$("#big_size_pop").hide();
	$("#big_img").attr("src", "");
}

function expense_del(){
    if (!confirm("해당 품의서를 정말 삭제하시겠습니까?")) {
        return false;
    }

    let ex_id = "<?php echo $ex_id; ?>";

    var formData = new FormData();
    formData.append('ex_id', ex_id);

    $.ajax({
        type: "POST",
        url: "./expense_del_update.php",
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        contentType: false,
        processData: false,
        success: function(data) {
            console.log('data:::', data);
            if(data.result == false) { 
                alert(data.msg);
                //$(".btn_submit").attr('disabled', false);
                return false;
            }else{
                alert(data.msg);

                setTimeout(() => {
                    window.location.replace("./expense_list.php?<?php echo $qstr ?>");
                }, 700);
            }
        },
        error:function(e){
            alert(e);
        }
    });
}

//시행자 등록
function enforce_change(){
    let ex_id = "<?php echo $ex_id; ?>";
    let enforce_deaprt = $("#enforce_deaprt option:selected").val();
    let enforce_grade = $("#enforce_grade option:selected").val();
    let enforce_id = $("#enforce_id option:selected").val();


    var formData = new FormData();
    formData.append('ex_id', ex_id);
    formData.append('enforce_deaprt', enforce_deaprt);
    formData.append('enforce_grade', enforce_grade);
    formData.append('enforce_id', enforce_id);

    $.ajax({
        type: "POST",
        url: "./expense_enforce_change.php",
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        contentType: false,
        processData: false,
        success: function(data) {
            console.log('data:::', data);
            if(data.result == false) { 
                alert(data.msg);
                $("#" + data.data).focus();
                //$(".btn_submit").attr('disabled', false);
                return false;
            }else{
                alert(data.msg);

                setTimeout(() => {
                    location.reload();
                }, 1000);
            }
        },
        error:function(e){
            alert(e);
        }
    });
   
}

// 파일첨부
var fileNo = 0;
var filesArr = new Array();
let fileImg = new Array();
var imgdatas = '';
var attFileCnt1 = 0;
var attFileCnt2 = document.querySelectorAll('.filebox').length; //이미 추가된 파일

//이미 추가된 파일이 있는 경우 빈값입력
for(var j=0;j<attFileCnt2;j++){

    //console.log("fileArr index", j);
    filesArr.splice(j, 0, new Blob([''], { type: 'application/octet-stream' }));

}

function addFile(obj){
    var maxFileCnt = 8;   // 첨부파일 최대 개수
    var attFileCnt = document.querySelectorAll('.filebox').length;    // 기존 추가된 첨부파일 개수
    var remainFileCnt = maxFileCnt - attFileCnt;    // 추가로 첨부가능한 개수
    var curFileCnt = obj.files.length;  // 현재 선택된 첨부파일 개수

    let cnt = attFileCnt;

    // 첨부파일 개수 확인
    if (curFileCnt > remainFileCnt) {
        alert("첨부파일은 최대 " + maxFileCnt + "개 까지 첨부 가능합니다.");
    } else {
        for (const file of obj.files) {

            console.log('file', file);
            // 첨부파일 검증
            if (validation(file)) {
                // 파일 배열에 담기
                var reader = new FileReader();
                reader.onload = function (e) {
                    filesArr.push(file);

                    // let previewHTML = `
                    //     <div class="filebox">
                    //         <img src="${e.target.result}" alt="">
                    //     </div>
                    // `;
                    console.log('cnt', cnt);

                    let previewHTML = `
                        <div class="img_upload_box_wrapper8">
                            <div class="img_upload_box ver4 filebox">
                                <input type="file" name="img_up${cnt}" id="img_up${cnt + 1}" accept="image/*" onchange="fileUp(this, 'img_up${cnt + 1}', ${cnt}, 'before')">
                                <label for="img_up${cnt + 1}">
                                    <img src="${e.target.result}" class="img_up${cnt + 1}" alt="">
                                </label>
                            </div>
                            <button type="button" class="img_del_btn" onclick="file_dels(this);">
                                삭제
                            </button>
                        </div>
                    `;

                    cnt++;

                    $('.img_upload_wrap').append(previewHTML);
                };
                reader.readAsDataURL(file);

                attFileCnt22 = document.querySelectorAll('.filebox').length + curFileCnt;

                // console.log('attFileCnt2', attFileCnt2 + curFileCnt);

                // if(attFileCnt22 == maxFileCnt){
                //     $(".work_img_up1").hide();
                // }
            } else {
                continue;
            }

            
        }
    }

    console.log('fire arr after add', filesArr);
    // 초기화
    //document.querySelector("input[type=file]").value = "";
}


function file_dels(btn) {
    // 클릭된 버튼이 포함된 wrapper 찾기
    const wrapper = btn.closest('.img_upload_box_wrapper8');

    // 해당 요소의 index를 구함 (현재 렌더링된 순서 기준)
    const wrappers = Array.from(document.querySelectorAll('.img_upload_box_wrapper8'));
    const index = wrappers.indexOf(wrapper);

    if (index !== -1) {
        // 1. filesArr에서 해당 파일 제거
        filesArr.splice(index, 1);

        // 2. DOM에서 제거
        wrapper.remove();

        // 3. 남은 요소들을 다시 정렬
        const newWrappers = document.querySelectorAll('.img_upload_box_wrapper8');
        newWrappers.forEach((el, i) => {
            const fileInput = el.querySelector('input[type="file"]');
            const label = el.querySelector('label');
            const img = el.querySelector('img');

            // ID, name, class 재설정
            fileInput.name = `img_up${i}`;
            fileInput.id = `img_up${i + 1}`;
            fileInput.setAttribute('onchange', `fileUp(this, 'img_up${i + 1}', ${i}, 'before')`);
            label.setAttribute('for', `img_up${i + 1}`);
            img.className = `img_up${i + 1}`;
        });

        // 4. 업로드 버튼 다시 보이게 (선택 사항)
        // if (filesArr.length < 7) {
        //     $(".work_img_up1").show();
        // }

        console.log('fire arr after delete', filesArr);
    }
}

function fileUp(input, type, index, datas){
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('.' + type).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);

        //filesArr.push(input.files[0]);
    
        filesArr[index] = input.files[0];

        console.log('file up file arr', filesArr)
      
    }
}

/* 첨부파일 검증 */
function validation(obj){
    const fileTypes = ['application/pdf', 'image/gif', 'image/jpeg', 'image/png', 'image/bmp', 'image/tif', 'image/heic'];
    //'application/haansofthwp', 'application/x-hwp'
    if (obj.name.length > 100) {
        alert("파일명이 100자 이상인 파일은 제외되었습니다.");
        return false;
    } else if (obj.size > (100 * 1024 * 1024)) {
        alert("최대 파일 용량인 100MB를 초과한 파일은 제외되었습니다.");
        return false;
    } else if (obj.name.lastIndexOf('.') == -1) {
        alert("확장자가 없는 파일은 제외되었습니다.");
        return false;
    } else if (!fileTypes.includes(obj.type)) {
        alert("첨부가 불가능한 파일은 제외되었습니다.");
        return false;
    } else {
        return true;
    }
}

function expense_submit(){

    $("#building_info_pop").show();

 
        let w_status = "<?php echo $w; ?>";
        let post_id;
        let building_id = $("#building_id").val();
        let dong_id;

        if(w_status == "u"){
            post_id = $("#post_id").val();
            dong_id = $("#dong_id").val();
        }else{
            post_id = $("#post_id option:selected").val();
            dong_id = $("#dong_id option:selected").val();
        }
        
        let ex_name = $("#ex_name").val();
        let ex_department = $("#ex_department").val();
        let ex_grade = $("#ex_grade").val();
        let ex_title = $("#ex_title").val();
        let ex_approver1 = $("#ex_approver1 option:selected").val();
        let ex_approver2 = $("#ex_approver2 option:selected").val();
        let ex_approver3 = $("#ex_approver3 option:selected").val();
        let ex_content = $("#ex_content").val();
    
        // let enforce_deaprt = $("#enforce_deaprt option:selected").val();
        // let enforce_grade = $("#enforce_grade option:selected").val();
        // let enforce_id = $("#enforce_id option:selected").val();

        let ex_id = $("#ex_id").val();

        var formData = new FormData();
        formData.append('w', w_status);
        formData.append('post_id', post_id);
        formData.append('building_id', building_id);
        formData.append('dong_id', dong_id);
        formData.append('ex_name', ex_name);
        formData.append('ex_department', ex_department);
        formData.append('ex_grade', ex_grade);
        formData.append('ex_title', ex_title);
        formData.append('ex_approver1', ex_approver1);
        formData.append('ex_approver2', ex_approver2);
        formData.append('ex_approver3', ex_approver3);
        formData.append('ex_content', ex_content);

        // formData.append('enforce_deaprt', enforce_deaprt);
        // formData.append('enforce_grade', enforce_grade);
        // formData.append('enforce_id', enforce_id);

        formData.append('ex_id', ex_id);
        

        for (var i = 0; i < filesArr.length; i++) {
            // 삭제되지 않은 파일만 폼데이터에 담기
            formData.append("expense_file[]", filesArr[i]);
        }

        // 파일삭제 체크된 삭제 항목 추가
        $("input[name^=ex_file_del]").each(function() {
            if($(this).is(":checked") == true){
                formData.append("ex_file_del[]", '1'); // 체크된 파일의 번호 추가
            }else{
                formData.append("ex_file_del[]", '0'); // 체크된 파일의 번호 추가
            }
        });

        
    setTimeout(() => {
        $.ajax({
            type: "POST",
            url: "./expense_form_update.php",
            data: formData,
            cache: false,
            async: false,
            dataType: "json",
            contentType: false,
            processData: false,
            success: function(data) {
                console.log('data:::', data);
                if(data.result == false) { 
                    alert(data.msg);
                    $("#" + data.data).focus();
                    $("#building_info_pop").hide();
                    //$(".btn_submit").attr('disabled', false);
                    return false;
                }else{
                    alert(data.msg);

                    $("#building_info_pop").hide();

                    setTimeout(() => {
                        if(w_status == 'u'){
                            location.reload();
                        }else{
                            window.location.href = './expense_form.php?w=u&ex_id=' + data.data;
                        }
                        
                    }, 1000);
                }
            },
            error:function(e){
                alert(e);
            }
        });
    }, 50);
   
}

function fexpense_submit(f) {
    

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';
?>