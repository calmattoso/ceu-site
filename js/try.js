// Helper function used to obtain the number of keys in an object
Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
};

// Updates the explanation text to be that of lesson_id
function load_text(lesson_id){
  var $ceu_slide = $("#ceu-slide-text");
  
  // Update the title
  $ceu_slide
    .children("h3")
    .html(Lessons[lesson_id].name);
  
  // Update the explanation
  $ceu_slide
    .children("div")
    .html(Lessons[lesson_id].text);
}
// Sets the lesson code to that of the provided lesson_id
function load_code(lesson_id){
  $("#ceu-code-container textarea").val(Lessons[lesson_id].code);
}

// Sets the lesson input to that of the provided lesson_id
function load_input(lesson_id){
  $("#ceu-input-text textarea").val(Lessons[lesson_id].input);
}

// Updates the lesson number displayed in the Lesson panel textbar
// to be equal to lesson_num. Also updates the current slide reference,
// in the Slides object, to lesson_num
function update_number(lesson_num){
  $("#ceu-slide-number").text(lesson_num);
  Slides.cur_slide = lesson_num;
}

// Object used to load a new lesson; it holds also holds the [lesson_id -> numeric_id] array
var Slides = {
  cur_slide: 1,
  slides: [
    "ex_intro", "ex_hello", "ex_events", "ex_parand", "ex_paror", "ex_term", "ex_par", "ex_AB", "ex_tight", 
    "ex_det01", "ex_det02", "ex_det03", "ex_det04", "ex_atomic", "ex_glitch", "ex_int_hello", 
    "ex_int_vars", "ex_int_stack", "ex_async10", "ex_async0", "ex_simul", "ex_cblock", "ex_m4"
  ],
  next: function(){
    this.cur_slide++;
    
    if(this.cur_slide > this.slides.length){
      this.cur_slide = 1;
    }
    
    load_lesson(this.cur_slide);
  },
  previous: function(){
    this.cur_slide--;
    
    if(this.cur_slide == 0){
      this.cur_slide = this.slides.length;
    }
    
    load_lesson(this.cur_slide);
  }
};

// Loads the lesson of numeric_id equal to index
function load_lesson(index){
  lesson_id = Slides.slides[index - 1];

  update_number(index);
  load_text(lesson_id);
  load_code(lesson_id);
  load_input(lesson_id);    
}    