<?php
$sub_menu = $_GET['type'] == "Y" ? "200200" : "200300";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else if($w == 'a'){
    $hthd = "해지 ";
    $html_title = '수정';
}else{
    $html_title = '추가';
}

$g5['title'] .= $hthd.'단지 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');


$sql = "SELECT * FROM a_building
        WHERE building_id = {$building_id}";
$row = sql_fetch($sql);

//지역
$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

//동
$dong_sql = "SELECT * FROM a_building_dong WHERE is_del = 0 and building_id = '{$building_id}' ORDER BY dong_name + 0 asc, dong_id desc";
// echo $dong_sql.'<br>';
$dong_res = sql_query($dong_sql);

//파일
$files_pdf = "SELECT * FROM g5_board_file WHERE bo_table = 'building' and wr_id = '{$building_id}' and bf_file != ''";
$files_pdf_res = sql_query($files_pdf);

$files_pdf_list = array();

while($files_pdf_row = sql_fetch_array($files_pdf_res)){
    array_push($files_pdf_list, $files_pdf_row);
}

$building_info_sql = "SELECT * FROM a_building_info WHERE building_id = '{$building_id}'";
$building_info_row = sql_fetch($building_info_sql);

$mb_ids = $member['mb_id'];
$mng_infos = get_manger($mb_ids);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql.'<br>';
    echo $post_sql.'<br>';
    echo $building_info_sql.'<br>';
    //print_r2($files_pdf_list);
    // print_r2($mng_infos);
}

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>

