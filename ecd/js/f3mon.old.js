var selectedRun;
var mySwiper;


var runInfo = {
    runNumber : false,
    startTime : false,
    endTime : false,
    lastLs : false,
    streams : false,
    status : "NORUN", //NORUN,LIVE,CLOSED 
    sysName: DEFAULTINDEX,
    indexList: false,
    indexName: false,
    
    timer : false,
    interval : 5000,
    running: true,
    

    start : function(){ this.running = true; this.run(); },
    stop : function(){ 
        clearTimeout(this.timer); 
        this.running = false; this.runNumber = false; this.startTime = false; this.endTime = false; this.lastLs = false;
        this.streams = false; this.status = false;
    },
    run : function(){
        console.log("Runinfo...");
        if (!this.runNumber){ this.status = "NORUN"; }
        else {
            //GET RUN INFO (DATE)
            $.getJSON('php/runInfo.php?', { runNumber : runInfo.runNumber, sysName: runInfo.sysName },
                function(j){
                        runInfo.startTime = j.startTime ;
                        if (j.endTime){ runInfo.endTime = j.endTime; runInfo.status = "CLOSED"; } 
                        else { runInfo.endTime = "ongoing"; runInfo.status = "LIVE"; }
                })


            //UPDATE STREAMS 
            $.getJSON('php/streamsinrun.php?',{runNumber : runInfo.runNumber, sysName: runInfo.sysName },
                function(j){
                    j.forEach(function(data){
                            stream = data["term"];
                            if (!runInfo.streams){runInfo.streams = [];}
                            if (runInfo.streams.indexOf(stream) == -1 ){ runInfo.streams.push(stream); }
            })});

            //GET LAST LS
            $.getJSON('php/getLastLs.php?',{runNumber : runInfo.runNumber, sysName: runInfo.sysName },
                function(j){ if (j){runInfo.lastLs = j[0]; } 
            })
        }
        runInfo.updateUi();


        console.log("Runinfo:"+runInfo.runNumber,runInfo.startTime,runInfo.endTime,runInfo.streams,runInfo.lastLs,runInfo.status,runInfo.sysName);
        if (runInfo.running){runInfo.timer = setTimeout(function(){runInfo.run()},runInfo.interval)};
    },
    updateUi : function(){
        endTime = (runInfo.endTime && runInfo.endTime != "ongoing") ? new Date(runInfo.endTime).toLocaleString() : runInfo.endTime;
        startTime = (runInfo.startTime) ? new Date(runInfo.startTime).toLocaleString() : runInfo.startTime;
        if (runInfo.runNumber){ $('#runNumber').text(runInfo.runNumber); } else{ $('#runNumber').text("no Run ongoing");  }
        if (runInfo.lastLs){ $('#lastls').text(runInfo.lastLs); } else{ $('#lastls').text(NASTRING);}
        if (runInfo.streams){ $('#streams').text(runInfo.streams.join(", ")); } else{ $('#streams').text(NASTRING)}
        if (runInfo.startTime){$('#startTime').text(startTime);} else{ $('#startTime').text(NASTRING); }
        if (runInfo.endTime){$('#endTime').text(endTime);} else{ $('#endTime').text(NASTRING); }
    }
}

var runRanger = {   
    timer : false,
    interval : 5000,
    running: true,
    
    start : function(){ this.running = true; this.run(); },
    stop : function(){ clearTimeout(this.timer); this.running = false; },
    run : function(){
        console.log("Runranger...");
        $.getJSON('php/runList.php',{size : 1, sysName: runInfo.sysName},
            function(j){
                run = j["runlist"][0];
                if(runInfo.runNumber!=run.runNumber && !run.endTime){
                    changeRun(run.runNumber);
        }})
        
        if (runRanger.running){runRanger.timer = setTimeout(function(){runRanger.run()},runRanger.interval)};
    },
}

