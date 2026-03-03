<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$building_sql = "SELECT building.*, post.post_name FROM a_building as building
                 LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                 WHERE building.building_id = '{$building_id}'";
$building_row = sql_fetch($building_sql);

$building_file = "SELECT * FROM g5_board_file WHERE bo_table = 'building' and wr_id = '{$building_id}' ORDER BY bf_no asc";
//echo $building_file;
$building_file_res = sql_query($building_file);
$building_file_total = sql_num_rows($building_file_res);
//echo $building_file;

$my_building_sql = "SELECT * FROM a_mng_building WHERE mb_id = '{$member['mb_id']}'";
$my_building_res = sql_query($my_building_sql);

$my_building_arr = array();

while($my_building_row = sql_fetch_array($my_building_res)){
    array_push($my_building_arr, $my_building_row['building_id']);
}


$building_info = sql_fetch("SELECT *, COUNT(*) as cnt FROM a_building_info WHERE building_id = '{$building_id}'");
// print_r2($building_info);

if($_SERVER['REMOTE_ADDR'] == '59.16.155.80'){
    // print_r2($building_row);
}
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <div class="building_info_boxs">
                    <div class="build_box build_flex">
                        <div class="build_label">건물명</div>
                        <div class="build_cts"><?php echo $building_row['building_name'];?></div>
                    </div>
                    <div class="build_box build_flex">
                        <div class="build_label">주소</div>
                        <div class="build_cts"><?php echo $building_row['building_addr'].' '.$building_row['building_addr2']; ?></div>
                    </div>
                    <div class="build_box">
                        <div class="build_label">1층 현관 비밀번호</div>
                        <div class="build_cts">
                            <div class="build_cts_ta"><?php echo $building_row['open_password']; ?></div>
                        </div>
                    </div>
                    <div class="build_box">
                        <div class="build_label">CCTV 비밀번호</div>
                        <div class="build_cts">
                            <div class="build_cts_ta"><?php echo $building_row['cctv_password']; ?></div>
                        </div>
                    </div>
                    <div class="build_box">
                        <div class="build_label">비고</div>
                        <div class="build_cts">
                            <div class="build_cts_ta"><?php echo nl2br($building_row['building_bigo']); ?>
                        </div>
                        </div>
                    </div>
                    <div class="build_box build_flex">
                        <div class="build_label">건축주</div>
                        <div class="build_cts"><?php echo $building_row['building_owner']; ?></div>
                    </div>
                    <div class="build_box build_flex">
                        <div class="build_label">분양 사무실</div>
                        <div class="build_cts"><?php echo $building_row['building_estate']; ?></div>
                    </div>
                    <div class="build_box build_flex">
                        <div class="build_label">시공사</div>
                        <div class="build_cts"><?php echo $building_row['building_company']; ?></div>
                    </div>
                </div>
                <?php if(in_array($building_row['building_id'], $my_building_arr)){?>
                <div class="building_info_update_btn">
                    <a href="/building_info_form.php?building_id=<?php echo $building_row['building_id']; ?>">
                        <img src="/images/pencil_icons.svg" alt=""> 수정하기
                    </a>
                </div>
                <?php }?>
            </div>
        </div>
        <div class="building_info_bot">
            <div class="inner">
                <div class="building_info_bot_box">
                    <div class="building_bot_label">단지 정보</div>
                    <div class="building_bot_cts">
                        <div class="building_info">
                            <div class="building_info_box">
                                <div class="building_info_label">건물명</div>
                                <div class="building_info_cont"><?php echo $building_info['building_info_name']; ?></div>
                            </div>
                            <div class="building_info_box">
                                <div class="building_info_label">용도</div>
                                <div class="building_info_cont"><?php echo $building_info['building_info_type']; ?></div>
                            </div>
                            <div class="building_info_box">
                                <div class="building_info_label">법정동 주소</div>
                                <div class="building_info_cont"><?php echo $building_info['building_info_addr1']; ?></div>
                            </div>
                            <div class="building_info_box">
                                <div class="building_info_label">도로명 주소</div>
                                <div class="building_info_cont"><?php echo $building_info['building_info_addr2']; ?></div>
                            </div>
                            <div class="building_info_box">
                                <div class="building_info_label">연면적(㎡)</div>
                                <div class="building_info_cont"><?php echo $building_info['building_info_size']; ?></div>
                            </div>
                            <div class="building_info_box">
                                <div class="building_info_label">사용승인일</div>
                                <div class="building_info_cont"><?php echo $building_info['building_info_use_date']; ?></div>
                            </div>
                            <div class="building_info_box">
                                <div class="building_info_label">층수(지상/지하)</div>
                                <div class="building_info_cont"><?php echo $building_info['building_info_floor_up']; ?></div>
                            </div>
                            <div class="building_info_box">
                                <div class="building_info_label">승강기(승용/비상)</div>
                                <div class="building_info_cont"><?php echo $building_info['building_info_elevation']; ?></div>
                            </div>
                            <div class="building_info_box">
                                <div class="building_info_label">주차대수(옥내/옥외)</div>
                                <div class="building_info_cont"><?php echo $building_info['building_info_parking1']; ?></div>
                            </div>
                            <div class="building_info_box">
                                <div class="building_info_label">구조</div>
                                <div class="building_info_cont"><?php echo $building_info['building_info_structure']; ?></div>
                            </div>
                            <div class="building_info_box">
                                <div class="building_info_label">기계식주차(옥내/옥외)</div>
                                <div class="building_info_cont"><?php echo $building_info['building_info_parking2']; ?></div>
                            </div>
                            <div class="building_info_box">
                                <div class="building_info_label">호수(호)</div>
                                <div class="building_info_cont"><?php echo $building_info['building_info_ho']; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if($building_file_total > 0){?>
                <div class="building_info_bot_box">
                    <div class="building_bot_label">첨부 파일</div>
                    <div class="building_bot_cts">
                        <ul class="building_file_list">
                            <?php for($i=0;$building_row = sql_fetch_array($building_file_res);$i++){?>
                            <li>
                                <a href="javascript:buidling_file_download('/data/file/building/<?php echo $building_row['bf_file'];?>', '<?php echo $building_row['bf_source']; ?>');" class="build_file_down">
                                    <?php echo $building_row['bf_source']; ?>
                                </a>
                            </li>
                            <?php }?>
                        </ul>
                    </div>
                </div>
                <?php }?>
                <div class="building_info_bot_box">
                    <div class="building_bot_label">관리 규약</div>
                    <div class="building_bot_cts">
                        <a href="/mng_policy2.php?building_id=<?php echo $building_id; ?>" class="building_bot_cts_btn">관리 규약 보기</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
function buidling_file_download(url, name){
    console.log('url', url);

    sendMessage('building_file', {"url":url, "name":name});
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>