<?php
include_once('./_common.php');
require_once G5_EDITOR_LIB;
include_once(G5_PATH.'/head_sm.php');

$building_sql = "SELECT building.*, post.post_name FROM a_building as building
                 LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                 WHERE building.building_id = '{$building_id}'";
$building_row = sql_fetch($building_sql);

$expense_row = sql_fetch("SELECT * FROM a_expense_report WHERE ex_id = '{$ex_id}'");

$file_sql = "SELECT * FROM g5_board_file WHERE bo_table = 'expense' and wr_id = {$ex_id} and bf_file != '' order by bf_no asc";
//echo $file_sql;
$file_res = sql_query($file_sql);

if($expense_row['dong_id'] != '-1'){
    $where_dong = " and mng_t.dong_id = '{$expense_row['dong_id']}' ";
}

$mng_sql = "SELECT mng_t.*, mng_g.gr_name FROM a_mng_team as mng_t
            LEFT JOIN a_mng_team_grade as mng_g on mng_t.mt_grade = mng_g.gr_id
            WHERE mng_t.post_id = '{$expense_row['post_id']}' and mng_t.build_id = '{$expense_row['building_id']}' {$where_dong} and mng_t.is_del = 0 ORDER BY mt_id desc ";
// echo $mng_sql;
$mng_res = sql_query($mng_sql);
$mng_res2 = sql_query($mng_sql);
$mng_res3 = sql_query($mng_sql);
//print_r2($mng_info);

if($_SERVER['REMOTE_ADDR'] == '59.16.155.80'){
    // print_r2($building_row);
}

