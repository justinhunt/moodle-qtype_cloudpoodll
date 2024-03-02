define(["jquery", "core/log", "qtype_cloudpoodll/cloudpoodllloader", "core/templates"], function ($, log, cloudpoodll, templates) {
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
                details: $("input[name=" + name + "details]"),
                answer: $("input[name=" + name + "]"),
                answerstatus: $("div#" + name + "_asc"),
            };
            this.configs[config.data_id] = config;
            var that=this;

            //setup recorder
            var gspeech = "";

            cloudpoodll.init(config.data_id, function (evt) {
                var theconfig=that.configs[evt.id];
                //we need to only do our event (not another recorder on this page)
                if (!theconfig){return;}
                //log the details on this event
                that.logDetails(theconfig.controls.details,
                    theconfig.controls.answerstatus,
                    evt);

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
        },

        logDetails: function(details,answerstatus,theevent){
          //make sure we have a details control to work with
          if(details.length>0){
              //get new, or existing details
              var detailsobj={recevents: []};
              var json_string=details.val();
              if(json_string!==null && json_string!==''){
                  try {
                      detailsobj = JSON.parse(json_string);
                  } catch (error) {
                      log.debug('not valid details string')
                  }
              }
              //we don't want to log everything, so just do the main data
              var logdata={}
              logdata.type=theevent.type;
              switch(theevent.type){
                  case "recording":
                      logdata.action=theevent.action;
                      break;

                  case "awaitingprocessing":
                      logdata.targetfile = theevent.mediaurl;
                      break;

                  case "error":
                      logdata.code=theevent.code;
                      logdata.message=theevent.message;
                      break;

                  case "filesubmitted":
                      logdata.finalfile = theevent.mediaurl;
                      break;

                  case "uploadcommenced":
                      logdata.srcfile = theevent.sourcefilename;
                      logdata.mimetype = theevent.sourcemimetype;
                      break;

                  default:
                      //just the type I guess

              }
              //add the new event details
              detailsobj.recevents.push(logdata)
              details.val(JSON.stringify(detailsobj));

              //update our question display so its clear what happened
              logdata.insession=true;
              logdata[logdata.type]=true;
              templates.render('qtype_cloudpoodll/answerstatus',logdata).then(
                  function(html,js){
                      answerstatus.html(html);
                  }
              );

          }
        },
    };//end of return object
});