var riverStatus = {

    status : "CHECKING...", 
    main : false,
    collector : false,
    
    timer : false,
    interval : 10000,
    running: true,
    

    start : function(){ this.running = true; this.run(); },
    stop : function(){ 
        clearTimeout(this.timer); 
        this.running = false;   
        this.main = false;
        this.collector = false;
        this.status = "CHECKING...";
    },
    run : function(){
        console.log("RiverStatus...");

        $.when($.getJSON('php/riverStatus.php'))
            .then(function(j){
                riverStatus.main = false;
                riverStatus.collector = false;
                j.forEach(function(item){
                    //console.log(item);
                    if (item.name == "runriver"){riverStatus.main = item.node}
                    if (item.name == "runriver_"+runInfo.runNumber){riverStatus.collector = item.node}
                })
                riverStatus.updateUi();
            })


        //console.log("river status main,collector ",riverStatus.main,riverStatus.collector);
        if (riverStatus.running){riverStatus.timer = setTimeout(function(){riverStatus.run()},riverStatus.interval)};
    },
    updateUi : function(){
        riverStatus.status = "OK";
        
        outString = "<div><ul>";
        if ( !riverStatus.main ){ riverStatus.status = "DANGER"; outString = "<li>Main RunRiver not working. </li>"; }
        else { outString+="<li>Main RunRiver Running on node "+riverStatus.main+". </li>" }

        if (riverStatus.collector){ outString+="<li>RunRiver_"+runInfo.runNumber+" running on node "+riverStatus.collector+". </li>" }
        else if (runInfo.status == "LIVE") {riverStatus.status = "FAIL"; outString+="<li>Data collector not running.</li>"; }        
        outString+="</ul></div>" 

        $("#riverstatus").removeClass("btn-success");
        $("#riverstatus").removeClass("btn-warning");
        $("#riverstatus").removeClass("btn-danger");

        $('#riverstatus').attr('data-content',outString )

        //console.log(riverStatus);
        if (riverStatus.status=="OK"){$("#riverstatus").addClass("btn-success"); }
        else if (riverStatus.status=="WARNING" || riverStatus =="DANGER"){ $("#riverstatus").addClass("btn-warning"); }
        else if (riverStatus.status=="FAIL"){$("#riverstatus").addClass("btn-danger")}

    }
}

var runList = {  
    table: false,

    timer : false,
    interval : 10000,
    running: true,
    

    start : function(){this.running = true; this.run(); },
    stop : function(){ clearTimeout(this.timer); this.running = false; },
    run : function(){
        console.log("runList...");
        if (!runList.table){
            runListTable.ajax = "php/runListTable.php?sysName="+runInfo.sysName;
            runListTable.initComplete = runList.updateUi;    
            runList.table = $('#runListTable').DataTable(runListTable);
        }
        else {
            runList.table.ajax.url("php/runListTable.php?sysName="+runInfo.sysName);
            runList.table.ajax.reload(runList.updateUi,false);
        }
        
        
    },
    updateUi : function(){
        $('#runListNum').text("Num runs: "+runList.table.data().length);
        if (runList.running){runList.timer = setTimeout(function(){runList.run()},runList.interval)};
    }
}

var disksStatus = {
    ramdisk: 0,
    outdisk: 0,
    fudisk: 0,

    timer : false,
    interval : 10000,
    running: true,
    

    start : function(){ this.running = true; this.run(); },
    stop : function(){ clearTimeout(this.timer); this.running = false; },
    run : function(){
        console.log("disksStatus...");

        if (runInfo.runNumber){
            $.getJSON('php/getDisksStatus.php', { runNumber : runInfo.runNumber, sysName: runInfo.sysName },
            function(j){
                disksStatus.ramdisk = (j.ramdiskused.value/j.ramdisk.value*100.).toFixed(2);
                disksStatus.outdisk = (j.outputused.value/j.output.value*100.).toFixed(2);
                disksStatus.fudisk = (j.dataused.value/j.data.value*100.).toFixed(2);
            })    
        }
        disksStatus.updateUi();
        if (disksStatus.running){disksStatus.timer = setTimeout(function(){disksStatus.run()},disksStatus.interval)};
    },
    updateUi : function(){
        if (!runInfo.runNumber){
            $('#buramdisk').text(NASTRING);
            $('#buoutdisk').text(NASTRING);
            $('#fuoutdisk').text(NASTRING);
        } else {
            $('#buramdisk').text(disksStatus.ramdisk+"% used");
            $('#buoutdisk').text(disksStatus.outdisk+"% used");
            $('#fuoutdisk').text(disksStatus.fudisk+"% used");
        }
    }
}

