(function ($) {
  var loc_id;

  	function DropDown(el) {
	    this.dd = el;
	    this.placeholder = this.dd.children('span');
	    this.opts = this.dd.find('ul.dropdown > li');
	    this.val = '';
	    this.index = -1;
	    this.initEvents();
  	}
  
  	DropDown.prototype = {
     	initEvents : function() {
      		var obj = this;
      		obj.dd.on('click', function(event){
        	$(this).toggleClass('active');
        	return false;
      	});
      	obj.opts.on('click',function(){
	        var opt = $(this);
	        obj.val = opt.text();
	        console.log(obj.val);
	        obj.index = opt.index();
	        obj.placeholder.text(obj.val);
	        loc_id = $(this).children('a')[0].id;//get the loc_id based on click event
	        console.log(loc_id);
	    $.getJSON( "http://ws.countingopinions.com/get_surveys.php?loc_id=" + loc_id + "&org_id=" + ls_id, function (data) {
    		console.log(data);//Dynamically get the survey version
    		for(i = data.length - 1; i >= 0;i--){
    			$('select#survey_id').append($("<option></option>")
                    .attr("value",data[i].id)
                    .text(data[i].name)); 
    		} 
    	}); 
    	$.getJSON( "http://ws.countingopinions.com/get_prompts.php?loc_id=" + loc_id, function (data) {
    		console.log(data.promptBeginSurvey);//Dynamically get the prompts string and display on the page	
    		$('a#get-survey').text(data.promptBeginSurvey);
    		$('select#branch_id').children('option').text(data.promptInternalTest);
    		$('div#sincerely').text(data.promptSincerely);
    		$('option#survey_version').text("- "+ data.promptSurveyVersion + " -")
    	}); 
        $.getJSON( "http://ws.countingopinions.com/get_org.php", { 
        	org_id:ls_id , loc_id:loc_id, libsat: ""}, function( data ) {
        		console.log(data);	
        		$('div#co_welcome').html(data.welcome);
			});	//Get welcome page content
    		console.log(loc_id);
	        $('#page1').animate({
	         top: '-50%'
	        }, 500, function() {
	          $('#page1').css('top', '150%');
	          $('#page1').appendTo('.container-middle');
	        });
	        $('#page1').next().animate({
	          top: '0%'
	        }, 1000);
      });
      
    },
    getValue : function() {
      return this.val;
    },
    getIndex : function() {
      return this.index;
    }
  }

// Comparison Helper for handlebars.js
// Pass in two values that you want and specify what the operator should be
// e.g. {{#compare val1 val2 operator="=="}}{{/compare}}

  Handlebars.registerHelper('compare', function(lvalue, rvalue, options) {
    if (arguments.length < 3 ) throw new Error("Handlerbars Helper 'compare' needs 2 parameters");
    operator = options.hash.operator || "==";
    var operators = {
      '==':       function(l,r) { return l == r; },
      '===':      function(l,r) { return l === r; },
      '!=':       function(l,r) { return l != r; },
      '<':        function(l,r) { return l < r; },
      '>':        function(l,r) { return l > r; },
      '<=':       function(l,r) { return l <= r; },
      '>=':       function(l,r) { return l >= r; },
      'typeof':   function(l,r) { return typeof l == r; }
    }

    if (!operators[operator])
      throw new Error("Handlerbars Helper 'compare' doesn't know the operator "+operator);
    var result = operators[operator](lvalue,rvalue);
    if( result ) {
      return options.fn(this);
    } else {
      return options.inverse(this);
    }
  });
    $(document).click(function() {
      // all dropdowns
      $('.wrapper-dropdown-3').removeClass('active');
      
    });
  var survey_id= 100;
  var respondent_id=-1;
  var ls_id =6005;
  var sp_id=0;
  var _response;
   
  $(document).ready(function(){
    var dd = new DropDown( $('#dd') );
    var decodeSelection  =  decodeURIComponent(encodeURIComponent("Français"));
    var frenchSelection = $('#dd').find( "li a" ).eq( 1 );
    frenchSelection .text(decodeSelection);//decode the unrecoginzed French characters.
    $('#get-survey').click(function() {
      loadQuestions();
      $('#page2').animate({
        top: '-80%'
      }, 500, function() {
        $('#page2').css('top', '150%');
        $('#page2').appendTo('.container-middle');
      });
      $('#page2').next().animate({
        top: '0%'
      }, 1000);
      
    function ShowHideQsts(element) {             
       var keepshow =false;   
       var Unchecked =false;
       var target ;     
	    $.each(_response.grouped_filters, function(i, o) {
	        //console.log(_response.grouped_filters);
	       	$.each(o.filters, function(j, p) {    
	           if (element.name ==p['related_parname'] && element.value== p['selected_value'] && element.checked== true ) {
	               if (p['show_option'] == "Y") {                                      
	                   $(o.target).show();                 
	               } else {
	                   $(o.target).hide();
	               }    
	            }
	            else if(element.name ==p['related_parname'] && element.value== p['selected_value'] && element.checked== false) { 
			             $.each(o.filters, function(j, p) {    
			               	if ( $('[name="' + p['related_parname'] + '"]' ).is(":checked")== true && p['show_option'] == "Y") {                                               
			                   keepshow=true;     
			              	}
			             });
		                 if (keepshow==false) { $(o.target).hide(); }
		            }          
	          }) ;
        });
    }
    function ajaxCall(element) {
      	//submit the data to php server after clicking on the radio button through AJAX in JSON format
      	// $(".scale-radio input").click( function(e){
       	var question =  element.name.split('_');
       	var qid= question[0].substr(1);
       	var part_no=question[1].substr(2);
       	var order = {
	        survey_id : $('#survey_id').val(),
	        respondent_id : respondent_id,//(first call, then should have a valid value return from first call)
	        question_id :qid , 
	        question_part_no : 1,//1 (or2,3)
	        qvalue : element.value,//(select or entry value)
	        org_id : 99999,
	        branch_id : $('#branch_id').val(), 
	        question_type :element.type
    	}; 
        //console.log(part_no);
        //console.log(respondent_id);
        $.ajax({
            type:"post",
            url:"ajax_thanks.php",
            data: (order),
            datatype:'JSON',            
            success: function (result) {
	            //console.log(result);
	            var resultparsed = JSON.parse(result);
	            respondent_id = resultparsed.respondent_id;
	            //console.log('RESP=' + respondent_id);
            }
        })
        return false;
          // });
    }
    function loadQuestions() {
        survey_id = $('#survey_id').val();
		console.log("loc_id" + loc_id);  
        $.getJSON( "http://ws.countingopinions.com/get_survey_questions.php", 
        { ls_id: ls_id, survey_id: survey_id, sp_id:sp_id, loc_id:loc_id },
        
         function(response) {
           //console.log('response:', JSON.stringify(response));
          response.sections = preprocessSurveyQuestions(response);
          response.grouped_filters = preprocessSurveyFilters(response);
          //console.log(response);
          _response=response;
         //console.log('response:', (response));
          // Fake a required question for the first question. TODO
          response.questions[0].required = true;
//          console.log($.parseJSON(response));
//          response = $.parseJSON(response);

          var source   = $("#survey-template").html();
          var template = Handlebars.compile(source);
          //console.log(template(response))
          var html    = template(response);//Put the result JSON to the handlebars
          
          $('#page3 #co_markup').html(html);

          //Init progressbar
          $( "#progressbar" ).progressbar({
            value: 0
          });


          // Behaviours on question answered.
          var $currentQuestion, $nextQuestion;
          var endReached = false;

          
          
          
          $('.question:first').addClass('current');
          initializeVisibility(response.grouped_filters);
          tooltipFollow();
          $('.question input').click(function(e){
          
            //filter(e);

            //console.log(this);
            
            $(this).closest('.questionpart').addClass('answered');
            
            ajaxCall(this);
            
            ShowHideQsts(this);


  
            if (!$(this).closest('.question').children('.questionpart:not(.answered)').length) {
            
              updateAnsweredQuestions($(this));
              updateCurrentQuestion($(this));
              updateSkippedQuestions($(this));
              //scrollToSection($(this));
              updateProgressBar();
              //initializeVisibility(response.grouped_filters);
              filter($(this),response.grouped_filters);
              
            }
            
          });
          
          $('.question textarea').focusout(function(e){
          
            var comment = $(this).val().trim();
            
            if (comment.length > 0) {
              //filter(e);
              //console.log(this);
              ajaxCall(this);
              updateAnsweredQuestions($(this));
              updateCurrentQuestion($(this));
              updateSkippedQuestions($(this));
              //scrollToSection($(this));
              updateProgressBar();
              //initializeVisibility(response.grouped_filters);
              filter($(this),response.grouped_filters);
            }


            
          });
          
          function tooltipFollow(){
			var tooltips = document.querySelectorAll('.tooltiptext');	
			window.onmousemove = function (e) {
			    var x = (e.clientX + 20) + 'px',
			        y = (e.clientY + 20) + 'px';
			    for (var i = 0; i < tooltips.length; i++) {
			        tooltips[i].style.top = y;
			        tooltips[i].style.left = x;
			    }
			};          
		}

          function initializeVisibility(filter_array){
            //set up the initial visibilty

            for (var i = 0; i < filter_array.length; i++) {
                 //console.log(filter_array[i].target);
                   for (var j = 0; j < filter_array[i].filters.length; j++){
                     //console.log(filter_array);
                     var triggle_option = filter_array[i].filters[j].related_parname;
                     var target_group = filter_array[i].filters[j].group_id;
                     var target_question = filter_array[i].filters[j].question_id;
                     var target_option = filter_array[i].filters[j].selected_value;
                     var target_vis = filter_array[i].filters[j].show_option;
                     var target_id = target_question + "1" + target_option;
                     var target_question_title_id = "div#q" + target_question + "_1";

                     if(target_vis == "Y"){
                          
                          var isVisible = $("#" + target_id).is(':visible');
                          //console.log(target_id + isVisible);
                          $("#" + target_id).parent().hide();//hide the option
                          //console.log(target_question_title_id);
                          $(target_question_title_id).hide();
                          //console.log(target_id + isVisible);
                     }

                   }
                }



          }

          function filter($input,filter_array){
            //console.log($input.attr('name'));
            //var parname = filter_array[0].filters[0].related_parname;
            /*var $content_current = $input.parent().parent().parent().parent().parent();
            var $question_current = $input.parent().parent().parent().parent();
            var section_id = "#" + $content_current.attr('id');
            var question_id = "#" + $question_current.attr('id');
            var combine = section_id + " " + question_id;*/
            //console.log(combine);
            //console.log($content_current);
            //filter_array[i].filters should have some reference to tell the people the triggle question(work on later)
            //console.log(filter_array);

            var current_checked = $input.attr('name');
            var current_value = $input.attr('value');
            var current_div_id = $input.parent().parent().parent().parent().parent().attr('id');
            var checkboxes = $('#' + current_div_id + ' input[type="checkbox"]');
            var checkCount = checkboxes.filter(":checked").length;
            var allCleared = checkCount == 0;

            
            for (var i = 0; i < filter_array.length; i++) {
                 //console.log(filter_array[i].target);
                 for (var j = 0; j < filter_array[i].filters.length; j++){
                   var triggle_option = filter_array[i].filters[j].related_parname;
                   var target_group = filter_array[i].filters[j].group_id;
                   var target_option = filter_array[i].filters[j].selected_value;
                   var target_vis = filter_array[i].filters[j].show_option;
                   var target_id = target_group + "1" + target_option;
                   var target_question = filter_array[i].filters[j].question_id;
                   var target_question_title_id = "div#q" + target_question + "_1";
                   if(triggle_option == current_checked){
                        if(target_vis == "Y"){
                            if($input.prop('checked')){
                                if(checkCount == 1){
                                  $("#" + target_id).parent().show();
                                  $("#" + target_id).prop("checked", true);
                                }
                                else{
                                  $(target_question_title_id).show();
                                  $("#" + target_id).parent().show();
                                  $("#" + target_id).parent().parent().find('input[type=radio]:checked').removeAttr('checked');
                                }
                            }
                            else{
                                $("#" + target_id).parent().hide();
                                $("#" + target_id).prop("checked", false);
                                if(checkCount == 1){
                                    var selected = [];
                                    $('#' + current_div_id + ' input:checked').each(function() {
                                        selected.push($(this).attr('name'));
                                    });//track which checkboxes are selected.
                                    for (var k = 0; j < filter_array[i].filters.length; k++){
                                         var triggle_option = filter_array[i].filters[k].related_parname;
                                         var target_group = filter_array[i].filters[k].group_id;
                                         var target_option = filter_array[i].filters[k].selected_value;
                                         var target_vis = filter_array[i].filters[k].show_option;
                                         var target_id = target_group + "1" + target_option; 
                                         if(selected[0] == triggle_option){
                                            $("#" + target_id).prop("checked", true);
                                         }                                
                                    }

                                }
                            }
                        } 
                        if(target_vis == 'N'){
                            if($input.prop('checked')){
                                $("#" + target_id).parent().hide();
                            }
                            else{
                                $(target_question_title_id).show();
                                $("#" + target_id).parent().show();
                            }
                        }
                        
                   }
                }
                 
              }

            
          }
          // Scroll to questions.
          function scrollToSection()
          {
          
            if (!endReached)
             {
              var $questions = $('.question');
              var currentIndex = $questions.index($('.current'));
              
              if (currentIndex > 1) 
              {
                var $target = $('.question').eq(currentIndex - 2);
                var targetTop = $target.position().top;
                
                $('.content').animate({
                  scrollTop: targetTop + $('.content').scrollTop()
                }, 500);
               
               }//scroll to the current question
             }
             
             /*
            else
            {
              // reached end, scroll to first skipped questions.
              var $skipped = $('.skipped:first');
            
              $('.content').animate({
                scrollTop: $skipped.position().top + $('.content').scrollTop()
              }, 500);
            
            }
            */
            
          }

          // Status indicator for answered questions.
          function updateAnsweredQuestions($input) 
          {
            $input.closest('.question').removeClass('skipped');
            $input.closest('.question').addClass('answered');
          }

          // Status indicator for skipped questions.
          function updateSkippedQuestions() 
          {
            if (!endReached)
            {
              $currentQuestion.parent().prevAll('.section').children('.question:not(.answered)').addClass('skipped');
            }
            else 
            {
              $('.question:not(.answered)').addClass('skipped');
            }
          }

          // Status indicator for current question.
          function updateCurrentQuestion($input)
          {

             

             $('.question').removeClass('current next');
            
             if ($input.closest('.question').hasClass('multi')) 
             {
              // Current question is current multi question.
              if (!endReached)
              {
                $currentQuestion = $input.closest('.question');
              }
              
              $currentQuestion.addClass('current');
              
              $nextQuestion = $('.question.current ~ .question:visible').first();
              
              if (!$nextQuestion.length)
              {
                $nextQuestion = $('.question.current').parent().nextAll(':visible').first().children('.question:visible').first();
              }
              
              if ($nextQuestion.length) 
              {
                $nextQuestion.addClass('next');
              }
              
              return;
             
             }
            // Current question is next unanswered question after the furthest
            // answered question.
            
            if (!endReached)
             {
              $currentQuestion = $('.question.answered:last + .question');
              
              if (!$currentQuestion.length)
               {
                $currentQuestion = $('.question.answered:last').parent().next().children('.question').first();
              
                if (!$currentQuestion.length)
                {
                  endReached = true;
                }
               }
             }
             
            if (endReached) 
            {
              $currentQuestion = $('.question:not(.answered):first');
            }
            
            $currentQuestion.addClass('current');
           
            if ($currentQuestion.hasClass('multi') || $currentQuestion.hasClass('comment'))
            {
              if (!endReached)
               {
                $nextQuestion = $('.question.current + .question');
                
                if (!$nextQuestion.length)
                {
                  $nextQuestion = $('.question.current').parent().next().children('.question').first();
                }
                
                if ($nextQuestion.length)
                {
                  $nextQuestion.addClass('next');
                }
                
               }
            }
            
          }// end of updateCurrentQuestion
          
          // Preprocess survey question.
          function preprocessSurveyQuestions(response) {
            var processedQuestions = {}, sectionArray = []; 
            na_upper = {"option_value":"Not Applicable","option_no":"11","option_name":"Not Applicable","option_prompt":"Not Applicable","subheader":""}; 
            na_lower =  {"option_value":"not applicable","option_no":"11","option_name":"not applicable","option_prompt":"not applicable","subheader":""};
            na_abbrev = {"option_value":"Not Applicable","option_no":"11","option_name":"N/A","option_prompt":"N/A","subheader":""}; 
            // Place into sections.
            for (var q in response.questions) {
              for(r = 0; r < response.questions[q].questionparts.length; r++){
                //console.log(r);
                if(response.questions[q].questionparts[r].options!= null){
                      if(response.questions[q].questionparts[r].options[0].option_name.charAt(0)  == "1"){
                        response.questions[q].questionparts[r].options.push(na_abbrev);
                      }
                      else if(response.questions[q].questionparts[r].options[0].option_name.charAt(0) == response.questions[q].questionparts[0].options[0].option_name.charAt(0).toUpperCase()){
                        response.questions[q].questionparts[r].options.push(na_upper);
                      }
                      else{
                        response.questions[q].questionparts[r].options.push(na_lower);
                      }   
                  }//Append N/A Option for each question.
              }

                //console.log(response.questions[q].questionparts[0].options);
              if (!processedQuestions.hasOwnProperty(response.questions[q].group_id)) {
                processedQuestions[response.questions[q].group_id] = [];
              }
              processedQuestions[response.questions[q].group_id].push(response.questions[q]);
            }
            for (var s in processedQuestions) {
              sectionArray.push({questions: processedQuestions[s], sectionID: s})
            }
            return sectionArray;
          }

          // Preprocess survey Filters.
          function preprocessSurveyFilters(response) 
          {
          
            var processedFilters = {}, sectionArray = [];
            // Place into sections.
            for (var q in response.filters) 
            {
              var target = "#section-" + response.filters[q].group_id + " #qd" + response.filters[q].question_id;
              
              if (!processedFilters.hasOwnProperty(target))
              {
                processedFilters[target] = [];
              }
              processedFilters[target].push(response.filters[q]);
            }
            
            for (var s in processedFilters)
            {
              sectionArray.push({filters: processedFilters[s], target: s})
            }
            
            return sectionArray;
          }

          function updateProgressBar()
          {
          
            var questions, remaining, progress;
          
            questions = $('.question').length;
            remaining = $('.question:not(.answered)').length;
            progress = (questions - remaining) / questions * 100;
          
            $( "#progressbar" ).progressbar({
              value: progress
            });
            
          }

          function updateSectionVisibility()
          {
            $.each($('.section'), function(i, o){
          
             if ($('.question:visible', o).length == 0)
              {
                $(o).hide();
              }
              else 
              {
                $(o).show();
              }
              // Set opacity on preamble.
              if ($('.question:visible.current, .question:visible.next', o).length)
              {
                $(o).addClass('section-active');
              }
              else
              {
                $(o).removeClass('section-active');
              }
               
            });
            
          }
        });
      }

    });
    /*
    $('#page3 .content').on('click', function()
     {
      $('.container-top').animate({ height: 'hide'}, 'slow');
      $('.container-middle').animate({ height: '100%'}, 'slow');
    });
    */
  });

})(jQuery);

function setRadioButtons(a,b) {}

