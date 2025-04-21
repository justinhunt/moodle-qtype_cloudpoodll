define(["jquery",
        "core/log",
        "qtype_cloudpoodll/cloudpoodll"],
    function ($, log, CloudPoodll) {
        return {
            //for making multiple instances
            clone: function(){
                return $.extend(true,{},this);
            },

            init: function (recorderid, thecallback) {
                CloudPoodll.createRecorder(recorderid);
                CloudPoodll.theCallback = thecallback;
                CloudPoodll.initEvents();
                $("iframe").on("load", function () {
                    $(".qtype_cloudpoodll_recording_cont").css("background-image", "none");
                });
            }
        };
    }
);