var streamChart = {
    slider: false,
    sliderBound: true,
    sliderChanging: false,

    chart: false,
    mchart: false,
    minLs: false,
    maxLs: false,
    mMinLs: false,
    mMaxLs: false,
    mTitle: false,
    mmTitle: false,
    //range: false,
    unit: "Events", //Events, Bytes
    lsInterval : false,
    zoomed: false,

    timer : false,
    interval : 5000,
    running: true,
    
    took: 0,

    ddData :false,

    start : function(){ 
        this.running = true; 
        this.initSlider();
        this.initChart();
        this.run(); 
    },
    stop : function(){ 
        clearTimeout(this.timer); 
        this.running = false; 
        this.zoomed= false;
        this.disableDrillDown();
        if(this.chart){this.chart.showLoading(WAITINGCHART);}

    },
    run : function(){
        console.log("streamChart...");
        if (runInfo.runNumber && runInfo.streams && !streamChart.sliderChanging){

            streamChart.updateSlider();
            if(!streamChart.zoomed){
                streamChart.minLs = $("#ls-slider").rangeSlider("min");
                streamChart.maxLs = $("#ls-slider").rangeSlider("max");
                streamChart.updateChart();        
            }            
            return;
        } 
        streamChart.next();
    },
    updateChart : function(){
        //streamChart.chart.showLoading();
        //console.log("updateChart ",streamChart.minLs,streamChart.maxLs);
        clearTimeout(streamChart.timer); 
        //streamChart.range = Math.round( (streamChart.maxLs-streamChart.minLs+1) / 20 );
        //console.log(streamChart.range,streamChart.minLs,streamChart.maxLs);


        serie = streamChart.chart.get("minimerge");
        if (serie == null){
            serie = streamChart.chart.addSeries({
                type            : 'column',
                id              : "minimerge",
                name            : "minimerge",
                yAxis           : "mpercent",
                showInLegend    : false,
                point           : {"events":{"click": streamChart.mDrillDown}},
                cursor          : "pointer"
            });
        }

        runInfo.streams.forEach(function(stream){
                //Creates data and total serie if doesnt exist
                serie = streamChart.chart.get(stream);
                if (serie == null){
                    serie = streamChart.chart.addSeries({
                        type    : 'column',
                        id      : stream,
                        name    : stream,
                        yAxis   : "rates",
                    });
                    streamChart.chart.addSeries({
                        color   : serie.color,
                        showInLegend: false,          
                        type    : 'spline',
                        id      : stream+"_complete",
                        name    : stream+"_complete",
                        yAxis   : "percent",
                    });
                }
        });  
        $.when(  $.getJSON('php/streamhist.php?',
            {
                runNumber   : runInfo.runNumber,
                from        : streamChart.minLs,
                to          : streamChart.maxLs,
                intervalNum : 20,
                sysName     : runInfo.sysName,
                streamList  : runInfo.streams,
            })).done(function(j){

                    //console.log(j);

                    streamList = j.streamList;
                    took = j.took;
                    mmdata = j.minimerge;
                    lsList = j.lsList;

                    streamChart.lsInterval = j.interval;

                    serie = streamChart.chart.get("minimerge");
                    serie.setData(mmdata.percents,false,false,false);

                    streamList.forEach(function(stream){
                        streamData = j.streams[stream]
                        //console.log(stream,streamData.dataIn.toString(),totals.toString());
                        if (streamChart.unit == "Events"){ data = streamData.dataOut; }
                        else{ data = streamData.fileSize; }    
                        serie = streamChart.chart.get(stream);

                        serie.setData(data,false,false,false);
                        cdata = streamData.percent;
                        
                        serie = streamChart.chart.get(stream+"_complete");
                        serie.setData(cdata,false,false,false);
                    })


                    //console.log(mmdata.total);
                    //console.log(data);

                    $("#querytime").text(took.toString()+" ms");
                    streamChart.chart.xAxis[0].setCategories(lsList); //doenst work. dunno why.
                    streamChart.chart.redraw();
                    streamChart.chart.hideLoading();
                    streamChart.next();
                
            })
    },
    next: function(){
        clearTimeout(streamChart.timer); 
        if (streamChart.running){streamChart.timer = setTimeout(function(){streamChart.run()},streamChart.interval)};      
    },
    initChart: function(){
        if (this.chart) { this.chart.destroy(); }
        lsChartConfig.chart.events.selection = streamChart.selection;
        //lsChartConfig.chart.events.drilldown = streamChart.mmDrillDown;
        this.chart = new Highcharts.Chart(lsChartConfig);
        this.chart.showLoading(WAITINGCHART);


    },
    initSlider: function(){
        if(streamChart.slider){$("#ls-slider").rangeSlider("destroy");$("#ls-slider").unbind();};
        $("#ls-slider").rangeSlider(ls_slider);
        $("#ls-slider").bind("valuesChanged", function(e, data){
            console.log("Values just changed. min: " + data.values.min + " max: " + data.values.max);
            if (data.values.max == runInfo.lastLs) { streamChart.sliderBound = true} else {streamChart.sliderBound=false}
            //if (!streamChart.zoomed){
            //    streamChart.minLs = data.values.min;
            //    streamChart.maxLs = data.values.max;
            //    streamChart.updateChart();    
            //}
            
        });
        $("#ls-slider").bind("userValuesChanged", function(e, data){
            //console.log("userValuesChanged");
            streamChart.minLs = data.values.min;
            streamChart.maxLs = data.values.max;
            streamChart.updateChart();   
        });
        $("#ls-slider").bind("valuesChanging", function(e, data){
            clearTimeout(streamChart.timer);
        });
        streamChart.slider = true;
        streamChart.sliderBound = true;
    },
    updateSlider: function(){
        if (runInfo.lastLs < 21) {return};
        sliderTickStep = parseInt(runInfo.lastLs / 10 );
        $("#ls-slider").rangeSlider("bounds", 1, runInfo.lastLs);
        range = $("#ls-slider").rangeSlider("values");
        
        if (streamChart.sliderBound) {
            diff = runInfo.lastLs - range.max;
            $("#ls-slider").rangeSlider("values", range.min+diff, runInfo.lastLs);
        };
    },
    disableDrillDown : function(){
        if (streamChart.mchart) { streamChart.mchart.destroy(); streamChart.mchart = false;}
        streamChart.selectSR();
        if (!$(".btn-sr,.btn-dd").hasClass("disabled")) {$(".btn-sr,.btn-dd").addClass('disabled') };

    },
    selectSR : function(){
        $(".btn-sr,.btn-dd").removeClass('disabled');
        $(".btn-sr").removeClass('btn-default').addClass('btn-primary');
        $(".btn-dd").removeClass('btn-primary').addClass('btn-default');
        mySwiper2.swipeTo(0);
    },
    selectDD : function(){
        $(".btn-sr,.btn-dd").removeClass('disabled');
        $(".btn-dd").removeClass('btn-default').addClass('btn-primary');
        $(".btn-sr").removeClass('btn-primary').addClass('btn-default');
        mySwiper2.swipeTo(1);
    },
    mmDrillUp : function(){
        $("#ddtitle").text(streamChart.mTitle);
    },
    mmDrillDown : function(event){    //second level drill down
        stream = event.point.name;
        console.log(stream);
        $.when(  $.getJSON('php/miniperbu.php?',
            {
                runNumber   : runInfo.runNumber,
                from        : streamChart.mMinLs,
                to          : streamChart.mMaxLs,
                sysName     : runInfo.sysName,
                stream      : stream,
            })).done(function(j){
                console.log(j);
                serie = streamChart.mchart.addSeriesAsDrilldown(event.point, { 
                    type    : 'column',
                    id      : "drilldown",
                    name    : "BUs",
                    yAxis   : "percent",
                    data    : j.percents,
                });
                streamChart.mmTitle = " Stream: "+stream+" || LS Range: "+streamChart.mMinLs.toString()+" - "+streamChart.mMaxLs.toString();
                $("#ddtitle").text(streamChart.mmTitle);
            })
    },
    mDrillDown : function(event){   //first level drill down
        console.log(event);
        console.log(event.point);
        console.log(event.point.name);

        streamChart.mMinLs = event.point.name;
        if (streamChart.lsInterval == 1) { streamChart.mMaxLs = streamChart.mMinLs }
            else {streamChart.mMaxLs = event.point.name + streamChart.lsInterval;}
        
        $.when(  $.getJSON('php/miniperstream.php?',
            {
                runNumber   : runInfo.runNumber,
                from        : streamChart.mMinLs,
                to          : streamChart.mMaxLs,
                sysName     : runInfo.sysName,
                streamList  : runInfo.streams,
            })).done(function(j){
                console.log(j);
                if (streamChart.mchart) { streamChart.mchart.destroy(); }
                mChartConfig.chart.events.drilldown = streamChart.mmDrillDown;
                mChartConfig.chart.events.drillup = streamChart.mmDrillUp;
                streamChart.mchart = new Highcharts.Chart(mChartConfig);
                serie = streamChart.mchart.addSeries({
                        type    : 'column',
                        id      : "drilldown",
                        name    : "drilldown",
                        yAxis   : "percent",
                        data    : j.percents,

                });
                streamChart.mTitle = " LS Range: "+streamChart.mMinLs.toString()+" - "+streamChart.mMaxLs.toString();
                $("#ddtitle").text(streamChart.mTitle);
                streamChart.selectDD();
            })
    },
    selection : function(event){          
        console.log("selection");
        console.log(event);


        if (event.xAxis) {
            event.preventDefault();
            var xAxis = event.xAxis[0];
            min = Math.round(xAxis.min);
            max = Math.round(xAxis.max);

            console.log(xAxis.axis.categories);
            //console.log(streamChart.chart.xAxis[0].categories);
            min = xAxis.axis.categories[min];
            max = xAxis.axis.categories[max];

            if (min<1) { min = 1};
            if ((max-min)<21){max = min+20}
            streamChart.minLs = min;
            streamChart.maxLs = max;
            
            streamChart.updateChart();

            if(!streamChart.zoomed){
                // use jQuery HTML capabilities to add a button to reset the selection 
                streamChart.chart.$resetButton = $('<button>Reset view</button>')
                .css({
                    position: 'absolute',
                    top: '20px',
                    right: '50px',
                    zIndex: 50
                })
                .click(function() {
                    streamChart.zoomed = false;
                    streamChart.minLs = $("#ls-slider").rangeSlider("min");
                    streamChart.maxLs = $("#ls-slider").rangeSlider("max");
                    streamChart.updateChart();     
                    streamChart.chart.$resetButton.remove(); 
                })
                .appendTo(streamChart.chart.container);
            }
            streamChart.zoomed = true;
        }
    }
}

