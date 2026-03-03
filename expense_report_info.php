<?php
include_once('./_common.php');
if($types == "sm"){
    include_once(G5_PATH.'/head_sm.php');
}else{
    include_once(G5_PATH.'/head.php');
}

$expense_sql = "SELECT * FROM a_expense_report WHERE ex_id = '{$ex_id}'";
$expense_row = sql_fetch($expense_sql);

//단지정보
$building_sql = "SELECT building.*, post.post_name FROM a_building as building
                 LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                 WHERE building.building_id = '{$expense_row['building_id']}'";
$building_row = sql_fetch($building_sql);

$writer = "";

//echo $expense_row['wid'];
if($expense_row['wid'] == "admin"){
    $writer = "신반상회";
}else{
    $mng_info2 = get_manger($expense_row['wid']);

    $writer = $mng_info2['md_name']." / ".$mng_info2['mg_name']." / ".$mng_info2['mng_name'];
}

$expense_file = "SELECT * FROM g5_board_file WHERE bo_table = 'expense' and wr_id = '{$ex_id}' ORDER BY bf_no asc ";
// if($_SERVER['REMOTE_ADDR'] == ADMIN_IP)  echo $expense_file;
$expense_file_res = sql_query($expense_file); 

$file_arr = array();
while($expense_file_row = sql_fetch_array($expense_file_res)){

    $file_name = G5_PATH."/data/file/expense/".$expense_file_row['bf_file'];

    // echo $file_name.'<br>';

    // echo file_exists($file_name) ? '1' : '0';

    if(file_exists($file_name)){
        array_push($file_arr, $expense_file_row);
    }
}

if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){
    // print_r2($file_arr);
}
// print_r2($file_arr);

if($types == "sm"){
    $enforce_info = get_manger($expense_row['enforce_id']);
}
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="bbs_wrap">
                <div class="bbs_title_box">
                    <p class="bbs_title"><?php echo $expense_row['ex_title']; ?></p>
                    <div class="bbs_info_box">
                        <p class="bbs_date"><?php echo date("Y.m.d", strtotime($expense_row['created_at']));?></p>
                        <p class="bbs_date">작성자 : <?php echo $expense_row['ex_name']; ?></p>
                    </div>
                </div>
                <?php if($types == "sm"){?>
                    <div class="ex_report_submit_box mgt20">
                        <?php if($expense_row['enforce_id'] != ""){?>
                        <div class="ex_report_submit_text"><?php echo $enforce_info['md_name']; ?> / <?php echo $enforce_info['mg_name']; ?> / <?php echo $enforce_info['mng_name']; ?></div>
                        <?php }else{ ?>
                            <div class="ex_report_submit_text"></div>
                        <?php }?>
                        <button type="button" onclick="popOpen('ex_report_submit_pop')" class="ex_report_submit_btn">시행자 <?php echo $expense_row['enforce_deaprt'] != "" ? "변경" : "등록";?></button>
                    </div>
                <?php }?>
                <div class="expense_img_box">
                    <?php echo $expense_row['ex_content']; ?>
                    <div class="expense_img_box_inner mgt10">
                        <div class="swiper expense_swp">
                            <div class="swiper-wrapper">
                                <?php for($i=0;$i < count($file_arr);$i++){?>
                                <div class="swiper-slide">
                                    <div onclick="imgZoom('/data/file/expense/<?php echo $file_arr[$i]['bf_file'];?>')">
                                        <img src="/data/file/expense/<?php echo $file_arr[$i]['bf_file'];?>" alt="">
                                    </div>
                                </div>
                                <?php }?>
                            </div>
                            <div class="swiper-pagination"></div>
                        </div>
                    </div>
                    <script>
                        function imgZoom(imgPath){
                            sendMessage('imgZoom', {"content":imgPath});
                        }
                    </script>
                </div>
                <?php if($types=='sm'){?>
                <div class="comple_answers mgt20">
                    <p class="regi_list_title">기타사항</p>
                    <div class="expense_content_box">
                    <?php echo $expense_row['ex_content']; ?>
                    </div>
                </div>
                <?php }?>
            </div>
        </div>
    </div>
