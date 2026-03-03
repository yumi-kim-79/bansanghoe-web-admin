<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$building_sql = "SELECT building.*, post.post_name FROM a_building as building
                 LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                 WHERE building.building_id = '{$building_id}'";
$building_row = sql_fetch($building_sql);

//echo $building_sql;
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <div class="building_mng_box">
                <p class="building_mng_tit"><?php echo $building_row['post_name']; ?> - <?php echo $building_row['building_name'];?></p>
                <button class="building_mng_tit2" onclick="copyToClipboard('<?php echo $building_row['building_addr']; ?>');"><?php echo $building_row['building_addr'].' '.$building_row['building_addr2']; ?> <i><img src="/images/copy_icons_g.svg" alt=""></i></button>
            </div>
            <script>
                function copyToClipboard(text) {
                    navigator.clipboard.writeText(text).then(() => {
                        showToast("복사되었습니다. 원하는 곳에 붙여넣기하여 주세요.");
                    }).catch(() => {
                        prompt("키보드의 ctrl+C 또는 마우스 오른쪽의 복사하기를 이용해주세요.",text);
                    });
                };
            </script>
            <ul class="mypage_menu_list">
                <li>
                    <a href="/building_info.php?building_id=<?php echo $building_id; ?>">
                    단지 정보
                    </a>
                </li>
                <li>
                    <a href="/building_memo.php?building_id=<?php echo $building_id; ?>">
                        단지 메모
                    </a>
                </li>
                <li>
                    <a href="/sm_manage_company.php?building_id=<?php echo $building_id; ?>">
                        용역 업체
                    </a>
                </li>
                <li>
                    <a href="/sm_manage_info.php?building_id=<?php echo $building_id; ?>">
                        관리단 정보
                    </a>
                </li>
                <li>
                    <a href="/household_mng.php?building_id=<?php echo $building_id; ?>">
                        세대 관리
                    </a>
                </li>
                <li>
                    <a href="/sm_car_manage.php?building_id=<?php echo $building_id; ?>">
                        차량 관리
                    </a>
                </li>
                <li>
                    <a href="/sm_board.php?building_id=<?php echo $building_id; ?>">
                        공문/안내문/이벤트
                    </a>
                </li>
                <li>
                    <a href="/expense_report_list.php?building_id=<?php echo $building_id; ?>">
                        품의서
                    </a>
                </li>
                
                <li>
                    <a href="/online_vote.php?types=sm&building_id=<?php echo $building_id; ?>">
                        온라인 투표
                    </a>
                </li>
                
            </ul>
        </div>
    </div>
</div>
<?php
include_once(G5_PATH.'/tail.php');
?>