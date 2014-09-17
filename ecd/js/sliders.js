var sliderTickStep = 10;

var ls_slider = {
  scales: [
  // Primary scale
  {
    first: function(val){ return val; },
    next: function(val){ return val + sliderTickStep; },
    stop: function(val){ return false; },
    label: function(val){ return val; },
    //format: function(tickContainer, tickStart, tickEnd){ 
    //  tickContainer.addClass("myCustomClass");
    //}
  },
  // Secondary scale
  //{
  //  first: function(val){ return val; },
  //  next: function(val){
  //    if (val % 10 === 9){
  //      return val + 2;
  //    }
  //    return val + 1;
  //  },
  //  stop: function(val){ return false; },
  //  label: function(){ return null; }
  //}
  ],
  wheelMode: "scroll",
  range: {min: 20},
  defaultValues: { min: 1, max: 21 },
  bounds: {min:1, max:21},
  step: 1,
  //arrows:false,
  //valueLabels:"change",
  //durationIn: 1000,
  //durationOut: 1000

}