// print_r2($_SESSION);
// echo $user_building['post_id'];
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <p class="mng_title"><?php echo $building_row['building_name']; ?></p>
            </div>
        </div>
        <div class="holiday_req_wrap">
            <div class="inner">
                <ul class="regi_list m0">
                    <li>
                        <?php
                        $sql_building = "SELECT * FROM a_building_dong WHERE building_id = '{$building_id}' and is_del = 0";
                        $res_building = sql_query($sql_building);
                        ?>
                        <p class="regi_list_title">동 선택 <span>*</span></p>
                        <div class="ipt_box">
                            <select name="dong_id" id="dong_id" class="bansang_sel" onchange="dong_change();" >
                                <option value="">선택</option>
                                <option value="-1" <?php echo get_selected($expense_row['dong_id'], '-1'); ?>>전체</option>
                                <?php
                                while($row_building = sql_fetch_array($res_building)){
                                ?>
                                <option value="<?php echo $row_building['dong_id']?>" <?php echo get_selected($expense_row['dong_id'], $row_building['dong_id']); ?>><?php echo $row_building['dong_name'];?>동</option>
                                <?php }?>
                            </select>
                            <script>
                            function dong_change(){
                                var dongSelect = document.getElementById("dong_id");
                                var dongValue = dongSelect.options[dongSelect.selectedIndex].value;

                                console.log('dongValue', dongValue);

                                let post_id = "<?php echo $building_row['post_id']; ?>";
                                let building_id = "<?php echo $building_id; ?>";

                                $.ajax({

                                url : "/adm/expense_approver_ajax.php", //ajax 통신할 파일
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
                        </div>
                    </li>
                    <li>
                        <?php 
                        //부서
                        $sql_depart = "SELECT * FROM a_mng_department WHERE is_del = 0 ORDER BY is_prior asc, md_idx desc";
                        $depart_res = sql_query($sql_depart);
                        ?>
                        <p class="regi_list_title">부서 <span>*</span></p>
                        <div class="ipt_box">
                            <select name="ex_department" id="ex_department" class="bansang_sel" onchange="department_change();" readonly>
                                <option value="">선택</option>
                                <?php for($i=0;$depart_row = sql_fetch_array($depart_res);$i++){
                                
                                $selectVal = $w == 'u' ? $expense_row['ex_department'] : $mng_info['mng_department'];
                                ?>
                                <option value="<?php echo $depart_row['md_idx']; ?>" <?php echo get_selected($selectVal, $depart_row['md_idx']); ?>><?php echo $depart_row['md_name']; ?></option>
                            <?php }?>
                            </select>
                            <script>
                                function department_change(){

                                    let html = `<option value="">선택</option>`;
                                    $("#ex_grade").html(html);
                                    $("#ex_name").html(html);

                                    $.ajax({

                                    url : "/expense_depart_ajax.php", //ajax 통신할 파일
                                    type : "POST", // 형식
                                    data: { }, //파라미터 값
                                    success: function(msg){ //성공시 이벤트
                                        console.log(msg);
                                        $("#ex_grade").html(msg);
                                    }

                                    });
                                }
                            </script>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">직급 <span>*</span></p>
                        <div class="ipt_box">
                            <!-- <input type="text" name="ex_grade" id="ex_grade" class="bansang_ipt ver2" placeholder="직급을 입력해주세요." value="<?php echo $w == "u" ? $row['ex_grade'] : $mng_info['mg_name']; ?>" readonly> -->
                            <?php
                            $sql_grade = "SELECT * FROM a_mng_grade WHERE is_del = 0 ORDER BY is_prior asc, mg_idx desc";
                            // echo $sql_grade;
                            $res_grade = sql_query($sql_grade);

                            // print_r2($mng_info);
                            ?>
                            <select name="ex_grade" id="ex_grade" class="bansang_sel" onchange="grade_change();" readonly>
                                <option value="">선택</option>
                                <?php for($i=0;$row_grade = sql_fetch_array($res_grade);$i++){
                                    $selectVal = $w == 'u' ? $expense_row['ex_grade'] : $mng_info['mg_name'];
                                    ?>
                                    <option value="<?php echo $row_grade['mg_name'];?>" <?php echo get_selected($selectVal, $row_grade['mg_name']); ?>><?php echo $row_grade['mg_name']; ?></option>
                                <?php }?>
                            </select>
                            <script>
                            function grade_change(){
                                var gradeSelect = document.getElementById("ex_grade");
                                var gradeValue = gradeSelect.options[gradeSelect.selectedIndex].value;

                                //let html = `<option value="">선택</option>`;
                                let departId = $("#ex_department option:selected").val();
                                let gradeId = gradeValue;
                                console.log(departId, gradeId);
                                //$("#ex_name").html(html);

                                $.ajax({

                                url : "/expense_grade_ajax.php", //ajax 통신할 파일
                                type : "POST", // 형식
                                data: { "departId":departId, "gradeId":gradeId}, //파라미터 값
                                success: function(msg){ //성공시 이벤트

                                    console.log(msg);
                                    $("#ex_name").html(msg);
                                }

                                });
                            }
                            </script>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">작성자 <span>*</span></p>
                        <div class="ipt_box">
                            <?php
                            $sql_wheres = "";
                            if($w == "u"){
                                $sql_wheres = " and mng_department = '{$expense_row['ex_department']}' and mng_grades = '{$expense_row['ex_grade']}' ";
                            }else{
                                $sql_wheres = "and mng_department = '{$mng_info['mng_department']}' and mng_grades = '{$mng_info['mng_grades']}'";
                            }
                            $mng_sql = "SELECT * FROM a_mng WHERE is_del = 0 {$sql_wheres} ORDER BY mng_idx desc";
                            //echo $mng_sql;
                            $mng_res_w = sql_query($mng_sql);
                            ?>
                            <!-- <select name="ex_name" id="ex_name" class="bansang_sel">
                                <option value="">선택</option>
                                <?php while($mng_row = sql_fetch_array($mng_res_w)){
                                    $selectVal = $w == 'u' ? $expense_row['ex_name'] : $mng_info['mng_id'];
                                    ?>
                                    <option value="<?php echo $mng_row['mng_id']; ?>" <?php echo get_selected($selectVal, $mng_row['mng_id']); ?>><?php echo $mng_row['mng_name']; ?></option>
                                <?php }?>
                            </select> -->
                            <input type="text" name="ex_name" id="ex_name" value="<?php echo $w == 'u' ?  $expense_row['ex_name'] : $mng_info['mng_name'];?>" class="bansang_ipt" readonly>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">제목 <span>*</span></p>
                        <div class="ipt_box">
                            <input type="text" name="ex_title" id="ex_title" class="bansang_ipt ver2" placeholder="제목을 입력해주세요." value="<?php echo $expense_row['ex_title']; ?>">
                        </div>
                    </li>
                    <li>
						<p class="regi_list_title">사진첨부 <span class="pic_ver">*8장까지 등록 가능합니다.</span></p>
						<div class="ipt_box">
                            <div class="img_upload_wrap">
                                <div class="img_upload_box ver1">
                                    <input type="file" name="img_up[]" id="img_up" onchange="addFile(this);" multiple accept="image/*">
                                    <label for="img_up">
                                        <img src="/images/file_plus.svg" alt="">
                                    </label>
                                </div>
                                <?php if($w == "u" && $file_res){?>
                                    <?php for($i=0;$file_row = sql_fetch_array($file_res);$i++){?>
                                        <div class="img_upload_box_wrapper4">
                                            <div class="img_upload_box ver4 filebox">
                                                <input type="file" name="img_up<?php echo $i + 1;?>" id="img_up<?php echo $i + 1;?>" accept="image/*" onchange="fileUp(this, 'img_up<?php echo $i + 1;?>', <?php echo $i; ?>, 'before')">
                                               
                                                <img src="/data/file/expense/<?php echo $file_row['bf_file']; ?>" class="img_up<?php echo $i + 1;?>" alt="" onclick="bigSize('/data/file/expense/<?php echo $file_row['bf_file']; ?>')">

                                                <div class="file_del">
                                                    <input type="checkbox" name="ex_file_del[<?php echo $file_row['bf_no'];?>]" id="ex_file_del<?php echo $i+1;?>" class="ex_file_del" value="1">
                                                    <label for="ex_file_del<?php echo $i+1;?>">삭제</label>
                                                </div>
                                            </div>
                                            <?php if($expense_row['ex_status'] == 'N'){?>
                                            <label class="img_labels" for="img_up<?php echo $i + 1;?>">
                                            이미지 첨부
                                            </label>
                                            <?php }?>
                                        </div>
                                    <?php }?>
                                <?php }?>
                            </div>
						</div>
					</li>
                    <li>
                        <p class="regi_list_title">기타사항</p>
                        <div class="ipt_box">
                            <textarea name="ex_content" id="ex_content" class="bansang_ipt ver2 ta" placeholder="기타사항을 입력해주세요."><?php echo $expense_row['ex_content'];?></textarea>
                        </div>
                    </li>
                    <li class="ver2">
                        <p class="regi_list_title">최초 결재자 <span class="pic_ver">*단지의 관리단 리스트가 보여집니다.</span></p>
                        <div class="ipt_box">
                            <select name="ex_approver1" id="ex_approver1" class="bansang_sel">
                                <option value="">선택</option>
                                <?php for($i=0;$mng_row = sql_fetch_array($mng_res);$i++){?>
                                    <option value="<?php echo $mng_row['mb_id']; ?>" <?php echo get_selected($mng_row['mb_id'], $expense_row['ex_approver1']); ?>><?php echo $mng_row['mt_name'].' '.$mng_row['gr_name']; ?></option>
                                <?php }?>
                            </select>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">중간 결재자 <span class="pic_ver">*단지의 관리단 리스트가 보여집니다.</span></p>
                        <div class="ipt_box">
                            <select name="ex_approver2" id="ex_approver2" class="bansang_sel">
                                <option value="">선택</option>
                                <?php for($i=0;$mng_row = sql_fetch_array($mng_res2);$i++){?>
                                    <option value="<?php echo $mng_row['mb_id']; ?>" <?php echo get_selected($mng_row['mb_id'], $expense_row['ex_approver2']); ?>><?php echo $mng_row['mt_name'].' '.$mng_row['gr_name']; ?></option>
                                <?php }?>
                            </select>
                        </div>
                    </li>
                    <li>
                        <p class="regi_list_title">최종 결재자 <span class="pic_ver">*단지의 관리단 리스트가 보여집니다.</span></p>
                        <div class="ipt_box">
                            <select name="ex_approver3" id="ex_approver3" class="bansang_sel">
                                <option value="">선택</option>
                                <?php for($i=0;$mng_row = sql_fetch_array($mng_res3);$i++){?>
                                    <option value="<?php echo $mng_row['mb_id']; ?>" <?php echo get_selected($mng_row['mb_id'], $expense_row['ex_approver3']); ?>><?php echo $mng_row['mt_name'].' '.$mng_row['gr_name']; ?></option>
                                <?php }?>
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
            <div class="inner">
            <div class="fix_btn_wrap flex_ver ver3">
                <button type="button" onclick="historyBack();" class="fix_btn" id="fix_btn" >취소</button>
                <button type="button" onclick="expense_submit();" class="fix_btn on" id="fix_btn" >저장</button>
            </div>
            </div>
        </div>
        
    </div>
</div>

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
        <p>품의서를 저장 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>
<!-- heic 파일용 -->
<script src="https://cdn.jsdelivr.net/npm/heic2any/dist/heic2any.min.js"></script>
<script>
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
        showToast("첨부파일은 최대 " + maxFileCnt + "개 까지 첨부 가능합니다.");
    } else {
        for (const file of obj.files) {

            console.log('file', file);
            // 첨부파일 검증
            if (validation(file)) {
                // 파일 배열에 담기
                var reader = new FileReader();
                reader.onload = async function (e) {
                    //filesArr.push(file);

                    let processedFile = file;

                    // let previewHTML = `
                    //     <div class="filebox">
                    //         <img src="${e.target.result}" alt="">
                    //     </div>
                    // `;
                    console.log('cnt', cnt);

                    let previewHTML;

                    //heic 파일이라면 변환
                    if (file.type === "image/heic" || file.name.endsWith(".heic")) {

                        try {

                            const blob = await heic2any({ blob: file, toType: "image/jpeg" });
                            const url = URL.createObjectURL(blob);

                            console.log(url);

                            // 새 File 객체로 교체
                            processedFile = new File([blob], file.name.replace(/\.heic$/, '.jpg'), {
                                type: 'image/jpeg'
                            });

                            previewHTML = `
                                 <div class="img_upload_box_wrapper4">
                                    <div class="img_upload_box ver4 filebox">
                                        <input type="file" name="img_up${cnt}" id="img_up${cnt + 1}" accept="image/*" onchange="fileUp(this, 'img_up${cnt + 1}', ${cnt}, 'before')">
                                        <label for="img_up${cnt + 1}">
                                            <img src="${url}" class="img_up${cnt + 1}" alt="">
                                        </label>
                                    </div>
                                    <button type="button" class="img_del_btn" onclick="file_dels(this);">
                                        삭제
                                    </button>
                                </div>
                            `;
                        } catch (err) {
                            console.log('err', err);
                        }

                    }else{
                         previewHTML = `
                            <div class="img_upload_box_wrapper4">
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
                    }

                    filesArr.push(processedFile);
                    
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
    // 초기화
    //document.querySelector("input[type=file]").value = "";
}

function file_dels(btn) {
    // 클릭된 버튼이 포함된 wrapper 찾기
    const wrapper = btn.closest('.img_upload_box_wrapper4');

    // 해당 요소의 index를 구함 (현재 렌더링된 순서 기준)
    const wrappers = Array.from(document.querySelectorAll('.img_upload_box_wrapper4'));
    const index = wrappers.indexOf(wrapper);

    if (index !== -1) {
        // 1. filesArr에서 해당 파일 제거
        filesArr.splice(index, 1);

        // 2. DOM에서 제거
        wrapper.remove();

        // 3. 남은 요소들을 다시 정렬
        const newWrappers = document.querySelectorAll('.img_upload_box_wrapper4');
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
    let dong_id;
    let post_id = "<?php echo $building_row['post_id']; ?>";
    let building_id = "<?php echo $building_id; ?>";
    let ex_name = $("#ex_name").val();
    let ex_department = $("#ex_department option:selected").val();
    let ex_grade = $("#ex_grade option:selected").val();
    let ex_title = $("#ex_title").val();
    let ex_approver1 = $("#ex_approver1 option:selected").val();
    let ex_approver2 = $("#ex_approver2 option:selected").val();
    let ex_approver3 = $("#ex_approver3 option:selected").val();
    let ex_content = $("#ex_content").val();
    let ex_id = "<?php echo $ex_id; ?>";
    let mb_id = "<?php echo $member['mb_id']; ?>";

    if(w_status == "u"){
        dong_id = $("#dong_id").val();
    }else{
        dong_id = $("#dong_id option:selected").val();
    }

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
    formData.append('ex_id', ex_id);
    formData.append('mb_id', mb_id);

    //파일첨부
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
            url: "/expense_report_form_update.php",
            data: formData,
            cache: false,
            async: false,
            dataType: "json",
            contentType: false,
            processData: false,
            success: function(data) {
                console.log('data:::', data);
                if(data.result == false) { 

                    $("#building_info_pop").hide();

                    showToast(data.msg);

                    if(data.data == 'N'){
                        setTimeout(() => {
                            location.replace('/expense_report_info.php?types=sm&ex_id=' + ex_id);                        
                        }, 700);
                    }else{
                        $("#" + data.data).focus();
                    }

                
                    //$(".btn_submit").attr('disabled', false);
                    return false;
                }else{
                    showToast(data.msg);

                    setTimeout(() => {
                        if(w_status == 'u'){
                            location.replace('/expense_report_info.php?building_id=' + building_id  + '&types=sm&ex_id=' + ex_id);     
                        }else{
                            window.location.href = '/expense_report_info.php?types=sm&ex_id=' + data.data;
                        }
                        
                    }, 1000);
                }
            }
        });
    }, 50);

    
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>