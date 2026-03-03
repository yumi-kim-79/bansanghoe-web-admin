<?php
$sub_menu = "100200";
require_once './_common.php';

$html_title = '';
if($w == 'u'){
    $html_title = '확인';
}else{
    $html_title = '발송';
}

$g5['title'] .= "푸시 ". $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;


$sql = "SELECT * FROM a_push_send_history
        WHERE ph_idx = {$push_id}";
$row = sql_fetch($sql);

$year = date('Y');
$oneGradeYear = ($year - 7).'-12-31';
$nineGradeYear = ($year - 15).'-01-01';
//echo $eightGradeYear;


if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;

    //print_r2($row);
}

$disabled = $member['mb_level'] == '9' ? 'disabled' : '';

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js

?>

<form name="fpush" id="fpush" action="./push_form_update.php" onsubmit="return fpush_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="cal_code" value="<?php echo $cal_code; ?>">
    <input type="hidden" name="st_idx" value="<?php echo $row['st_idx']; ?>">
    <!-- <?php if($row['st_status'] && $w == 'u'):?>
    <input type="hidden" name="st_status" value="<?php echo $row['st_status']; ?>">
    <?php endif; ?> -->

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">푸시 정보</h2>
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col class="grid_4">
                <col>
            </colgroup>
            <tbody>
                <?php if($w == 'u'){?>
                <tr>
                    <th>회원 분류</th>
                    <td>
                        <?php echo $row['recv_id_type'] == 'user' ? '단지' : '사내 관리자';?>
                    </td>
                </tr>
                <tr>
                    <th>푸시발송인원</th>
                    <td>
                        <?php echo $row['ph_cnt'].'명';?>
                        <!-- <button type="button" class="btn btn_02" style="margin-left:10px">확인하기</button> -->
                    </td>
                </tr>
                <?php }?>
                <tr>
                    <th>제목</th>
                    <td><input type="text" name="push_title" id="push_title" class="bansang_ipt ver2 full" value="<?php echo $row['push_title']; ?>" required></td>
                </tr>
                <tr>
                    <th>내용</th>
                    <td>
                        <textarea name="push_content" id="push_content" class="bansang_ipt ver2 full ta" required><?php echo $row['push_content']; ?></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php if($w == ''){?>
    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm">푸시 발송하기</h2>
        <input type="hidden" name="select_push_ho" id="select_push_ho" value="">
        <input type="hidden" name="select_push_mng" id="select_push_mng" value="">
        <div class="radio_chk_wrap mgt10">
            <div class="radio_chk_box">
                <input type="radio" name="push_mem_type" id="push_mem_type1" value="user" checked>
                <label for="push_mem_type1">단지</label>
            </div>
            <div class="radio_chk_box">
                <input type="radio" name="push_mem_type" id="push_mem_type2" value="sm">
                <label for="push_mem_type2">사내 관리자</label>
            </div>
        </div>
        <script>
            $('input[name="push_mem_type"]').on('change', function() {
                var selectedValue = $('input[name="push_mem_type"]:checked').val();	
                
                console.log(selectedValue);
                $("#select_push_ho").val("");
                $("#select_push_mng").val("");

                if(selectedValue == 'user'){
                    $(".building_push_box").show();
                    $(".mng_push_box").hide();
                }else{
                    $(".building_push_box").hide();
                    $(".mng_push_box").show();
                }
            });
        </script>

        <div class="building_push_box">
            <?php
            $building_list = "SELECT * FROM a_building WHERE is_del = 0 ORDER BY building_name ASC";
            $building_res = sql_query($building_list);
            ?>
            <div class="push_mem_building_select_wrap mgt10 ipt_box flex_ver">
                <select name="building_id" id="building_id" class="bansang_sel" onchange="building_change();">
                    <option value="">단지 전체</option>
                    <?php while($building_row = sql_fetch_array($building_res)){?>
                        <option value="<?php echo $building_row['building_id']; ?>"><?php echo $building_row['building_name']; ?></option>
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
                <select name="dong_id" id="dong_id" class="bansang_sel" onchange="dong_change();">
                    <option value="">동 전체</option>
                </select>
                <script>
                    function dong_change(){
                        var dongSelect = document.getElementById("dong_id");
                        var dongValue = dongSelect.options[dongSelect.selectedIndex].value;

                        console.log('dongValue', dongValue);

                        $.ajax({

                        url : "./building_ho_ajax.php", //ajax 통신할 파일
                        type : "POST", // 형식
                        data: { "dong_id":dongValue}, //파라미터 값
                        success: function(msg){ //성공시 이벤트

                            //console.log(msg);
                            $("#ho_id").html(msg);
                        }

                        });
                    }
                </script>
                <select name="ho_id" id="ho_id" class="bansang_sel">
                    <option value="">호수 전체</option>
                </select>
                <button type="button" onclick="building_sch();" class="bansang_btns ver1">검색</button>

                <script>
                    function building_sch(){

                        // alert('뭐냐')
                        //$(".push_send_wraps").html("");
                        let select_push_ho = $("#select_push_ho").val();
                        let building_id = $("#building_id option:selected").val();
                        let dong_id = $("#dong_id option:selected").val();
                        let ho_id = $("#ho_id option:selected").val();

                        $.ajax({

                        url : "./push_building_list.php", //ajax 통신할 파일
                        type : "POST", // 형식
                        data: { "select_push_ho":select_push_ho, "building_id":building_id, "dong_id":dong_id, "ho_id":ho_id}, //파라미터 값
                        success: function(msg){ //성공시 이벤트
                            console.log(msg);

                            $(".push_list_wraps").html(msg);
                        }

                        });
                    }
                </script>
            </div>
        </div>
        <div class="mng_push_box">
        <?php
        $deaptment_list = "SELECT * FROM a_mng_department WHERE is_del = 0 ORDER BY is_prior ASC, md_idx asc";
        $deaptment_res = sql_query($deaptment_list);
        ?>
            <div class="push_mem_building_select_wrap mgt10 ipt_box flex_ver">
                <select name="mng_department" id="mng_department" class="bansang_sel" onchange="depart_change();">
                    <option value="">부서 전체</option>
                    <?php while($deaptment_row = sql_fetch_array($deaptment_res)){?>
                        <option value="<?php echo $deaptment_row['md_idx'];?>"><?php echo $deaptment_row['md_name'];?></option>
                    <?php }?>
                </select>
                <script>
                    function depart_change(){
                        var departSelect = document.getElementById("mng_department");
                        var departValue = departSelect.options[departSelect.selectedIndex].value;

                        console.log('departValue', departValue);

                        $.ajax({

                        url : "./push_form_depart_change.php", //ajax 통신할 파일
                        type : "POST", // 형식
                        data: { "mng_department":departValue}, //파라미터 값
                        success: function(msg){ //성공시 이벤트

                            //console.log(msg);
                            $("#mng_id").html(msg);
                        }

                        });
                    }
                </script>
                <select name="mng_id" id="mng_id" class="bansang_sel">
                    <option value="">인원 전체</option>
                </select>
                <button type="button" onclick="mng_sch();" class="bansang_btns ver1">검색</button>
                <script>
                    function mng_sch(){
                        //$(".push_mng_send_wraps").html("");

                        let mng_department = $("#mng_department option:selected").val();
                        let mng_id = $("#mng_id option:selected").val();

                        $.ajax({

                        url : "./push_mng_ist.php", //ajax 통신할 파일
                        type : "POST", // 형식
                        data: { "mng_department":mng_department, "mng_id":mng_id}, //파라미터 값
                        success: function(msg){ //성공시 이벤트
                            console.log(msg);

                            $(".push_mng_list_wraps").html(msg);
                        }

                        });
                    }
                </script>
            </div>
        </div>
        <div class="mng_push_box">
            <div class="push_send_wrap mgt10">
                <div class="push_send_box">
                    <div class="push_send_tit_wrap">
                        <p class="push_send_label">인원 리스트</p>
                    
                        <button type="button" onclick="push_send_mng_add();" class="bansang_btns ver1">추가</button>

                        <script>
                            function push_send_mng_add(){
                                var push_chk_arr = [];

                                $("input[name=mng_checked]:checked").each(function(){
                                    var chk = $(this).val();

                                    push_chk_arr.push(chk);
                                });

                                //단지 선택안하면 에러
                                if(push_chk_arr == ""){
                                    alert("매니저를 한명이상 선택해주세요.");
                                    return false;
                                }

                                let select_push_mng = $("#select_push_mng").val();

                                if(select_push_mng == ""){
                                    $("#select_push_mng").val(push_chk_arr.join(","));
                                }else{
                                    $("#select_push_mng").val(select_push_mng + "," + push_chk_arr.join(","));
                                }

                                select_push_mng = $("#select_push_mng").val();

                                $.ajax({

                                url : "./push_mng_send_list_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "select_push_mng":select_push_mng}, //파라미터 값
                                success: function(msg){ //성공시 이벤트
                                    console.log(msg);

                                    $(".push_mng_send_wraps").html(msg);
                                }

                                });

                                $.ajax({

                                url : "./push_mng_send_list_del_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "select_push_mng":select_push_mng}, //파라미터 값
                                success: function(msg){ //성공시 이벤트
                                    console.log(msg);

                                    $(".push_mng_list_wraps").html(msg);
                                }

                                });
                            }
                        </script>
                    </div>
                    <div class="push_send_list_wrap mgt10">
                        <div class="push_send_list_tb">
                            <div class="push_send_list_tr push_send_list_thead">
                                <div class="push_send_list_lefts">
                                    <div class="push_send_td">
                                        <input type="checkbox" name="mng_checked_all" id="mng_checked_all" value="1">
                                    </div>
                                </div>
                                <div class="push_send_list_rights ver2">
                                    <div class="push_send_td push_send_th">부서명</div>
                                    <div class="push_send_td push_send_th">직급</div>
                                    <div class="push_send_td push_send_th">이름</div>
                                </div>
                            </div>
                            <?php
                            $mng_sql = "SELECT mng.*, md.md_name, mg.mg_name FROM a_mng as mng
                                        LEFT JOIN a_mng_department as md ON mng.mng_department = md.md_idx
                                        LEFT JOIN a_mng_grade as mg ON mng.mng_grades = mg.mg_idx
                                        WHERE mng.is_del = 0 and mng_status = 1 ORDER BY mng.mng_grades desc, mng.mng_idx desc";
                            $mng_res = sql_query($mng_sql);
                            
                            ?>
                            <div class="push_send_list_tbody_wrap push_mng_list_wraps">
                                <?php for($i=0;$mng_row = sql_fetch_array($mng_res);$i++){?>
                                <div class="push_send_list_tr push_send_list_tbody">
                                    <div class="push_send_list_lefts">
                                        <div class="push_send_td">
                                            <input type="checkbox" name="mng_checked" class="mng_checked" value="<?php echo $mng_row['mng_id']; ?>">
                                        </div>
                                    </div>
                                    <div class="push_send_list_rights ver2">
                                        <div class="push_send_td"><?php echo $mng_row['md_name']; ?></div>
                                        <div class="push_send_td"><?php echo $mng_row['mg_name']; ?></div>
                                        <div class="push_send_td"><?php echo $mng_row['mng_name']; ?></div>
                                    </div>
                                </div>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="push_send_box">
                    <div class="push_send_tit_wrap">
                        <p class="push_send_label">발송 리스트</p>
                        
                        <button type="button" onclick="push_send_mng_remove();" class="bansang_btns ver2">삭제</button>

                        <script>
                            function push_send_mng_remove(){

                                var push_chk_arr = [];

                                $("input[name=mng_rm_checked]:checked").each(function(){
                                    var chk = $(this).val();

                                    push_chk_arr.push(chk);
                                });

                                //단지 선택안하면 에러
                                if(push_chk_arr == ""){
                                    alert("취소할 매니저를 한명이상 선택해주세요.");
                                    return false;
                                }

                                let select_push_mng = $("#select_push_mng").val();

                                let filter_bidx;
                                for(var i=0;i<push_chk_arr.length;i++){

                                    select_push_mng = $("#select_push_mng").val();

                                    let select_bf_chk_t = select_push_mng.split(","); //배열로 변경

                                    filter_bidx = select_bf_chk_t.filter((element) => element !== push_chk_arr[i]);

                                    $("#select_push_mng").val(filter_bidx.join(","));
                                }

                                select_push_mng = $("#select_push_mng").val();

                                $.ajax({

                                url : "./push_mng_send_list_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "select_push_mng":select_push_mng}, //파라미터 값
                                success: function(msg){ //성공시 이벤트
                                    console.log(msg);

                                    $(".push_mng_send_wraps").html(msg);
                                }

                                });

                                $.ajax({

                                url : "./push_mng_send_list_del_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "select_push_mng":select_push_mng}, //파라미터 값
                                success: function(msg){ //성공시 이벤트
                                    console.log(msg);

                                    $(".push_mng_list_wraps").html(msg);
                                }

                                });
                            }
                        </script>
                    </div>
                    <div class="push_send_list_wrap mgt10">
                        <div class="push_send_list_tb">
                            <div class="push_send_list_tr push_send_list_thead">
                                <div class="push_send_list_lefts">
                                    <div class="push_send_td">
                                        <input type="checkbox" name="mng_del_checked_all" id="mng_del_checked_all" value="1">
                                    </div>
                                </div>
                                <div class="push_send_list_rights ver2">
                                    <div class="push_send_td push_send_th">부서명</div>
                                    <div class="push_send_td push_send_th">직급</div>
                                    <div class="push_send_td push_send_th">이름</div>
                                </div>
                            </div>
                            <div class="push_send_list_tbody_wrap push_mng_send_wraps">
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>    
        </div>    

        <!-- 단지 푸시리스트 -->
        <div class="building_push_box">
            <?php
            
            $ho_sql = "SELECT ho.*, bu.building_name, do.dong_name FROM a_building_ho as ho
                        LEFT JOIN a_building as bu ON ho.building_id = bu.building_id
                        LEFT JOIN a_building_dong as do ON ho.dong_id = do.dong_id
                    WHERE ho.is_del = 0 and ho.ho_status = 'Y' ORDER BY bu.building_name asc, ho.ho_id asc";
            $ho_res = sql_query($ho_sql);
            ?>
            <div class="push_send_wrap mgt10">
                <div class="push_send_box">
                    <div class="push_send_tit_wrap">
                        <p class="push_send_label">인원 리스트</p>
                    
                        <button type="button" onclick="push_send_add();" class="bansang_btns ver1">추가</button>
                        <script>
                            function push_send_add(){

                                var push_chk_arr = [];

                                $("input[name=ho_checked]:checked").each(function(){
                                    var chk = $(this).val();

                                    push_chk_arr.push(chk);
                                });


                                //단지 선택안하면 에러
                                if(push_chk_arr == ""){
                                    alert("푸시를 발송할 단지를 하나이상 선택해주세요.");
                                    return false;
                                }

                                console.log('푸시를 발송할 단지를 idx', push_chk_arr);
                                
                                let select_push_ho = $("#select_push_ho").val();

                                if(select_push_ho == ""){
                                    $("#select_push_ho").val(push_chk_arr.join(","));
                                }else{
                                    $("#select_push_ho").val(select_push_ho + "," + push_chk_arr.join(","));
                                }

                                select_push_ho = $("#select_push_ho").val();
                                //푸시발송할 리스트 나타내기


                                //검색용
                                let building_id = $("#building_id option:selected").val();
                                let dong_id = $("#dong_id option:selected").val();
                                let ho_id = $("#ho_id option:selected").val();

                                $.ajax({

                                url : "./push_send_list_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "select_push_ho":select_push_ho}, //파라미터 값
                                success: function(msg){ //성공시 이벤트
                                    console.log(msg);

                                    $(".push_send_wraps").html(msg);
                                }

                                });

                                $.ajax({

                                url : "./push_send_list_del_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "select_push_ho":select_push_ho, "building_id":building_id, "dong_id":dong_id, "ho_id":ho_id}, //파라미터 값
                                success: function(msg){ //성공시 이벤트
                                    console.log(msg);

                                    $(".push_list_wraps").html(msg);
                                }

                                });

                            }
                        </script>
                    </div>
                    <div class="push_send_list_wrap mgt10">
                        <div class="push_send_list_tb">
                            <div class="push_send_list_tr push_send_list_thead">
                                <div class="push_send_list_lefts">
                                    <div class="push_send_td">
                                        <input type="checkbox" name="ho_checked_all" id="ho_checked_all" value="1">
                                    </div>
                                </div>
                                <div class="push_send_list_rights">
                                    <div class="push_send_td push_send_th">단지명</div>
                                    <div class="push_send_td push_send_th">동</div>
                                    <div class="push_send_td push_send_th">호수</div>
                                    <div class="push_send_td push_send_th">이름</div>
                                </div>
                            </div>
                            <div class="push_send_list_tbody_wrap push_list_wraps">
                                <?php for($i=0;$ho_row = sql_fetch_array($ho_res);$i++){?>
                                <div class="push_send_list_tr push_send_list_tbody">
                                    <div class="push_send_list_lefts">
                                        <div class="push_send_td">
                                            <input type="checkbox" name="ho_checked" class="ho_checked" value="<?php echo $ho_row['ho_id']; ?>">
                                        </div>
                                    </div>
                                    <div class="push_send_list_rights">
                                        <div class="push_send_td"><?php echo $ho_row['building_name'];?></div>
                                        <div class="push_send_td"><?php echo $ho_row['dong_name'];?>동</div>
                                        <div class="push_send_td"><?php echo $ho_row['ho_name'];?>호</div>
                                        <div class="push_send_td"><?php echo $ho_row['ho_tenant'];?></div>
                                    </div>
                                </div>
                                <?php }?>
                            </div>
                        </div>
                    </div>
                </div>
            
                <div class="push_send_box">
                    <div class="push_send_tit_wrap">
                        <p class="push_send_label">발송 리스트</p>
                        
                        <button type="button" onclick="push_send_remove();" class="bansang_btns ver2">삭제</button>
                        <script>
                            function push_send_remove(){

                                var push_chk_arr = [];

                                $("input[name=ho_checked_rm]:checked").each(function(){
                                    var chk = $(this).val();

                                    push_chk_arr.push(chk);
                                });


                                //단지 선택안하면 에러
                                if(push_chk_arr == ""){
                                    alert("취소할 단지를 하나이상 선택해주세요.");
                                    return false;
                                }

                                console.log('취소할 단지 idx', push_chk_arr);
                                
                                let select_push_ho = $("#select_push_ho").val();

                                console.log('select_push_ho::',select_push_ho);

                                let filter_bidx;
                                for(var i=0;i<push_chk_arr.length;i++){

                                    select_push_ho = $("#select_push_ho").val();

                                    let select_bf_chk_t = select_push_ho.split(","); //배열로 변경

                                    console.log('select_bf_chk_t:',select_bf_chk_t);

                                    filter_bidx = select_bf_chk_t.filter((element) => element !== push_chk_arr[i]);


                                    console.log('filter_bidx:',push_chk_arr[i]);

                                    $("#select_push_ho").val(filter_bidx.join(","));
                                }

                                //$("#select_push_ho").val(filter_bidx.join(","));

                                select_push_ho = $("#select_push_ho").val();
                                //푸시발송할 리스트 나타내기

                                let building_id = $("#building_id option:selected").val();
                                let dong_id = $("#dong_id option:selected").val();
                                let ho_id = $("#ho_id option:selected").val();

                                $.ajax({

                                url : "./push_send_list_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "select_push_ho":select_push_ho}, //파라미터 값
                                success: function(msg){ //성공시 이벤트
                                    console.log(msg);

                                    $(".push_send_wraps").html(msg);
                                }

                                });

                                $.ajax({

                                url : "./push_send_list_del_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "select_push_ho":select_push_ho, 'building_id':building_id, 'dong_id':dong_id, 'ho_id':ho_id}, //파라미터 값
                                success: function(msg){ //성공시 이벤트
                                    console.log(msg);

                                    $(".push_list_wraps").html(msg);
                                }

                                });
                            }
                        </script>
                    </div>
                    <div class="push_send_list_wrap mgt10">
                        <div class="push_send_list_tb">
                            <div class="push_send_list_tr push_send_list_thead">
                                <div class="push_send_list_lefts">
                                    <div class="push_send_td">
                                        <input type="checkbox" name="ho_del_checked_all" id="ho_del_checked_all" value="1">
                                    </div>
                                </div>
                                <div class="push_send_list_rights">
                                    <div class="push_send_td push_send_th">단지명</div>
                                    <div class="push_send_td push_send_th">동</div>
                                    <div class="push_send_td push_send_th">호수</div>
                                    <div class="push_send_td push_send_th">이름</div>
                                </div>
                            </div>
                            <div class="push_send_list_tbody_wrap push_send_wraps">
                            </div>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
    <?php }?>
    <div class="btn_fixed_top">
        <a href="./push_list.php" class="btn btn_02">목록</a>
        <?php if($w == ''){?>
        <input type="submit" value="저장" class="btn_submit btn btn_02" accesskey='s'>
        <?php }?>
    </div>
</form>
<script>
// 단지 인원리스트 전체선택
$("#ho_checked_all").click(function() {
	if($("#ho_checked_all").is(":checked")){
		$(".ho_checked").prop("checked", true);
	}else{
		$(".ho_checked").prop("checked", false);
	}
	$(".ho_checked").change();
});
$(".ho_checked").click(function() {
	var total = $(".ho_checked").length;
	var checked = $(".ho_checked:checked").length;

	if(total != checked) $("#ho_checked_all").prop("checked", false);
	else $("#ho_checked_all").prop("checked", true); 
});


// 발송리스트 전체선택
// 단지 전체선택
$("#ho_del_checked_all").click(function() {
	if($("#ho_del_checked_all").is(":checked")){
		$(".ho_checked_rm").prop("checked", true);
	}else{
		$(".ho_checked_rm").prop("checked", false);
	}
	$(".ho_checked_rm").change();
});
$(".ho_checked_rm").click(function() {
	var total = $(".ho_checked_rm").length;
	var checked = $(".ho_checked_rm:checked").length;

	if(total != checked) $("#ho_del_checked_all").prop("checked", false);
	else $("#ho_del_checked_all").prop("checked", true); 
});


//매니저 전체리스트
$("#mng_checked_all").click(function() {
	if($("#mng_checked_all").is(":checked")){
		$(".mng_checked").prop("checked", true);
	}else{
		$(".mng_checked").prop("checked", false);
	}
	$(".mng_checked").change();
});
$(".mng_checked").click(function() {
	var total = $(".mng_checked").length;
	var checked = $(".mng_checked:checked").length;

	if(total != checked) $("#mng_checked_all").prop("checked", false);
	else $("#mng_checked_all").prop("checked", true); 
});

//매니저 발송리스트
$("#mng_del_checked_all").click(function() {
	if($("#mng_del_checked_all").is(":checked")){
		$(".mng_rm_checked").prop("checked", true);
	}else{
		$(".mng_rm_checked").prop("checked", false);
	}
	$(".mng_rm_checked").change();
});
$(".mng_rm_checked").click(function() {
	var total = $(".mng_rm_checked").length;
	var checked = $(".mng_rm_checked:checked").length;

	if(total != checked) $("#mng_del_checked_all").prop("checked", false);
	else $("#mng_del_checked_all").prop("checked", true); 
});


 function fpush_submit(f) {

    if(f.push_mem_type.value == 'user'){
        if(f.select_push_ho.value == "") {
            alert("푸시를 발송할 단지를 하나이상 선택해주세요.");
            return false;
        }
    }

    if(f.push_mem_type.value == 'sm'){
        if(f.select_push_mng.value == "") {
            alert("푸시를 발송할 매니저를 한명이상 선택해주세요.");
            return false;
        }
    }
   

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