var microstatesChart = {

    chart: false,
    status: "off", //off, waitingforlegend, ready
    lastTime: 0,
    series: [],

    timer : false,
    interval : 5000,
    running: true,
    

    start : function(){ 
        this.running = true; 
        this.initChart();
        this.run(); 
    },
    stop : function(){ 
        clearTimeout(this.timer); 
        this.running = false;
        this.status = "off"; 
        this.lastTime = 0;
        this.series = [];
        if(this.chart){this.chart.showLoading(WAITINGCHART);}
    },
    run : function(){
        console.log("microstatesChart...");
        //console.log("mschart status "+this.status);
        if (riverStatus.collector){
            if (microstatesChart.status == "off"){ this.getLegend(); }
            else if (microstatesChart.status == "ready"){
                $.when( $.getJSON('php/nstates.php',{sysName: runInfo.sysName}))
                .then( function(j){
                    time = j.time;
                    data = j.entries;
                    //console.log(data);
                    if (time != microstatesChart.lastTime){
                        microstatesChart.lastTime = time;
                        microstatesChart.series.forEach(function(id){
                            serie = microstatesChart.chart.get(id);
                            shift = serie.data.length>30;
                            value = null;
                            if (typeof data[id] != "undefined"){value = data[id]};
                            serie.addPoint([time,value],false,shift);
                        })
                        microstatesChart.chart.redraw();
                    }
                    if (microstatesChart.running){microstatesChart.timer = setTimeout(function(){microstatesChart.run()},microstatesChart.interval)};
                })
                return;
            }
        }
        else if (runInfo.status == "CLOSED") { this.status = "off"; this.chart.showLoading("No data for this run");}
        if (microstatesChart.running){microstatesChart.timer = setTimeout(function(){microstatesChart.run()},microstatesChart.interval)};
    },
    initChart : function(){
        if (this.chart) { this.chart.destroy(); }
        this.chart = new Highcharts.Chart(msChartConfig);
        this.chart.showLoading(WAITINGCHART);
    },
    getLegend : function(){
        console.log("Microstate getLegend");
        if (microstatesChart.status == "off"){
            microstatesChart.status = "waitingforlegend";
            $.when($.getJSON("php/ulegenda.php?", { runNumber : runInfo.runNumber, sysName: runInfo.sysName }))
            .then(function(j){
                if (!j) { microstatesChart.status = "off"; return false; }
                names = j.split(" ");
                names.forEach(function(name){
                    value = name.split("=");
                    if (value[1] != "" && name != ""){

                        value[0] = parseInt(value[0]);
                        microstatesChart.series.push(value[0]);
                        serie = microstatesChart.chart.addSeries({
                            type    : "area",
                            //color   : calcolor(value[0]),
                            id      : value[0],
                            name    : value[1]
                        })
                    }
                })       
                microstatesChart.status = "ready";
                microstatesChart.chart.hideLoading();
            })

        }
        
    }
}

