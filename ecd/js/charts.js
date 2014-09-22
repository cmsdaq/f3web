lsChartConfig = 
{
    chart: {
        //animation: false,
        animation : {
            duration : 500,
            easing: 'linear'
        },
        renderTo : "streamrates-chart",
        //height: 400,
        zoomType: 'x',
        events: {},
    },
    tooltip: {
        //followPointer: true 
    },
    legend: {
        align: 'center',
        verticalAlign: 'bottom',
        maxHeight: 50,
    },
    title: {
        text: ''
    },
    xAxis: [
    {
        //lineWidth:0,
        gridLineWidth: 1,
        id: "ls",
        allowDecimals: false,
        title: {
            text: 'LS'
        },
        categories: true,
        tickmarkPlacement: 'on',
        type: "category"
    }
    ],
    yAxis: [
    {
        title: {text: 'Events'},
        id:"rates",
        height: 260,
        lineWidth: 1,
        offset:0
    },
    {
       title: {text: 'Completeness %'},
       max : 100,
       min : 0,
       height: 260,
       opposite : true,
       id: "percent",
       lineWidth: 1,
       offset:0
   },
   {
       title: {text: 'Merge %'},
       max : 100,
       min : 0,
       id: "mpercent",
       height: 30,
       top: 280,
       lineWidth: 1,
       offset:0,
   }],
   plotOptions:{

        series: {
            groupPadding: 0,
            pointPadding: 0,
            events: {
                legendItemClick: function(event) {
                        cSerie = this.chart.get(this.name+"_complete");
                        cSerie.setVisible(!this.visible,false);
                }
            }
        }
    }
};


msChartConfig =
{
    chart: {
        animation : false, //animation for stacked area is not supported
        
        ignoreHiddenSeries : true,
        renderTo : "microstatestime-chart",
        //height: 400,
        zoomType: 'xy',            
        


    },
    legend: {
        layout: "vertical",
        align: "right",
        verticalAlign: 'top',
        //floating: true,
        borderRadius: 5,
        borderWidth: 1,
        itemDistance: 5,
        symbolRadius: 5
    },
    colors: Colors.colorList(),
    type: 'area',

    title: {
        text: ''
    },
    subtitle: {
        text: ''
    },
    xAxis: {
        minPadding : 0,
        maxPadding : 0,
        type: 'datetime',
        //tickmarkPlacement: 'on',
        //title: {
        //    enabled: false
        //}
    },
    yAxis: {
        title: {
            text: 'Percent'
        }
    },

    plotOptions: {
        area: {
            gapsize: 1,
            stacking: 'percent',
            //pointPadding: 0,
            //groupPadding: 0,
            //fillOpacity: 0.5,
            connectNulls: false,
            //lineColor: '#ffffff',
            lineWidth: 0,
            marker: {
                enabled : false,
                states: {
                    hover: {
                        enabled : false,
                    }
                }
            }
        },
        areaspline: {
            stacking: 'percent',
            //pointPadding: 0,
            //groupPadding: 0,
            //fillOpacity: 0.5,
            connectNulls: false,
            //lineColor: '#ffffff',
            lineWidth: 0,
            gapSize: 2,
            marker: {
                enabled : false
            }
        },
        column: {
            stacking: 'percent',
            pointPadding: 0,
            groupPadding: 0,
            borderWidth: 0,
            //fillOpacity: 0.5,
            //connectNulls: false,
            //lineColor: '#ffffff',
            //lineWidth: 0,
            //marker: {
            //    enabled : false
            //}
        }
    }
};


//drilldown chart config
mChartConfig = 
{
    chart: {
        //animation: false,
        animation : {
            duration : 500,
            easing: 'linear'
        },
        renderTo : "drilldown-chart",
        //height: 400,
        //zoomType: 'x',
        events: {},
        marginRight: 50,
        //borderRadius: 20,
        //borderWidth: 2,
    },
    tooltip: { followPointer: true },
    legend: {enabled: false },
    title: { text: '' },
    xAxis: [
    {
        lineWidth:0,
        gridLineWidth: 1,
        id: "ls",
        allowDecimals: false,
        title: { text: 'LS' },
        tickmarkPlacement: 'on',
        type: "category"
    }
    ],
    yAxis: [
    {
       title: {text: 'Completeness %'},
       max : 100,
       min : 0,
       //opposite : true,
       id: "percent",
       lineWidth: 1,
       offset:0
   }],
   plotOptions:{

        series: {
            minPointLength: 3
        }
    },
    drilldown: {
            series: []
        }
};

hrChartConfig = 
{
    chart: {
        //animation: false,
        animation : {
            duration : 500,
            easing: 'linear'
        },
        renderTo : "hltrates-chart",
        //height: 400,
        //zoomType: 'x',
        //events: {},
    },
    tooltip: {
        followPointer: true
    },
    legend: {
        align: 'center',
        verticalAlign: 'bottom'
    },
    title: {
        text: ''
    },
    xAxis: [
    {
        minPadding:0,
        maxPadding:0,
        tickInterval: 1,
        //lineWidth:0,
        gridLineWidth: 1,
        allowDecimals: false,
        title: {
            text: 'LS'
        }
    }
    ],
    yAxis: [
    {
        title: {text: 'Num Event / s'},
        lineWidth: 1,
        min: 0,
    }],
   plotOptions:{

        series: {
            groupPadding: 0,
            pointPadding: 0,
        }
    }
};
