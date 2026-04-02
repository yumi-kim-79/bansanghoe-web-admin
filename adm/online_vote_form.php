<?php
$sub_menu = "600100";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '등록';
}

$g5['title'] .= '온라인 투표 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;


$sql = "SELECT vote.*, building.building_name FROM a_online_vote as vote
        LEFT JOIN a_building as building ON vote.building_id = building.building_id
        WHERE vote.vt_id = {$vt_id}";
$row = sql_fetch($sql);

//지역
$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);

//문항
$question_sql = "SELECT * FROM a_online_vote_question WHERE vt_id = '{$row['vt_id']}' and is_del = 0 ORDER BY vtq_id asc";
$question_res = sql_query($question_sql);

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql.'<br>';
    echo $post_sql.'<br>';
    echo $question_sql.'<br>';
    //print_r2($row);
}

if($w == 'u'){
    $building_name = get_builiding_info($row['building_id'])['building_name'];
}

// 투표 템플릿 데이터
include_once('./online_vote_template_data.php');
?>


<form name="fonlinevote" id="fonlinevote" action="./online_vote_form_update.php" onsubmit="return fonlinevote_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="vt_id" value="<?php echo $row['vt_id']; ?>">

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
                    <th>투표 템플릿</th>
                    <td colspan="3">
                        <div style="display:flex;gap:10px;align-items:center;">
                            <select id="tpl_category" class="bansang_sel" style="width:140px;" onchange="tplCategoryChange();">
                                <option value="">전체</option>
                                <option value="mandatory">의무관리</option>
                                <option value="non_mandatory">비의무관리</option>
                            </select>
                            <div style="position:relative;width:450px;">
                                <input type="text" id="tpl_search" class="bansang_ipt ver2" style="width:100%;" placeholder="템플릿 검색..." autocomplete="off" onfocus="tplDropdownShow();" oninput="tplFilterList();">
                                <button type="button" id="tpl_clear_btn" onclick="tplClearSelection();" style="display:none;position:absolute;right:-10px;top:-10px;width:18px;height:18px;background:#999;border:none;border-radius:50%;font-size:12px;color:#fff;cursor:pointer;padding:0;line-height:18px;text-align:center;z-index:10;">&times;</button>
                                <div id="tpl_dropdown" style="display:none;position:absolute;top:100%;left:0;right:0;max-height:300px;overflow-y:auto;background:#fff;border:1px solid #ddd;border-top:none;z-index:100;box-shadow:0 4px 8px rgba(0,0,0,0.1);"></div>
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th>지역</th>
                    <td colspan="3">
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

                                let html = `<option value="">선택</option>"`;

                                if(postValue != ""){
                                }else{
                                    $("#building_id").val("");
                                    $("#building_name").val("");

                                    $("#dong_id").html(html);
                                }
                            }
                        </script>
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
                            <!-- <div class="sch_box_right">
                                <button type="button" class="bansang_btns ver1" onclick="building_handler();">검색</button>
                            </div> -->
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
                        <?php
                        $sql_dong = "SELECT * FROM a_building_dong WHERE building_id = '{$row['building_id']}' and is_del = 0";
                        $res_dong = sql_query($sql_dong);
                        
                        ?>
                        <select name="dong_id" id="dong_id" class="bansang_sel" required>
                            <option value=""><?php echo $w == 'u' ? '동을' : '단지를'; ?> 선택해주세요.</option>
                            <?php if($w == 'u'){?>
                            <option value="-1" <?php echo get_selected($row['dong_id'], "-1"); ?>>전체</option>
                            <?php }?>
                            <?php
                            while($row_dong = sql_fetch_array($res_dong)){
                            ?>
                            <option value="<?php echo $row_dong['dong_id']?>" <?php echo get_selected($row['dong_id'], $row_dong['dong_id']); ?>><?php echo $row_dong['dong_name'].'동';?></option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>투표주제</th>
                    <td colspan="3">
                        <input type="text" name="vt_title" class="bansang_ipt ver2" size="100" value="<?php echo $row['vt_title']; ?>" required>
                    </td>
                </tr>
                <tr>
                    <th>투표기간</th>
                    <td colspan="3">
                        <select name="vt_period_type" id="vt_period_type" class="bansang_sel" onchange="period_change();" required>
                            <option value="">선택</option>
                            <option value="personnel" <?php echo get_selected($row['vt_period_type'], 'personnel'); ?>>인원수 마감</option>
                            <option value="period_not" <?php echo get_selected($row['vt_period_type'], 'period_not'); ?>>기간 없음</option>
                            <option value="period" <?php echo get_selected($row['vt_period_type'], 'period'); ?>>기간 설정</option>
                        </select>
                        <div class="dates_forms ipt_box flex_ver mgt10">
                            <input type="text" name="vt_sdate" id="sdate" class="bansang_ipt <?php echo $row['vt_period_type'] != 'period' ? 'ipt_date_not' : 'ver2'; ?> ipt_date" value="<?php echo $row['vt_period_type'] != 'period' ? '' : $row['vt_sdate']; ?>" <?php echo $row['vt_period_type'] != 'period' ? 'disabled' : ''; ?>> ~ <input type="text" name="vt_edate" id="edate" class="bansang_ipt <?php echo $row['vt_period_type'] != 'period' ? 'ipt_date_not' : 'ver2'; ?> ipt_date" value="<?php echo $row['vt_period_type'] != 'period' ? '' : $row['vt_edate']; ?>" <?php echo $row['vt_period_type'] != 'period' ? 'disabled' : ''; ?>>
                        </div>
                    </td>
                    <script>
                        function period_change(){
                            var periodSelect = document.getElementById("vt_period_type");
                            var periodValue = periodSelect.options[periodSelect.selectedIndex].value;

                            console.log('periodValue', periodValue);

                            if(periodValue != "period"){
                                $("#sdate").attr("disabled", true);
                                $("#sdate").removeClass("ver2");
                                $("#sdate").addClass("ipt_date_not");
                                $("#edate").attr("disabled", true);
                                $("#edate").addClass("ipt_date_not");
                                $("#edate").removeClass("ver2");
                            }else{
                                $("#sdate").attr("disabled", false);
                                $("#sdate").addClass("ver2");
                                $("#sdate").removeClass("ipt_date_not");
                                $("#edate").attr("disabled", false);
                                $("#edate").addClass("ver2");
                                $("#edate").removeClass("ipt_date_not");
                            }
                            // $.ajax({

                            // url : "./building_dong_ajax.php", //ajax 통신할 파일
                            // type : "POST", // 형식
                            // data: { "building_id":buildingValue}, //파라미터 값
                            // success: function(msg){ //성공시 이벤트

                            //     //console.log(msg);
                            //     $("#dong_id").html(msg);
                            // }

                            // });
                        }
                    </script>
                </tr>
                <?php if($w == 'u'){?>
                <tr>
                    <th>상태</th>
                    <td colspan="3">
                        <select name="vt_status" id="vt_status" class="bansang_sel" required>
                            <option value="0" <?php echo get_selected($row['vt_status'], '0'); ?>>대기</option>
                            <option value="1" <?php echo get_selected($row['vt_status'], '1'); ?>>진행중</option>
                            <?php if($w == "u"){?>
                            <option value="2" <?php echo get_selected($row['vt_status'], '2'); ?>>종료</option>
                            <?php }?>
                        </select>
                    </td>
                </tr>
                <?php }?>
                <tr>
                    <th>내용</th>
                    <td colspan="3"><?php echo editor_html('vt_content', get_text(html_purifier($row['vt_content']), 0)); ?></td>
                </tr>
                <tr>
                    <th>투표 항목</th>
                    <td colspan="3">
                        <?php if($w == "u"){?>
                        <div class="vote_question_wrap">
                            <?php for($i=0;$question_row = sql_fetch_array($question_res);$i++){?>
                            <div class="vote_question_box ipt_box flex_ver">
                                <input type="hidden" name="vtq_id[]" value="<?php echo $question_row['vtq_id']; ?>">
                                <input type="text" name="vtq_name[]" class="bansang_ipt ver2" size="100" value="<?php echo $question_row['vtq_name']; ?>" required>
                                <?php if($i==0){?>
                                <button type="button" onclick="question_add();" class="bansang_btns ver1">추가</button>
                                <?php }else{ ?>
                                <div class="dong_del_box">
                                    <input type="checkbox" name="vtq_del[<?php echo $i; ?>]" id="vtq_del<?php echo $i + 1; ?>" value="1">
                                    <label for="vtq_del<?php echo $i + 1; ?>">삭제</label>
                                </div>
                                <?php }?>
                            </div>
                            <?php }?>
                        </div>
                        <?php }else{ ?>
                        <div class="vote_question_wrap">
                            <div class="vote_question_box ipt_box flex_ver">
                                <input type="text" name="vtq_name[]" class="bansang_ipt ver2" size="100" required>
                                <button type="button" onclick="question_add();" class="bansang_btns ver1">추가</button>
                            </div>
                        </div>
                        <?php }?>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php if($w == "u"){
        
            $vote_q = "SELECT * FROM a_online_vote_question WHERE is_del = 0 and vt_id = '{$row['vt_id']}' ORDER BY vtq_id asc";
            // echo $vote_q;
            $vote_q_res = sql_query($vote_q);
        ?>
        <div class="tbl_frm01 tbl_wrap">
            <h2 class="h2_frm">투표 결과</h2>
            <div class="vote_result_wrap">
                <?php foreach($vote_q_res as $idx => $vote_q_row) {
                    $vote_cnt_row = sql_fetch("SELECT count(*) as cnt FROM a_online_vote_result WHERE vt_id = '{$vt_id}' and vtq_id = '{$vote_q_row['vtq_id']}' ");    
                ?>
                <div class="vote_result_box">
                    <div class="vote_result_label">투표 <?php echo $idx + 1;?>번 항목 : <?php echo $vote_q_row['vtq_name'].'-'; ?> <?php echo number_format($vote_cnt_row['cnt']); ?>명</div>
                    <div class="vote_result_btn_wrap">
                        <button type="button" class="btn btn_02" onClick="vote_result_show_handler('<?php echo $idx; ?>', '<?php echo $vote_q_row['vtq_id']; ?>');">투표자 보기</button>
                    </div>
                </div>
                <?php }?>
            </div>
        </div>
        <?php }?>
    </div>
    <div class="btn_fixed_top">
        <a href="./online_vote_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <?php if($w == 'u'){?>
        <button type="button" onclick="vote_del_handler();" class="btn btn_01">삭제</button>
        <?php }?>
        <input type="submit" value="저장" class="btn_submit btn btn_02" accesskey='s'>
    </div>
