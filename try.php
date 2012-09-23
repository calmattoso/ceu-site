<!DOCTYPE html>
<?     
  // Object that holds all information (name, code, input, lesson html) for each lesson
  $lessons = array(
    "ex_intro" => array(
      "name" => 'Welcome to C&eacute;u!'
    ),
    "ex_hello" => array(
      "name" => "Hello World!"
    ), 
    "ex_events" => array(
      "name" => "Input Events"
    ),
    "ex_parand" => array(
      "name" => "Parallel Compositions: par/and"
    ),
    "ex_paror" => array(
      "name" => "Parallel Compositions: par/or"
    ),
    "ex_term" => array(
      "name" => "Parallel Compositions: mixing"
    ),
    "ex_par" => array(
      "name" => "Parallel Compositions: par"
    ), 
    "ex_AB" => array(
      "name" => "Execution Model: Synchronous Execution"
    ),
    "ex_tight" => array(
      "name" => "Execution Model: Bounded Execution"
    ),
    "ex_det01" => array(
      "name" => "Execution Model: Deterministic Execution 1"
    ),
    "ex_det02" => array(
      "name" => "Execution Model: Deterministic Execution 2"
    ),
    "ex_det03" => array(
      "name" => "Execution Model: Deterministic Execution 3"
    ),
    "ex_det04" => array(
      "name" => "Execution Model: Deterministic Execution 4"
    ), 
    "ex_atomic" => array(
      "name" => "Execution Model: Atomic Execution"
    ),
    "ex_glitch" => array(
      "name" => "Execution Model: Glitch-free Execution"
    ),
    "ex_int_hello" => array(
      "name" => "Internal Events"
    ),
    "ex_int_vars" => array(
      "name" => "Internal Events: Reactive Variables"
    ),
    "ex_int_stack" => array(
      "name" => "Internal Events: Stacked Execution"
    ), 
    "ex_async10"  => array(
      "name" => "Asynchronous Execution 1"
    ),
    "ex_async0" => array(
      "name" => "Asynchronous Execution 2"
    ), 
    "ex_simul" => array(
      "name" => "Simulation"
    ),
    "ex_cblock" => array(
      "name" => "C Definitions"
    ),
    "ex_m4" => array(
      "name" => "Abstractions with m4"
    ),
    // Used to avoid a weird bug when generating the modal
    // Without this, the last lesson would be printed with 
    // the name of the previous one.
    "bug" => array() 
  );
  
  function ceu2js ($ex)
  {
    $ex = 'examples/' . $ex;
    $str = file_get_contents($ex);
    //$str = addslashes($str);
    //$str = preg_replace('/\n/', "\\n' +\n\t'", $str);
    
    return $str;
  }
  
  function html2js ($ex)
  {
    $ex = 'examples/' . $ex;
    $str = file_get_contents($ex);
    //$str = strip_tags($str);
    
    return $str;
  }
  
  // Fill the $lessons object with the missing data
  foreach($lessons as $key => &$data){
    if($key == "bug") continue; // ignore the bug element
    $data["code"] = ceu2js($key . ".ceu");
    $data["input"] = ceu2js($key . "_in.ceu");
    $data["text"] = html2js($key . ".html");
  }
?>


