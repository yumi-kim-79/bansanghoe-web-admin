<?php
include_once('./_common.php');
include_once(G5_PATH.'/head_sm.php');

$bbs_setting_sql = "SELECT * FROM a_bbs_setting WHERE is_view = 1 ORDER BY bbs_id asc";
$bbs_setting_res = sql_query($bbs_setting_sql);
?>
<!-- <script src="https://cdn.jsdelivr.net/npm/chart.js"></script> -->
<div id="wrappers">
    <div class="wrap_container">
        <div class="inner">
            <ul class="regi_list">
                <li>
                    <p class="regi_list_title">게시판 선택 <span>*</span></p>
                    <div class="ipt_box">
                        <select name="bbs_code" id="bbs_code" class="bansang_sel">
                            <option value="">선택</option>
                            <?php for($i=0;$bbs_setting_row = sql_fetch_array($bbs_setting_res);$i++){?>
                                <option value="<?php echo $bbs_setting_row['bbs_code']; ?>"><?php echo $bbs_setting_row['bbs_title']; ?></option>
                            <?php }?>
                        </select>
                    </div>
                </li>
                <li>
                    <p class="regi_list_title">제목 <span>*</span></p>
                    <div class="ipt_box">
                        <input type="text" name="bbs_title" id="bbs_title" class="bansang_ipt ver2" placeholder="제목을 입력해주세요.">
                    </div>
                </li>
                <li>
                    <p class="regi_list_title">내용 <span>*</span></p>
                    <div class="ipt_box">
                        <textarea name="bbs_content" id="bbs_content" class="bansang_ipt ver2 ta ta2" placeholder="내용을 입력해주세요."></textarea>
                    </div>
                </li>
                <li>
                    <p class="regi_list_title">사진첨부 <span class="pic_ver">*3장까지 등록 가능합니다.</span></p>
                    <div class="ipt_box">
                        <div class="img_upload_wrap">
                            <div class="img_upload_box ver1">
                                <input type="file" name="img_up[]" id="img_up" onchange="addFile(this);" multiple accept="image/*">
                                <label for="img_up">
                                    <img src="/images/file_plus.svg" alt="">
                                </label>
                            </div>
                        </div>
                    </div>
                </li>
                <li>
                    <p class="regi_list_title">파일 첨부 <span class="pic_ver">*PDF 파일만 업로드 가능합니다.</span></p>
                    <div class="ipt_box">
                        <?php for($i=1;$i<=3;$i++){?>
                        <div class="file_box">
                            <input type="file" name="bf_file[]" id="bf_file<?php echo $i;?>" class="bf_file" accept=".pdf">
                            <label for="bf_file<?php echo $i;?>">
                                <div class="file_contents_box_wrap">
                                    <div class="file_contents_box file_contents_box<?php echo $i; ?>"></div>
                                    <div class="file_content_del_box file_content_del_box<?php echo $i; ?>">
                                        <button type="button" onclick="clearFileInput('<?php echo $i;?>');" class="file_content_del">취소</button>
                                    </div>
                                </div>
                                <div class="label_box">파일첨부</div>
                            </label>
                        </div>
                        <script>
                            $("#bf_file<?php echo $i;?>").change(function() {
                                //readURL(this);
                                $(".file_contents_box<?php echo $i; ?>").text(this.files[0].name);
                                console.log(this.files[0].name);

                                $(".file_content_del_box<?php echo $i; ?>").show();
                            });
                        </script>
                        <?php }?>
                    </div>
                </li>
            </ul>
        </div>
        <div class="fix_btn_back_box"></div>
        <div class="fix_btn_box  ver3">
            <button type="button" onclick="board_submit();" class="fix_btn on" id="fix_btn" >저장</button>
        </div>
    </div>
</div>

<div id="building_info_pop">
    <div class="building_info_pop_inner"></div>
    <div class="building_pop_cont">
        <img src="/images/bansang_logos.svg" alt="">
        <p>저장 중입니다.</p>
        <p>잠시만 기다려주세요.</p>
    </div>
</div>
<!-- heic 파일용 -->
<script src="https://cdn.jsdelivr.net/npm/heic2any/dist/heic2any.min.js"></script>
<script>
//파일첨부 취소
function clearFileInput(idx) {

    let id = 'bf_file' + idx;

    console.log(id);

    const fileInput = document.getElementById('bf_file' + idx);
    if (fileInput) {
        fileInput.value = ''; // ✅ 첨부 파일 초기화
    }

    $(".file_contents_box" + idx).text("");
    $(".file_content_del_box" + idx).hide();
}


// 파일첨부
var fileNo = 0;
var filesArr = new Array();
let pdfFileArr = new Array();
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
        showToast("사진첨부는 최대 " + maxFileCnt + "개 까지 첨부 가능합니다.");
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

function board_submit(){

    $("#fix_btn").attr('disabled', true);
    $("#building_info_pop").show();

    setTimeout(() => {
        let mb_id = "<?php echo $member['mb_id']; ?>";
        let bbs_code = $("#bbs_code option:selected").val();
        let bbs_title = $("#bbs_title").val();
        let bbs_content = $("#bbs_title").val();

        var formData = new FormData();
        formData.append('mb_id', mb_id);
        formData.append('bbs_code', bbs_code);
        formData.append('bbs_title', bbs_title);
        formData.append('bbs_content', bbs_content);

        for (var i = 0; i < filesArr.length; i++) {
            // 삭제되지 않은 파일만 폼데이터에 담기
            formData.append("img_up[]", filesArr[i]);
        }

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
       

        $.ajax({
            type: "POST",
            url: "/board_write_update.php",
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
                    $("#building_info_pop").hide();
                    $("#fix_btn").attr('disabled', false);
                    return false;
                }else{
                    $("#building_info_pop").hide();

                    showToast(data.msg);
                    
                    setTimeout(() => {
                        location.replace('/board_list.php?tabIdx=' + data.data.bbs_id + '&tabCode=' + data.data.bbs_code); 

                    }, 700);
                }
            },
            error:function(e){
                alert(e);
            }
        });
    }, 50);
    
}
</script>
<?php
include_once(G5_PATH.'/tail.php');
?>