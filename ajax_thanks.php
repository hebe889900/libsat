<?

$DEBUG = 0;
session_start();
require_once('includes/sql.php');

function removeSpecialCharacter($str)
{
	$value = str_replace(",", "", $str);
	$value = str_replace("%", "", $value);
	$value = str_replace("$", "", $value);
	$value = preg_replace('/[^(\x20-\x7F)]*/','', $value); //remove all non ASCII characters.
	$value = trim($value);
	return $value;
}	

function getip()
{
   if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
   $ip = getenv("HTTP_CLIENT_IP");
   else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
   $ip = getenv("HTTP_X_FORWARDED_FOR");
   else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
   $ip = getenv("REMOTE_ADDR");
   else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
   $ip = $_SERVER['REMOTE_ADDR'];
   else $ip = "unknown";
   return($ip);
}

$org_id = $_REQUEST['org_id'];

$portal_id = $org_id;
if(isset($_REQUEST['portal_id']) && $_REQUEST['portal_id'] > 0) $portal_id = $_REQUEST['portal_id'];

$branch_id = 0;
if(isset($_REQUEST['branch_id']) && $_REQUEST['branch_id'] > 0) $branch_id = $_REQUEST['branch_id'];

$loc_id = 'en_CA';
if(isset($_REQUEST['loc_id']) && $_REQUEST['loc_id'] != '') $loc_id = $_REQUEST['loc_id'];

$survey_id = $_REQUEST['survey_id'];

$respondent_id = -1;
if(isset($_REQUEST['respondent_id']) && $_REQUEST['respondent_id'] > 0) $respondent_id = $_REQUEST['respondent_id'];

$question_id = -1;
if(isset($_REQUEST['question_id']) && $_REQUEST['question_id'] > 0) $question_id = $_REQUEST['question_id'];

$question_type = '';
if(isset($_REQUEST['question_type']) && $_REQUEST['question_type'] != '') $question_type = $_REQUEST['question_type'];

$question_part_no = 1;
if(isset($_REQUEST['question_part_no']) && $_REQUEST['question_part_no'] > 0) $question_part_no = $_REQUEST['question_part_no'];

$vname = '';
if(isset($_REQUEST['vname']) && $_REQUEST['vname'] != '') $vname = $_REQUEST['vname'];

$value = '';
if(isset($_REQUEST['qvalue']) && $_REQUEST['qvalue'] != '') $qvalue = $_REQUEST['qvalue'];

$today = date("Y-m-d");
$current_time = date('Y-m-d'); 
$duration = 0;
$org_id=99999; 

if($question_type == 'textarea') $question_type = 'comment';
if($question_type == 'multiple' || $question_type == 'checkbox') $question_type = 'option';
if($question_type == 'radio' || $question_type == 'select') {
	$SQL = "SELECT question_type FROM questionparts WHERE question_id LIMIT 1";
	$rs = mysql_query($SQL) or die ($SQL . "<br />" . mysql_error());
	if($fields = mysql_fetch_array($rs)) {
		$question_type = $fields['question_type'];
		if($question_type != 'scale') $question_type = 'option';
	}
}

if($respondent_id < 0)
{	//NEW respondent_id
	$SQL = "INSERT INTO respondents (respondent_name,postal_code,email,loc_id,org_id,branch_id,referal,source) VALUES ";
	$SQL .= " ('$respondent_name','$postal_code','$email','$loc_id',$org_id,$branch_id,'$referal','online')";
	mysql_query($SQL) or die ($SQL . "<br />" . mysql_error());

	$respondent_id = mysql_insert_id();
	
	$SQL = "INSERT INTO respondentsurveys (respondent_id,survey_id,loc_id,survey_date,duration,survey_timestamp,ip, org_id, branch_id) VALUES ";
	$SQL .= " ($respondent_id,$survey_id,'$loc_id','$current_time','$duration','" . date('Y-m-d G:i:s') . "','" . getip() . "', $org_id, $branch_id)";
	mysql_query($SQL) or die ($SQL . "<br />" . mysql_error());
	
}
 else 
{	
	$SQL = "UPDATE respondentsurveys SET pkey=MD5(CONCAT(respondent_id,survey_id)) WHERE respondent_id=$respondent_id AND survey_id=$survey_id LIMIT 1";
	mysql_query($SQL) or die ($SQL . "<br />" . mysql_error());
	
}