var logTable = {  
    table: false,
    scrollPos: 0,

    timer : false,
    interval : 30000,
    running: true,
    

    start : function(){ this.running = true; this.run(); },
    stop : function(){ clearTimeout(this.timer); this.running = false; },
    run : function(){
        console.log("logTable...");
        if (!logTable.table){
            console.log("Create table");
            logTableConfig.initComplete = logTable.updateUi;    
            logTable.table = $('#logTable').DataTable(logTableConfig);
        }
        else {
            logTable.scrollPos=$(".dataTables_scrollBody").scrollTop();
            logTable.table.ajax.reload(logTable.updateUi,false); 
        }        
        
    },
    updateUi: function(){
        $(".dataTables_scrollBody").scrollTop(logTable.scrollPos);
        num = logTable.table.page.info().recordsTotal;
        $('#logNum').text(num);
        if (num > 0){ 
            $('#logNum').removeClass("label-success");
            $('#logNum').addClass("label-danger");
        }else{
            $('#logNum').removeClass("label-danger");
            $('#logNum').addClass("label-success");
        }
        if (logTable.running){logTable.timer = setTimeout(function(){logTable.run()},logTable.interval)};   
    }
}



function getIndices(){
    console.log("GetIndices...");

    $.when($.getJSON('php/getIndices.php'))
    .then(function(j){
        runInfo.indexList = j;
        sysList = Object.keys(j);
        sysList.forEach(function(item){
            $("#indexlist").append('<li><a href="#">'+item+'</a></li>');
        })
        if (sysList.indexOf(DEFAULTINDEX) >= 0){defIndex = DEFAULTINDEX }
            else {defIndex = sysList[0]}
      
        runInfo.sysName = defIndex;
        $("#indexname").text(defIndex);

    })
}

