<?
ini_set('display_errors', 0);
session_start();
require_once('/home/copinion/public_html/includes/config.php');
require_once('includes/sql.php');
require_once(CO_INCLUDES . 'prompts.php');
require_once(LIBSAT_INCLUDES . 'library.php');
require_once(LIBSAT_INCLUDES . 'surveys.php');

if(isset($_REQUEST['loc_id'])) $prompt_loc_id = $_REQUEST['loc_id'];
else $prompt_loc_id = "en_CA";

$org_id = 6005;     
$region_id = 0;
$branch_id = 0;
$surveys = new Surveys($org_id);
$surveyArray = $surveys->get_surveys('SURVEY');

$library = new Library($org_id, $region_id, $branch_id, $loc_id);
$org = $library->get_org();
$regionArray = $library->get_regions();
$with_all_branch = true;
if($branch_id > 0) $with_all_branch = false;
$branchArray = $library->get_branches($view_region_id, $branch_id, $libsat_region_str, 'Public', $with_all_branch);
$langArray = $library->get_langs();
$arrlength = count($langArray);

//print($promptBeginSurvey);
//echo 'ORG<br>';
//print_r($org);
//echo '<br><br>Surveys<br>';
//print_r($surveyArray);
//echo '<br><br>Branches<br>';
//print_r($branchArray);
//echo '<br><br>Lannguages<br>';
//print_r($langArray);

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	<title>  Customer Satisfaction Survey</title>
  	<link href="css/styles.css" rel="stylesheet" type="text/css" />
	<link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
  	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/jquery-ui.min.js"></script>
  	<script src="js/handlebars-v1.3.0.js"></script>
  	<script src="js/script.js" type="text/javascript"></script>
  	<script id="survey-template" type="text/x-handlebars-template">
    {{#each sections}}
    <!--{{group_type}}-->
    <!--{{group_id}}-->
    <div class="section" id="section-{{sectionID}}">
    <div class="preamble">{{questions/0/preamble}}</div>
    {{#each questions}}
    {{#compare questionparts/0/question_type 'scale' operator="=="}}
    <!--{{group_id}}-->
    <!--{{group_type}}-->
    <!--scale-->
        <div class="radio question {{#if required}}required{{/if}}"  id="qd{{question_id}}">
        <fieldset class = "container">
            <div id="q{{question_id}}_1" class = "row">{{question_prompt}}</div>
            {{#each questionparts}}
            
            <div class="scale-wrapper questionpart">
                {{#if low_scale_text}}<div class="scale-text-wrapper row no-padding"><span class="low-scale-text col-lg-5 col-md-5 col-sm-5 col-xs-5 no-padding">{{{low_scale_text}}}</span><span class="high-scale-text col-lg-5 col-md-5 col-sm-5 col-xs-5 no-padding">{{{high_scale_text}}}</span></div>{{/if}}
                <div class="scale-radios row">
                    {{#each options}}
                <span class="scale-radio col-lg-1 col-md-1 col-sm-1 col-xs-1 no-padding">
                    <input class = "more-margin" type="radio" value="{{option_value}}" name="q{{../../question_id}}_p1_tB_g{{../gap_type}}" title="{{option_name}}" id = "{{../../question_id}}{{../question_part_no}}{{option_no}}" > <label class = "label-block more-margin" for={{../../question_id}}{{../question_part_no}}{{option_no}}>{{option_name}}
                      <span class="tooltiptext">{{option_value}}</span>
                      </label>
                </span>
                    {{/each}}
                </div>
            </div>
            
            {{/each}}
        </div>   
        </fieldset> 		
    {{/compare}}
    {{#compare questionparts/0/question_type 'option' operator="=="}}
    <!--{{group_id}}-->
    <!--{{group_type}}-->
    <!--option-->
        <div class="radio question {{#if required}}required{{/if}}"  id="qd{{question_id}}">
        	 <fieldset class = "container">
                <div id="q{{question_id}}_1" class = "row">{{question_prompt}}</div>
            {{#each questionparts}}
            <div class="scale-wrapper questionpart">
                {{#if low_scale_text}}<div class="scale-text-wrapper row no-padding"><span class="low-scale-text col-lg-5 col-md-5 col-sm-5 col-xs-5">{{{low_scale_text}}}</span><span class="high-scale-text col-lg-5 col-md-5 col-sm-5 col-xs-5">{{{high_scale_text}}}</span></div>{{/if}}
                <div class="scale-radios row">
                    {{#each options}}
                <div class="scale-radio col-lg-2 col-md-2 col-sm-4 col-xs-6 no-padding">
                    <input type="radio" value="{{option_value}}" name="q{{../../question_id}}_p1_tB" title="{{option_name}}" id = "{{../../question_id}}{{../question_part_no}}{{option_no}}"> <label for={{../../question_id}}{{../question_part_no}}{{option_no}}><span>{{option_name}}</span></label>
                </div>
                    {{/each}}
                </div>
            </div>        
            {{/each}}
            </fieldset>
        </div>
    {{/compare}}
    {{#compare questionparts/0/question_type 'multi' operator="=="}}
    <!--{{group_id}}-->
    <!--{{group_type}}-->
    <!--select-->
    <div class="multi question {{#if required}}required{{/if}}" id="qd{{question_id}}">
        <legend>{{question_prompt}}</legend>
        {{#each questionparts}}
        <filedset class = "container">
        <div class="multi-wrapper questionpart  row">
            {{#each options}}
            <div class="multi-choice col-lg-12 col-md-12 col-sm-12 col-xs-12 no-padding">
                <label class = "smallFont" for={{../../question_id}}{{../question_part_no}}{{option_no}}><input id="{{../../question_id}}{{../question_part_no}}{{option_no}}" type="checkbox" name="q{{../../question_id}}_p{{../question_part_no}}_t{{../gap_type}}_c0{{option_no}}" value="{{option_value}}"><span class = "leftMargin">{{option_prompt}}</span></label>
            </div>
            {{/each}}
        </div>
        </fieldset>
        {{/each}}
    </div>
    {{/compare}}
    {{#compare questionparts/0/question_type 'comment' operator="=="}}
    <!--{{group_id}}-->
    <!--{{group_type}}-->
    <!--comment-->
    <div class="comment question {{#if required}}required{{/if}}" id="qd{{question_id}}">
        <div class="q_block">{{question_prompt}}</div>
        <textarea rows="4" name="q{{question_id}}_p1_t{{gap_type}}" cols="86"></textarea>
    </div>
    {{/compare}}
    {{/each}}
    </div>
    {{/each}}
  	</script>
	<script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
</head>

<body>
<noscript style="margin:0;padding:0;">Javascript must be enabled in your browser to complete this survey</noscript>

<div class="container-top">
  <div class="header">
    <img src="css/image/logo.gif" class="img-responsive" title="" alt="Markham Pulic Library" />
  </div>
</div>

<div class="container-middle">
  <div id="page1" class="page">
    <div class="hero">
      <input type="hidden" name="durl" id="durl" value="en_CA" />
      <input type="hidden" name="branch_id" id="branch_id" value="99999" />
      <div class="language-select">
        <div id="dd" class="wrapper-dropdown-3" tabindex="1">
          <span>Select Language</span>
          <ul class="dropdown">
          	<?php
          	for($x = 0; $x < $arrlength; $x++) {
			echo '<li><a href="#" id ='.$langArray[$x][loc_id].'><i class="icon-envelope icon-large"></i>'
				.$langArray[$x][loc_name].		
			'</a></li>';
			}
			?>
            </ul>
        </div>
      </div>
    </div>
  </div>
  
  <div id="page2" class="page">
    <div class="content">
      <div id="co_welcome"></div>
      <div id="sincerely" style="padding-left:20px;"></div>
      <div id="co_signature" style="padding:0 20px 20px 20px;">
        <div class="signature" title=""></div>
        <form name="theSurvey" action="/index.php">
          <div class="co_Selection">
          <fieldset class="survey_select">
          <legend>Location and Version</legend>
            <select class="location_select" size="1" name="branch_id" id="branch_id">
            <option value="6005-99999"></option>
            </select>
            <input type="hidden" name="forg_id" id="forg_id" value="6005"/>
            <br />
            <label for="survey_id" style="display:none;">select a survey version</label>
            <select class="location_select" size="1" name="survey_id" id="survey_id">
            <option value="" id = "survey_version"></option>
            </select>	<!-- begin button -->
          
            <div class="wrapperbox">
              <div class="centeredbox">
                <div id="btn">
                  <ul>
                  <li><a id="get-survey" class="btn btn-primary" href="#"></a></li>
                  </ul>
                </div>
              </div>
            </div>
          </fieldset>
          </div>
        </form>
      </div>
    </div>
  </div>
   <div id="page3" class="page">
    <div class="content" id="co_markup"></div>
    <div id="progressbar"></div>
    <div id="survey-controls">
        <button type="button" id="survey-submit">Submit</button>
        <button type="button" id="survey-later">Finish later</button>
    </div>
   </div>
  
</div>
</body>
</html>