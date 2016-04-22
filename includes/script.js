
(function ($) {


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
        obj.index = opt.index();
        obj.placeholder.text(obj.val);
        
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
     
    function ShowHideQsts(element)
    {             
       var keepshow =false;   
       var Unchecked =false;
       var target ;     
       
        $.each(_response.grouped_filters, function(i, o)
        {
        //console.log(_response.grouped_filters);
          $.each(o.filters, function(j, p)
          {    
           
           if (element.name ==p['related_parname'] && element.value== p['selected_value'] && element.checked== true )
            {

                   if (p['show_option'] == "Y")   
                    {                                      
                       $(o.target).show();                 
                    }
                   else 
                    {
                       $(o.target).hide();
                    }    
                     
            }
            else if(element.name ==p['related_parname'] && element.value== p['selected_value'] && element.checked== false)
            { 
            
             $.each(o.filters, function(j, p)
              {    
                   if ( $('[name="' + p['related_parname'] + '"]' ).is(":checked")== true && p['show_option'] == "Y")
                  {                                               
                       keepshow=true;     
                  }
                               
              });
                 if (keepshow==false) { $(o.target).hide(); }
            }          
                
            }) ;
         
        });
              
    }
 
    function ajaxCall(element)
    {
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
             
            $.ajax
            ({
        
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

        $.getJSON( "http://ws.countingopinions.com/get_survey_questions.php", 
        { ls_id: ls_id, survey_id: survey_id, sp_id:sp_id },
        
         function(response) {
         
           //console.log('response:', JSON.stringify(response));

          response.sections = preprocessSurveyQuestions(response);
          
          response.grouped_filters = preprocessSurveyFilters(response);
          
          _response=response;
          
         //console.log('response:', (response));
          
          // Fake a required question for the first question. TODO
          response.questions[0].required = true;
//          console.log($.parseJSON(response));
//          response = $.parseJSON(response);

          var source   = $("#survey-template").html();
          var template = Handlebars.compile(source);
          //console.log(template(response))
          var html    = template(response);
          
          $('#page3 #co_markup').html(html);

          //Init progressbar
          $( "#progressbar" ).progressbar({
            value: 0
          });


          // Behaviours on question answered.
          var $currentQuestion, $nextQuestion;
          var endReached = false;
          
          filter('');
          
          updateSectionVisibility();
          
          $('.question:first').addClass('current');
          
          $('.question input').click(function(e){
          
            filter(e);
            
            $(this).closest('.questionpart').addClass('answered');
            
            ajaxCall(this);
            
            ShowHideQsts(this);

  
            if (!$(this).closest('.question').children('.questionpart:not(.answered)').length) {
            
              updateAnsweredQuestions($(this));
              updateCurrentQuestion($(this));
              updateSkippedQuestions($(this));
              scrollToSection($(this));
              updateProgressBar();
              updateSectionVisibility();
              
            }
            
          });
          
          $('.question textarea').focusout(function(e){
          
            var comment = $(this).val().trim();
            
            if (comment.length > 0) {
              //filter(e);
              //console.log(this.type);
              ajaxCall(this);
              updateAnsweredQuestions($(this));
              updateCurrentQuestion($(this));
              updateSkippedQuestions($(this));
              scrollToSection($(this));
              updateProgressBar();
              updateSectionVisibility();
            }
            
          });
          
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
            na_abbrev = {"option_value":"N/A","option_no":"11","option_name":"N/A","option_prompt":"N/A","subheader":""}; 
            // Place into sections.
            for (var q in response.questions) {
            	if(response.questions[q].questionparts[0].options!= null){
            			if(response.questions[q].questionparts[0].options[0].option_name.charAt(0)  == "1"){
            				response.questions[q].questionparts[0].options.push(na_abbrev);
            			}
            			else if(response.questions[q].questionparts[0].options[0].option_name.charAt(0) == response.questions[q].questionparts[0].options[0].option_name.charAt(0).toUpperCase()){
            				response.questions[q].questionparts[0].options.push(na_upper);
            			}
            			else{
            				response.questions[q].questionparts[0].options.push(na_lower);
            			}
                		
                	}//Append N/A Option for each question.
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

          

        });



      }

    });



    $('#page3 .content').on('click', function()
     {
      $('.container-top').animate({ height: 'hide'}, 'slow');
      $('.container-middle').animate({ height: '100%'}, 'slow');
    });
    
  });





})(jQuery);

function setRadioButtons(a,b) {}

