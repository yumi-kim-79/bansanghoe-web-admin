<?php
include_once("_common.php");
include_once(G5_PATH.'/head.sub.php');

$editor_url = G5_EDITOR_URL.'/'.$config['cf_editor'];
?>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<link href="<?php echo $editor_url ?>/summernote3/summernote-lite.min.css" rel="stylesheet">
<script src="<?php echo $editor_url ?>/summernote3/summernote.min.js"></script>
<link rel="stylesheet" href="/adm/css/editor.css">
<!-- include summernote css/js -->

<script src="<?php echo $editor_url ?>/summernote3/lang/summernote-ko-KR.js"></script>
<textarea id="summernote"></textarea>

<script>
    $(document).ready(function() {
        let presetContent = `
            <div style="
                background: url('/images/building_news_sample.jpg') no-repeat center center;
                background-size: 100% auto;
                padding: 50px;
                height:100%;
                color: black;
            ">
                <h2 style="text-align: center;">🎉 여기에 안내문을 작성하세요 🎉</h2>
                <p>이 영역에서 자유롭게 내용을 입력할 수 있습니다.</p>
            </div>
        `;

        $('#summernote').summernote({
            height: 1200, 
            focus: true,
            callbacks: {
                onInit: function() {
                    $('#summernote').summernote('code', presetContent);
                }
            }
        });
    });
</script>
<?php
include_once(G5_PATH.'/tail.sub.php');
?>