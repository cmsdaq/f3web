var NASTRING = "N/A";
var WAITINGCHART = "No monitoring information.";
var NODATACHART = "No monitoring information.";
var DEFAULTINDEX = "cdaq";


Colors = {};
Colors.names = {
    darkblue: "#00008b",
    
    black: "#000000",
    lightgreen: "#90ee90",
    


    blue: "#0000ff",
    lightblue: "#add8e6",
    green: "#008000",

    beige: "#f5f5dc",

    
    brown: "#a52a2a",
    darkcyan: "#008b8b",
    darkred: "#8b0000",
    
    
    
    darkgrey: "#a9a9a9",
    darkgreen: "#006400",
    darkkhaki: "#bdb76b",

    darkolivegreen: "#556b2f",
    darkorange: "#ff8c00",
    darkorchid: "#9932cc",
        red: "#ff0000",
    darkmagenta: "#8b008b",
    darksalmon: "#e9967a",
    darkviolet: "#9400d3",
    fuchsia: "#ff00ff",
    gold: "#ffd700",
    
    indigo: "#4b0082",
    khaki: "#f0e68c",
    
    lightcyan: "#e0ffff",
    
    lightgrey: "#d3d3d3",
    lightpink: "#ffb6c1",
    lightyellow: "#ffffe0",
    lime: "#00ff00",
    magenta: "#ff00ff",
    maroon: "#800000",
    navy: "#000080",
    olive: "#808000",
    orange: "#ffa500",
    pink: "#ffc0cb",
    purple: "#800080",
    violet: "#800080",

    silver: "#c0c0c0",
    aqua: "#00ffff",
    azure: "#f0ffff",
    white: "#ffffff",
    cyan: "#00ffff",
    yellow: "#ffff00"
};
Colors.random = function() {
    var result;
    var count = 0;
    for (var prop in this.names)
        if (Math.random() < 1/++count)
           result = prop;
    return { name: result, rgb: this.names[result]};
};

Colors.colorList = function(){
    var colors = [];
    for (var color in this.names){
        color = chroma(color).darken(10).hex();
        colors.push(color)
    }
    return colors;
}

//function zeroFill(data,categories){
//    var out = new Array();
//    categories.forEach(function(key){
//        index = getIndexOfK(data,key);
//        value = (index!=-1) ? data[index] : [key,0];
//        out.push(value);
//    });
//    return out;
//}

// return first recurrency of k in the arr
//function getIndexOfK(arr, k){
//    for(var i=0; i<arr.length; i++){
//        if (arr[i][0] == k){
//            return i;
//        }
//    }
//    return -1;
//}

//function calcPercent(dataIn,totals,docCounts){
//    var out = new Array();
//    //console.log(totals.toString());
//    //console.log(docCounts.toString());
//    for (i = 0; i < dataIn.length; i += 1) {
//        key = dataIn[i][0];
//
//        if (docCounts[i][1] == 0 ) {value = [key,0]; } 
//        else {value = [key,100];}
//        valueIn = dataIn[i][1];
//        valueTot = totals[i][1];
//        if (valueTot != 0 ){
//            value = [key,Math.round((valueIn/valueTot)*100.)];
//        }
//    out.push(value);
//    }
//    return out;
//}

//panelPrototype = 
//'   <div class="swiper-slide swiper-no-swiping"> 
//    <div class="panel panel-primary">
//    <div class="panel-heading clearfix">
//    <span class="panel-title"><i class="fa fa-bar-chart-o"></i> 
//    </span>
//    </div>
//    <div class="panel-body">
//    </div>
//    </div> 
//    </div> 
//'