//Handle OTHER text option choices
if(substr_count($par_name, "_OTHER") > 0) 
{
	$SQL = "INSERT IGNORE INTO responseothers (respondent_id, org_id, survey_id, question_id, question_part_no, option_no, others) VALUES ";
	$SQL .= " ($respondent_id, $org_id, $survey_id,  $question_id, $question_part_no, 1, '$qvalue')";
	mysql_query($SQL) or die ($SQL_temp . mysql_error());
	
} 
else 
{		
	if($question_type == "comment") 
	{ //comments
		$SQL = "SELECT respondent_id FROM comments WHERE respondent_id=$respondent_id AND question_id=$question_id AND question_part_no=$question_part_no LIMIT 1";
		$rs = mysql_query($SQL) or die ($SQL . mysql_error());
		
	     if(mysql_num_rows($rs) > 0) 
		{
			$SQL = "UPDATE comments SET comment='" . mysql_escape_string($qvalue) . "' WHERE respondent_id=$respondent_id AND survey_id=$survey_id AND question_id=$question_id AND question_part_no=$question_part_no LIMIT 1";
			mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			
		}
		
		else
	    { 
			$SQL = "INSERT IGNORE INTO comments (respondent_id,survey_id,question_id,question_part_no,comment,org_id,branch_id,survey_date) VALUES ";
			$SQL .= " ($respondent_id,$survey_id,$question_id,$question_part_no,'" . mysql_escape_string($qvalue) . "',$org_id,$branch_id,'$current_time')";
			mysql_query($SQL) or die ($SQL . mysql_error());
		}
	} 
	else if($question_type == "text" || $question_type == "hidden") 
	{	//text field / hidden field
		$SQL = "SELECT respondent_id FROM responsevalues WHERE respondent_id=$respondent_id AND question_id=$question_id AND question_part_no=$question_part_no LIMIT 1";
		$rs = mysql_query($SQL) or die ($SQL . mysql_error());
		
		if(mysql_num_rows($rs) > 0) 
		{
			$SQL = "UPDATE responsevalues SET response_value='" . mysql_escape_string($qvalue) . "' WHERE respondent_id=$respondent_id AND survey_id=$survey_id AND question_id=$question_id AND question_part_no=$question_part_no LIMIT 1";
			mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			
		} 
		else 
		{
			$SQL = "INSERT IGNORE INTO responsevalues (respondent_id,survey_id,question_id,question_part_no,response_value,org_id,branch_id) VALUES ";
			$SQL .= " ($respondent_id,$survey_id,$question_id,$question_part_no,'" . mysql_escape_string($qvalue) . "',$org_id,$branch_id)";
			mysql_query($SQL) or die ($SQL . '<br>' . mysql_error());
		}
		
	} 
	else if ($question_type == "option" || $question_type == "multi") 
	{
		$SQL = "SELECT respondent_id FROM multiresponses WHERE respondent_id=$respondent_id AND question_id=$question_id AND question_part_no=$question_part_no LIMIT 1";
		$rs = mysql_query($SQL) or die ($SQL . mysql_error());
		
		if(mysql_num_rows($rs) > 0) 
		{
			$SQL = "UPDATE multiresponses SET response_value='" . mysql_escape_string($qvalue) . "' WHERE respondent_id=$respondent_id AND survey_id=$survey_id AND question_id=$question_id AND question_part_no=$question_part_no LIMIT 1";
			mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
	
		} 
		else 
		{
			$SQL = "INSERT IGNORE INTO multiresponses (respondent_id,survey_id,question_id,question_part_no,choice_id,response_value_type,response_value,org_id,branch_id) VALUES ";
			$SQL .= " ($respondent_id,$survey_id,$question_id,$question_part_no,'$choice_id','$question_type','$qvalue',$org_id,$branch_id)";
			mysql_query($SQL) or die ($SQL . mysql_error());
			
		}
	} 
	else	//scale question 
	{
		$SQL = "SELECT respondent_id FROM responses WHERE respondent_id=$respondent_id AND question_id=$question_id AND question_part_no=$question_part_no LIMIT 1";
		$rs = mysql_query($SQL) or die ($SQL . mysql_error());
		
		if(mysql_num_rows($rs) > 0) 
		{
			$SQL = "UPDATE responses SET response_value='" . mysql_escape_string($qvalue) . "' WHERE respondent_id=$respondent_id AND survey_id=$survey_id AND question_id=$question_id AND question_part_no=$question_part_no LIMIT 1";
			mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			
		} 
		else 
		{
			$SQL = "INSERT IGNORE INTO responses (respondent_id,survey_id,question_id,question_part_no,response_value_type,response_value,org_id,branch_id) VALUES ";
			$SQL .= " ($respondent_id,$survey_id,$question_id,$question_part_no,'$question_type','$qvalue',$org_id,$branch_id)";
			mysql_query($SQL) or die ($SQL . mysql_error());
		}
	}
}	

$result['respondent_id'] = $respondent_id;
$result['status'] = 'OK';

echo json_encode($result);

?>
