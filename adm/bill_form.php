<?php
$sub_menu = "300600";
require_once './_common.php';


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '추가';
}

$g5['title'] .= '고지서 ' . $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;

$sql = "SELECT bill.*, post.post_name, building.building_name FROM a_bill as bill
        LEFT JOIN a_post_addr as post on bill.post_id = post.post_idx
        LEFT JOIN a_building as building on bill.building_id = building.building_id
        WHERE bill.bill_id = '{$bill_id}'";
$row = sql_fetch($sql);


$post_sql = "SELECT * FROM a_post_addr ORDER BY is_prior asc, post_idx asc";
$post_res = sql_query($post_sql);


if($status != ''){
    $qstr .= "&status={$status}";
}

if($bill_year != ''){
    $qstr .= "&bill_year={$bill_year}";
}

if($bill_month != ''){
    $qstr .= "&bill_month={$bill_month}";
}

if($post_id != ''){
    $qstr .= "&post_id={$post_id}";
}


if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql;

    //print_r2($row);
}

?>
<style>
h3, h4 {
    text-align: center;
    margin-top:20px;
}
.sub_table th, .sub_table td {
    padding: 10px 5px; /* 셀 내부 여백 */
    
    text-align: center; /* 텍스트 가운데 정렬 */
    background: #fff;
}
.sub_table th {
    background-color: #f4f4f4; /* 헤더 배경색 */
    font-weight: bold;
}


.sub_table_labels {margin-bottom: 20px;}