</form>
<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>게시글을 저장 중입니다.</p>
        <p>푸시 발송에 시간이 소요됩니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>
<div class="cm_pop" id="vote_result_pop" >
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('vote_result_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">투표자 보기</div>
        <div class="vote_result_cont_wrap">
            <div class="vote_result_select mgt20">1번 선택지</div>
            <div class="vote_result_pop_wrap mgt20">
                <div class="vote_result_pop_head">
                    <div class="vote_pop_hd_label">동/호수</div>
                    <div class="vote_pop_hd_label">투표자(세대주)</div>
                </div>
                <div class="vote_result_pop_body">
                    <div class="vote_pop_bd_label1">101동/101호</div>
                    <div class="vote_pop_bd_label2">홍길동</div>
                </div>
            </div>
        </div>
		<div class="cm_pop_btn_box flex_ver flex_ver_ta">
			<button type="button" class="cm_pop_btn ver2" onClick="popClose('vote_result_pop');">확인</button>
		</div>
	</div>
</div>

<style>
/* CHEditor5 에디터 영역 기본 글씨체/크기 (iframe이 아닌 경우 적용) */
.cheditor_editer_frame, .cheditor_edit_area {
    font-family: 'Arial Black', 'Arial', sans-serif !important;
    font-size: 16px !important;
    line-height: 1.6 !important;
}
</style>

<script>

// CHEditor5 기본 글씨체/크기 설정 (config + iframe + 툴바)
$(document).ready(function(){
    var defaultFontName = "'Arial Black', 'Arial', sans-serif";
    var defaultFontSize = "16px";

    var applyDefaults = setInterval(function(){
        if(typeof ed_vt_content === 'undefined') return;

        // 1. config 기본값 변경 (툴바 기본 표시값에 영향)
        ed_vt_content.config.editorFontName = defaultFontName;
        ed_vt_content.config.editorFontSize = defaultFontSize;

        // 2. iframe body 스타일 적용
        if(ed_vt_content.editorIframe){
            try {
                var iframeDoc = ed_vt_content.editorIframe.contentDocument || ed_vt_content.editorIframe.contentWindow.document;
                var body = iframeDoc.body;
                if(body){
                    body.style.fontFamily = defaultFontName;
                    body.style.fontSize = defaultFontSize;
                    body.style.lineHeight = "1.6";

                    // 3. 툴바 FontName/FontSize select 텍스트 강제 업데이트
                    try {
                        var toolbarDiv = ed_vt_content.currentRS.elNode.parentNode;
                        var btns = toolbarDiv.querySelectorAll('.che_toolbar button');
                        btns.forEach(function(btn){
                            var span = btn.querySelector('span > span');
                            if(!span) return;
                            var txt = span.textContent;
                            // FontName 버튼: 기존 기본 폰트명이 표시된 버튼 찾기
                            if(txt.indexOf('맑은 고딕') > -1 || txt.indexOf('Malgun') > -1 || txt.indexOf('gulim') > -1 || txt === 'sans-serif'){
                                span.textContent = 'Arial Black';
                            }
                            // FontSize 버튼: 기존 기본 크기가 표시된 버튼 찾기
                            if(txt === '12px' || txt === '14' || txt === '14px' || txt === '12'){
                                span.textContent = '16px';
                            }
                        });
                    } catch(e2){}

                    clearInterval(applyDefaults);
                }
            } catch(e){}
        }
    }, 300);
    setTimeout(function(){ clearInterval(applyDefaults); }, 5000);
});

// 템플릿 적용 후 툴바 FontName/FontSize 강제 업데이트
function updateToolbarFontDisplay(){
    try {
        var toolbarDiv = ed_vt_content.currentRS.elNode.parentNode;
        var btns = toolbarDiv.querySelectorAll('.che_toolbar button span > span');
        btns.forEach(function(span){
            var txt = span.textContent;
            if(txt.indexOf('맑은 고딕') > -1 || txt.indexOf('Malgun') > -1 || txt.indexOf('gulim') > -1 || txt === 'sans-serif' || txt.indexOf('noto') > -1 || txt.indexOf('Noto') > -1){
                span.textContent = 'Arial Black';
            }
            if(txt === '12px' || txt === '14px' || txt === '14' || txt === '12' || txt === '13px' || txt === '13'){
                span.textContent = '16px';
            }
        });
    } catch(e){}
}

// 투표 템플릿 JSON 데이터 (PHP → JS)
var tplData = <?php echo json_encode($vote_templates, JSON_UNESCAPED_UNICODE); ?>;

// 전체 템플릿 목록 (type/idx 포함)
var tplAllItems = [];
$.each(['mandatory', 'non_mandatory'], function(_, type){
    $.each(tplData[type], function(idx, tpl){
        tplAllItems.push({ type: type, idx: idx, label: tpl.label, typeName: type === 'mandatory' ? '의무' : '비의무' });
    });
});

// 드롭다운 목록 렌더링
function tplRenderDropdown(items){
    var $dd = $('#tpl_dropdown');
    $dd.empty();
    if(items.length === 0){
        $dd.append('<div style="padding:10px 12px;color:#999;font-size:13px;">검색 결과가 없습니다.</div>');
    } else {
        $.each(items, function(i, item){
            var color = item.type === 'mandatory' ? '#388FCD' : '#4E5E81';
            var $item = $('<div style="padding:8px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #f0f0f0;">' +
                '<span style="display:inline-block;min-width:42px;font-weight:700;color:' + color + ';">[' + item.typeName + ']</span>' +
                $('<span>').text(item.label).prop('outerHTML') +
                '</div>');
            $item.on('mouseover', function(){ $(this).css('background', '#f0f7ff'); });
            $item.on('mouseout', function(){ $(this).css('background', '#fff'); });
            $item.on('mousedown', function(e){
                e.preventDefault();
                tplApplyItem(item.type, item.idx);
            });
            $dd.append($item);
        });
    }
    $dd.show();
}

// 필터링된 목록 반환
function tplGetFiltered(){
    var cat = $('#tpl_category').val();
    var keyword = $('#tpl_search').val().toLowerCase();
    return tplAllItems.filter(function(item){
        if(cat && item.type !== cat) return false;
        if(keyword && item.label.toLowerCase().indexOf(keyword) === -1) return false;
        return true;
    });
}

// 대분류 변경
function tplCategoryChange(){
    $('#tpl_search').val('');
    tplRenderDropdown(tplGetFiltered());
    $('#tpl_search').focus();
}

// 검색 입력
function tplFilterList(){
    tplRenderDropdown(tplGetFiltered());
}

// 드롭다운 표시
function tplDropdownShow(){
    tplRenderDropdown(tplGetFiltered());
}

// 드롭다운 닫기 (포커스 아웃 시)
$(document).ready(function(){
    $('#tpl_search').on('blur', function(){
        setTimeout(function(){ $('#tpl_dropdown').hide(); }, 150);
    });
});

// 템플릿 적용
function tplApplyItem(type, idx){
    var tpl = tplData[type][idx];
    if(!tpl) return;

    // 투표주제 (title)
    $("input[name='vt_title']").val(tpl.title);

    // 내용 에디터 (content - HTML) - font 인라인 스타일 제거 + 불필요한 라벨 제거
    var htmlContent = tpl.content;
    // font-family, font-size 인라인 스타일 제거
    htmlContent = htmlContent.replace(/\s*font-family\s*:[^;"']*[;]?/gi, '');
    htmlContent = htmlContent.replace(/\s*font-size\s*:[^;"']*[;]?/gi, '');
    htmlContent = htmlContent.replace(/\s*style\s*=\s*["']\s*["']/gi, '');
    // 라벨 제거
    htmlContent = htmlContent.replace(/\[SM 오프닝\]\s*/g, '');
    htmlContent = htmlContent.replace(/\[제안 사유 및 기대효과\]\s*(<br\s*\/?>)?\s*/gi, '');
    htmlContent = htmlContent.replace(/(<br\s*\/?\s*>){3,}/gi, '<br><br>');
    // 기본 글씨체/크기로 감싸기
    htmlContent = '<div style="font-family:\'Arial Black\',\'Arial\',sans-serif;font-size:16px;line-height:1.6;">' + htmlContent + '</div>';

    // textarea 직접 설정
    $("textarea[name='vt_content']").val(htmlContent);

    // smarteditor2
    if(typeof oEditors !== 'undefined'){
        try { oEditors.getById['vt_content'].exec('SET_IR', [htmlContent]); } catch(e){}
    }
    // ckeditor
    if(typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['vt_content']){
        try { CKEDITOR.instances['vt_content'].setData(htmlContent); } catch(e){}
    }
    // summernote
    if($.fn.summernote && $("textarea[name='vt_content']").data('summernote')){
        try { $("textarea[name='vt_content']").summernote('code', htmlContent); } catch(e){}
    }

    // 템플릿 적용 후 툴바 글씨체/크기 표시 업데이트
    setTimeout(function(){ updateToolbarFontDisplay(); }, 200);

    // 검색창에 선택된 템플릿 이름 표시 및 드롭다운 닫기
    $('#tpl_search').val(tpl.label).prop('readOnly', true);
    $('#tpl_clear_btn').show();
    $('#tpl_dropdown').hide();
    alert('템플릿이 적용되었습니다.\n내용을 확인 후 수정하세요.');
}

// 템플릿 선택 초기화
function tplClearSelection(){
    // 검색창 초기화
    $('#tpl_search').val('').prop('readOnly', false).focus();
    $('#tpl_clear_btn').hide();

    // 투표주제 초기화
    $("input[name='vt_title']").val('');

    // 에디터 초기화
    $("textarea[name='vt_content']").val('');
    if(typeof oEditors !== 'undefined'){
        try { oEditors.getById['vt_content'].exec('SET_IR', ['']); } catch(e){}
    }
    if(typeof CKEDITOR !== 'undefined' && CKEDITOR.instances['vt_content']){
        try { CKEDITOR.instances['vt_content'].setData(''); } catch(e){}
    }
    if($.fn.summernote && $("textarea[name='vt_content']").data('summernote')){
        try { $("textarea[name='vt_content']").summernote('code', ''); } catch(e){}
    }
}

//투표 삭제
function vote_del_handler(){
    let vt_id = "<?php echo $vt_id?>";
    let vt_status = $("#vt_status option:selected").val();

    let vt_status_text = "";
    switch(vt_status){
        case "0":
            vt_status_text = "대기";
            break;
        case "1":
            vt_status_text = "진행중";
            break;
        case "2":
            vt_status_text = "종료";
            break;
    }

    let building_name = "<?php echo $building_name; ?>";
    let vt_title = "<?php echo $row['vt_title']; ?>";

    if (!confirm(building_name + "의 " + vt_title + " 투표를 정말 삭제하시겠습니까?")) {
        return false;
    }

    if (!confirm("해당 투표건은 " + vt_status_text + " 상태입니다\n선택한 투표건을 정말 삭제하시겠습니까?")) {
        return false;
    }

    // console.log(vt_id, vt_status_text, building_name);

    let sendData = {'vt_id': vt_id};

    $.ajax({
        type: "POST",
        url: "./online_vote_del_update.php",
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
                    location.replace('./online_vote_list.php');
                }, 100);
            }
        },
    });

}


function vote_result_show_handler(idx, vtq_id){

    $.ajax({

    url : "./online_vote_result_ajax.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { "vtq_id":vtq_id, 'idx':idx}, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".vote_result_cont_wrap").html(msg); 
        popOpen('vote_result_pop');
    }

    });
}


$(function(){

    //, minDate:"0d"
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d" });
});

//문항 추가
function question_add(){
    let html = `<div class="vote_question_box">
                                <input type="text" name="vtq_name[]" class="bansang_ipt ver2" size="100" requried>
                                <button type="button" onclick="question_remove(this);" class="bansang_btns ver2">삭제</button>
                            </div>`;

    //let endLength = $(".house_hold_box_inner").length;
    $(".vote_question_wrap").append(html);
   
}

//문항 삭제
function question_remove(ele){
    ele.closest('.vote_question_box').remove();
}


//날짜형식
function checkValidDate(value) {
	var result = true;
	try {
	    var date = value.split("-");
	    var y = parseInt(date[0], 10),
	        m = parseInt(date[1], 10),
	        d = parseInt(date[2], 10);
	    
	    var dateRegex = /^(?=\d)(?:(?:31(?!.(?:0?[2469]|11))|(?:30|29)(?!.0?2)|29(?=.0?2.(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00)))(?:\x20|$))|(?:2[0-8]|1\d|0?[1-9]))([-.\/])(?:1[012]|0?[1-9])\1(?:1[6-9]|[2-9]\d)?\d\d(?:(?=\x20\d)\x20|$))?(((0?[1-9]|1[012])(:[0-5]\d){0,2}(\x20[AP]M))|([01]\d|2[0-3])(:[0-5]\d){1,2})?$/;
	    result = dateRegex.test(d+'-'+m+'-'+y);
	} catch (err) {
		result = false;
	}    
    return result;
}


function fonlinevote_submit(f) {
 

    if(f.vt_period_type.value == "period"){
        if(!checkValidDate(f.vt_sdate.value)){
            alert("투표 시작일을 날짜 형식에 맞게 입력해주세요.");
            f.vt_sdate.focus();
            return false;
        }

        if(!checkValidDate(f.vt_edate.value)){
            alert("투표 종료일을 날짜 형식에 맞게 입력해주세요.");
            f.vt_edate.focus();
            return false;
        }
    }
    
    if(f.vt_sdate.value > f.vt_edate.value){
        alert("투표 시작일이 종료일보다 이후일 수 없습니다.");
        f.vt_sdate.focus();
        return false;
    }

    if(f.w.value == ""){
        $("#building_info_pop").show();
    }

    return true;
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

