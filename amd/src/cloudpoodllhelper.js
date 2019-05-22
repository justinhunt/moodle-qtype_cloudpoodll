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

            //register controls
            var name = CSS.escape(config.inputname);
            config.controls = {
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
                            // that.controls.updatecontrol.val();
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
                        }
                        theconfig.uploadstate = "posted";
                        break;
                    case "error":
                        alert("PROBLEM: " + evt.message);
                        break;
                }
            }//end of callback function

            );//end of cp init

            //defunct
            //config = this.register_controls(config);
            //this.setup_recorder(config);
        },

        setup_recorder: function (config) {
            var gspeech = "";
            var recorder_callback = function (evt) {
                switch (evt.type) {
                    case "recording":
                        if (evt.action === "started") {
                            gspeech = "";
                            // that.controls.updatecontrol.val();
                        }
                        break;
                    case "speech":
                        gspeech += "  " + evt.capturedspeech;
                        config.controls.transcript.val(gspeech);
                        config.controls.answer.val(gspeech);
                        break;
                    case "awaitingprocessing":
                        if (config.uploadstate != "posted") {
                            config.controls.mediaurl.val(evt.mediaurl);
                            config.controls.answer.val(evt.mediaurl);
                        }
                        config.uploadstate = "posted";
                        break;
                    case "error":
                        alert("PROBLEM: " + evt.message);
                        break;
                }
            };
            var cp = cloudpoodll.clone();
            cp.init(config.data_id, recorder_callback);
        },

        register_controls: function (config) {
            var name = CSS.escape(config.inputname);
            config.controls = {
                mediaurl: $("input[name=" + name + "mediaurl]"),
                transcript: $("input[name=" + name + "transcript]"),
                answer: $("input[name=" + name + "]"),
            };
            return config;
        },

        register_events: function (config) {
            //nothing here
        }
    };//end of return object
});