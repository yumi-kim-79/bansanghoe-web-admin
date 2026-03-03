<?php
$sub_menu = "700100";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '등록';
}

$g5['title'] .= '점검일지 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;


$sql = "SELECT insp.*, post.post_name, building.building_name, dong.dong_name, indus.industry_name, cmp.company_name FROM a_inspection as insp 
        LEFT JOIN a_building as building on insp.building_id = building.building_id
        LEFT JOIN a_building_dong as dong on insp.dong_id = dong.dong_id
        LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
        LEFT JOIN a_industry_list as indus on insp.inspection_category = indus.industry_idx
        LEFT JOIN a_manage_company as cmp on insp.inspection_cmp = cmp.company_idx
        WHERE insp.inspection_idx = '{$inspection_idx}'";
$row = sql_fetch($sql);

//점검일지에 이미지 파일 등록했는지
$inspection_file = "SELECT * FROM g5_board_file WHERE bo_table = 'inspection' and wr_id = '{$inspection_idx}' ORDER BY bf_no asc ";
$inspection_file_res = sql_query($inspection_file); 

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;

    //print_r2($row);
}
?>

<form name="fstudent" id="fstudent" action="./student_form_update.php" onsubmit="return fstudent_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="st_idx" value="<?php echo $row['st_idx']; ?>">
    <!-- <?php if($row['st_status'] && $w == 'u'):?>
    <input type="hidden" name="st_status" value="<?php echo $row['st_status']; ?>">
    <?php endif; ?> -->

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
                    <th>지역</th>
                    <td >
                        <input type="text" name="post_name" id="post_name" class="bansang_ipt" value="<?php echo $row['post_name']; ?>" readonly>
                    </td>
                    <th>단지</th>
                    <td >
                        <input type="text" name="building_name" id="building_name" class="bansang_ipt" value="<?php echo $row['building_name']; ?>" readonly size="50">
                    </td>
                </tr>
                <!-- <tr>
                    <th>동</th>
                    <td colspan="3">
                        <input type="text" name="dong_name" id="dong_name" class="bansang_ipt" value="<?php echo $row['dong_name'].'동'; ?>" readonly>
                    </td>
                </tr> -->
                <tr>
                    <th>항목</th>
                    <td>
                        <input type="text" name="industry_name" id="industry_name" class="bansang_ipt" value="<?php echo $row['industry_name']; ?>" readonly>
                    </td>
                    <th>업체</th>
                    <td>
                        <input type="text" name="company_name" id="company_name" class="bansang_ipt" value="<?php echo $row['company_name']; ?>" readonly size="50">
                    </td>
                </tr>
                <tr>
                    <th>점검일자</th>
                    <td>
                        <input type="text" name="created_at" id="created_at" class="bansang_ipt" value="<?php echo date("Y-m-d", strtotime($row['created_at'])); ?>" readonly>
                    </td>
                    <th>상태</th>
                    <td>
                        <?php
                        switch($row['inspection_status']){
                            case "N":
                                $istatus = "승인대기";
                                break;
                            case "Y":
                                $istatus = "승인";
                                break;
                            case "R":
                                $istatus = "재점검";
                                break;
                            case "H":
                                $istatus = "보류";
                                break;
                        }
                        
                        ?>
                        <input type="text" name="istatus" id="istatus" class="bansang_ipt" value="<?php echo $istatus; ?>" readonly>
                    </td>
                </tr>
            </tbody>
        </table>
        <div class="tbl_frm01 tbl_wrap mgt20">
            <div class="h2_frm_wraps">
                <h2 class="h2_frm">점검일지 항목</h2>
                <?php if($row['inspection_status'] != 'Y'){?>
                <div class="btn_wraps">
                    <?php if($row['inspection_status'] != 'R'){?>
                    <button type="button" onclick="status_change('R');" class="btn btn_01">재요청</button>
                    <?php }?>
                    <?php if($row['inspection_status'] != 'H'){?>
                    <button type="button" onclick="status_change('H');" class="btn btn_02">보류</button>
                    <?php }?>
                    <button type="button" onclick="status_change('Y');" class="btn btn_03">승인</button>
                </div>
                <script>
                    function status_change(status){

                        var status_t;
                        switch(status){
                            case "R":
                                status_t = "재요청 하시겠습니까?\n작성자에게 재요청 연락하시기 바랍니다.";
                                break;
                            case "H":
                                status_t = "보류 하시겠습니까?\n입주민에게 해당 점검일지는 보여지지 않습니다.";
                                break;
                            case "Y":
                                status_t = "승인 하시겠습니까?\n입주민에게 점검일지가 보여집니다.";
                                break;
                        }

                        if (!confirm(status_t)) {
                            return false;
                        }

                        let sendData = {'inspection_idx': '<?php echo $inspection_idx; ?>', 'inspection_status':status};

                        $.ajax({
                            type: "POST",
                            url: "./inspection_status_change_ajax.php",
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
                                    alert(data.msg);

                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 300);
                                }
                            },
                        });

                    }
                </script>
                <?php }?>
            </div>
            <table>
                <tr>
                    <th>점검제목</th>
                    <td>
                        <input type="text" name="inspection_title" id="inspection_title" class="bansang_ipt" value="<?php echo $row['inspection_title']; ?>" readonly>
                    </td>
                </tr>
                <tr>
                    <th>작성자</th>
                    <td><?php echo $row['inspection_name']; ?></td>
                </tr>
                <tr>
                    <th>작성자 연락처</th>
                    <td><?php echo $row['inspection_hp']; ?></td>
                </tr>
                <tr>
                    <th>첨부사진</th>
                    <td>
                        <div class="inspection_mng_photo_wrap">
                            <?php for($i=0;$inspection_file_row = sql_fetch_array($inspection_file_res);$i++){?>
                                <div class="inspection_mng_photo_box" onclick="bigSize('/data/file/inspection/<?php echo $inspection_file_row['bf_file']; ?>')">
                                    <img src="/data/file/inspection/<?php echo $inspection_file_row['bf_file']; ?>" alt="">
                                </div>
                            <?php }?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>특이사항</th>
                    <td>
                        <textarea name="" id="" class="bansang_ipt ta full" readonly><?php echo $row['inspection_memo']; ?></textarea>
                    </td>
                </tr>
            </table>
            
            
        </div>
    </div>
    <div class="btn_fixed_top">
        
        <a href="./inspection_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <!-- <input type="submit" value="저장" class="btn_submit btn btn_02" accesskey='s'> -->
    </div>
</form>

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


function fstudent_submit(f) {
   

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

