<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$complain_sql = "SELECT complain.*, cstatus.cs_name, building.building_id, building.building_name, dong.dong_name, ho.ho_name FROM a_online_complain as complain
                 LEFT JOIN a_complain_status as cstatus ON complain.complain_status = cstatus.cs_code
                 LEFT JOIN a_building as building ON building.building_id = complain.building_id
                 LEFT JOIN a_building_dong as dong ON dong.dong_id = complain.dong_id
                 LEFT JOIN a_building_ho as ho ON ho.ho_id = complain.ho_id
                 WHERE complain.complain_idx = '{$complain_idx}'";
$complain_row = sql_fetch($complain_sql);

if($_SERVER['REMOTE_ADDR'] == '59.16.155.80'){
    // print_r2($complain_row);
}

$complain_file = "SELECT * FROM g5_board_file WHERE bo_table = 'complain' and wr_id = '{$complain_idx}' ORDER BY bf_no asc ";
$complain_file_res = sql_query($complain_file); 

$complain_ans_file = "SELECT * FROM g5_board_file WHERE bo_table = 'complain_answer' and wr_id = '{$complain_idx}' ORDER BY bf_no asc ";
$complain_ans_file_res = sql_query($complain_ans_file); 


$depart_sql = "SELECT * FROM a_mng_department WHERE is_del = 0 ORDER BY is_prior asc, md_idx asc";
$depart_res = sql_query($depart_sql);

//답변 첨부사진
$complain_answer_file_sql = "SELECT * FROM g5_board_file WHERE bo_table = 'complain_answer' and wr_id = {$complain_idx} and bf_file != '' order by bf_no asc";
$complain_answer_file_res = sql_query($complain_answer_file_sql);
$complain_answer_file_cnt = sql_num_rows($complain_answer_file_res);

