(function ($) {
  $(document).ready(function () {
    /** summernote start */
    $(".summernote").summernote({
      lang: "ko-KR",
      height: 300,
      fontNames: [
        "Arial",
        "Arial Black",
        "Comic Sans MS",
        "Courier New",
        "GungSeo",
        "AppleMyungjo",
        "NanumGothic",
        "NanumMyeongjo",
        "Gulim",
        "Pretendard",
      ],
      fontNamesIgnoreCheck: [
        "Arial",
        "Arial Black",
        "Comic Sans MS",
        "Courier New",
        "GungSeo",
        "AppleMyungjo",
        "NanumGothic",
        "NanumMyeongjo",
        "Gulim",
        "Pretendard",
      ],
      fontSizes: [
        "8",
        "9",
        "10",
        "11",
        "12",
        "13",
        "14",
        "15",
        "16",
        "17",
        "18",
        "19",
        "20",
        "24",
        "30",
        "36",
        "48",
        "64",
      ],
      dialogsInBody: true,
      // toolbar
      toolbar: [
        //["style", ["style"]],
        // [
        //   "font",
        //   [
        //     "bold",
        //     "italic",
        //     "underline",
        //     "strikethrough",
        //     "superscript",
        //     "subscript",
        //     "clear",
        //   ],
        // ],
        ["font", ["bold", "italic", "underline", "clear"]],
        ["fontname", ["fontname"]],
        ["fontsize", ["fontsize"]],
        ["color", ["color"]],
        ["para", ["ul", "ol", "paragraph"]],
        ["height", ["height"]],
        //["table", ["table"]],
        ["insert", ["link", "picture", "table"]],
        ["view", ["fullscreen"]],
        // ["help", ["help"]],
      ],
      callbacks: {
        onImageUpload: function (files) {
          /** upload start */

          var maxSize = 20 * 1024 * 1024; // limit 20MB
          // TODO: implements insert image
          var isMaxSize = false;
          var maxFile = null;
          for (var i = 0; i < files.length; i++) {
            if (files[i].size > maxSize) {
              isMaxSize = true;
              maxFile = files[i].name;
              break;
            }
            //sendFile(files[i], this);
          }

          if (isMaxSize) {
            // 사이즈 제한에 걸렸을 때
            alert(
              "[" + maxFile + "] 파일이 업로드 용량(20MB)을 초과하였습니다."
            );
          } else {
            for (var i = 0; i < files.length; i++) {
              sendFile(files[i], this);
            }
          }
          /** upload end */
        },
        onPaste: function (e) {
          var clipboardData = e.originalEvent.clipboardData;
          if (
            clipboardData &&
            clipboardData.items &&
            clipboardData.items.length
          ) {
            var item = clipboardData.items[0];
            if (item.kind === "file" && item.type.indexOf("image/") !== -1) {
              e.preventDefault();
            }
          }
        },
      },
    });

    $(".summernote").summernote("fontSize", 14);
    /** summernote end */
  });
})(jQuery);
