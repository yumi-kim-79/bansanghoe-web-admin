<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$building_sql = "SELECT building.*, post.post_name FROM a_building as building
                 LEFT JOIN a_post_addr as post on building.post_id = post.post_idx
                 WHERE building.building_id = '{$building_id}'";
$building_row = sql_fetch($building_sql);

// ★업체 목록 조회는 sm_manage_company_ajax.php 한 곳에서만 한다(2026-08).
//   이 페이지는 로드 직후 tab_handler()가 ajax 결과로 .content_box_wrap 을 통째로 덮어쓰므로
//   여기서 목록을 한 번 더 그려봐야 화면에 남지 않는다. 그런데도 조건이 두 벌로 갈라져 있어
//   "관리자엔 있는데 앱엔 없다"는 혼선의 원인이 됐다(조건 불일치). → 목록 쿼리 제거.
?>
<div id="wrappers">
    <div class="wrap_container">
        <div class="parking_sc parking_sc1">
            <div class="inner">
                <p class="mng_title"><?php echo $building_row['post_name']; ?> - <?php echo $building_row['building_name'];?></p>
            </div>
        </div>
        <div class="car_content">
            <div class="inner">
                <ul class="tab_lnb">
                    <!-- 250826 수정 <li class="tab01 on" onclick="tab_handler('1', 'out')" >입주민 공개 정보</li> -->
                    <li class="tab01 on" onclick="tab_handler('1', 'out')" >관리업체정보</li>
                    <!-- <li class="tab02" onclick="tab_handler('2', 'in')">내부 공개용(입주민 공유 금지)</li> -->
                    <li class="tab02" onclick="tab_handler('2', 'in')">입주민 비공개 업체</li>
                </ul>
            </div>
            <!--
            fire_icons_sm 소방
            parking_tower_icons 주차타워
            mng_icons 관리소장
            elector_icon_sm 전기
            elevetor_icons 승강기
            car_lift_sm 카리프트
            clean_icons_sm 청소
            disinfection_icons_sm 소독
            repair_icon_sm 기계설비
            more_icon_sm 기타
            -->
            <div class="mng_list_box ver2">
                <div class="mng_company_alert">
                <p>계약 상세 정보는 입주민에게 공개 <strong>[ 절대 금지 ]</strong></p>
                <p>금액, 계약기간 등을 안내할 경우<br />문제 발생 할 수 있으므로 상급자 확인 필요</p>
                </div>
                <div class="inner content_box_wrap">
                    <!-- 목록은 sm_manage_company_ajax.php 가 채운다(로드 직후 tab_handler 실행) -->
                    <div class="faq_empty_box">불러오는 중...</div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
let tabIdx = "<?php echo $tabIdx ?? '1'; ?>";

tab_handler(tabIdx, 'out');

function tab_handler(index, code){
    $(".tab_lnb li").removeClass("on");
    $(".tab0" + index).addClass("on");

    let building_id = "<?php echo $building_id; ?>";

    $.ajax({

    url : "/sm_manage_company_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "code":code, "building_id":building_id}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".content_box_wrap").html(msg);
    }

    });
}

</script>
<?php
include_once(G5_PATH.'/tail.php');
?>