function startItAll(){
    console.log("startItAll");
    getIndices();
    runInfo.start();   
    runRanger.start();
    riverStatus.start();
    runList.start();
    disksStatus.start();
    streamChart.start(); 
    microstatesChart.start();
    logTable.start();
};

function setControls(){
    console.log("setControls")
    $("#indexlist").on('click','li',function(){
        runInfo.sysName = $(this).find("a").text();
        runInfo.indexName = runInfo.indexList[runInfo.sysName];
        $("#indexname").text(runInfo.sysName);
        console.log(runInfo.indexList);
        console.log("Index changed to: "+runInfo.indexName)
        
    })

    mySwiper = $('.swiper-main').swiper({
        noSwiping: true,
        mode: "vertical"
    });
    mySwiper2 = $('.swiper-nested-sr').swiper({
        noSwiping: true,
        mode: "horizontal"
    });

    //setTimeout(function () { mySwiper.reInit(); mySwiper.resizeFix(true); }, 500);
    $(".btn-dd").click(function(e){
        streamChart.selectDD();
    });
    $(".btn-sr").click(function(e){
        streamChart.selectSR();
    });
    $("#logButton").click(function(e){
        mySwiper.swipeTo(1);
    });
    $("#f3monButton").click(function(e){
        mySwiper.swipeTo(0);
    });

    $('.btn-unit').click(function() {
        if (streamChart.unit=="Events"){streamChart.unit="Bytes"}else{streamChart.unit="Events"}
        streamChart.chart.yAxis[0].update({title: {text: streamChart.unit},}); 
        console.log(streamChart.unit);
        $(this).find('.btn').toggleClass('active');  
        
        if ($(this).find('.btn-primary').size()>0) {
            $(this).find('.btn').toggleClass('btn-primary');
        }
        if ($(this).find('.btn-danger').size()>0) {
            $(this).find('.btn').toggleClass('btn-danger');
        }
        if ($(this).find('.btn-success').size()>0) {
            $(this).find('.btn').toggleClass('btn-success');
        }
        if ($(this).find('.btn-info').size()>0) {
            $(this).find('.btn').toggleClass('btn-info');
        }
        
        $(this).find('.btn').toggleClass('btn-default');
           
    });

    $("#riverstatus").popover({container:'body',html:true});
    $('#runranger').tooltip({animation:true});
    $("#runranger").click(function(e){
        $(this).toggleClass("btn-success");
        $(this).toggleClass("btn-danger");
        if ($(this).hasClass("btn-success")){runRanger.start(); } 
        else { runRanger.stop() }
    });

    $("#runListTable").on("click", ".run-show",function() {
        //console.log($(this).closest(".row-tools").parent().html());
        rn = $(this).closest(".row-tools").attr("number");

        runRanger.stop();
        changeRun(rn);

        $("#runranger").removeClass("btn-success");
        $("#runranger").addClass("btn-danger");
        //stopItAll();
    });

    $("#runListTable").on("click", ".run-close",function() {
        
        rn = $(this).closest(".row-tools").attr("number");
        selectedRun = rn;
        $("#closeModal").find("#runNumber").text(selectedRun);
        $("#closeModal").modal("show");
    });

    $("#closeModal").on("click", ".run-close-ok",function() {
        //console.log(selectedRun);
        $.when($.getJSON('php/closeRun.php',{runNumber : selectedRun}))
        .then(
            function(j){
             console.log(selectedRun+" closed.");
        })

    });
}

function changeRun(runNumber){
    console.log("Changing runNmber to: "+runNumber);

    runInfo.stop();
    riverStatus.stop()
    streamChart.stop();
    microstatesChart.stop();
    
    runInfo.runNumber = runNumber;
    
    runInfo.start();
    riverStatus.start()
    streamChart.start();
    microstatesChart.start();
}

setControls();
startItAll();