.sub_table_inner {height: 600px;border-left: 1px solid #ddd;border-top:1px solid #ddd;}
.sub_table_inner table {border-collapse: separate; border-spacing: 0;width: max-content; min-width: 100%;table-layout: fixed;}
.sub_table_inner table th, .sub_table_inner table td {min-width: 100px;box-sizing: border-box;white-space: nowrap;background: #fff;width: 100px;padding: 5px 5px;border:none;border-right: 1px solid #ddd;border-bottom:1px solid #ddd;}
.sub_table_inner table tr:first-child {position: sticky;top: 0;z-index: 3; }
.sub_table_inner table tr:nth-child(2) {position: sticky;top: 33px;z-index: 3; }
.sub_table_inner table tr:nth-child(3) {position: sticky;top: 66px;z-index: 3; }

.sub_table_inner table td:first-child {
    position: sticky;
    left: 0;
    z-index: 2; /* 헤더의 첫 셀(겹침) 처리 위해 조정 */
    background: #fff; /* 배경색을 줘야 아래 콘텐츠가 보이지 않음 */
    box-shadow: 2px 0 4px rgba(0,0,0,0.03);
}

.sub_table tr:first-child td:first-child {
    z-index: 5;
    /* background: #e9ecef; */
  }
     
</style>
<form name="fbill" id="fbill" action="./bill_form_update.php" onsubmit="return fbill_submit(this);" method="post" enctype="multipart/form-data">
    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="bill_id" value="<?php echo $row['bill_id']; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2">검침 정보</h2>
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
                    <td colspan="3">
                    <?php if($w == "u"){?>
                        <input type="hidden" name="post_id" value="<?php echo $row['post_id']; ?>">
                        <input type="text" name="post_name" id="post_name" class="bansang_ipt" value="<?php echo $row['post_name']; ?>" readonly>
                        <?php }else{ ?>
                            <select name="post_id" id="post_id" class="bansang_sel"  onchange="post_change();">
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

                                    if(postValue != ""){
                                    }else{
                                        $("#building_id").val("");
                                        $("#building_name").val("");
                                        $(".bill_info_tables").hide();
                                    }
                                }
                            </script>
                        <?php }?>
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
                            let w = "<?php echo $w; ?>";
                            let year = $("#bill_year option:selected").val();
                            let month = $("#bill_month option:selected").val();

                            let sendData = {'building_id': id, "year": year, "month": month};

                            $.ajax({
                                type: "POST",
                                url: "./bii_status_check.php",
                                data: sendData,
                                cache: false,
                                async: false,
                                dataType: "json",
                                success: function(data) {
                                    console.log('data:::', data);

                                    if(data.result == false) { 
                                        if(w == ''){
                                            $(".bill_info_tables").hide();
                                        }
                                        alert('이미 해당 월에 저장된 고지서가 있습니다.');
                                        return false;
                                    }else{
                                        
                                        $(".bill_info_tables").show();

                                        let sendData2 = {'building_id': id};

                                        $.ajax({
                                            type: "POST",
                                            url: "./building_post_ajax.php",
                                            data: sendData2,
                                            cache: false,
                                            async: false,
                                            dataType: "json",
                                            success: function(data) {
                                                console.log('data:::', data);

                                                if(data.result == false) { 
                                                    alert(data.msg);
                                                    return false;
                                                }else{
                                                
                                                    $("#building_id").val(id);
                                                    $("#building_name").val(name);
                                                    $(".sch_result_box").hide();
                                                    $("#building_sch").val("");

                                                    $("#post_id").val(data.msg).change();
                                                
                                                }
                                            },
                                        });

                                    }
                                },
                            });
                          

                            //building_post_ajax
                           
                            


                            

                        }

                       </script>
                    </td>
                </tr>
                <tr>
                    <th>고지서 등록 년/월</th>
                    <td>
                        <?php
                        $nowYear = date("Y");
                        $bfYear = $nowYear - 1;

                        $nowMonth = date("n");

                        $selectYear = $w == 'u' ? $row['bill_year'] : $nowYear;
                        $selectMonth = $w == 'u' ? $row['bill_month'] : $nowMonth;
                        ?>
                        <div class="select_flex">
                            <?php if($w == "u" && $row['is_submit'] == 'Y'){?>
                                <input type="text" name="bill_year" id="bill_year" class="bansang_ipt" readonly value="<?php echo $row['bill_year']; ?>">
                                <input type="text" name="bill_month" id="bill_month" class="bansang_ipt" readonly value="<?php echo $row['bill_month']; ?>">
                            <?php }else{ ?>
                            <select name="bill_year" id="bill_year" class="bansang_sel">
                                <?php for($i=$bfYear;$i<=$nowYear;$i++){?>
                                <option value="<?php echo $i;?>" <?php echo get_selected($selectYear, $i); ?>><?php echo $i.'년';?></option>
                                <?php }?>
                            </select>
                            <select name="bill_month" id="bill_month" class="bansang_sel" onchange="month_change();">
                                <?php for($i=1;$i<=12;$i++){?>
                                    <option value="<?php echo $i; ?>" <?php echo get_selected($selectMonth, $i); ?>><?php echo $i.'월'; ?></option>
                                <?php }?>
                            </select>
                            <?php }?>
                        </div>
                        <script>
                            function month_change(){
                                var monthSelect = document.getElementById("bill_month");
                                var monthValue = monthSelect.options[monthSelect.selectedIndex].value;

                                //console.log('buildingValue', buildingValue);
                                let buildingValue = $("#building_id").val();
                                let year = $("#bill_year option:selected").val();

                                let sendData = {'building_id': buildingValue, "year": year, "month": monthValue};

                                $.ajax({
                                    type: "POST",
                                    url: "./bii_status_check.php",
                                    data: sendData,
                                    cache: false,
                                    async: false,
                                    dataType: "json",
                                    success: function(data) {
                                        console.log('data:::', data);

                                        if(data.result == false) { 
                                            $(".bill_info_tables").hide();
                                            alert('이미 해당 월에 발행된 고지서가 있습니다.');
                                            return false;
                                        }else{
                                            
                                            $(".bill_info_tables").show();
                                        }
                                    },
                                });
                            }
                        </script>
                    </td>
                </tr>
                <tr>
                    <th>납부기한</th>
                    <td colspan="3">
                        <div class="ipt_date_boxs_wrap">
                            <div class="ipt_date_boxs">
                                <input type="text" name="bill_due_date" id="bill_due_date" class="bansang_ipt ver2 ipt_date ipt_date_visit" value="<?php echo $row['bill_due_date']; ?>" required>
                                <!-- <button type="button" onclick="date_del('ipt_date_visit', 'date_del_btn')" class="date_del_btn <?php echo $row['bill_due_date'] != '' ? '' : 'date_del_btn_hd'; ?> date_del_btn1">
                                    <span></span>
                                    <span></span>
                                </button>
                                <script>
                                    function date_del(ele, btnele){
                                        $("." + ele).val("");
                                        $("." + btnele).hide();
                                    }
                                </script> -->
                            </div>
                        </div>
                    </td>
                </tr>
                <?php if($w == 'u'){?>
                <tr>
                    <th>고지서 구분</th>
                    <td colspan="3">
                        <select name="vt_add" id="vt_add" class="bansang_sel">
                            <option value="1" <?php echo get_selected($row['vt_add'], '1'); ?>>부가세 포함</option>
                            <option value="0" <?php echo get_selected($row['vt_add'], '0'); ?>>부가세 별도</option>
                        </select>
                    </td>
                </tr>
                <?php }?>
            </tbody>
        </table>
    </div>
    
    <div class="tbl_frm01 tbl_wrap bill_info_tables" style="<?php echo $w== 'u' ? "display:block" : "";?>">
        <div class="h2_frm_wraps">
            <h2 class="h2_frm">고지서 정보</h2>
            <div class="h2_frm_btn_wrap">
                <?php if($w == "u"){?>
                <button type="button" onclick="print_info('<?php echo $bill_id; ?>');"  class="btn btn_02">고지서 인쇄</button>
                <?php }?>
         
                <!-- 260127 발행시에는 엑셀 업로드 못하게 수정 -->
                <?php if($row['is_submit'] != 'Y'){?>
                <button type="button" onclick="excelPop();" class="btn btn_04">엑셀 업로드</button>
                <?php }else{?>
                    <?php if($_SERVER['REMOTE_ADDR'] == ADMIN_IP){?>
                     <button type="button" onclick="excelPop();" class="btn btn_04">엑셀 업로드</button>
                    <?php }?>
                <?php }?>
      
                <!-- <?php if($w == 'u'){?>
                <button type="button" class="btn btn_04">엑셀 다운로드</button>
               
                <?php }?> -->
                <?php if($w == 'u'){?>
                    <a href="/bbs/download_bill_excel.php?bill_id=<?php echo $bill_id; ?>" class="btn btn_04" >엑셀 다운로드</a>
                <?php }?>
                <!-- <a href="./bill_excel_upload.php?bill_id=<?php echo $bill_id; ?>" onclick="return excelform(this.href);"  class="btn btn_04">엑셀 업로드</a> -->
            </div>
            <script>
                function print_info(bill_id) // 회원 엑셀 업로드를 위하여 추가
                { 

                    var opt = "width=810,height=1200,left=10,top=10"; 
                    var url = "./bill_print_all.php?bill_id=" + bill_id;

                    window.open(url, "win_news", opt); 

                    return false; 

                }
            </script>
        </div>
        
        <div id="sub_table_wrap">
           <?php if($w == 'u'){
            //총 동수
            $bill_item_sql = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' GROUP BY dong_name ORDER BY bi_idx asc";
            // echo $bill_item_sql;
            $bill_item_res = sql_query($bill_item_sql);
            $bill_item_total = sql_num_rows($bill_item_res);
            
            for($i=0;$bill_row=sql_fetch_array($bill_item_res);$i++){

                $bill_it_list = "SELECT * FROM a_bill_item WHERE bill_id = '{$bill_id}' and dong_name = '{$bill_row['dong_name']}' ORDER BY bi_idx asc";
                $bill_it_list_res = sql_query($bill_it_list);
            ?>
               <div class="sub_table_inner_wrapper">
                    <h4 class="sub_table_labels">동 : <?php echo $bill_row['dong_name']; ?></h4>
                    <div id="sub_table_inners<?php echo $i + 1;?>" class="sub_table_inner scroll-wrapper" >
                        <table class="sub_table sub_table_ver2">
                            <?php while($bill_it_list_row = sql_fetch_array($bill_it_list_res)){
                                
                                $bill_opts = explode("|", $bill_it_list_row['bi_option']);
                                ?>
                                <tr>
                                    <td><?php echo $bill_it_list_row['bi_name']; ?></td>
                                    <?php foreach($bill_opts as $opt_row){?>
                                        <td><?php echo $opt_row; ?></td>
                                    <?php }?>
                                </tr>
                            <?php }?>
                        </table>
                    </div>
               </div>
            <?php }?>
           <?php }?>
        </div>
    </div>
    <?php
    $r_s_date = $row['r_submited'] != '' ? date("Y-m-d H:i", strtotime($row['r_submited_at'])) : '';
    ?>
    <div class="btn_fixed_top">
        <a href="./bill_list.php?<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <!-- <button type="button" class="btn btn_03">저장</button> -->
        <input type="submit" value="<?php echo $w == 'u' ? '수정' : '저장';?>" id="btn_submit_bill" style="<?php echo $row['is_submit'] == 'N' && $w == "u" ? "display:inline-block;" : ""; ?>" class="btn_submit btn btn_03" accesskey='s'>
        <?php if($w == "u" && $row['is_submit'] == 'N'){?>
        <button type="button" onclick="billSubmit();" class="btn btn_01">발행</button>
        <button type="button" onclick="billReserPop();" class="btn btn_03">예약 발행</button>
        <?php }?>
        <?php if($w == 'u' && $row['is_submit'] == 'Y' || $row['is_submit'] == 'R'){?>
            <button type="button" onclick="billCancel();" class="btn btn_01">발행 취소</button>

            <?php if($row['is_submit'] == 'R'){?>
                <button type="button" class="btn btn_02" disabled><?php echo $r_s_date.' '; ?>예약 발행 중</button>
                <button type="button" onclick="billSubmit();" class="btn btn_01">즉시 발행</button>
            <?php }?>
        <?php }?>
        <?php if($row['is_submit'] == 'Y'){
            // print_r2($row);
            ?>
         
            <button disabled class="btn btn_05" style="cursor:auto;"><?php echo $row['r_submited'] == 'Y' ? $row['updated_at'] : $row['submited_at']; ?> 발행되었습니다.</button>
         
        <?php }?>
    </div>