<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Try & Learn C&eacute;u</title>
    
    <!-- CSS files -->
    <link href="css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap -->
    <link href="css/try.css" rel="stylesheet">  <!-- Online editor css -->
    
    <!-- JS files -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.8.0/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script>
      var Lessons = <? echo json_encode($lessons); ?>;  
      delete Lessons.bug;
    </script>
    <script src="js/try.js"></script>
    <script>
      $(document).ready(function() {
        var $code_textarea, $input_textarea;

        // Selection of a Tutorial Lesson in the modal 
        $("#ceu-index-modal a").click(function(){
          $li = $(this).parent();
        
          load_lesson($li.index() + 1);
          $("#ceu-index-modal").modal("hide");
        });
        
        // Clicking on the INCREASE or DECREASE font size buttons
         $("#ceu-font-controls .btn").click(function(){
          if($(this).attr("id") === "ceu-font-increase"){
            change_font_size(2);
          }
          else {
            change_font_size(-2);
          }    
        });
        
        
        // Clicking on NEXT or PREVIOUS buttons
        $("#ceu-slide .pull-right .btn").click(function(){
          if($(this).attr("id") === "ceu-next"){
            Slides.next();
          }
          else {
            Slides.previous();
          }    
        });
         
        // Clicking on the RESET button (Resets the text, code, input and results)
        $("#ceu-reset").click(function(){
          load_lesson(Slides.cur_slide);
        });
        
        // Clicking on the RUN button
        $code_textarea = $("#ceu-code-container textarea");
        $input_textarea = $("#ceu-input-text textarea");

        $("#ceu-run").click(function(){
          var $results_panel = $("#ceu-results-text").empty();

          // tell the user we're waiting for the remote server
          $results_panel.html("<p class='lead'>Compiling and running code...</p>");

          // Construct the object that will be sent with the code to be compiled/executed
          var request = {
            samples: Slides.slides[Slides.cur_slide - 1],
            go: 'Run!',
            mode: 'run',
          };
          
          if($("#ceu-code input").prop("checked")){
            request.ana = 'on';
          }
          
          // Get the CODE and INPUT entered by the user (the user might have modified both)
          request.code = $code_textarea.val();
          request.input = $input_textarea.val();
          
          // Show the OUTPUT and DEBUG in the RESULTS panel
          $.post('run.php', request, function(response){
            // Clear the results panel
            $results_panel.empty();                        
 
            // Output section
            $("<div>")
              .html("<p><b>Output</b></p><pre>" + response.output + "</pre>")
              .appendTo($results_panel);

            // Debug section
            $("<div>")
              .html("<p><b>Debug</b></p><pre>" + response.debug + "</pre>")
              .appendTo($results_panel);
          }, "json");
        });
        
        // For some reason the code for the intro lesson is not being loaded
        Lessons.ex_intro['code'] = '/* Have fun with Ceu ! */\n return 0;';
        
        // Selection of the first slide to be loaded:
        // -- User specified one: ?sample=<value>
        // -- Default one: ex_intro
        var first_slide_id = <?php
          if(isset($_REQUEST['sample'], $lessons[$_REQUEST['sample']])){
            echo "'" . $_REQUEST['sample'] . "'";
            }
          else {
            echo "'ex_intro'";
          }        
        ?>;
        
        // The ugly code in parentheses converts the lesson id to its numeric id
        // which is necessary to load the lesson(look up load_lesson in try.js)
        first_slide_index = ($.inArray(first_slide_id, Slides.slides) + 1);
        
        load_lesson(first_slide_index);
      });
    </script>
  </head>
  <body>
    <div id="ceu-body">
      <div>
        <!-- Left columns -->
        <div id="ceu-tutorial">
          <div id="ceu-slide">
            <div class="relative">
              <div class="ceu-textbar">
                <a href="index.html"><img src="img/ceu.png"></a>
                <button type="button" class="btn btn-small" data-toggle="modal" data-target="#ceu-index-modal" title="View Table of Contents">
                  <i class="icon-list"></i> Index
                </button>
                <span id="ceu-font-controls">
                  <button id="ceu-font-decrease" class="btn btn-mini" alt="Decrease font size." title="Decrease font size"><i class="icon-minus-sign"></i></button>
                  Font Size 
                  <button id="ceu-font-increase" class="btn btn-mini" alt="Increase font size." title="Increase font size"><i class="icon-plus-sign"></i></button>
                </span>
                <span class="pull-right">
                  <button id="ceu-left" type="button" class="btn btn-small" title="Previous lesson">Previous</button>
                  <span id="ceu-slide-number">1</span>
                  <button id="ceu-next" type="button" class="btn btn-small" title="Next lesson">Next</button>
                </span>
              </div>
              <div id="ceu-slide-text">
                <h3>Title</h3>
                <div>TEXT</div>
              </div>
            </div>
          </div>
          
          <div id="ceu-results">
            <div class="relative">
              <div class="ceu-textbar">Results</div>
              <div id="ceu-results-text">
                
              </div>
            </div>
          </div>
        </div>
        
        <!-- Right columns -->
        <div id="ceu-ide">
          <div id="ceu-code">
            <div class="relative">
              <div class="ceu-textbar">
                Code
                <div class="pull-right">
                  <input id="ceu-static" style="margin-top: -2px;font-size:14px" type="checkbox" name="debug" value="static_analysis" /> 
                    <label style="display:inline;" for="ceu-static">static analysis</label>
                  </input>&nbsp;&nbsp;
                  <button id="ceu-reset" type="button" class="btn btn-small" title="Reset lesson">Reset</button>
                  <button id="ceu-run"   type="button" class="btn btn-small btn-primary" title="Compile and execute code">Run <i class="icon-play-circle icon-white"></i></button>
                </div>
              </div>
              <div id="ceu-code-container">
                <textarea></textarea>
              </div>
            </div>
          </div>
          <div id="ceu-input">
            <div class="relative">
              <div class="ceu-textbar">Input</div>
              <div id="ceu-input-text">
                <textarea></textarea>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Modals -->
    <div id="ceu-index-modal" class="modal hide fade" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h3>Tutorial Lessons</h3>
      </div>
      <div class="modal-body">
        <ol>
          <?
            foreach($lessons as $key => $data){
              if($key == "bug") continue;
              echo "<li data-lesson-id=\"$key\"><a href=\"#\">" . $lessons[$key]['name'] . "</a></li>\n";
            }
          ?>
        </ol>
      </div>
      <div class="modal-footer"></div>
    </div> 
  </body>
</html>