<form name="fbuilding" id="fbuilding" action="./building_mng_add_update.php" onsubmit="return fbuilding_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="building_id" value="<?php echo $row['building_id']; ?>">
    <input type="hidden" name="type" value="<?php echo $type;?>">

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">단지 정보</h2>
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
                    <th>단지명</th>
                    <td><input type="text" name="building_name" id="building_name" class="bansang_ipt ver2" value="<?php echo $row['building_name']; ?>" required size="50"></td>
                    <th>단지면적(㎡)</th>
                    <td>
                        <input type="number" name="building_size" id="building_size" class="bansang_ipt ver2" value="<?php echo $row['building_size']; ?>" min="0">
                    </td>
                </tr>
                <tr>
                    <th>동 설정</th>
                    <td colspan="3">
                        <?php echo help("최소 1개의 동을 입력해주세요. (숫자와 -만 입력)");?>
                        <?php if($w=="u" || $w == 'a'){?>

                        <div class="dong_select_wrap">
                            <?php for($i=0;$dong_row = sql_fetch_array($dong_res);$i++){?>
                            <div class="ipt_box flex_ver dong_form_box">
                                <input type="hidden" name="dong_id[]" value="<?php echo $dong_row['dong_id']; ?>">
                                <input type="text" name="dong_name[]" class="bansang_ipt ver2" value="<?php echo $dong_row['dong_name']; ?>" oninput="validateInput(this);">
                                <!-- oninput="validateInput(this);" -->
                                <?php if($i==0){?>
                                <button type="button" class="bansang_btns ver1" onclick="dong_add();">추가</button>
                                <?php }else{ ?>
                                <div class="dong_del_box">
                                    <input type="checkbox" name="dong_del[<?php echo $i; ?>]" id="dong_del<?php echo $i + 1; ?>" value="1">
                                    <label for="dong_del<?php echo $i + 1; ?>">삭제</label>
                                </div>
                                <?php }?>
                            </div>
                            <?php }?>
                        </div>
                            
                        <?php }else{ ?>
                        <div class="dong_select_wrap">
                            <div class="ipt_box flex_ver dong_form_box">
                                <input type="text" name="dong_name[]" class="bansang_ipt ver2">
                                <button type="button" class="bansang_btns ver1" onclick="dong_add();">추가</button>
                            </div>
                        </div>
                        <?php }?>

                        <script>
                            // oninput="validateInput(this);"
                            function dong_add(){
                                let html = `<div class="ipt_box flex_ver dong_form_box">
                                <input type="text" name="dong_name[]" class="bansang_ipt ver2" oninput="validateInput(this);">
                                <button type="button" onclick="dong_remove(this);" class="bansang_btns ver2">삭제</button>
                            </div>`;
                                $(".dong_select_wrap").append(html);
                            }

                            function dong_remove(ele){
                                ele.closest('.dong_form_box').remove();
                            }

                            //동 입력시 숫자와 -만 입력
                            function validateInput(input) {
                                // 숫자와 하이픈만 허용 (나머지는 제거)
                                input.value = input.value.replace(/[^0-9\-]/g, '');
                            }
                        </script>
                    </td>
                </tr>
                <tr>
                    <th>지역 선택</th>
                    <td colspan="3">
                        <select name="post_id" id="post_id" class="bansang_sel" required>
                            <option value="">지역 선택</option>
                            <?php for($i=0;$post_row = sql_fetch_array($post_res);$i++){?>
                                <option value="<?php echo $post_row['post_idx']; ?>" <?php echo get_selected($row['post_id'], $post_row['post_idx']); ?>><?php echo $post_row['post_name']; ?></option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>주소</th>
                    <td colspan='3' class="td_addr_line">
                        <label for="st_zip" class="sound_only">우편번호</label>
                        <div class="ipt_box flex_ver">
                            <input type="text" name="building_addr_zip" value="<?php echo $row['building_addr_zip']; ?>" id="building_addr_zip" class="bansang_ipt ver2" size="10" maxlength="6">
                            <button type="button" class="bansang_btns ver1" onclick="win_zip('fbuilding', 'building_addr_zip', 'building_addr', 'building_addr2', 'building_addr3', 'building_addr_jibeon', 'Y');">주소 검색</button>
                        </div>
                        <input type="text" name="building_addr" value="<?php echo $row['building_addr'] ?>" id="building_addr" class="bansang_ipt ver2 mgt10" size="60" readonly>
                      
                        <input type="text" name="building_addr2" value="<?php echo $row['building_addr2'] ?>" id="building_addr2" class="bansang_ipt ver2 mgt10" size="60">
                      
                        <input type="text" name="building_addr3" value="<?php echo $row['building_addr3'] ?>" id="building_addr3" class="bansang_ipt ver2 mgt10" size="60">
                        <input type="hidden" name="building_addr_jibeon" value="<?php echo $row['building_addr_jibeon']; ?>">

                        <script>
                            function building_api(){
                                //console.log($("#building_addr").val());
                                let addr = $("#building_addr").val();

                                if(addr != ""){
                                    buildingInfoPopOpen();

                                    $.ajax({

                                    url : "./building_mng_info_ajax.php", //ajax 통신할 파일
                                    type : "POST", // 형식
                                    data: { "addr":addr}, //파라미터 값
                                    success: function(msg){ //성공시 이벤트
                                        console.log(msg);
                                        $(".building_info_wrap").html(msg);

                                        buildingInfoPopClose();
                                    }

                                    });
                                }
                               
                            }
                        </script>
                    </td>
                </tr>
                <tr>
                    <th>건물 정보</th>
                    <td colspan='3'>
                        <?php echo help("주소로 불러와지지 않는 경우 직접 입력해주세요.");?>
                        <div class="building_info_wrap">
                            <input type="hidden" name="building_api" value="<?php echo $w == "u" ? "Y" : "";?>">
                            <div class="builiding_info_tr">
                                <div class="building_info_th building_info_td">건물명</div>
                                <div class="building_info_td">
                                    <input type="text" name="building_info_name" id="building_info_name" value="<?php echo $building_info_row['building_info_name']; ?>">
                                </div>
                                <div class="building_info_th building_info_th2 building_info_td">용도</div>
                                <div class="building_info_td"><input type="text" name="building_info_type" id="building_info_type" value="<?php echo $building_info_row['building_info_type']; ?>"></div>
                            </div>
                            <div class="builiding_info_tr">
                                <div class="building_info_th building_info_td">법정동 주소</div>
                                <div class="building_info_td"><input type="text" name="building_info_addr1" id="building_info_addr1" value="<?php echo $building_info_row['building_info_addr1'];?>"></div>
                                <div class="building_info_th building_info_th2 building_info_td">도로명 주소</div>
                                <div class="building_info_td"><input type="text" name="building_info_addr2" id="building_info_addr2" value="<?php echo $building_info_row['building_info_addr2']; ?>"></div>
                            </div>
                            <div class="builiding_info_tr">
                                <div class="building_info_th building_info_td">연면적(㎡)</div>
                                <div class="building_info_td"><input type="text" name="building_info_size" id="building_info_size" value="<?php echo $building_info_row['building_info_size']; ?>"></div>
                                <div class="building_info_th building_info_th2 building_info_td">사용승인일</div>
                                <div class="building_info_td"><input type="text" name="building_info_use_date" id="building_info_use_date" value="<?php echo $building_info_row['building_info_use_date']; ?>"></div>
                            </div>
                            <div class="builiding_info_tr">
                                <div class="building_info_th building_info_td">층수(지상/지하)</div>
                                <div class="building_info_td"><input type="text" name="building_info_floor_up" id="building_info_floor_up" value="<?php echo $building_info_row['building_info_floor_up']; ?>"></div>
                                <div class="building_info_th building_info_th2 building_info_td">승강기(승용/비상)</div>
                                <div class="building_info_td"><input type="text" name="building_info_elevation" id="building_info_elevation" value="<?php echo $building_info_row['building_info_elevation']; ?>"></div>
                            </div>
                            <div class="builiding_info_tr">
                                <div class="building_info_th building_info_td">주차대수(옥내/옥외)</div>
                                <div class="building_info_td"><input type="text" name="building_info_parking1" id="building_info_parking1" value="<?php echo $building_info_row['building_info_parking1'];?>"></div>
                                <div class="building_info_th building_info_th2 building_info_td">구조</div>
                                <div class="building_info_td"><input type="text" name="building_info_structure" id="building_info_structure" value="<?php echo $building_info_row['building_info_structure']; ?>"></div>
                            </div>
                            <div class="builiding_info_tr">
                                <div class="building_info_th building_info_td">기계식주차(옥내/옥외)</div>
                                <div class="building_info_td"><input type="text" name="building_info_parking2" id="building_info_parking2" value="<?php echo $building_info_row['building_info_parking2']; ?>"></div>
                                <div class="building_info_th building_info_th2 building_info_td">호수(호)</div>
                                <div class="building_info_td"><input type="text" name="building_info_ho" id="building_info_ho" value="<?php echo $building_info_row['building_info_ho']; ?>"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php if($w == 'u' || $w == 'a'){?>
                <tr>
                    <th>운영 여부</th>
                    <td colspan='3'>
                        <select name="is_use" id="is_use" class="bansang_sel">
                            <option value="1" <?php echo get_selected($row['is_use'], "1"); ?>>정상</option>
                            <option value="0" <?php echo get_selected($row['is_use'], "0"); ?>>해지</option>
                        </select>
                    </td>
                </tr>
                <?php }?>
                <tr>
                    <th>단지 메모</th>
                    <td colspan='3'>
                        <textarea name="building_memo" id="building_memo" class="bansang_ipt ver2 full ta"><?php echo $row['building_memo']?></textarea>
                    </td>
                </tr>
                <tr>
                    <th>고지서 납부계좌</th>
                    <td colspan='3'>
                        <div class="ipt_boxs">
                            <div class="ipt_labels mgb5">은행명</div>
                            <input type="text" name="building_bill_account_bank" id="building_bill_account_bank" class="bansang_ipt ver2" value="<?php echo $row['building_bill_account_bank']; ?>" size="50" required>
                        </div>
                        <div class="ipt_boxs mgt10">
                            <div class="ipt_labels mgb5">계좌번호</div>
                            <input type="text" name="building_bill_account" id="building_bill_account" class="bansang_ipt ver2" value="<?php echo $row['building_bill_account']; ?>" size="50" required>
                        </div>

                        <div class="ipt_boxs mgt10">
                            <div class="ipt_labels mgb5">예금주</div>
                            <input type="text" name="building_bill_account_name" id="building_bill_account_name" class="bansang_ipt ver2" value="<?php echo $row['building_bill_account_name']; ?>" size="50">
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>고지서 공지사항</th>
                    <td colspan='3'>
                        <textarea name="building_bill_notice" id="building_bill_notice" class="bansang_ipt ver2 ta full" required><?php echo $row['building_bill_notice']; ?></textarea>
                    </td>
                </tr>
                <tr>
                    <th>1층 현관 비밀번호</th>
                    <td>
                        <input type="text" name="open_password" id="open_password" class="bansang_ipt ver2" value="<?php echo $row['open_password']?>">
                    </td>
                    <th>CCTV 비밀번호</th>
                    <td>
                        <input type="text" name="cctv_password" id="cctv_password" class="bansang_ipt ver2" value="<?php echo $row['cctv_password']?>">
                    </td>
                </tr>
                <tr>
                    <th>건축주</th>
                    <td>
                        <input type="text" name="building_owner" id="building_owner" class="bansang_ipt ver2" value="<?php echo $row['building_owner']?>">
                    </td>
                    <th>분양사무실</th>
                    <td>
                        <input type="text" name="building_estate" id="building_estate" class="bansang_ipt ver2" value="<?php echo $row['building_estate']?>">
                    </td>
                </tr>
                <tr>
                    <th>시공사</th>
                    <td colspan="3">
                        <input type="text" name="building_company" id="building_company" class="bansang_ipt ver2" value="<?php echo $row['building_company']?>">
                    </td>
                </tr>
                <tr>
                    <th>비고</th>
                    <td colspan="3"><textarea name="building_bigo" id="building_bigo" class="bansang_ipt ver2 full ta"><?php echo $row['building_bigo']?></textarea></td>
                </tr>
                <tr>
                    <th>건출물 대장 / 기타 첨부자료</th>
                    <td colspan="3">
                        <?php echo help("pdf 파일만 첨부 가능합니다.");?>
                        <div class="bn_file_wrap">
                            <div class="ipt_box">
                            <?php for($i=1;$i<=4;$i++){?>
                                <div class="file_box_wrapper">
                                    <div class="file_box">
                                        <input type="file" name="bf_file[]" id="bf_file<?php echo $i;?>" class="bf_file" accept=".pdf">
                                        <label for="bf_file<?php echo $i;?>">
                                            <div class="file_contents_box file_contents_box<?php echo $i; ?>"><?php echo $files_pdf_list[$i - 1]['bf_source']; ?></div>
                                            <div class="label_box">파일첨부</div>
                                        </label>
                                    </div>
                                    <?php if($w == "u" && $files_pdf_list[$i - 1]['bf_source'] != ""){?>
                                        <div class="file_pdf_del">
                                            <input type="checkbox" name="pdf_file_del[<?php echo $i - 1;?>]" id="pdf_file_del<?php echo $i;?>" value="1">
                                            <label for="pdf_file_del<?php echo $i;?>">삭제</label>
                                        </div>
                                        <div class="file_pdf_download">
                                            <a href="/bbs/download_file.php?no=<?php echo $files_pdf_list[$i-1]['bf_no'];?>&bo_table=building&wr_id=<?php echo $building_id; ?>" class="bansang_btns ver1" >다운로드</a>
                                        </div>
                                    <?php }?>
                                    <script>
                                        $("#bf_file<?php echo $i;?>").change(function() {
                                            //readURL(this);
                                            $(".file_contents_box<?php echo $i; ?>").text(this.files[0].name);
                                            console.log(this.files[0].name);
                                        });
                                    </script>
                                </div>
                        
                            <?php }?>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>관리규약</th>
                    <td colspan="3">
                        <textarea name="building_policy" id="building_policy" class="bansang_ipt ver2 full ta"><?php echo $row['building_policy']?></textarea>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <a href="./building_mng.php?type=<?php echo $type; ?>&amp;<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <!-- <?php if($member['mb_level'] == 10 || $mng_infos['mng_certi'] == 'A' || $mng_infos['mng_certi'] == 'B'){?>
        <input type="submit" value="저장" class="btn_submit btn" accesskey='s'>
        <?php }?> -->
        <input type="submit" value="저장" class="btn_submit btn" accesskey='s'>
    </div>
</form>

<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>건물 정보를 가져오는 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>

<script>
function handleOnInput(e)  {
    e.value = e.value.replace(/[^A-Za-z]/ig, '')
}

function buildingInfoPopOpen(){
    $("#building_info_pop").show();
    bodyLock();
}

function buildingInfoPopClose(){
    $("#building_info_pop").hide();
    bodyUnlock();
}

function fbuilding_submit(f) {

    if(f.w.value == "u" && f.is_use.value == "0"){
        if (!confirm("단지를 정말 해지하시겠습니까?")) {
            return false;
        }
    }

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