</div>
<div class="cm_pop" id="report_del_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">품의서를 삭제하시겠습니까?</p>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="popClose('report_del_pop');">취소</button>
            <button type="button" class="cm_pop_btn ver2" onClick="expenseDelHandler();">삭제</button>
		</div>
	</div>
</div>
<div class="cm_pop" id="ex_report_submit_pop">
    <?php
     $sql_depart2 = "SELECT * FROM a_mng_department WHERE is_del = 0 ORDER BY is_prior asc, md_idx desc";
     $depart_res2 = sql_query($sql_depart2);
    ?>
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onclick="popClose('ex_report_submit_pop')">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">시행자 <?php echo $expense_row['enforce_deaprt'] != "" ? "변경" : "등록";?> </div>
        <div class="report_label_box mgt20">
            <div class="report_pop_label">단지</div>
            <div class="report_pop_label2"><?php echo $building_row['building_name']; ?></div>
        </div>
        <div class="report_select_box_wrap mgt20">
            <div class="report_select_box">
                <div class="report_select_label">부서</div>
                <div class="report_selects">
                    <select name="enforce_deaprt" id="enforce_deaprt" class="bansang_sel" onchange="en_department_change();">
                        <option value="">부서 선택</option>
                        <?php for($i=0;$depart_row = sql_fetch_array($depart_res2);$i++){?>
                            <option value="<?php echo $depart_row['md_idx']; ?>" <?php echo get_selected($expense_row['enforce_deaprt'], $depart_row['md_idx']); ?>><?php echo $depart_row['md_name']; ?></option>
                        <?php }?>
                    </select>
                    <script>
                        function en_department_change(){
                            var departmentSelect = document.getElementById("enforce_deaprt");
                            var departmentValue = departmentSelect.options[departmentSelect.selectedIndex].value;

                            console.log('departmentValue', departmentValue);

                            $("#enforce_id").html("<option value=''>선택하세요.</opiton>")

                            $.ajax({

                            url : "/adm/expense_grade_ajax.php", //ajax 통신할 파일
                            type : "POST", // 형식
                            data: { "department":departmentValue}, //파라미터 값
                            success: function(msg){ //성공시 이벤트

                                console.log(msg);
                                $("#enforce_grade").html(msg);
                            }

                            });
                        }
                    </script>
                </div>
            </div>
            <div class="report_select_box">
                <div class="report_select_label">직급</div>
                <div class="report_selects">
                    <?php
                    $sql = "SELECT mng.*, grade.mg_name FROM a_mng as mng
                    LEFT JOIN a_mng_grade as grade on mng.mng_grades = grade.mg_idx
                    WHERE mng.mng_department = '{$expense_row['enforce_deaprt']}' GROUP BY mng.mng_grades ORDER BY mng.mng_grades desc, mng.mng_idx desc";
                    $res = sql_query($sql);

                    ?>
                    <select name="enforce_grade" id="enforce_grade" class="bansang_sel" onchange="en_grade_change();">
                        <option value="">직급 선택</option>
                        <?php for($i=0;$gr_row = sql_fetch_array($res);$i++){?>
                        <option value="<?php echo $gr_row['mng_grades']; ?>" <?php echo get_selected($expense_row['enforce_grade'], $gr_row['mng_grades']); ?>><?php echo $gr_row['mg_name']; ?></option>
                        <?php }?>
                    </select>
                    <script>
                        function en_grade_change(){
                            var gradeSelect = document.getElementById("enforce_grade");
                            var gradeValue = gradeSelect.options[gradeSelect.selectedIndex].value;

                            let departValue = $("#enforce_deaprt option:selected").val();

                            $.ajax({

                            url : "/adm/enforce_name_ajax.php", //ajax 통신할 파일
                            type : "POST", // 형식
                            data: { "gradeValue":gradeValue, "departValue":departValue}, //파라미터 값
                            success: function(msg){ //성공시 이벤트

                                //console.log(msg);
                                $("#enforce_id").html(msg);
                            }

                            });
                        }
                    </script>
                </div>
            </div>
            <div class="report_select_box">
                <div class="report_select_label">시행자</div>
                <div class="report_selects">
                    <?php
                    $sql = "SELECT mng.*, grade.mg_name FROM a_mng as mng
                    LEFT JOIN a_mng_grade as grade on mng.mng_grades = grade.mg_idx
                    WHERE mng.mng_department = '{$expense_row['enforce_deaprt']}' and mng.mng_grades = '{$expense_row['enforce_grade']}' ORDER BY mng.mng_grades desc, mng.mng_idx desc";
                    //echo $sql;
                    $res = sql_query($sql);
                    ?>
                    <select name="enforce_id" id="enforce_id" class="bansang_sel">
                        <option value="">시행자 선택</option>
                        <?php for($i=0;$ei_row = sql_fetch_array($res);$i++){?>
                        <option value="<?php echo $ei_row['mng_id']; ?>" <?php echo get_selected($expense_row['enforce_id'], $ei_row['mng_id']); ?>><?php echo $ei_row['mng_name'].' '.$ei_row['mg_name']; ?></option>
                        <?php }?>
                    </select>
                </div>
            </div>
        </div>
		<div class="cm_pop_btn_box">
            <button type="button" class="cm_pop_btn ver2" onClick="enforceChangeHandler();">확인</button>
		</div>
	</div>