</form>

<div class="cm_pop" id="excel_type_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('excel_type_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">엑셀 업로드</div>
        <div class="excel_upload_btn_wrap">
            <button type="button" onclick="excelupload('yes');" class="btn btn_04">부가세 포함</button>
            <button type="button" onclick="excelupload('no');" class="btn btn_04">부가세 별도</button>
        </div>
    </div>
</div>

<!-- 예약 발행 -->
<div class="cm_pop" id="reservation_submit_pop">
	<div class="cm_pop_back"></div>
	<div class="cm_pop_cont">
        <div class="cm_pop_close_btn" onClick="popClose('reservation_submit_pop');">
            <div class="cm_pop_bar cm_pop_bar1"></div>
            <div class="cm_pop_bar cm_pop_bar2"></div>
        </div>
        <div class="cm_pop_title">예약 발행</div>
        <div class="cm_pop_conts cm_pop_rv_conts">
            <div class="cm_rv_time">예상 예약 발행 시간 : 2025-05-14 10:00</div>
            <p>* 예약 발행 후 예약 발행 설정 시점으로  24시간 후 앱 발송 됩니다.</p>
            <p>* 24시간 이내 예약 발행 취소 가능하며, 24시간 이내 강제 발송 가능합니다.</p>
        </div>
        <div class="excel_upload_btn_wrap">
            <button type="button" onclick="popClose('reservation_submit_pop');" class="btn btn_02">취소</button>
            <button type="button" onclick="billReserHandler();" class="btn btn_03">예약 발행</button>
        </div>
    </div>
