<?php
require_once './_common.php';

if($w == 'u' && $bbs_idx != ''){
    $bbs_infos = sql_fetch("SELECT * FROM a_bbs WHERE bbs_idx = '{$bbs_idx}'");
    $bbs_code = $bbs_infos['bbs_code'];
}

switch($bbs_code){
    case "notice":
        $sub_menu = "920100";
    break;
    case "security":
        $sub_menu = "920200";
    break;
    case "bill":
        $sub_menu = "920300";
    break;
    case "onsite_schedule":
        $sub_menu = "920400";
    break;
    case "team_leader":
        $sub_menu = "920500";
    break;
    case "etc1":
        $sub_menu = "920600";
    break;
    case "etc2":
        $sub_menu = "920700";
    break;
    case "etc3":
        $sub_menu = "920800";
    break;
    case "etc4":
        $sub_menu = "920900";
    break;
    case "etc5":
        $sub_menu = "920910";
    break;
}



$bbs_setting = sql_fetch("SELECT bbs_title FROM a_bbs_setting WHERE bbs_code = '{$bbs_code}'");


$html_title = '';
if($w == 'u'){
    $html_title = '수정';
}else{
    $html_title = '등록';
}

$g5['title'] .= $bbs_setting['bbs_title'].' '. $html_title;
require_once './admin.head.php';
include_once(G5_PLUGIN_PATH.'/jquery-ui/datepicker.php');
require_once G5_EDITOR_LIB;


$sql = "SELECT * FROM a_bbs
        WHERE bbs_code = '{$bbs_code}' and bbs_idx = {$bbs_idx}";
$row = sql_fetch($sql);


$files_img = "SELECT * FROM g5_board_file WHERE bo_table = 'bbs_img' and wr_id = '{$bbs_idx}' and bf_file != ''";
$files_img_res = sql_query($files_img);
$files_img_cnt = sql_num_rows($files_img_res);
$files_img_max = 3;


$files_pdf = "SELECT * FROM g5_board_file WHERE bo_table = 'bbs_pdf' and wr_id = '{$bbs_idx}' and bf_file != ''";
$files_pdf_res = sql_query($files_pdf);

$files_pdf_list = array();

while($files_pdf_row = sql_fetch_array($files_pdf_res)){
    array_push($files_pdf_list, $files_pdf_row);
}

if($_SERVER['REMOTE_ADDR'] == "59.16.155.80"){
    echo $sql.'<br>';
    echo $files_img.'<br>';
    echo $files_pdf.'<br>';

    //echo $files_img_cnt;
    //print_r2($row);
}

