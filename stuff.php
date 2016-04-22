<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />
<title>Untitled 1</title>
</head>

<body>
<div id="co_welcome">
<? 	if (isset($welcome) && !empty($welcome)) { 
		if($branch_id > 0) {
			$welcome = str_replace('[BRANCH_ID]', $branch_id, $welcome);
		} else {
			$welcome = str_replace('[BRANCH_ID]', '-1', $welcome);
		}
		$welcome = str_replace('[LOC_ID]',  $loc_id, $welcome);
		$welcome = str_replace('[SURVEY_ID]', $survey_id, $welcome);
		$welcome = str_replace('[ORG_ID]', $org_id, $welcome);
		$welcome = str_replace('[SP_ID]', $branch_id, $welcome);
		$welcome = str_replace('[LS_ID]', $org_id, $welcome);
		echo stripcslashes($welcome);
	} 
?>
</div>
<div id="sincerely"><? echo $promptSincerely; ?></div>
<div id="co_signature">
	<div class="signature" title="<? echo trim($contact_name); ?>">
		<?
		if($contact_name != "") echo $contact_name;
		if($contact_title != "") {
			if(strpos($contact_title, '<br />') === false) {
				if($contact_name != "")  echo ', ';
			}	
			echo stripcslashes($contact_title);
		}
		?>
		<br /><? echo stripcslashes($org_name); ?>
	</div>
	<form name="theSurvey" action="<? echo htmlentities($_SERVER['PHP_SELF']); ?>">
	<div class="co_Selection"><fieldset class="survey_select"><legend><? echo $promptSelectLocationVersion; ?></legend>
	<?	
	//Select from list of locations
	if($from_source == "Test") {
		echo '<label for="branch_id">' . utf8_encode($promptSelectLocation) . '</label>';
		echo '<select class="location_select" size="1" name="branch_id" id="branch_id">';
		echo '<option value="' . $org_id . '-99999">' . $promptInternalTest . '</option>';
		echo '</select>';
	} else {
		if ($br_count <= 1) { // if only one location and not pre-selected
	    	if($from_branch_id > 0) {
				echo '<label for="branch_id">' . utf8_encode($promptSelectLocation) . '</label>';
				echo '<select class="location_select" size="1" name="branch_id" id="branch_id" style="display:none;">';
			} else {
				echo '<label for="branch_id">' . utf8_encode($promptSelectLocation) . '</label>';
				echo '<select class="location_select" size="1" name="branch_id" id="branch_id">';
			}	
			for($i=0;$i<count($branchArray);$i++) {
				//if($branchArray[$i]['branch_id'] == 0) continue;
		       	echo '<option value="' . $branchArray[$i]['org_id'] . '-' . $branchArray[$i]['branch_id'] . '" selected="selected">'  . htmlentities(utf8_decode($branchArray[$i]['branch_name'])) . '</option>';
			}  	
		} else { // // display label and select list options
			echo '<label for="branch_id">' . utf8_encode($promptSelectLocation) . '</label>'; 
			echo '<select class="location_select" size="1" name="branch_id" id="branch_id">';
			if($from_branch_id > 0) {
	          	echo '<option value="' . $from_org_id . '-' . $from_branch_id . '" selected="selected">' . htmlentities(utf8_decode($from_branch_name)) . '</option>';
			} else {
				if ($br_count > 1) {
				    echo '<option value="' . $org_id . '-' . '99999"> -- ' . $promptSelectLocation . ' -- </option>';
				}
				$last_br_count = -1;
				$indent = '';
				if($show_optgroup == 1) $indent = '&nbsp;&nbsp;';
				for($i=0;$i<count($branchArray);$i++) {
					if($from_org_id != $org_id && $branchArray[$i]['ls_id'] != $org_id) continue;
					if($branchArray[$i]['branch_id'] == 0) {
						if($show_optgroup == 1 && $last_br_count > 0) echo '</optgroup>';
						if($show_optgroup == 1 && $branchArray[$i]['br_count'] > 0) {
							$last_br_count = $branchArray[$i]['br_count'];
							$t_group_name = $branchArray[$i]['branch_name'];
							if($_SESSION['location_prompt_prefix'] == 1 && $branchArray[$i]['prefix'] != "") {
								if($_SESSION['prefix_display_mode'] == 1) {
									$t_group_name = $branchArray[$i]['prefix'] . ' - ' . $t_group_name;
								} else {
									$t_group_name = $t_group_name . ' - [' . $branchArray[$i]['prefix'] . ']';
								}	
							}
							if($_SESSION['location_prompt_suffix'] == 1 && $branchArray[$i]['suffix'] != "") {
								$t_group_name .= ' - [' . $branchArray[$i]['suffix'] . ']';
							}
							echo '<optgroup label="' . $t_group_name . '">';
						}	
						continue;
					}
	        		echo '<option value="' . $branchArray[$i]['org_id'] . '-' . $branchArray[$i]['branch_id'] . '">' . $indent . $branchArray[$i]['branch_name'] . '</option>';
	         	}
	         	if($portal == 1 && $show_optgroup == 1  && $last_bt_count > 0) echo '</optgroup>';
			}
		} 
		echo '</select>';
	}
	echo '<input type="hidden" name="forg_id" id="forg_id" value="' . $from_org_id . '" />';
	
    echo '<br /><label for="survey_id">' . $promptSurveyVersion . '</label>';
    if ($survey_count > 1) {
		echo '<select class="location_select" size="1" name="survey_id" id="survey_id">';
		echo '<option value="">  -- ' . $promptSurveyVersion . '  -- </option>';
		for($i=0;$i<count($surveyArray);$i++) {
			$selected = "";
			if($survey_id == $surveyArray[$i]['survey_id']) $selected = ' selected="selected"';
			echo '<option value="' . $surveyArray[$i]['survey_id'] . '"' . $selected . '>' . $surveyArray[$i]['survey_prompt'] . '</option>';
		}
	   	echo '</select>';
	} else {
		echo '<select class="location_select" size="1" name="survey_id" id="survey_id">';
		for($i=0;$i<count($surveyArray);$i++) {
			echo '<option value="' . $surveyArray[$i]['survey_id'] . '" selected="selected">' . $surveyArray[$i]['survey_prompt'] . '</option>';
		}
		echo '</select>';
	} 
	?>
	<!-- begin button -->
	<div class="wrapperbox">
	    <div class="centeredbox">
	    	<div id="btn">    	
	        <ul><li><a href="javascript:getNewWin('<? echo $loc_id; ?>');"><? echo $promptBeginSurvey; ?></a></li></ul>           
	        </div>
	    </div>
	</div>
    </fieldset>
	</div>
	</form>
</div></div>

</body>

</html>
