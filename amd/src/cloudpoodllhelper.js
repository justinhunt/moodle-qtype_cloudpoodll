define(["jquery", "core/log", "qtype_cloudpoodll/cloudpoodllloader"], function ($, log, cloudpoodll) {
    "use strict"; // jshint ;_;

    log.debug("cloudpoodll helper: initialising");

    return {

        configs: {},

        init: function (opts) {
            var config ={};
            config.component = opts["component"];
            config.data_id = opts["data_id"];
            config.inputname = opts["inputname"];
            config.transcriber = opts["transcriber"];
            config.uploadstate= false;
            config.safesave = opts["safesave"];

            //register controls
            var name = CSS.escape(config.inputname);
            config.controls = {
                nextpagebtn: $(".submitbtns .mod_quiz-next-nav"),
                mediaurl: $("input[name=" + name + "mediaurl]"),
                transcript: $("input[name=" + name + "transcript]"),
                answer: $("input[name=" + name + "]"),
            };
            this.configs[config.data_id] = config;
            var that=this;

            //setup recorder
            var gspeech = "";

            cloudpoodll.init(config.data_id, function (evt) {
                var theconfig=that.configs[evt.id];
                //we need to only do our event (not another recorder on this page)
                if (!theconfig){return;}

                switch (evt.type) {
                    case "recording":
                        if (evt.action === "started") {
                            gspeech = "";
                            // post a custom event that a filter template might be interested in
                            var cpquestionStarted = new CustomEvent("cpquestionStarted", {details: evt});
                            document.dispatchEvent(cpquestionStarted);

                            //if opts safe save
                            if(theconfig.safesave==1) {
                                theconfig.controls.nextpagebtn.attr('disabled', 'disabled');
                                //Add a page unload check ..
                                $(window).on('beforeunload', that.preventPrematureLeaving);
                            }
                        }
                        break;
                    case "speech":
                        gspeech += "  " + evt.capturedspeech;
                        theconfig.controls.transcript.val(gspeech);
                        theconfig.controls.answer.val(gspeech);
                        break;
                    case "awaitingprocessing":
                        if (theconfig.uploadstate != "posted") {
                            theconfig.controls.mediaurl.val(evt.mediaurl);
                            theconfig.controls.answer.val(evt.mediaurl);

                            // post a custom event that a filter template might be interested in
                            var cpquestionUploaded = new CustomEvent("cpquestionUploaded", {details: evt});
                            document.dispatchEvent(cpquestionUploaded);

                            //if opts safe save
                            if(theconfig.safesave==1) {
                                theconfig.controls.nextpagebtn.removeAttr('disabled', 'disabled');
                                //deactivate premature leaving
                                $(window).off('beforeunload', that.preventPrematureLeaving);
                            }
                        }
                        theconfig.uploadstate = "posted";
                        break;
                    case "error":
                        alert("PROBLEM: " + evt.message);
                        break;
                }
            }//end of callback function

            );//end of cp init

        },
        preventPrematureLeaving: function(e){
            log.debug('preventPrematureLeaving has been called');
            e.preventDefault();
            return e.returnValue = "Your recording has not been uploaded. Cancel to stay on this page.";
        },

        register_events: function (config) {
            //nothing here
        }
    };//end of return object
});