// add_javascript('js 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
//add_javascript(G5_POSTCODE_JS, 0);    //다음 주소 js
?>
<!-- action="./bbs_form_update.php" -->
 <!-- onsubmit="return fbbs_submits(this);" -->

    <input type="hidden" name="w" value="<?php echo $w ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="sst" value="<?php echo $sst ?>">
    <input type="hidden" name="sod" value="<?php echo $sod ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="token" value="">
    <input type="hidden" name="bbs_code" value="<?php echo $bbs_code; ?>">
    <input type="hidden" name="bbs_idx" value="<?php echo $row['bbs_idx']; ?>">

    <div class="tbl_frm01 tbl_wrap">
        <h2 class="h2_frm ver2"><?php echo $bbs_setting['bbs_title']; ?> 정보</h2>
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
                  <th>제목</th>
                  <td colspan="3">
                    <input type="text" name="bbs_title" id="bbs_title" class="bansang_ipt ver2" size="100" value="<?php echo $row['bbs_title']; ?>">
                  </td>
               </tr>
               <tr>
                    <th>내용</th>
                    <td colspan="3"><?php echo editor_html('bbs_content', get_text($row['bbs_content'], 0)); ?></td>
               </tr>
               <tr>
                    <th>사진 첨부</th>
                    <td>
                        <?php echo help('사진은 최대 3장까지 등록가능합니다.'); ?>
                        <div class="ipt_box">
                            <div class="img_upload_wrap">
                                <?php if($files_img_cnt < $files_img_max){?>
                                <div class="img_upload_box ver1">
                                    <input type="file" name="img_up[]" id="img_up" onchange="addFile(this);" multiple accept="image/*">
                                    <label for="img_up">
                                        <img src="/images/file_plus.svg" alt="">
                                    </label>
                                </div>
                                <?php }?>
                                <?php if($w=="u" && $files_img_res){ ?>
                                    <?php for($i=0;$files_img_row = sql_fetch_array($files_img_res);$i++){?>
                                        <div class="img_upload_box filebox">
                                            <input type="file" name="img_up<?php echo $i + 1;?>" id="img_up<?php echo $i + 1;?>" accept="image/*" onchange="fileUp(this, 'img_up<?php echo $i + 1;?>', <?php echo $i; ?>, 'before')">
                                            <label for="img_up<?php echo $i + 1;?>">
                                                <img src="/data/file/bbs_img/<?php echo $files_img_row['bf_file']; ?>" class="img_up<?php echo $i + 1;?>" alt="">
                                            </label>

                                            <div class="file_del">
                                                <input type="checkbox" name="img_file_del[<?php echo $files_img_row['bf_no'];?>]" id="img_file_del<?php echo $i+1;?>" class="img_file_del" value="1">
                                                <label for="img_file_del<?php echo $i+1;?>">삭제</label>
                                            </div>
                                        </div>
                                    <?php }?>
                                <?php }?>
                            </div>
                        </div>
                    </td>
               </tr>
               <tr>
                    <th>파일 첨부</th>
                    <td colspan="3">
                        <?php echo help("pdf 파일만 첨부 가능합니다.");?>
                        <div class="bn_file_wrap">
                            <div class="ipt_box">
                                <?php for($i=1;$i<=3;$i++){?>
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
                                            <a href="/bbs/download_file.php?no=<?php echo $files_pdf_list[$i-1]['bf_no'];?>&bo_table=bbs_pdf&wr_id=<?php echo $bbs_idx; ?>" class="btn btn_03" >다운로드</a>
                                        </div>
                                    <?php }?>
                                </div>
                                <script>
                                    $("#bf_file<?php echo $i;?>").change(function() {
                                        //readURL(this);
                                        $(".file_contents_box<?php echo $i; ?>").text(this.files[0].name);
                                        console.log(this.files[0].name);
                                    });
                                </script>
                                <?php }?>
                            </div>
                        </div>
                    </td>
               </tr>
               <tr>
                    <th>노출여부</th>
                    <td>
                        <select name="is_view" id="is_view" class="bansang_sel">
                            <option value="1" <?php echo get_selected($row['is_view'], "1"); ?>>노출</option>
                            <option value="0" <?php echo get_selected($row['is_view'], "0"); ?>>미노출</option>
                        </select>
                    </td>
               </tr>
            </tbody>
        </table>
    </div>
    <div class="btn_fixed_top">
        <a href="./bbs_list.php?bbs_code=<?=$bbs_code; ?>&<?php echo $qstr ?>" class="btn btn_02">목록</a>
        <button type="button" class="btn_submit btn btn_03" onclick="fbbs_submit();">저장</button>
        <!-- <input type="submit" value="저장" class="btn_submit btn btn_02" accesskey='s'> -->
    </div>
    <form name="fbbs" id="fbbs"  method="post" enctype="multipart/form-data">
</form>
<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>게시글을 저장 중입니다.</p>
        <!-- <p>푸시 발송에 시간이 소요됩니다.</p> -->
        <p>잠시만 기다려주세요.</p>
    </div>