//추가내용 첨부사진
$complain_add_file_sql = "SELECT * FROM g5_board_file WHERE bo_table = 'complain_add' and wr_id = {$complain_idx} and bf_file != '' order by bf_no asc";
$complain_add_file_res = sql_query($complain_add_file_sql);
$complain_add_file_res2 = sql_query($complain_add_file_sql);
$complain_add_cnt = sql_num_rows($complain_add_file_res);
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <div class="move_content_box_wrap">
                    <div class="move_ct_box">
                        <div class="move_ct_label">진행상태</div>
                        <div class="move_cts ver2"><?php echo $complain_row['cs_name']; ?></div>
                    </div>
                    <div class="move_ct_box">
                        <div class="move_ct_label">신청자</div>
                        <div class="move_cts">
                            <?php 
                            $mng_team_row = sql_fetch("SELECT mgt.*, mgtg.gr_name, COUNT(*) as cnt FROM a_mng_team as mgt 
                            LEFT JOIN a_mng_team_grade as mgtg ON mgt.mt_grade = mgtg.gr_id
                            WHERE mgt.build_id = '{$complain_row['building_id']}' and mgt.mb_id = '{$complain_row['complain_id']}' and mgt.is_del = 0");
                            
                            //print_r2($mng_team_row);
                            $complain_name = $complain_row['complain_name'];

                            if($mng_team_row['cnt'] > 0){
                                $complain_name .= " (".$mng_team_row['gr_name'].")";
                            }
                          
                            echo $complain_name; 
                            ?>
                        </div>
                    </div>
                    <div class="move_ct_box">
                        <div class="move_ct_label">신청자 휴대폰 번호</div>
                        <div class="move_cts">
                            <a href="tel:<?php echo $complain_row['complain_hp']; ?>">
                                <img src="/images/phone_icons_b.svg" alt="">
                                <?php echo $complain_row['complain_hp']; ?>
                            </a>
                        </div>
                    </div>
                    <div class="move_ct_box">
                        <div class="move_ct_label">신청자 세대</div>
                        <div class="move_cts"><?php echo $complain_row['building_name']; ?> <?php echo $complain_row['dong_name'].'동'; ?> <?php echo $complain_row['ho_name'].'호'; ?></div>
                    </div>
                    <div class="move_ct_box">
                        <div class="move_ct_label">민원 날짜</div>
                        <div class="move_cts"><?php echo date("Y.m.d", strtotime($complain_row['created_at'])); ?></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mng_box_inner">
            <div class="inner">
                <ul class="regi_list m0">
                    <li>
                        <div class="ipt_box ipt_flex ipt_box_ver2">
                            <div class="bansang_sel_box">
                                <p class="regi_list_title">부서</p>
                                <?php if($complain_row['mng_department'] != ""){?>
                                <input type="hidden" name="mng_department" id="mng_department" value="<?php echo $complain_row['mng_department'];?>" class="bansang_ipt ver2" readonly>
                                <input type="text" name="mng_deapart_name" class="bansang_ipt" readonly value="<?php echo get_department_name($complain_row['mng_department']); ?>">
                                <?php }else {?>
                                <select name="mng_department" id="mng_department" class="bansang_sel" onchange="dapart_change();">
                                    <option value="">선택</option>
                                    <?php for($i=0;$depart_row = sql_fetch_array($depart_res);$i++){?>
                                        <option value="<?php echo $depart_row['md_idx'];?>" <?php echo get_selected($complain_row['mng_department'], $depart_row['md_idx']); ?>><?php echo $depart_row['md_name'];?></option>
                                    <?php } ?>
                                </select>
                                <script>
                                    function dapart_change(){
                                        var departSelect = document.getElementById("mng_department");
                                        var departValue = departSelect.options[departSelect.selectedIndex].value;

                                        console.log(departValue);
                                        let building_id = "<?php echo $complain_row['building_id']; ?>";

                                        $.ajax({

                                        url : "/adm/mng_select_ajax.php", //ajax 통신할 파일
                                        type : "POST", // 형식
                                        data: { "departValue":departValue, "building_id":building_id, "group":1}, //파라미터 값
                                        success: function(msg){ //성공시 이벤트

                                            console.log(msg);
                                            $("#mng_id").html(msg);
                                            
                                        }

                                        });
                                    }
                                </script>
                                <?php }?>
                            </div>
                            <div class="bansang_sel_box">
                            <p class="regi_list_title">담당자</p>
                            <?php if($complain_row['mng_id'] != ""){?>
                                <input type="hidden" name="mng_id" id="mng_id" value="<?php echo $complain_row['mng_id'];?>" class="bansang_ipt ver2" readonly>
                                <input type="text" name="mng_names" class="bansang_ipt" readonly value="<?php echo get_manger($complain_row['mng_id'])['mng_name']; ?>">
                            <?php }else{ ?>
                                <?php 
                                $mng_sql = "SELECT mng_b.*, mng.mng_department, mng.mng_grades, mng.mng_name, mng_gr.mg_name FROM a_mng_building as mng_b
                                            LEFT JOIN a_mng as mng ON mng_b.mb_id = mng.mng_id
                                            LEFT JOIN a_mng_grade as mng_gr ON mng_gr.mg_idx = mng.mng_grades
                                            WHERE mng_b.is_del = 0 and mng.mng_department = '{$complain_row['mng_department']}' and mng_b.building_id = '{$complain_row['building_id']}'
                                            ORDER BY mng_b.mng_id desc";
                                //echo $mng_sql;
                                $mng_res = sql_query($mng_sql);
                                ?>
                               
                                <select name="mng_id" id="mng_id" class="bansang_sel">
                                    <option value="">선택</option>
                                    <?php while($mng_row = sql_fetch_array($mng_res)){?>
                                    <option value="<?php echo $mng_row['mb_id']; ?>"><?php echo $mng_row['mng_name'].' '.$mng_row['mg_name'];?></option>
                                    <?php }?>
                                </select>
                            <?php }?>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        <div class="bar ver2"></div>
        <div class="inner">
            <div class="bbs_wrap">
                <div class="bbs_title_box">
                    <p class="bbs_title"><?php echo $complain_row['complain_title']; ?></p>
                    <p class="bbs_date"><?php echo date("Y.m.d", strtotime($complain_row['wdate'])); ?></p>
                </div>
                <div class="bbs_content_box">
                <?php echo nl2br($complain_row['complain_content']); ?>
                </div>
                <?php if($complain_file_res){?>
                <div class="bbs_img_wrap">
                    <?php for($i=0;$complain_file_row = sql_fetch_array($complain_file_res);$i++){?>
                    <div class="bbs_img_box" onclick="bigSize('/data/file/complain/<?php echo $complain_file_row['bf_file']; ?>')">
                        <img src="/data/file/complain/<?php echo $complain_file_row['bf_file']; ?>" alt="">
                    </div>
                    <?php }?>
                </div>
                <?php }?>
            </div>
        </div>
        <?php if($complain_status == "CC"){?>
        <div class="bar"></div>
        <div class="online_complain_answer ver2">
            <div class="inner">
                <div class="compl_anser_tit">
                    <img src="/images/answer_icons.svg" alt=""> 처리결과
                </div>
                <div class="comple_answers mgt10">
                    <textarea name="complain_answer" id="complain_answer" class="bansang_ipt ver2 ta" placeholder="처리 결과를 입력하세요."><?php echo $complain_row['complain_answer']; ?></textarea>
                </div>
                <div class="img_upload_wrap img_upload_wrap2 mgt20">
                    <div class="img_upload_box ver1">
                        <input type="file" name="img_a_up[]" id="img_a_up" onchange="addFile2(this);" multiple accept="image/*">
                        <label for="img_a_up">
                            <img src="/images/file_plus.svg" alt="">
                        </label>
                    </div>
                    <?php for($i=0;$complain_answer_file_row = sql_fetch_array($complain_answer_file_res);$i++){?>
                        <div class="img_upload_box ver44 filebox2">
                            <input type="file" name="img_a_up<?php echo $i + 1;?>" id="img_a_up<?php echo $i + 1;?>" accept="image/*" onchange="fileUp2(this, 'img_a_up<?php echo $i + 1;?>', <?php echo $i; ?>, 'before')">
                            <label for="img_a_up<?php echo $i + 1;?>">
                                <img src="/data/file/complain_answer/<?php echo $complain_answer_file_row['bf_file']; ?>" class="img_a_up<?php echo $i + 1;?>" alt="">
                            </label>

                            <div class="file_del">
                                <input type="checkbox" name="complain_answer_file_del[<?php echo $complain_answer_file_row['bf_no'];?>]" id="complain_answer_file_del<?php echo $i+1;?>" class="complain_answer_file_del" value="1">
                                <label for="complain_answer_file_del<?php echo $i+1;?>">삭제</label>
                            </div>
                        </div>
                    <?php }?>
                </div>
            </div>
        </div>
        <div class="online_complain_status mgt30">
            <div class="inner">
                <div class="compl_anser_tit">
                   상태
                </div>
                <div class="complain_status_box">
                    <select name="complain_status" id="complain_status" class="bansang_sel mgt10">
                        <option value="CC" <?php echo get_selected($complain_row['complain_status'], 'CC'); ?>>진행중</option>
                        <option value="CD" <?php echo get_selected($complain_row['complain_status'], 'CD'); ?>>완료</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="online_complain_add ver2">
            <div class="inner">
                <div class="compl_anser_tit">
                   추가 내용 <span>*관리자만 보여지는 항목입니다.</span>
                </div>
                <div class="comple_answers mgt10">
                    <textarea name="complain_memo" id="complain_memo" class="bansang_ipt ver2 ta" placeholder="추가 내용을 입력하세요."><?php echo $complain_row['complain_memo']; ?></textarea>
                </div>
                <div class="img_upload_wrap img_upload_wrap3 mgt20">
                    <div class="img_upload_box ver1">
                        <input type="file" name="img_add_up[]" id="img_add_up" onchange="addFile3(this);" multiple accept="image/*">
                        <label for="img_add_up">
                            <img src="/images/file_plus.svg" alt="">
                        </label>
                    </div>
                    <?php for($i=0;$complain_add_file_row = sql_fetch_array($complain_add_file_res);$i++){?>
                        <div class="img_upload_box filebox3">
                            <input type="file" name="img_add_up<?php echo $i + 1;?>" id="img_add_up<?php echo $i + 1;?>" accept="image/*" onchange="fileUp3(this, 'img_add_up<?php echo $i + 1;?>', <?php echo $i; ?>, 'before')">
                            <label for="img_add_up<?php echo $i + 1;?>">
                                <img src="/data/file/complain_add/<?php echo $complain_add_file_row['bf_file']; ?>" class="img_add_up<?php echo $i + 1;?>" alt="">
                            </label>

                            <div class="file_del">
                                <input type="checkbox" name="complain_add_file_del[<?php echo $complain_add_file_row['bf_no'];?>]" id="complain_add_file_del<?php echo $i+1;?>" class="complain_add_file_del" value="1">
                                <label for="complain_add_file_del<?php echo $i+1;?>">삭제</label>
                            </div>
                        </div>
                    <?php }?>
                </div>
            </div>
        </div>
        <?php }?>
        <?php if($complain_row['complain_answer'] != '' && $complain_status == "CD"){?>
        <div class="online_complain_answer ver2">
            <div class="inner">
                <div class="compl_anser_tit">
                    <img src="/images/answer_icons.svg" alt=""> 처리결과
                </div>
                <div class="comple_answers mgt10">
                    <textarea name="complain_answer" id="complain_answer" class="bansang_ipt ver2 ta" readonly><?php echo $complain_row['complain_answer']; ?></textarea>
                </div>
                <?php if($complain_ans_file_res){?>
                <div class="bbs_img_wrap ver2">
                    <?php for($i=0;$complain_ans_file_row = sql_fetch_array($complain_ans_file_res);$i++){?>
                    <div class="bbs_img_box" onclick="bigSize('/data/file/complain_answer/<?php echo $complain_ans_file_row['bf_file']; ?>')">
                        <img src="/data/file/complain_answer/<?php echo $complain_ans_file_row['bf_file']; ?>" alt="">
                    </div>
                    <?php }?>
                </div>
                <?php }?>
            </div>
        </div>
        <div class="online_complain_answer bottom ver2 mgt30">
            <div class="inner">
                <div class="compl_anser_tit">
                 추가내용
                </div>
                <div class="comple_answers mgt10">
                    <textarea name="complain_memo" id="complain_memo" class="bansang_ipt ver2 ta" readonly><?php echo $complain_row['complain_memo']; ?></textarea>
                </div>
                <?php if($complain_add_file_res2){?>
                <div class="bbs_img_wrap ver2">
                    <?php for($i=0;$complain_add_file_row = sql_fetch_array($complain_add_file_res2);$i++){?>
                    <div class="bbs_img_box" onclick="bigSize('/data/file/complain_add/<?php echo $complain_add_file_row['bf_file']; ?>')">
                        <img src="/data/file/complain_add/<?php echo $complain_add_file_row['bf_file']; ?>" alt="">
                    </div>
                    <?php }?>
                </div>
                <?php }?>
            </div>
        </div>
        <?php }?>
        <?php if($complain_status != "CD"){?>
        <div class="fix_btn_back_box"></div>
        <div class="fix_btn_box flex_ver ver3">
            <?php if($w == ""){?>
            <button type="button" class="fix_btn" id="fix_btn" onClick="historyBack();">취소</button>
            <?php }?>
            <?php if($complain_status == "CC"){?>
            <button type="button" class="fix_btn on" id="fix_btn" onClick="complain_answer_update();">저장</button>
            <?php }else{ ?>
            <button type="button" class="fix_btn on" id="fix_btn" onClick="complain_mng_update();">저장</button>
            <?php }?>
        </div>
        <?php }?>
    </div>
</div>

<div class="cm_pop" id="department_ch_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
		<p class="cm_pop_desc2">담당자 변경</p>
        <ul class="regi_list">
            <li>
                <p class="regi_list_title">부서</p>
                <div class="ipt_box">
                    <?php
                    $depart_ch_sql = "SELECT * FROM a_mng_department WHERE is_del = 0 ORDER BY is_prior asc, md_idx asc";
                    $depart_ch_res = sql_query($depart_ch_sql);
                    ?>
                    <select name="depart_ch" id="depart_ch" class="bansang_sel" onchange="dapart_change2();">
                        <option value="">선택</option>
                        <?php for($i=0;$depart_row = sql_fetch_array($depart_res);$i++){?>
                            <option value="<?php echo $depart_row['md_idx'];?>" <?php echo get_selected($complain_row['mng_department'], $depart_row['md_idx']); ?>><?php echo $depart_row['md_name'];?></option>
                        <?php } ?>
                    </select>
                    <script>
                        function dapart_change2(){
                            var departSelect = document.getElementById("depart_ch");
                            var departValue = departSelect.options[departSelect.selectedIndex].value;

                            console.log(departValue);
                            let building_id = "<?php echo $complain_row['building_id']; ?>";

                            $.ajax({

                            url : "/adm/mng_select_ajax.php", //ajax 통신할 파일
                            type : "POST", // 형식
                            data: { "departValue":departValue, "building_id":building_id, "group":1}, //파라미터 값
                            success: function(msg){ //성공시 이벤트

                                console.log(msg);
                                $("#mg_name_ch").html(msg);
                                
                            }

                            });
                        }
                    </script>
                </div>
            </li>
            <li>
                <p class="regi_list_title">담당자</p>
                <div class="ipt_box">
                    <?php 
                    $mng_sql2 = "SELECT mng_b.*, mng.mng_department, mng.mng_grades, mng.mng_name, mng_gr.mg_name FROM a_mng_building as mng_b
                                LEFT JOIN a_mng as mng ON mng_b.mb_id = mng.mng_id
                                LEFT JOIN a_mng_grade as mng_gr ON mng_gr.mg_idx = mng.mng_grades
                                WHERE mng_b.is_del = 0 and mng.mng_department = '{$complain_row['mng_department']}' and mng_b.building_id = '{$complain_row['building_id']}'
                                ORDER BY mng_b.mng_id desc";
                    // echo $mng_sql;
                    $mng_res2 = sql_query($mng_sql2);
                    ?>
                    <select name="mg_name_ch" id="mg_name_ch" class="bansang_sel">
                        <option value="">선택</option>
                        <?php while($mng_row = sql_fetch_array($mng_res2)){?>
                        <option value="<?php echo $mng_row['mb_id']; ?>" <?php echo get_selected($complain_row['mng_id'], $mng_row['mb_id']); ?>><?php echo $mng_row['mng_name'].' '.$mng_row['mg_name'];?></option>
                        <?php }?>
                    </select>
                </div>
            </li>
            <li>
                <p class="regi_list_title">변경 사유</p>
                <div class="ipt_box">
                    <textarea name="depart_ch_memo2" id="depart_ch_memo2" class="bansang_ipt ver2 ta" placeholder="변경 이유를 입력해주세요."></textarea>
                </div>
            </li>
        </ul>
		<div class="cm_pop_btn_box flex_ver">
			<button type="button" class="cm_pop_btn" onClick="popClose('department_ch_pop');">취소</button>
            <button type="button" class="cm_pop_btn ver2" onClick="mng_change_handler();">변경</button>
		</div>
	</div>
</div>

<!-- 담당자 변경 -->
<!-- heic 파일용 -->
<script src="https://cdn.jsdelivr.net/npm/heic2any/dist/heic2any.min.js"></script>
<script>
function mng_change_handler(){
    let complain_idx = "<?php echo $complain_idx; ?>";
    let depart_ch = $("#depart_ch option:selected").val();
    let mg_name_ch = $("#mg_name_ch option:selected").val();
    let depart_ch_memo2 = $("#depart_ch_memo2").val();
    let wid = "<?php echo $member['mb_id']; ?>";

    let sendData = {'mng_department': depart_ch, 'mng_id':mg_name_ch, 'complain_idx':complain_idx, 'mng_change_memo':depart_ch_memo2, 'wid':wid};

    $.ajax({
        type: "POST",
        url: "/sm_complain_info_mng_change.php",
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
                    location.replace("/sm_index.php?tabIdx=3");
                    //window.location.reload();
                }, 700);
            }
        },
    });
}
</script>