</div>

<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>고지서를 발행 중입니다.</p>
        <p>푸시 발송에 시간이 소요됩니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>

<script>
// 고지서 발행
function billSubmit(){
    if (!confirm("고지서 발행 하시겠습니까?")) {
        return false;
    }

    let bill_id = "<?php echo $bill_id; ?>";

    let sendData = {'bill_id': bill_id};

    $("#building_info_pop").show(); //팝업 띄우기

    setTimeout(() => {
        $.ajax({
            type: "POST",
            url: "./bill_form_submit.php",
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
                    
                    window.location.reload();

                    ("#building_info_pop").hide(); //팝업 띄우기
                }
            },
        });
    }, 50);
}

// 발행 취소
function billCancel(){
    if (!confirm("고지서 발행을 취소 하시겠습니까?")) {
        return false;
    }

    let bill_id = "<?php echo $bill_id; ?>";

    let sendData = {'bill_id': bill_id};

    $.ajax({
        type: "POST",
        url: "./bill_submit_cancel.php",
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
                
                window.location.reload();
            }
        },
    });
}

// 고지서 예약 발행
function billReserPop(){

    $.ajax({

    url : "./bill_rv_submit.php", //ajax 통신할 파일
    type : "POST", // 형식
    data: { }, //파라미터 값
    success: function(msg){ //성공시 이벤트
        console.log(msg);
        $(".cm_pop_rv_conts").html(msg); 

        popOpen('reservation_submit_pop');
    }

    });
}

// 예약 발행
function billReserHandler(){
    if (!confirm("고지서 예약 발행 하시겠습니까?")) {
        return false;
    }

    let bill_id = "<?php echo $bill_id; ?>";
    let rv_time = $("#rv_time").val();

    let sendData = {'bill_id': bill_id, 'rv_time':rv_time};

    $.ajax({
        type: "POST",
        url: "./bill_form_submit_update.php",
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
                
                window.location.reload();
            }
        },
    });
}

function excelPop(){

    let building_id = $("#building_id option:selected").val();
    let bill_year = $("#bill_year option:selected").val();
    let bill_month = $("#bill_month option:selected").val();
    let bill_due_date = $("#bill_due_date").val();

    popOpen('excel_type_pop');

    return false;

    if(building_id == ""){
        alert("단지를 선택해주세요.");
        return false;
    }

    if(bill_year == ""){
        alert("고지서 등록 연도를 선택해주세요.");
        return false;
    }

    if(bill_month == ""){
        alert("고지서 등록 월을 선택해주세요.");
        return false;
    }

    if(bill_due_date == ""){
        alert("납부기한을 선택해주세요.");
        return false;
    }

    popOpen('excel_type_pop');
}