</div>
<script>

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
    var maxFileCnt = 3;   // 첨부파일 최대 개수
    var attFileCnt = document.querySelectorAll('.filebox').length;    // 기존 추가된 첨부파일 개수
    var remainFileCnt = maxFileCnt - attFileCnt;    // 추가로 첨부가능한 개수
    var curFileCnt = obj.files.length;  // 현재 선택된 첨부파일 개수

    let cnt = attFileCnt;

    // 첨부파일 개수 확인
    if (curFileCnt > remainFileCnt) {
        alert("사진 첨부파일은 최대 " + maxFileCnt + "개 까지 첨부 가능합니다.");
    } else {
        for (const file of obj.files) {

            console.log('file', file);
            // 첨부파일 검증
            if (validation(file)) {
                // 파일 배열에 담기
                var reader = new FileReader();
                reader.onload = function (e) {
                    filesArr.push(file);

                    // let previewHTML = `
                    //     <div class="filebox">
                    //         <img src="${e.target.result}" alt="">
                    //     </div>
                    // `;
                    console.log('cnt', cnt);

                    let previewHTML = `
                         <div class="img_upload_box filebox">
                            <input type="file" name="img_up${cnt + 1}" id="img_up${cnt + 1}" accept="image/*" onchange="fileUp(this, 'img_up${cnt + 1}', ${cnt}, 'before')">
                            <label for="img_up${cnt + 1}">
                                <img src="${e.target.result}" class="img_up${cnt + 1}" alt="">
                            </label>
                        </div>
                    `;

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

    console.log('filesArr', filesArr);
    //document.querySelector("input[type=file]").value = "";
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

function fbbs_submits(f){
    // return true;

    fbbs_submit();
}

function fbbs_submit() {

    $("#building_info_pop").show(); //팝업 띄우기
    $(".btn_submit").attr('disabled', true); //중복동작 막기

    //팝업이 안나오는 오류로 settimeout 지정
    setTimeout(() => {
        var formData = new FormData();
        formData.append('w', "<?php echo $w; ?>");
        formData.append('bbs_code', "<?php echo $bbs_code; ?>");
        formData.append('bbs_idx', "<?php echo $bbs_idx; ?>");
        formData.append('bbs_title', $("#bbs_title").val());
        formData.append('bbs_content', $("#bbs_content").val());
        formData.append('is_view', $("#is_view option:selected").val());

        //사진 첨부
        for (var i = 0; i < filesArr.length; i++) {
            // 삭제되지 않은 파일만 폼데이터에 담기
            formData.append("img_up[]", filesArr[i]);
        }
        
        // 체크된 삭제 항목 추가
        $("input[name^=img_file_del]").each(function() {
            if($(this).is(":checked") == true){
                formData.append("img_file_del[]", '1'); // 체크된 파일의 번호 추가
            }else{
                formData.append("img_file_del[]", '0'); // 체크된 파일의 번호 추가
            }
        });

        //파일 첨부
        $("input[name='bf_file[]']").each(function(index) {
            var files = this.files;  // 현재 파일 입력 필드에서 선택된 파일을 가져옴
            if (files.length > 0) {
                // 파일이 존재하는 경우에만 FormData에 추가
                for (var i = 0; i < files.length; i++) {
                    formData.append('bf_file[]', files[i]);  // 고유 인덱스 사용
                }
            }else{

                formData.append('bf_file[]', new Blob([''])); 
                //formData.append(j, 0, new Blob([''], { type: 'application/octet-stream' }));
            }
        });

        // 체크된 삭제 항목 추가
        $("input[name^=pdf_file_del]").each(function() {
            if($(this).is(":checked") == true){
                formData.append("pdf_file_del[]", '1'); // 체크된 파일의 번호 추가
            }else{
                formData.append("pdf_file_del[]", '0'); // 체크된 파일의 번호 추가
            }
        });
    

        var w_status = "<?php echo $w; ?>";

        $.ajax({
            type: "POST",
            url: "./bbs_form_update.php",
            data: formData,
            cache: false,
            async: true,
            dataType: "json",
            contentType: false,
            processData: false,
            success: function(data) {
                console.log('data:::', data);
                if(data.result == false) { 
                    alert(data.msg);
                    $(".btn_submit").attr('disabled', false);

                    $("#building_info_pop").hide();
                    return false;
                }else{
                    alert(data.msg);

                    setTimeout(() => {
                        if(w_status == 'u'){
                            location.reload();
                        }else{
                            window.location.href = './bbs_list.php?bbs_code=<?php echo $bbs_code?>';
                        }
                        
                    }, 1000);

                    $("#building_info_pop").hide();
                }
            },
            error:function(e){

                console.log('e', e);
                // alert(e);

                $("#building_info_pop").hide();
            }
        });

    }, 50);
    
}
</script>
<?php
run_event('admin_member_form_after', $mb, $w);

require_once './admin.tail.php';