<div class="cm_pop" id="department_ch_memo_pop">
    <div class="cm_pop_back"></div>
    <div class="cm_pop_cont">
        <p class="cm_pop_desc2">변경 사유</p>
        <ul class="regi_list">
            <li>
                <div class="ipt_box">
                    <textarea name="depart_ch_memo" id="depart_ch_memo" class="bansang_ipt ver2 ta" placeholder="변경 이유를 입력해주세요."><?php echo $complain_row['mng_change_memo']; ?></textarea>
                </div>
            </li>
        </ul>
        <div class="cm_pop_btn_box">
            <button type="button" class="cm_pop_btn ver2" onClick="popClose('department_ch_memo_pop');">확인</button>
		</div>
    </div>
</div>
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


//답변 파일 첨부
// 파일첨부
var filesArr2 = new Array();
var attFileCntAnw1 = 0;
var attFileCntAnw2 = document.querySelectorAll('.filebox2').length; //이미 추가된 파일

//이미 추가된 파일이 있는 경우 빈값입력
for(var j=0;j<attFileCntAnw2;j++){

    //console.log("fileArr index", j);
    filesArr2.splice(j, 0, new Blob([''], { type: 'application/octet-stream' }));

}

function addFile2(obj){

    console.log('2222');

    var maxFileCnt = 5;   // 첨부파일 최대 개수
    var attFileCnt = document.querySelectorAll('.filebox2').length;    // 기존 추가된 첨부파일 개수
    var remainFileCnt = maxFileCnt - attFileCnt;    // 추가로 첨부가능한 개수
    var curFileCnt = obj.files.length;  // 현재 선택된 첨부파일 개수

    let cnt = attFileCnt;

    // 첨부파일 개수 확인
    if (curFileCnt > remainFileCnt) {
        alert("답변 첨부파일은 최대 " + maxFileCnt + "개 까지 첨부 가능합니다.");
    } else {
        for (const file of obj.files) {

            console.log('file', file);
            // 첨부파일 검증
            if (validation(file)) {
                // 파일 배열에 담기
                var reader = new FileReader();
                reader.onload = async function (e) {
                    //filesArr2.push(file);
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
                                <div class="img_upload_box ver44 filebox2">
                                    <input type="file" name="img_a_up${cnt}" id="img_a_up${cnt + 1}" accept="image/*" onchange="fileUp2(this, 'img_a_up${cnt + 1}', ${cnt}, 'before')">
                                    <label for="img_a_up${cnt + 1}">
                                        <img src="${url}" class="img_a_up${cnt + 1}" alt="">
                                    </label>
                                </div>
                            `;
                        } catch (err) {
                            console.log('err', err);
                        }

                    }else{
                        previewHTML = `
                            <div class="img_upload_box ver44 filebox2">
                                <input type="file" name="img_a_up${cnt}" id="img_a_up${cnt + 1}" accept="image/*" onchange="fileUp2(this, 'img_a_up${cnt + 1}', ${cnt}, 'before')">
                                <label for="img_a_up${cnt + 1}">
                                    <img src="${e.target.result}" class="img_a_up${cnt + 1}" alt="">
                                </label>
                            </div>
                        `;
                    }

                    filesArr2.push(processedFile);

                    cnt++;

                    $('.img_upload_wrap2').append(previewHTML);
                };
                reader.readAsDataURL(file);

                attFileCnt23 = document.querySelectorAll('.filebox2').length + curFileCnt;

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

function fileUp2(input, type, index, datas){
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('.' + type).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);

        //filesArr.push(input.files[0]);
    
        filesArr2[index] = input.files[0];

        console.log('file up file arr 2', filesArr)
      
    }
}

//추가내용 파일 첨부
// 파일첨부
var filesArr3 = new Array();
var attFileCntAdd = 0;
var attFileCntAdd2 = document.querySelectorAll('.filebox3').length; //이미 추가된 파일

//이미 추가된 파일이 있는 경우 빈값입력
for(var j=0;j<attFileCntAdd2;j++){

    //console.log("fileArr index", j);
    filesArr3.splice(j, 0, new Blob([''], { type: 'application/octet-stream' }));

}

function addFile3(obj){

    console.log('3333');

    var maxFileCnt = 5;   // 첨부파일 최대 개수
    var attFileCnt = document.querySelectorAll('.filebox3').length;    // 기존 추가된 첨부파일 개수
    var remainFileCnt = maxFileCnt - attFileCnt;    // 추가로 첨부가능한 개수
    var curFileCnt = obj.files.length;  // 현재 선택된 첨부파일 개수

    let cnt = attFileCnt;

    // 첨부파일 개수 확인
    if (curFileCnt > remainFileCnt) {
        alert("추가내용 첨부파일은 최대 " + maxFileCnt + "개 까지 첨부 가능합니다.");
    } else {
        for (const file of obj.files) {

            console.log('file', file);
            // 첨부파일 검증
            if (validation(file)) {
                // 파일 배열에 담기
                var reader = new FileReader();
                reader.onload = async function (e) {
                    //filesArr3.push(file);

                    let processedFile = file;
                    // let previewHTML = `
                    //     <div class="filebox">
                    //         <img src="${e.target.result}" alt="">
                    //     </div>
                    // `;
                    console.log('cnt', cnt);

                    let previewHTML;

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
                                <div class="img_upload_box filebox3">
                                    <input type="file" name="img_add_up${cnt}" id="img_add_up${cnt + 1}" accept="image/*" onchange="fileUp3(this, 'img_add_up${cnt + 1}', ${cnt}, 'before')">
                                    <label for="img_add_up${cnt + 1}">
                                        <img src="${url}" class="img_add_up${cnt + 1}" alt="">
                                    </label>
                                </div>
                            `;
                        } catch (err) {
                            console.log('err', err);
                        }

                    }else{
                        previewHTML = `
                            <div class="img_upload_box filebox3">
                                <input type="file" name="img_add_up${cnt}" id="img_add_up${cnt + 1}" accept="image/*" onchange="fileUp3(this, 'img_add_up${cnt + 1}', ${cnt}, 'before')">
                                <label for="img_add_up${cnt + 1}">
                                    <img src="${e.target.result}" class="img_add_up${cnt + 1}" alt="">
                                </label>
                            </div>
                        `;
                    }

                    filesArr3.push(processedFile);

                    cnt++;

                    $('.img_upload_wrap3').append(previewHTML);
                };
                reader.readAsDataURL(file);

                attFileCnt233 = document.querySelectorAll('.filebox3').length + curFileCnt;

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

function fileUp3(input, type, index, datas){
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            $('.' + type).attr('src', e.target.result);
        }
        reader.readAsDataURL(input.files[0]);

        //filesArr.push(input.files[0]);
    
        filesArr3[index] = input.files[0];

        console.log('file up file arr 3', filesArr)
      
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

//담당 매니저 설정
function complain_mng_update(){
    let complain_idx = "<?php echo $complain_idx; ?>";
    let complain_status = "<?php echo $complain_status; ?>";
    let complain_id = "<?php echo $complain_row['complain_id']; ?>";
    let mng_department; 
    let mng_id;
    let wid = "<?php echo $member['mb_id']; ?>";

    if(complain_status == "CA"){
        mng_department = $("#mng_department").val();
    }else if(complain_status == "CB"){
        mng_department = $("#mng_department option:selected").val();
    }

    if(complain_status == "CA"){
        mng_id = $("#mng_id").val();
    }else if(complain_status == "CB"){
        mng_id = $("#mng_id option:selected").val();
    }
    

    let sendData = {'mng_department': mng_department, 'mng_id':mng_id, 'complain_idx':complain_idx, 'complain_status':complain_status, 'complain_id':complain_id, 'wid':wid};

    $.ajax({
        type: "POST",
        url: "/sm_complain_info_status_update.php",
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
                    location.replace("/sm_index.php?tabIdx=3");
                }, 700);
            }
        },
    });
}


//답변 처리하기
function complain_answer_update(){
 
    let complain_idx = "<?php echo $complain_idx; ?>";
    let complain_answer = $("#complain_answer").val();
    let complain_status = $("#complain_status option:selected").val();
    let complain_memo = $("#complain_memo").val();
    let complain_id = "<?php echo $complain_row['complain_id']; ?>";
    let wid = "<?php echo $member['mb_id']; ?>";

    var formData = new FormData();
    formData.append('complain_idx', complain_idx);
    formData.append('complain_id', complain_id);
    formData.append('complain_answer', complain_answer);
    formData.append('complain_memo', complain_memo);
    formData.append('complain_status', complain_status);
    formData.append('wid', wid);

    //답변 파일 첨부
    for (var i = 0; i < filesArr2.length; i++) {
        // 삭제되지 않은 파일만 폼데이터에 담기
        formData.append("answer_file[]", filesArr2[i]);
    }

    // 답변파일삭제 체크된 삭제 항목 추가
    $("input[name^=complain_answer_file_del]").each(function() {
        if($(this).is(":checked") == true){
            formData.append("complain_answer_file_del[]", '1'); // 체크된 파일의 번호 추가
        }else{
              formData.append("complain_answer_file_del[]", '0'); // 체크된 파일의 번호 추가
        }
    });

     //추가 파일 첨부
     for (var i = 0; i < filesArr3.length; i++) {
        // 삭제되지 않은 파일만 폼데이터에 담기
        formData.append("answer_add_file[]", filesArr3[i]);
    }

    // 추가파일 체크된 삭제 항목 추가
    $("input[name^=complain_add_file_del]").each(function() {
        if($(this).is(":checked") == true){
            formData.append("complain_add_file_del[]", '1'); // 체크된 파일의 번호 추가
        }else{
              formData.append("complain_add_file_del[]", '0'); // 체크된 파일의 번호 추가
        }
    });

    $.ajax({
        type: "POST",
        url: "/sm_complain_info_answer_update.php",
        data: formData,
        cache: false,
        async: false,
        dataType: "json",
        contentType: false,
        processData: false,
        success: function(data) {
            console.log('data:::', data);
            if(data.result == false) { 
                showToast(data.msg);
                //$(".btn_submit").attr('disabled', false);
                return false;
            }else{
                showToast(data.msg);

                setTimeout(() => {
                    location.replace('/sm_complain_info.php?complain_idx=' + complain_idx + '&complain_status=' + data.data);
                }, 700);
            }
        },
        error:function(e){
            showToast(e);
        }
    });
}


let complain_status_val = "<?php echo $complain_status; ?>";
if(complain_status_val == "CC"){
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