function excelupload(type){

    popClose('excel_type_pop');

    let building_id = $("#building_id").val();
    let bill_year = $("#bill_year option:selected").val();
    let bill_month = $("#bill_month option:selected").val();


    //let url = "./bill_excel_upload.php?building_id=" + building_id + "&bill_year=" + bill_year + "&bill_month=" + bill_month + "&excel_type=" + type;
    let url = "./bill_excel_upload.php?excel_type=" + type + "&building_id=" + building_id;

    var opt = "width=600,height=450,left=10,top=10"; 

    window.open(url, "win_excel", opt);

    return false;
}

function showExcelData(html){
    document.getElementById('sub_table_wrap').innerHTML = html;

    const scrollContainers = document.querySelectorAll('.scroll-wrapper');

    scrollContainers.forEach(scrollContainer => {
    let isDown = false;
    let startX;
    let scrollLeft;

    scrollContainer.addEventListener('mousedown', e => {
        isDown = true;
        scrollContainer.classList.add('active');
        startX = e.pageX - scrollContainer.offsetLeft;
        scrollLeft = scrollContainer.scrollLeft;
    });

    scrollContainer.addEventListener('mouseleave', () => {
        isDown = false;
    });

    scrollContainer.addEventListener('mouseup', () => {
        isDown = false;
    });

    scrollContainer.addEventListener('mousemove', e => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - scrollContainer.offsetLeft;
        const walk = (x - startX) * 1; // 드래그 속도
        scrollContainer.scrollLeft = scrollLeft - walk;
    });

    // 모바일 터치
    scrollContainer.addEventListener('touchstart', e => {
        startX = e.touches[0].pageX;
        scrollLeft = scrollContainer.scrollLeft;
    });

    scrollContainer.addEventListener('touchmove', e => {
        const x = e.touches[0].pageX;
        const walk = (x - startX) * 1;
        scrollContainer.scrollLeft = scrollLeft - walk;
    });
    });
}

$(function(){
    $(".ipt_date").datepicker({ changeMonth: true, changeYear: true, dateFormat: "yy-mm-dd", showButtonPanel: true, yearRange: "c-99:c+99", maxDate: "+365d", minDate:"0d", onSelect: function(dateText, inst) {
        console.log("선택된 날짜: ", inst); // 선택된 날짜를 콘솔에 출력
        // 다른 처리 로직도 추가 가능
        $(this).siblings(".date_del_btn").show();
    } });
});


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

function fbill_submit(f) {
    

    if(!checkValidDate(f.bill_due_date.value)){
        alert("납부 기한을 날짜 형식에 맞게 입력해주세요.");
        f.bill_due_date.focus();
        return false;
    }
    //document.querySelectorAll("input").forEach(i => console.log(i.name));


    return true;
}

// const scrollWrapper = document.querySelector('.scroll-wrapper');

// scrollWrapper.addEventListener('wheel', function(e) {
//   if (e.deltaY !== 0) {
//     e.preventDefault();
//     scrollWrapper.scrollLeft += e.deltaY;
//   }
// });


const scrollContainers = document.querySelectorAll('.scroll-wrapper');

scrollContainers.forEach(scrollContainer => {
  let isDown = false;
  let startX;
  let scrollLeft;

  scrollContainer.addEventListener('mousedown', e => {
    isDown = true;
    scrollContainer.classList.add('active');
    startX = e.pageX - scrollContainer.offsetLeft;
    scrollLeft = scrollContainer.scrollLeft;
  });

  scrollContainer.addEventListener('mouseleave', () => {
    isDown = false;
  });

  scrollContainer.addEventListener('mouseup', () => {
    isDown = false;
  });

  scrollContainer.addEventListener('mousemove', e => {
    if (!isDown) return;
    e.preventDefault();
    const x = e.pageX - scrollContainer.offsetLeft;
    const walk = (x - startX) * 1; // 드래그 속도
    scrollContainer.scrollLeft = scrollLeft - walk;
  });

  // 모바일 터치
  scrollContainer.addEventListener('touchstart', e => {
    startX = e.touches[0].pageX;
    scrollLeft = scrollContainer.scrollLeft;
  });

  scrollContainer.addEventListener('touchmove', e => {
    const x = e.touches[0].pageX;
    const walk = (x - startX) * 1;
    scrollContainer.scrollLeft = scrollLeft - walk;
  });
});
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