</div>

<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>시행자를 등록 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>
<script>

let swiper = new Swiper(".expense_swp", {
    slidesPerView: "auto",
    pagination: {
        el: '.swiper-pagination',
        type: 'fraction',
    },
    autoHeight: true,
});

//품의서 삭제
function expenseDelHandler(){
    let building_id = "<?php echo $building_id; ?>";
    let ex_id = "<?php echo $ex_id; ?>";

    let sendData = {'ex_id':ex_id};

    $.ajax({
        type: "POST",
        url: "/expense_report_del.php",
        data: sendData,
        cache: false,
        async: false,
        dataType: "json",
        success: function(data) {
            console.log('data:::', data);

            if(data.result == false) { 
                showToast(data.msg);
                return false;
            }else{
                showToast(data.msg);
                
                setTimeout(() => {
                    location.replace('/expense_report_list.php?building_id=' + building_id);
                }, 700);
            }
        },
    });
}


//시행자 변경 및 등록
function enforceChangeHandler(){

    $("#building_info_pop").show();

    let ex_id = "<?php echo $ex_id; ?>";
    let w = "<?php echo $expense_row['enforce_id'] != "" ? "u" : ""; ?>";
    let enforce_deaprt = $("#enforce_deaprt option:selected").val(); //부서
    let enforce_grade = $("#enforce_grade option:selected").val(); //직급
    let enforce_id = $("#enforce_id option:selected").val(); //시행자
    let mb_id = "<?php echo $member['mb_id']; ?>";

    let sendData = {'ex_id':ex_id, 'w':w, 'enforce_deaprt': enforce_deaprt, 'enforce_grade':enforce_grade, 'enforce_id':enforce_id, "mb_id":mb_id};

    setTimeout(() => {
        $.ajax({
            type: "POST",
            url: "/expense_report_info_enforce_change.php",
            data: sendData,
            cache: false,
            async: false,
            dataType: "json",
            success: function(data) {
                console.log('data:::', data);

                if(data.result == false) { 
                    $("#building_info_pop").hide();
                    showToast(data.msg);
                    return false;
                }else{
                    $("#building_info_pop").hide();
                    popClose('ex_report_submit_pop');

                    showToast(data.msg);

                    setTimeout(() => {
                        window.location.reload();
                    }, 700);
                
                }
            },
        });
    }, 50);

    
}

var types = "<?php echo $types; ?>";

if(types == "sm"){
    const homebtn = document.querySelector('.home_btn');
    const tooltipBox = document.querySelector('.tooltip_btn');
    homebtn.addEventListener('click', () => {
    const dropdown = document.querySelector('.tooltip_box');
    dropdown.style.display = 'block';
    });

    homebtn.addEventListener('blur', () => {
    const dropdown = document.querySelector('.tooltip_box');

    setTimeout(() => {
        dropdown.style.display = '';
    }, 200);
    });
}

</script>
<?php
include_once(G5_PATH.'/tail.php');
?>