buttonString= '<div class="btn-group btn-group-xs row-tools pull-right">';
buttonString+= '<a role="button" class="btn btn-default btn-xs run-close" data-toggle="tooltip" title="Close data collector."><span class="fa fa-power-off"></span></a>';
buttonString+= '<a role="button" class="btn btn-default btn-xs run-show" data-toggle="tooltip" title="Display this run."><span class="fa fa-play-circle "></span></a>';
buttonString+= '</div>'; 

runListTable = {
    //processing: true,
    //serverSide: true,
    ajax: "php/runListTable.php",

    bAutoWidth: false,
//    bJQueryUI: true, 
    order: [[ 1, "desc" ]],
    iDisplayLength: 5,
    lengthChange: false,
    info: false,
    dom: '<flrtip>',    
    columns: [
        {   data: 'runNumber', title:'Number'},
        {   data: 'startTime', title:'Start', type: "date-euro",
            render: function(data){
                        d = new Date(data).toString("dd/MM/yy HH:mm");
                        return d
                    },
        },
        {   data: 'endTime', title:'End', type: "date-euro", bSortable: false, // the sorting feature for this field cause stuck
            render: function(data){
                if (data){
                    d = new Date(data).toString("dd/MM/yy HH:mm");}
                else {
                    d = "ongoing"};
                return d
            },
        },
        {   data: 'buttons', title:'', defaultContent:"" , bSortable: false, bSearchable: false,
            fnCreatedCell: function (item, data, row) { 
                $(item).append(buttonString).find(".row-tools").attr("number",row["runNumber"]);
            }
        }
    ],
    serverParams : function ( aoData ) {
        //console.log(runInfo.startTime,runInfo.endTime)
        aoData["sysName"] = runInfo.sysName;
    }
};


logTableConfig = {
    //processing: true,
    serverSide: true,
    ajax: "php/logtable.php",
    scrollY: "400px",
    scrollCollapse: true,
    autoWidth: false,
    //bJQueryUI: true, 
    order: [[ 3, "desc" ]],
    //iDisplayLength: 5,
    //lengthChange: false,
    //info: false,
    //dom: '<flrtip>',   
    language: {
         infoFiltered: ""
    },
    columns: [
        {   data: 'host', title:'Host', width:"15%"},
        {   data: 'severity', title:'Severity', width:"10%" },
        {   data: 'message', title:'Message', width:"60%"  },
        {   data: 'msgtime', title:'MsgTime', width:"15%"}
    ],
    serverParams : function ( aoData ) {
        //console.log(runInfo.startTime,runInfo.endTime)
        startTime = (!runInfo.startTime) ? "now+1s" : runInfo.startTime;
        endTime = (runInfo.endTime == "ongoing" || !runInfo.endTime ) ? "now" : runInfo.endTime ;
        aoData["startTime"] = startTime;
        aoData["endTime"] = endTime;
        aoData["sysName"] = runInfo.sysName;
    }
};