<?
class Surveys {
	var $formtypeArray;
	var $surveyArray;
	var $surveyIDArray;
	var $dimensionArray;
	var $questionArray;
	var $filterArray;
	var $categoryArray;
	var	$cat_index;
	var $org_id;
	var $loc_id;
	function Surveys($org_id, $loc_id='en_CA') {
		$this->org_id = $org_id;
		$this->loc_id = $loc_id;
		$this->formtypeArray = array();
		$this->surveyArray = array();
		$this->surveyIDArray = array();
		$this->dimensionArray = array();
		$this->questionArray = array();
		$this->filterArray = array();
		$this->categoryArray = array();
		$this->cat_index = 0;
	}
	function get_formtypes($with_allformtype_option=0) {
		if(count($this->formtypeArray) > 0) {
			return $this->formtypeArray;
		}
		$index = 0;
		if($with_allformtype_option > 0) {
			$this->formtypeArray[$index++] = "All";
		}	
		//$SQL = "SELECT DISTINCT s.type FROM orgsurveys o, surveys s WHERE o.org_id=" . $this->org_id . " AND o.survey_id=s.survey_id AND o.survey_status=1 ORDER BY s.type";
		$SQL = "SELECT DISTINCT s.type FROM orgsurveys o, surveys s WHERE o.org_id=" . $this->org_id . " AND o.survey_id=s.survey_id ORDER BY s.type";
		$rs = mysql_query($SQL) or die ($SQL . ' ::' . mysql_error());  
		while($fields = mysql_fetch_array($rs)) {
			$this->formtypeArray[$index++] = $fields['type'];
		}
		return $this->formtypeArray;
	}
	function get_survey_ids($formtype='SURVEY') {
		if(count($this->surveyIDArray) > 0) {
			return $this->surveyIDArray;
		}
		$index = 0;
		$formtype_query = "";
		if($formtype != 'All') $formtype_query = " AND s.type='" . $formtype . "' ";
		$SQL = "SELECT o.*, s.type FROM orgsurveys o, surveys s WHERE o.org_id=" . $this->org_id . " AND o.survey_id=s.survey_id " . $formtype_query . " ORDER BY s.survey_id";
		$rs = mysql_query($SQL) or die ($SQL . ' ::' . mysql_error());  
		while($fields = mysql_fetch_array($rs)) {
			$this->surveyIDArray[$index++] = $fields['survey_id'];
		}
		return $this->surveyIDArray;
	}
	function get_surveys($formtype='SURVEY', $with_allsurvey_option=0) {
		if(count($this->surveyArray) > 0) {
			return $this->surveyArray;
		}
		$index = 0;
		if($with_allsurvey_option > 0) {
			$this->surveyArray [$index]['survey_id'] = -1;
			$this->surveyArray [$index++]['survey_name'] = "-- All --";
		}	
		
		//check CONTACTME form
		if($formtype == "All" || $fromtype == "CONTACTME") {
			$contactme_survey_id = 224;
			$SQL = "SELECT o.*, s.type FROM orgsurveys o, surveys s WHERE o.org_id=" . $this->org_id . " AND o.survey_id=s.survey_id AND s.type='CONTACTME' LIMIT 1";
			$rs = mysql_query($SQL) or die ($SQL . ' ::' . mysql_error());  
			if($fields = mysql_fetch_array($rs)) {
				$contactme_survey_id = $fields['survey_id'];
			}	
		}
		$formtype_query = "";
		if($formtype != 'All') $formtype_query = " AND s.type='" . $formtype . "' ";
		$SQL = "SELECT o.survey_id, o.survey_name, o.survey_status, s.survey_prompt, s.type FROM orgsurveys o, surveys s WHERE o.org_id=" . $this->org_id . " AND o.survey_id=s.survey_id " . $formtype_query . " ORDER BY s.type, o.survey_name";
		$rs = mysql_query($SQL) or die ($SQL . ' ::' . mysql_error()); 
		while($fields = mysql_fetch_array($rs)) {
			$this->surveyArray[$index]['survey_id'] = $fields['survey_id'];
			$this->surveyArray[$index]['survey_name'] = $fields['survey_name'];
			$this->surveyArray[$index]['survey_prompt'] = $fields['survey_prompt'];
			$this->surveyArray[$index]['survey_status'] = $fields['survey_status'];
			$this->surveyArray[$index++]['type'] = $fields['type'];
		}
		if($contactme_survey_id == 224) {
			$SQL = "SELECT o.survey_id, o.survey_name, o.survey_status, s.survey_prompt, s.type FROM orgsurveys o, surveys s WHERE o.org_id=0 AND o.survey_id=s.survey_id AND s.survey_id=224 LIMIT 1";
			$rs = mysql_query($SQL) or die ($SQL . ' ::' . mysql_error()); 
			if($fields = mysql_fetch_array($rs)) {
				$this->surveyArray[$index]['survey_id'] = $fields['survey_id'];
				$this->surveyArray[$index]['survey_name'] = $fields['survey_name'];
				$this->surveyArray[$index]['survey_prompt'] = $fields['survey_prompt'];
				$this->surveyArray[$index]['survey_status'] = $fields['survey_status'];
				$this->surveyArray[$index++]['type'] = $fields['type'];
			}
		}

		return $this->surveyArray;
	}
	function get_dimensions($survey_id=-1, $formtype='SURVEY', $with_alldimension_option=0) {
		if(count($this->dimensionArray) > 0) {
			return $this->dimensionArray;
		}
		$index = 0;
		if($with_alldimension_option == 1) {
			$this->dimensionArray[$index++] = "All";
		}	
		$formtype_query = "";
		if($formtype != 'All') $formtype_query = " AND s.type='" . $formtype . "' ";
		$survey_query = '';
		if($survey_id > 0) {
			$survey_query = " AND o.survey_id=" . $survey_id . " ";
		} 
		$SQL = "SELECT q.dimension AS dimension, dl.sequence AS sequence FROM surveys s, orgsurveys o, surveypagegroups sg, groupquestions gq, questions q, dimensionslocal dl ";
		$SQL .= " WHERE s.survey_id=o.survey_id " . $formtype_query . " AND o.org_id=" . $this->org_id . " AND o.survey_id=sg.survey_id AND sg.group_id=gq.group_id AND gq.question_id=q.question_id " . $survey_query . " AND q.dimension=dl.dimension ";
		$SQL .= " UNION "; 
		$SQL .= " SELECT q.dimension AS dimension, dl.sequence AS sequence FROM surveys s, orgsurveys o, surveypagegroups sg, surveyorggroupquestions gq, questions q, dimensionslocal dl "; 
		$SQL .= " WHERE  s.survey_id=o.survey_id " . $formtype_query . " AND o.org_id=" . $this->org_id . " AND o.org_id=gq.org_id AND o.survey_id=sg.survey_id AND sg.group_id=gq.group_id AND gq.question_id=q.question_id " . $survey_query  . " AND q.dimension=dl.dimension ";
		$SQL .= " UNION "; 
		$SQL .= " SELECT q.dimension AS dimension, dl.sequence AS sequence FROM surveys s, orgsurveys o, surveyorgpagegroups sg, groupquestions gq, questions q, dimensionslocal dl ";
		$SQL .= " WHERE  s.survey_id=o.survey_id " . $formtype_query . " AND o.org_id=" . $this->org_id . " AND o.org_id=sg.org_id AND o.survey_id=sg.survey_id AND sg.group_id=gq.group_id AND gq.question_id=q.question_id " . $survey_query . " AND q.dimension=dl.dimension ";
		$SQL .= " ORDER BY sequence";
		$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
		while($fields = mysql_fetch_array($rs)) {
			$this->dimensionArray[$index++] = $fields['dimension'];
		}
		return $this->dimensionArray;
	}
	//get org categories
	function get_sub_category($parent_category_id, $level) {
		$SQL .= " SELECT DISTINCT cr.parent_category_id, cc.comment_category_id, cc.comment_name, cc.comment_description, cc.contact_email, cc.contact_bcc";
		$SQL .= " FROM commentcategories cc, categoryrelationship cr WHERE cc.org_id=cr.org_id AND cc.comment_category_id=cr.comment_category_id AND cr.parent_category_id=$parent_category_id AND cc.org_id=" . $this->org_id;
		$SQL .= " ORDER BY comment_category_id";
		$rs = mysql_query($SQL) or die($SQL . '<br />' . mysql_error());
		if(mysql_num_rows($rs) < 1) return;
		while($row = mysql_fetch_array($rs)) {
			$parent_category_id2 = $row['comment_category_id'];
			$this->categoryArray[$this->cat_index]['parent_category_id'] = $row['parent_category_id'];
			$this->categoryArray[$this->cat_index]['comment_category_id'] = $row['comment_category_id'];
			$this->categoryArray[$this->cat_index]['level'] = $level + 1;
			$this->categoryArray[$this->cat_index]['comment_name'] = $row['comment_name'];
			$this->categoryArray[$this->cat_index]['comment_description'] = $row['comment_description'];
			$this->categoryArray[$this->cat_index]['contact_email'] = $row['contact_email'];
			$this->categoryArray[$this->cat_index++]['contact_bcc'] = $row['contact_bcc'];
			$this->get_sub_category($parent_category_id2, $level);
		}
	}
	function get_categories($branch_id=0) {
		$level = 0;
		if(!empty($this->categoryArray)) return $this->categoryArray;
		if($branch_id == 0) {
			$SQL = "SELECT DISTINCT cc.comment_category_id AS parent_category_id, cc.comment_category_id, cc.comment_name, cc.comment_description, cc.contact_email, cc.contact_bcc "; 
			$SQL .= " FROM commentcategories cc WHERE cc.org_id=" . $this->org_id . " AND comment_category_id NOT IN (SELECT comment_category_id FROM categoryrelationship cr WHERE cr.org_id=" . $this->org_id . ") ORDER BY parent_category_id"; 
			$rs = mysql_query($SQL) or die($SQL . '<br>' . mysql_error());
			while($row = mysql_fetch_array($rs)) {
				$parent_category_id = $row['parent_category_id'];
				$this->categoryArray[$this->cat_index]['parent_category_id'] = $row['parent_category_id'];
				$this->categoryArray[$this->cat_index]['comment_category_id'] = $row['comment_category_id'];
				$this->categoryArray[$this->cat_index]['level'] = $level;
				$this->categoryArray[$this->cat_index]['comment_name'] = $row['comment_name'];
				$this->categoryArray[$this->cat_index]['comment_description'] = $row['comment_description'];
				$this->categoryArray[$this->cat_index]['contact_email'] = $row['contact_email'];
				$this->categoryArray[$this->cat_index++]['contact_bcc'] = $row['contact_bcc'];
				$this->get_sub_category($parent_category_id, $level);
			}
		} else {
			$SQL = "SELECT DISTINCT cr.parent_category_id,cc.comment_category_id, cc.comment_name, cc.comment_description, bc.contact_email, bc.contact_bcc";
			$SQL .= " FROM commentcategories cc LEFT JOIN branchcategories bc ON (cc.comment_category_id=bc.comment_category_id AND cc.org_id=bc.org_id AND bc.branch_id=$branch_id AND bc.loc_id='" . $this->loc_id . "') LEFT JOIN categoryrelationship cr ON (cc.org_id=cr.org_id AND (cc.comment_category_id = cr.comment_category_id OR cc.comment_category_id=cr.parent_category_id))";
			$SQL .= " WHERE cr.parent_category_id IS NULL AND cc.org_id=" . $this->org_id;
			$SQL .= " UNION ";
			$SQL .= " SELECT DISTINCT cr.parent_category_id, cc.comment_category_id, cc.comment_name, cc.comment_description, bc.contact_email, bc.contact_bcc";
			$SQL .= " FROM categoryrelationship cr, commentcategories cc LEFT JOIN branchcategories bc ON (cc.comment_category_id=bc.comment_category_id AND cc.org_id=bc.org_id AND bc.branch_id=$branch_id AND bc.loc_id='$loc_id')  WHERE (cc.org_id=cr.org_id AND (cc.comment_category_id = cr.comment_category_id OR cc.comment_category_id = cr.parent_category_id))";
			$SQL .= " AND cc.org_id = cr.org_id AND cc.org_id=" . $this->org_id;
			$SQL .= " ORDER BY parent_category_id,sequence, comment_category_id";
			$rs = mysql_query($SQL) or die($SQL . '<br>' . mysql_error());
			while($row = mysql_fetch_array($rs)) {
				$parent_cate_gory_id = $row['parent_category_id'];
				$this->categoryArray[$this->cat_index]['parent_category_id'] = $row['parent_category_id'];
				$this->categoryArray[$this->cat_index]['comment_category_id'] = $row['comment_category_id'];
				$this->categoryArray[$this->cat_index]['level'] = 0;
				if($row['parent_category_id'] != $row['comment_category_id']) $this->categoryArray[$this->cat_index]['level'] = 1;
				$this->categoryArray[$this->cat_index]['comment_name'] = $row['comment_name'];
				$this->categoryArray[$this->cat_index]['comment_description'] = $row['comment_description'];
				$this->categoryArray[$this->cat_index]['contact_email'] = $row['contact_email'];
				$this->categoryArray[$this->cat_index++]['contact_bcc'] = $row['contact_bcc'];
				$this->get_sub_category($parent_category_id, $level);
			}
		}
		return $this->categoryArray;
	}		
	function get_questions_v1($survey_id=-1, $dimension='All', $formtype='SURVEY', $with_allquestion_option=0) {
		$this->questionArray = array();
		$index = 0;
		if($with_allquestion_option == 1) {
			$this->questionArray[$index]['question_id'] = "-1";
			$this->questionArray[$index]['question_name'] = "All";
			$this->questionArray[$index]['question_prompt'] = "All";
			$this->questionArray[$index++]['dimension'] = "All";
		}	
		$survey_query = "";
		$formtype_query = "";
		$survey_group = false;
		if($survey_id > 0) {
			if(strpos($survey_id,",") === false) {
				$survey_query = " AND s.survey_id=" . $survey_id . " ";
				$survey_group = false;
			} else {
				$survey_group = true;
				$sArray = explode(",", $survey_id);
				$survey_str = "";
				for($i=0;$i<count($sArray);$i++) {
					if($i > 0) $survey_str .= ",";
					$survey_str .= $sArray[$i];
				}
				$survey_query = " AND s.survey_id IN (" . $survey_str . ") ";
			}	
		} else {
			$survey_group = true;
			if($formtype != "All") {
				$formtype_query = " AND s.type='" . $formtype . "' ";
			}
		}	
		$dimension_query = '';
		if($dimension != "All") $dimension_query = " AND q.dimension='" . $dimension . "' ";
		$SQL = "SELECT q.question_id, q.dimension AS dimension, q.question_name, q.varname as varname, q.question_sequence as question_sequence, ql.question_prompt, ql.shortform, ql.preamble, ql.short_preamble, g.group_type FROM  surveys s, orgsurveys o, surveypagegroups sg, groupquestions gq, groups g, questions q, questionslocal ql ";
		$SQL .= " WHERE o.org_id=" . $this->org_id . " AND s.survey_id=o.survey_id AND o.survey_id=sg.survey_id AND o.survey_status>0 AND sg.group_id=gq.group_id AND gq.group_id=g.group_id AND gq.question_id=q.question_id AND q.question_id=ql.question_id AND ql.loc_id='" . $this->loc_id . "' " . $survey_query . $formtype_query . $dimension_query;
		$SQL .= " AND gq.question_id NOT IN (SELECT question_id FROM surveyorgqsexcluded WHERE org_id=o.org_id) "; 
		$SQL .= " UNION ALL"; 
		$SQL .= " SELECT q.question_id, q.dimension AS dimension, q.question_name, q.varname as varname, q.question_sequence as question_sequence, ql.question_prompt, ql.shortform, ql.preamble, ql.short_preamble, g.group_type FROM surveys s, orgsurveys o, surveypagegroups sg, surveyorggroupquestions gq, groups g, questions q, questionslocal ql "; 
		$SQL .= " WHERE o.org_id=" . $this->org_id . " AND o.survey_id=s.survey_id AND o.org_id=gq.org_id AND o.survey_id=sg.survey_id AND o.survey_status>0 AND sg.group_id=gq.group_id AND gq.group_id=g.group_id AND gq.question_id=q.question_id AND q.question_id=ql.question_id AND ql.loc_id='" . $this->loc_id . "'"  . $survey_query . $formtype_query . $dimension_query;
		$SQL .= " UNION ALL"; 
		$SQL .= " SELECT q.question_id, q.dimension AS dimension, q.question_name, q.varname as varname, q.question_sequence as question_sequence, ql.question_prompt, ql.shortform, ql.preamble, ql.short_preamble, g.group_type FROM surveys s, orgsurveys o, surveyorgpagegroups sg, groupquestions gq, groups g, questions q, questionslocal ql ";
		$SQL .= " WHERE o.org_id=" . $this->org_id . " AND o.survey_id=s.survey_id AND o.org_id=sg.org_id AND o.survey_id=sg.survey_id AND o.survey_status>0 AND sg.group_id=gq.group_id AND gq.group_id=g.group_id AND gq.question_id=q.question_id AND q.question_id=ql.question_id AND ql.loc_id='" . $this->loc_id . "' " . $survey_query . $formtype_query . $dimension_query;
		$SQL .= " ORDER BY dimension, question_sequence";
		$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
		while($fields = mysql_fetch_array($rs)) {
			//if($fields['group_type'] == "contact" || $fields['group_type'] == "address" || $fields['group_type'] == "library") continue;
			if($fields['group_type'] == "contact" || $fields['group_type'] == "address") continue;
			$this->questionArray[$index]['question_name'] = $fields['question_prompt'];
			$this->questionArray[$index]['varname'] = $fields['varname'];
			$this->questionArray[$index]['question_prompt'] = $fields['question_prompt'];
			$this->questionArray[$index]['shortform'] = $fields['shortform'];
			$this->questionArray[$index]['preamble'] = $fields['preamble'];
			$this->questionArray[$index]['short_preamble'] = $fields['short_preamble'];
			$this->questionArray[$index]['question_id'] = $fields['question_id'];
			$this->questionArray[$index++]['dimension'] = $fields['dimension'];
		}
		for($i=0;$i<count($this->questionArray);$i++) {	
			if($this->questionArray[$i]['question_id'] == -1) continue;
			if(isset($_SESSION['branch_id']) && $_SESSION['branch_id'] > 0) {
				$branch_query = " AND branch_id IN (" .  $_SESSION['branch_id'] . ",0) ORDER BY branch_id DESC LIMIT 1";
			} else {
				$branch_query = " AND branch_id=0 ORDER BY branch_id DESC LIMIT 1";
			}	
			$sqlx = "SELECT * FROM surveyorgqsslocal WHERE org_id=" . $this->org_id . " AND question_id=" . $this->questionArray[$i]['question_id'] . " AND loc_id='" . $this->loc_id . "' " . $branch_query;
			$rsx = mysql_query($sqlx) or die ($sqlx . '<br />' . mysql_error());
			if($xrow = mysql_fetch_array($rsx)) {
				$this->questionArray[$i]['question_name']  = $xrow['question_prompt'];
				$this->questionArray[$i]['question_prompt'] = $xrow['question_prompt'];
			}
		}	
		return $this->questionArray;	
	}
	function get_questions($survey_id=-1, $dimension='All', $formtype='SURVEY', $with_allquestion_option=false, $with_hidden_question=true) {
		$this->questionArray = array();
		$index = 0;
		if($with_allquestion_option == 1) {
			$this->questionArray[$index]['question_id'] = "-1";
			$this->questionArray[$index]['question_name'] = "All";
			$this->questionArray[$index]['question_prompt'] = "All";
			$this->questionArray[$index++]['dimension'] = "All";
		}	
		$survey_query = "";
		$formtype_query = "";
		$survey_group = false;
		if($survey_id > 0) {
			if(strpos($survey_id,",") === false) {
				$survey_query = " AND s.survey_id=" . $survey_id . " ";
				$survey_group = false;
			} else {
				$survey_group = true;
				$sArray = explode(",", $survey_id);
				$survey_str = "";
				for($i=0;$i<count($sArray);$i++) {
					if($i > 0) $survey_str .= ",";
					$survey_str .= $sArray[$i];
				}
				$survey_query = " AND s.survey_id IN (" . $survey_str . ") ";
			}	
		} else {
			$survey_group = true;
			if($formtype != "All") {
				$survey_query = " AND s.type='" . $formtype . "' ";
			}
		}	
		$dimension_query = '';
		if($dimension != "All") $dimension_query = " AND q.dimension='" . $dimension . "' ";
		$index = 0;	
		$groupArray = array();
		$SQL = "SELECT sg.survey_id AS survey_id, sg.page_id AS page_id, sg.group_id AS group_id, sg.sequence AS sequence, g.group_type FROM surveys s, orgsurveys os, surveypages sp, surveypagegroups sg, groups g WHERE s.survey_id=os.survey_id AND os.org_id=" . $this->org_id . " AND os.survey_id=sp.survey_id AND sp.survey_id=sg.survey_id AND sp.page_id=sg.page_id " . $survey_query . " AND sg.group_id=g.group_id ";
		$SQL .= " UNION ";
		$SQL .= " SELECT sg.survey_id AS survey_id, sg.page_id AS page_id, sg.group_id AS group_id, sg.sequence AS sequence, g.group_type FROM surveys s, orgsurveys os, surveypages sp, surveyorgpagegroups sg, groups g WHERE s.survey_id=os.survey_id AND os.org_id=" . $this->org_id . " AND os.survey_id=sp.survey_id AND sp.survey_id AND sp.survey_id=sg.survey_id AND sp.page_id=sg.page_id " . $survey_query . " AND sg.org_id=" . $this->org_id . " AND sg.group_id=g.group_id ORDER BY page_id, sequence";
		$rs_page = mysql_query($SQL) or die ($SQL . "<br />" . mysql_error());
		while($page_fields = mysql_fetch_array($rs_page)) {
			$group_type = $page_fields['group_type'];
			if(!$with_hidden_questions && $group_type == 'hidden') continue;
			if($page_fields['group_type'] == "contact" || $page_fields['group_type'] == "address" || $page_fields['group_type'] == "hidden") continue;
			$group_id = $page_fields['group_id'];
			$group_exist = false;
			for($i=0;$i<count($groupArray);$i++) {
				if($group_id == $groupArray[$i]) {
					$group_exist = true;
					break;
				}	
			}
			if($group_exist) continue;
			array_push($groupArray, $group_id);
			$temp_survey_id = $page_fields['survey_id'];
			$SQL = "SELECT new_group_id AS group_id FROM surveyorggrpsreplaced WHERE org_id=" . $this->org_id . " AND orig_group_id=$group_id AND survey_id=$temp_survey_id LIMIT 1";
			$rs_group = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			if($row_group = mysql_fetch_array($rs_group)) {
				$group_id = $row_group['group_id'];
			}
			if($group_id == 0) continue;
			
			$SQL = "SELECT q.question_id, q.dimension AS dimension, q.question_name, q.varname as varname, g.hide_on_report, g.sequence as sequence, ql.question_prompt, ql.preamble, ql.short_preamble FROM groupquestions g, questions q, questionslocal ql WHERE g.group_id=" . $group_id . $dimension_query . " AND g.question_id NOT IN (SELECT DISTINCT question_id FROM surveyorgqsexcluded WHERE org_id=" . $this->org_id . $excluded_question_branch_query . " ) AND g.question_id=q.question_id " . $required_query . " AND q.question_id=ql.question_id AND ql.loc_id='" . $this->loc_id . "'";
			$SQL .= " UNION ALL ";
			$SQL .= "SELECT q.question_id, q.dimension AS dimension, q.question_name, q.varname as varname, 0 AS hide_on_report, g.sequence as sequence, ql.question_prompt, ql.preamble, ql.short_preamble  FROM surveyorggroupquestions g, questions q, questionslocal ql WHERE g.group_id=" . $group_id . $dimension_query . "  AND g.org_id=" . $this->org_id . " AND g.question_id=q.question_id AND q.question_id=ql.question_id AND ql.loc_id='" . $this->loc_id . "' ORDER BY sequence";
			$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			while($fields = mysql_fetch_array($rs)) {
				$question_exist = false;
				for($i=0;$i<count($this->questionArray);$i++) {
					if($fields['question_id'] == $this->questionArray[$i]['question_id']) {
						$question_exist = true;
						break;
					}	
				}
				if($question_exist) continue;	
				$this->questionArray[$index]['group_id'] = $group_id;
				$this->questionArray[$index]['question_name'] = $fields['question_prompt'];
				$this->questionArray[$index]['varname'] = $fields['varname'];
				$this->questionArray[$index]['hide_on_report'] = $fields['hide_on_report'];
				$this->questionArray[$index]['question_prompt'] = $fields['question_prompt'];
				$this->questionArray[$index]['preamble'] = $fields['preamble'];
				$this->questionArray[$index]['short_preamble'] = $fields['short_preamble'];
				$this->questionArray[$index]['question_id'] = $fields['question_id'];
				$this->questionArray[$index++]['dimension'] = $fields['dimension'];
			}
		}	
		if(isset($_SESSION['branch_id']) && $_SESSION['branch_id'] > 0) {
			$branch_query = " AND branch_id IN (" .  $_SESSION['branch_id'] . ",0) ORDER BY branch_id DESC LIMIT 1";
		} else {
			$branch_query = " AND branch_id=0 ORDER BY branch_id DESC LIMIT 1";
		}	
		for($i=0;$i<count($this->questionArray);$i++) {	
			$SQLx = "SELECT question_prompt FROM surveyorgqsslocal WHERE org_id=" . $this->org_id . " AND question_id=" . $this->questionArray[$i]['question_id'] . " AND loc_id='" . $this->loc_id . "' " . $branch_query;
			$rsx = mysql_query($SQLx) or die ($SQLx . '<br />' . mysql_error());
			if($xrow = mysql_fetch_array($rsx)) {
				$this->questionArray[$i]['question_name']  = $xrow['question_prompt'];
				$this->questionArray[$i]['question_prompt'] = $xrow['question_prompt'];
			}
		}
		return $this->questionArray;	
	}
	function get_questions_by_group($survey_id=-1, $dimension='All', $formtype='SURVEY', $with_allquestion_option=0) {
		$this->questionArray = array();
		$index = 0;
		if($with_allquestion_option == 1) {
			$this->questionArray[$index]['question_id'] = "-1";
			$this->questionArray[$index]['question_name'] = "All";
			$this->questionArray[$index]['question_prompt'] = "All";
			$this->questionArray[$index++]['dimension'] = "All";
		}	
		$survey_query = "";
		$formtype_query = "";
		$survey_group = false;
		if($survey_id > 0) {
			if(strpos($survey_id,",") === false) {
				$survey_query = " AND s.survey_id=" . $survey_id . " ";
				$survey_group = false;
			} else {
				$survey_group = true;
				$sArray = explode(",", $survey_id);
				$survey_str = "";
				for($i=0;$i<count($sArray);$i++) {
					if($i > 0) $survey_str .= ",";
					$survey_str .= $sArray[$i];
				}
				$survey_query = " AND s.survey_id IN (" . $survey_str . ") ";
			}	
		} else {
			$survey_group = true;
			if($formtype != "All") {
				$formtype_query = " AND s.type='" . $formtype . "' ";
			}
		}	
		$dimension_query = '';
		if($dimension != "All") $dimension_query = " AND q.dimension='" . $dimension . "' ";
		$index = 0;	
		if($survey_id > 0) {
			$SQL = "SELECT DISTINCT sg.group_id AS group_id, g.group_type FROM surveys s, orgsurveys os, surveypagegroups sg, groups g WHERE s.survey_id=os.survey_id " . $survey_query . " AND os.survey_status=1 AND os.org_id=" . $this->org_id . " AND sg.survey_id=os.survey_id AND sg.group_id=g.group_id ";
			$SQL .= " UNION ";
			$SQL .= " SELECT DISTINCT sg.group_id AS group_id, g.group_type FROM surveys s, orgsurveys os, surveyorgpagegroups sg, groups g WHERE s.survey_id=os.survey_id " . $survey_query . " AND os.survey_status=1 AND os.org_id=" . $this->org_id . " AND sg.survey_id=os.survey_id AND sg.org_id=" . $this->org_id . " AND sg.group_id=g.group_id";
		} else {	
			$SQL = "SELECT DISTINCT sg.group_id AS group_id, g.group_type FROM surveys s, orgsurveys os, surveypagegroups sg, groups g WHERE s.survey_id=os.survey_id " . $formtype_query . $survey_query . "  AND os.survey_status=1 AND os.org_id=" . $this->org_id . " AND sg.survey_id=os.survey_id AND sg.group_id=g.group_id ";
			$SQL .= " UNION ";
			$SQL .= " SELECT DISTINCT sg.group_id AS group_id, g.group_type FROM surveys s, orgsurveys os, surveyorgpagegroups sg, groups g WHERE s.survey_id=os.survey_id " . $formtype_query . $survey_query . " AND os.survey_status=1 AND os.org_id=" . $this->org_id . " AND sg.survey_id=os.survey_id AND sg.org_id=" . $this->org_id . " AND sg.group_id=g.group_id";
		}
		$rs_page = mysql_query($SQL) or die ($SQL . "<br />" . mysql_error());
		while($page_fields = mysql_fetch_array($rs_page)) {
			if($page_fields['group_type'] == "contact" || $page_fields['group_type'] == "address" || $page_fields['group_type'] == "hidden") continue;
			$group_id = $page_fields['group_id'];
			$group_type = $page_fields['group_type'];
			$SQL = "SELECT new_group_id AS group_id FROM surveyorggrpsreplaced s WHERE s.org_id=" . $this->org_id . " AND s.orig_group_id=$group_id " . $survey_query . " LIMIT 1";
			$rs_group = mysql_query($SQL) or die ($SQL . '<br>' . mysql_error());
			if($row_group = mysql_fetch_array($rs_group)) {
				$group_id = $row_group['group_id'];
			}
			if($group_id == 0) continue;

			$required_query = "";
			if($page_fields['group_type'] == "contact" || $page_fields['group_type'] == "address" || $page_fields['group_type'] == "library") $required_query = "AND q.required=1 ";
			$SQL = "SELECT DISTINCT q.question_id, q.dimension AS dimension, q.question_name, q.varname as varname, g.hide_on_report, g.sequence as sequence, ql.question_prompt, ql.shortform, ql.preamble, ql.short_preamble FROM groupquestions g, questions q, questionslocal ql WHERE g.group_id=" . $group_id . $dimension_query . " AND g.question_id NOT IN (SELECT DISTINCT question_id FROM surveyorgqsexcluded WHERE org_id=" . $this->org_id . $excluded_question_branch_query . " ) AND g.question_id=q.question_id " . $required_query . " AND q.question_id=ql.question_id AND ql.loc_id='" . $this->loc_id . "'";
			$SQL .= " UNION ";
			$SQL .= "SELECT DISTINCT q.question_id, q.dimension AS dimension, q.question_name, q.varname as varname, 0 AS hide_on_report, g.sequence as sequence, ql.question_prompt, ql.shortform, ql.preamble, ql.short_preamble  FROM surveyorggroupquestions g, questions q, questionslocal ql WHERE g.group_id=" . $group_id . $dimension_query . "  AND g.org_id=" . $this->org_id . " AND g.question_id=q.question_id AND q.question_id=ql.question_id AND ql.loc_id='" . $this->loc_id . "' ORDER BY sequence";
			$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			while($fields = mysql_fetch_array($rs)) {
				//check question_id in other group first, if already exist, skip it and add the extra questions in that group
				$bexist = false;
				if($survey_group) {
					for($k=0;$k<count($this->questionArray);$k++) {
						if($fields['question_id'] == $this->questionArray[$k]['question_id']) {
							$bexist = true;
							break;
						}
					}
				}	
				if($bexist) continue;
				$this->questionArray[$index]['group_id'] = $group_id;
				$this->questionArray[$index]['group_type'] = $group_type;
				$this->questionArray[$index]['question_name'] = $fields['question_prompt'];
				$this->questionArray[$index]['varname'] = $fields['varname'];
				$this->questionArray[$index]['hide_on_report'] = $fields['hide_on_report'];
				$this->questionArray[$index]['question_prompt'] = $fields['question_prompt'];
				$this->questionArray[$index]['shortform'] = $fields['shortform'];
				$this->questionArray[$index]['preamble'] = $fields['preamble'];
				$this->questionArray[$index]['short_preamble'] = $fields['short_preamble'];
				$this->questionArray[$index]['question_id'] = $fields['question_id'];
				$this->questionArray[$index++]['dimension'] = $fields['dimension'];
			}
		}
		if(isset($_SESSION['branch_id']) && $_SESSION['branch_id'] > 0) {
			$branch_query = " AND branch_id IN (" .  $_SESSION['branch_id'] . ",0) ORDER BY branch_id DESC LIMIT 1";
		} else {
			$branch_query = " AND branch_id=0 ORDER BY branch_id DESC LIMIT 1";
		}	
		for($i=0;$i<count($this->questionArray);$i++) {	
			$SQLx = "SELECT question_prompt FROM surveyorgqsslocal WHERE org_id=" . $this->org_id . " AND question_id=" . $this->questionArray[$i]['question_id'] . " AND loc_id='" . $this->loc_id . "' " . $branch_query;
			$rsx = mysql_query($SQLx) or die ($SQLx . '<br />' . mysql_error());
			if($xrow = mysql_fetch_array($rsx)) {
				//$questionArray[$i]['question_name']  = $xrow['question_prompt'];
				$this->questionArray[$i]['question_prompt'] = $xrow['question_prompt'];
			}
		}
		return $this->questionArray;	
	}

	function get_survey_groups($survey_id) {
		$this->groupArray = array();
		$index = 0;	
		$SQL = "SELECT sg.survey_id AS survey_id, sg.page_id AS page_id, sg.group_id AS group_id, sg.sequence AS sequence, g.group_type FROM surveypages sp, surveypagegroups sg, groups g WHERE sp.survey_id=sg.survey_id AND sp.page_id=sg.page_id AND sp.survey_id=$survey_id AND sg.group_id=g.group_id ";
		$SQL .= " UNION ";
		$SQL .= " SELECT sg.survey_id AS survey_id, sg.page_id AS page_id, sg.group_id AS group_id, sg.sequence AS sequence, g.group_type FROM surveypages sp, surveyorgpagegroups sg, groups g WHERE sp.survey_id=sg.survey_id AND sp.page_id=sg.page_id AND sp.survey_id=$survey_id AND sg.org_id=" . $this->org_id . " AND sg.group_id=g.group_id ORDER BY page_id, sequence";
		$rs = mysql_query($SQL) or die ($SQL . "<br />" . mysql_error());
		while($fields = mysql_fetch_array($rs)) {
			$group_id = $fields['group_id'];
			$this->groupArray[$index]['group_id'] = $group_id;
			$this->groupArray[$index]['group_type'] = $fields['group_type'];
			$this->groupArray[$index]['group_prompt'] = $fields['group_prompt'];
			$SQL = "SELECT new_group_id AS group_id FROM surveyorggrpsreplaced WHERE org_id=" . $this->org_id . " AND orig_group_id=$group_id AND survey_id=$survey_id LIMIT 1";
			$rs_group = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			if($row_group = mysql_fetch_array($rs_group)) {
				$this->groupArray[$index]['group_id'] = $row_group['group_id'];
			}
			$index++;
		}
		return $this->groupArray;	
	}
	
	function get_survey_questions($survey_id, $dimension='All', $with_options=false, $with_filters=false, $with_hidden_questions=true) {
		$this->questionArray = array();
		if(isset($_SESSION['branch_id']) && $_SESSION['branch_id'] > 0) {
			$excluded_question_branch_query = " AND branch_id IN (" . $_SESSION['branch_id'] . ",0) ";
		} else {
			$excluded_question_branch_query = " AND branch_id=0 ";
		}
		$dimension_query = '';
		if($dimension != "All") $dimension_query = " AND q.dimension='" . $dimension . "' ";
		
		$q_query = ''; 
		if($with_options) {
			$q_query = ", q.css";
		}

		$index = 0;	
		$SQL = "SELECT sg.survey_id AS survey_id, sg.page_id AS page_id, sg.group_id AS group_id, sg.sequence AS sequence, g.group_type FROM surveypages sp, surveypagegroups sg, groups g WHERE sp.survey_id=sg.survey_id AND sp.page_id=sg.page_id AND sp.survey_id=$survey_id AND sg.group_id=g.group_id ";
		$SQL .= " UNION ";
		$SQL .= " SELECT sg.survey_id AS survey_id, sg.page_id AS page_id, sg.group_id AS group_id, sg.sequence AS sequence, g.group_type FROM surveypages sp, surveyorgpagegroups sg, groups g WHERE sp.survey_id=sg.survey_id AND sp.page_id=sg.page_id AND sp.survey_id=$survey_id AND sg.org_id=" . $this->org_id . " AND sg.group_id=g.group_id ORDER BY page_id, sequence";
		$rs_page = mysql_query($SQL) or die ($SQL . "<br />" . mysql_error());
		while($page_fields = mysql_fetch_array($rs_page)) {
			//if($page_fields['group_type'] == "contact" || $page_fields['group_type'] == "address" || $page_fields['group_type'] == "hidden") continue;
			$group_id = $page_fields['group_id'];
			$group_type = $page_fields['group_type'];
			if(!$with_hidden_questions && $group_type == 'hidden') continue;
			$SQL = "SELECT new_group_id AS group_id FROM surveyorggrpsreplaced WHERE org_id=" . $this->org_id . " AND orig_group_id=$group_id AND survey_id=$survey_id LIMIT 1";
			$rs_group = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			if($row_group = mysql_fetch_array($rs_group)) {
				$group_id = $row_group['group_id'];
			}
			if($group_id == 0) continue;

			$required_query = "";
			if($page_fields['group_type'] == "contact" || $page_fields['group_type'] == "address" || $page_fields['group_type'] == "library") $required_query = "AND q.required=1 ";
			$SQL = "SELECT q.question_id, q.dimension AS dimension, q.question_name, q.varname as varname, g.hide_on_report, g.sequence as sequence, ql.question_prompt, ql.preamble, ql.short_preamble" . $q_query . " FROM groupquestions g, questions q, questionslocal ql WHERE g.group_id=" . $group_id . $dimension_query . " AND g.question_id NOT IN (SELECT DISTINCT question_id FROM surveyorgqsexcluded WHERE org_id=" . $this->org_id . $excluded_question_branch_query . " ) AND g.question_id=q.question_id " . $required_query . " AND q.question_id=ql.question_id AND ql.loc_id='" . $this->loc_id . "'";
			$SQL .= " UNION ALL ";
			$SQL .= "SELECT q.question_id, q.dimension AS dimension, q.question_name, q.varname as varname, 0 AS hide_on_report, g.sequence as sequence, ql.question_prompt, ql.preamble, ql.short_preamble" . $q_query . " FROM surveyorggroupquestions g, questions q, questionslocal ql WHERE g.group_id=" . $group_id . $dimension_query . "  AND g.org_id=" . $this->org_id . " AND g.question_id=q.question_id AND q.question_id=ql.question_id AND ql.loc_id='" . $this->loc_id . "' ORDER BY sequence";
			$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			while($fields = mysql_fetch_array($rs)) {
				if($with_options) {
					$this->questionArray[$index]['page_id'] = $page_fields['page_id'];
					$this->questionArray[$index]['css'] = $fields['css'];
				}
				$this->questionArray[$index]['group_id'] = $group_id;
				$this->questionArray[$index]['group_type'] = $group_type;
				$this->questionArray[$index]['question_name'] = $fields['question_prompt'];
				$this->questionArray[$index]['varname'] = $fields['varname'];
				$this->questionArray[$index]['hide_on_report'] = $fields['hide_on_report'];
				$this->questionArray[$index]['question_prompt'] = $fields['question_prompt'];
				$this->questionArray[$index]['preamble'] = $fields['preamble'];
				$this->questionArray[$index]['short_preamble'] = $fields['short_preamble'];
				$this->questionArray[$index]['question_id'] = $fields['question_id'];
				$this->questionArray[$index]['dimension'] = $fields['dimension'];
				if($with_options) {
					$questionPartArray = array();
					$SQLp = "SELECT qp.question_part_no, qp.question_type, qp.question_format, qp.options_type, qp.options, qp.options_value_max, qp.options_value_min, qp.options_value_default, qhl.scale_text, qhl.low_scale_text, qhl.high_scale_text, qhl.part_prompt, qhl.gap_type";
					$SQLp .= " FROM questionparts qp LEFT JOIN qoptionsheaderlocal qhl ON (qp.question_id=qhl.question_id AND qp.question_part_no=qhl.question_part_no AND qhl.loc_id='" . $this->loc_id . "') WHERE  qp.question_id=" . $fields['question_id'] . " ORDER BY qp.question_part_no";
					$rsp = mysql_query($SQLp) or die($SQLp . '<br />' . mysql_error());
					$pindex = 0;
					while($rowp = mysql_fetch_array($rsp)) {
						$questionPartArray[$pindex]['question_part_no'] = $rowp['question_part_no'];
						$questionPartArray[$pindex]['question_type'] = $rowp['question_type'];
						$questionPartArray[$pindex]['question_format'] = $rowp['question_format'];
						$questionPartArray[$pindex]['options_type'] = $rowp['options_type'];
						$questionPartArray[$pindex]['options'] = $rowp['options'];
						$questionPartArray[$pindex]['options_value_max'] = $rowp['options_value_max'];
						$questionPartArray[$pindex]['options_value_min'] = $rowp['options_value_min'];
						$questionPartArray[$pindex]['options_value_default'] = $rowp['options_value_default'];
						$questionPartArray[$pindex]['scale_text'] = $rowp['scale_text'];
						$questionPartArray[$pindex]['low_scale_text'] = $rowp['low_scale_text'];
						$questionPartArray[$pindex]['high_scale_text'] = $rowp['high_scale_text'];
						$questionPartArray[$pindex]['part_prompt'] = $rowp['part_prompt'];
						$questionPartArray[$pindex]['gap_type'] = $rowp['gap_type'];
						if($rowp['question_type'] == "radio" || $rowp['question_type'] == "scale" || $rowp['question_type'] == "select" || $rowp['question_type'] == "checkbox" || $rowp['question_type'] == "option" || $rowp['question_type'] == "multi") {
							$questionPartArray[$pindex]['options'] = $this->get_options($fields['question_id'], $rowp['question_part_no']);
						} else {
							$questionPartArray[$pindex]['options'] = null;
						}	
						$pindex++;
					}
					$this->questionArray[$index]['questionparts'] = $questionPartArray;
				}
				$index++;	
			}	
		}	
		if(isset($_SESSION['branch_id']) && $_SESSION['branch_id'] > 0) {
			$branch_query = " AND branch_id IN (" .  $_SESSION['branch_id'] . ",0) ORDER BY branch_id DESC LIMIT 1";
		} else {
			$branch_query = " AND branch_id=0 ORDER BY branch_id DESC LIMIT 1";
		}	
		for($i=0;$i<count($this->questionArray);$i++) {	
			$SQLx = "SELECT question_prompt FROM surveyorgqsslocal WHERE org_id=" . $this->org_id . " AND question_id=" . $this->questionArray[$i]['question_id'] . " AND loc_id='" . $this->loc_id . "' " . $branch_query;
			$rsx = mysql_query($SQLx) or die ($SQLx . '<br />' . mysql_error());
			if($xrow = mysql_fetch_array($rsx)) {
				$this->questionArray[$i]['question_name']  = $xrow['question_prompt'];
				$this->questionArray[$i]['question_prompt'] = $xrow['question_prompt'];
			}
		}
		if($with_filters) {
			//get filters for the survey
			$this->filterArray = array();
			$SQL = "SELECT * FROM questiondependent WHERE survey_id=$survey_id ORDER BY group_id";
			$rs_dependent = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			$index = 0;		
			while($fields = mysql_fetch_array($rs_dependent)) {
				$this->filterArray[$index]['group_id'] = $fields['group_id'];
				$this->filterArray[$index]['question_id'] = $fields['question_id'];
				$this->filterArray[$index]['related_parname'] = $fields['related_parname'];
				$this->filterArray[$index]['selected_value'] = $fields['selected_value'];
				$this->filterArray[$index]['selected_count'] = $fields['selected_count'];
				$this->filterArray[$index++]['show_option'] = $fields['show_option'];
			}	
			$data['questions'] = $this->questionArray;
			$data['filters'] = $this->filterArray;
			return $data;
		} else {	
			return $this->questionArray;	
		}
	}
	function get_question_v1($question_id) {
		$SQL = "SELECT q.question_id, q.dimension AS dimension, q.question_name, q.varname as varname, g.sequence as sequence, ql.question_prompt, ql.preamble, ql.short_preamble FROM questions q, questionslocal ql WHERE q.question_id=$question_id q.question_id=ql.question_id AND AND ql.loc_id='" . $this->loc_id . "' LIMIT 1";
		$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
		if($fields = mysql_fetch_array($rs)) {
			$question['question_name'] = $fields['question_prompt'];
			$question['varname'] = $fields['varname'];
			$question['question_prompt'] = $fields['question_prompt'];
			$question['preamble'] = $fields['preamble'];
			$question['short_preamble'] = $fields['short_preamble'];
			$question['question_id'] = $fields['question_id'];
			$question['dimension'] = $fields['dimension'];
	
			$SQLx = "SELECT question_prompt FROM surveyorgqsslocal WHERE org_id=" . $this->org_id . " AND question_id=" . $question_id . " AND loc_id='" . $this->loc_id . "' LIMIT 1";
			$rsx = mysql_query($SQLx) or die ($SQLx . '<br />' . mysql_error());
			if($xrow = mysql_fetch_array($rsx)) {
				$question['question_name']  = $xrow['question_prompt'];
				$question['question_prompt'] = $xrow['question_prompt'];
			}
		}
		return $question;	
	}
	function get_form_questions($survey_id) {
		$index = 0;	
		$SQL = "SELECT sg.survey_id AS survey_id, sg.page_id AS page_id, sg.group_id AS group_id, sg.sequence AS sequence, g.group_type FROM surveypages sp, surveypagegroups sg, groups g WHERE sp.survey_id=sg.survey_id AND sp.page_id=sg.page_id AND sp.survey_id=$survey_id AND sg.group_id=g.group_id  ORDER BY page_id, sequence";
		$rs_page = mysql_query($SQL) or die ($SQL . "<br />" . mysql_error());
		while($page_fields = mysql_fetch_array($rs_page)) {
			//if($page_fields['group_type'] == "contact" || $page_fields['group_type'] == "address" || $page_fields['group_type'] == "hidden") continue;
			$required_query = '';
			if($page_fields['group_type'] == "contact" || $page_fields['group_type'] == "address") $required_query = " AND qp.required=1 ";
			$group_id = $page_fields['group_id'];
			$group_type = $page_fields['group_type'];
			$SQL = "SELECT q.question_id, q.dimension AS dimension, qp.question_part_no,qp.question_type, qp.question_format, qp.options_type, q.question_name, q.varname as varname, g.hide_on_report, g.readonly, g.show_value, g.hide_on_report, g.sequence as sequence, ql.question_prompt, ql.shortform, ql.preamble, ql.short_preamble FROM groupquestions g, questions q, questionslocal ql, questionparts qp WHERE g.group_id=" . $group_id . " AND g.question_id=q.question_id AND q.question_id=ql.question_id AND ql.loc_id='" . $this->loc_id . "' AND q.question_id=qp.question_id " . $required_query . " ORDER BY g.sequence, qp.question_part_no";
			$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			while($fields = mysql_fetch_array($rs)) {
				$this->questionArray[$index]['group_id'] = $group_id;
				$this->questionArray[$index]['group_type'] = $group_type;
				$this->questionArray[$index]['question_id'] = $fields['question_id'];
				$this->questionArray[$index]['question_type'] = $fields['question_type'];
				$this->questionArray[$index]['question_format'] = $fields['question_format'];
				$this->questionArray[$index]['options_type'] = $fields['options_type'];
				$this->questionArray[$index]['question_part_no'] = $fields['question_part_no'];
				$this->questionArray[$index]['question_name'] = $fields['question_prompt'];
				$this->questionArray[$index]['varname'] = $fields['varname'];
				$this->questionArray[$index]['hide_on_report'] = $fields['hide_on_report'];
				$this->questionArray[$index]['readonly'] = $fields['readonly'];
				$this->questionArray[$index]['show_value'] = $fields['show_value'];
				$this->questionArray[$index]['hide_on_report'] = $fields['hide_on_report'];
				$this->questionArray[$index]['question_prompt'] = $fields['question_prompt'];
				$this->questionArray[$index]['shortform'] = $fields['shortform'];
				$this->questionArray[$index]['preamble'] = $fields['preamble'];
				$this->questionArray[$index]['short_preamble'] = $fields['short_preamble'];
				$this->questionArray[$index++]['dimension'] = $fields['dimension'];
			}
		}	
		return $this->questionArray;	
	}
	function get_question_parts($question_id) {
		$questionPartArray = array();
		$index = 0;
		$SQL = "SELECT * FROM questionparts WHERE question_id=$question_id ORDER BY question_part_no";
		$rs = mysql_query($SQL) or die ($SQL . "<br />" . mysql_error());
		while($rows = mysql_fetch_array($rs)) {
			$questionPartArray[$index]['question_type'] = $rows['question_type'];
			$questionPartArray[$index]['question_format'] = $rows['question_format'];
			$questionPartArray[$index]['options_type'] = $rows['options_type'];
			$index++;	
		}
		return $questionPartArray;
	}

	function get_import_questions($survey_id) {
		$this->questionArray = array();
		if(isset($_SESSION['branch_id']) && $_SESSION['branch_id'] > 0) {
			$excluded_question_branch_query = " AND branch_id IN (" . $_SESSION['branch_id'] . ",0) ";
		} else {
			$excluded_question_branch_query = " AND branch_id=0 ";
		}
		if(isset($_SESSION['branch_id']) && $_SESSION['branch_id'] > 0) {
			$branch_query = " AND branch_id IN (" .  $_SESSION['branch_id'] . ",0) ORDER BY branch_id DESC LIMIT 1";
		} else {
			$branch_query = " AND branch_id=0 ORDER BY branch_id DESC LIMIT 1";
		}	
		$dimension_query = '';
		$index = 0;	
		$SQL = "SELECT sg.survey_id AS survey_id, sg.page_id AS page_id, sg.group_id AS group_id, sg.sequence AS sequence, g.group_type FROM surveypagegroups sg, groups g WHERE sg.survey_id=$survey_id AND sg.group_id=g.group_id ";
		$SQL .= " UNION ALL ";
		$SQL .= " SELECT sg.survey_id AS survey_id, sg.page_id AS page_id, sg.group_id AS group_id, sg.sequence AS sequence, g.group_type FROM surveyorgpagegroups sg, groups g WHERE sg.survey_id=$survey_id AND sg.org_id=" . $this->org_id . " AND sg.group_id=g.group_id ORDER BY page_id, sequence";
		$rs_page = mysql_query($SQL) or die ($SQL . "<br />" . mysql_error());
		while($page_fields = mysql_fetch_array($rs_page)) {
			//if($page_fields['group_type'] == "contact" || $page_fields['group_type'] == "address" || $page_fields['group_type'] == "library") continue;
			if($page_fields['group_type'] == "contact" || $page_fields['group_type'] == "address") continue;
			$SQL = "SELECT q.question_id, q.dimension AS dimension, q.question_name, q.varname as varname, g.sequence as sequence, ql.question_prompt, ql.preamble, ql.short_preamble FROM groupquestions g, questions q, questionslocal ql WHERE g.group_id=" . $page_fields['group_id'] . $dimension_query . " AND g.question_id NOT IN (SELECT DISTINCT question_id FROM surveyorgqsexcluded WHERE org_id=" . $this->org_id . $excluded_question_branch_query . " ) AND g.question_id=q.question_id AND q.question_id=ql.question_id AND ql.loc_id='" . $this->loc_id . "'";
			$SQL .= " UNION ALL ";
			$SQL .= "SELECT q.question_id, q.dimension AS dimension, q.question_name, q.varname as varname, g.sequence as sequence, ql.question_prompt, ql.preamble, ql.short_preamble  FROM surveyorggroupquestions g, questions q, questionslocal ql WHERE g.group_id=" . $page_fields['group_id'] . $dimension_query . "  AND g.org_id=" . $this->org_id . " AND g.question_id=q.question_id AND q.question_id=ql.question_id AND ql.loc_id='" . $this->loc_id . "' ORDER BY sequence";
			$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			while($fields = mysql_fetch_array($rs)) {
				$temp_q_prompt = $fields['question_prompt'];
				$SQLx = "SELECT question_prompt FROM surveyorgqsslocal WHERE org_id=" . $this->org_id . " AND question_id=" . $fields['question_id'] . " AND loc_id='" . $this->loc_id . "' " . $branch_query;
				$rsx = mysql_query($SQLx) or die ($SQLx . '<br />' . mysql_error());
				if($xrow = mysql_fetch_array($rsx)) {
					$temp_q_prompt = $xrow['question_prompt'];
				}
				$SQL = "SELECT qp.question_part_no, qp.question_type, qp.question_format, qhl.scale_text FROM questionparts qp LEFT JOIN qoptionsheaderlocal qhl ON (qp.question_id=qhl.question_id AND qp.question_part_no=qhl.question_part_no AND qhl.loc_id='" . $this->loc_id . "') WHERE qp.question_id=" . $fields['question_id'] . " ORDER BY qp.question_part_no";
				$rsh = mysql_query($SQL) or die ($SQL . "<br />" . mysql_error());
				while($rowh = mysql_fetch_array($rsh)) {
					$this->questionArray[$index]['question_name'] = $temp_q_prompt;
					$this->questionArray[$index]['varname'] = $fields['varname'];
					$this->questionArray[$index]['question_prompt'] = $temp_q_prompt;
					$this->questionArray[$index]['preamble'] = $fields['preamble'];
					$this->questionArray[$index]['short_preamble'] = $fields['short_preamble'];
					$this->questionArray[$index]['scale_text'] = $rowh['scale_text'];
					$this->questionArray[$index]['question_id'] = $fields['question_id'];
					$this->questionArray[$index]['question_part_no'] = $rowh['question_part_no'];
					$this->questionArray[$index]['question_type'] = $rowh['question_type'];
					$this->questionArray[$index]['question_format'] = $rowh['question_format'];
					$this->questionArray[$index++]['dimension'] = $fields['dimension'];
				}	
			}
		}	
		return $this->questionArray;	
	}
	function get_group($group_id, $survey_id=-1) {
		$SQL = "SELECT g.group_type,g.group_name,g.required,glocal.group_prompt,glocal.preamble,glocal.group_description FROM groups g, groupslayout glayout LEFT JOIN groupslocal glocal ON (glayout.group_id=glocal.group_id AND glocal.loc_id='" . $this->loc_id . "') WHERE g.group_id=glayout.group_id LIMIT 1";
		$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
		if($fields = mysql_fetch_array($rs)) {
			$group['group_name'] = $fields['group_name'];
			$group['group_prompt'] = $fields['group_prompt'];
			$group['preamble'] = $fields['preamble'];
			$group['group_description'] = $fields['group_description'];
			$group['group_type'] = $fields['group_type'];
			$group['display'] = 1;
			$group['own'] = 0;
			$SQL = " SELECT group_id FROM surveyorgpagegroups sg WHERE sg.survey_id=$survey_id AND sg.org_id=" . $this->org_id . " AND sg.group_id=$group_id LIMIT 1";
			$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			if(mysql_num_rows($rs) > 0) {
				$group['own'] = 1;
			}
			$SQL = "SELECT new_group_id FROM surveyorggrpsreplaced WHERE orig_group_id=$group_id LIMIT 1";
			$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			if($fields = mysql_fetch_array($rs)) {
				$group['new_group_id'] = $fields['new_group_id'];
				if($group['new_group_id'] == 0) {
					$group['display'] = 0;
				} 
			}
		}
		return $group;
	}
	function get_question($question_id, $with_options=false) {
		//get original question
		$SQL = "SELECT q.question_id, q.dimension AS dimension, q.question_name, q.varname as varname, ql.question_prompt, ql.preamble, ql.short_preamble FROM questions q, questionslocal ql WHERE q.question_id=$question_id AND q.question_id=ql.question_id AND ql.loc_id='" . $this->loc_id . "' LIMIT 1";
		$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
		$fields = mysql_fetch_array($rs);
		$question['question_name'] = $fields['question_prompt'];
		$question['varname'] = $fields['varname'];
		$question['question_prompt'] = $fields['question_prompt'];
		$question['preamble'] = $fields['preamble'];
		$question['short_preamble'] = $fields['short_preamble'];
		$question['question_id'] = $fields['question_id'];
		$question['dimension'] = $fields['dimension'];
		$question['display'] = 1;
		$SQL = "SELECT DISTINCT question_id FROM surveyorgqsexcluded WHERE org_id=" . $this->org_id . " AND question_id=$question_id LIMIT 1";
		$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
		if(mysql_num_rows($rs) > 0) {
			$question['display'] = 0;
		}
		//check question_prompt updated
		$SQL = "SELECT * FROM surveyorgqsslocal WHERE org_id=" . $this->org_id . " AND question_id=$question_id AND loc_id='" . $this->loc_id . "' LIMIT 1";
		$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
		if($fields = mysql_fetch_array($rs)) {
			$question['question_name'] = $fields['question_prompt'];
			$question['question_prompt'] = $fields['question_prompt'];
			$question['shortform'] = $fields['shortform'];
			$question['preamble'] = $fields['preamble'];
			$question['short_preamble'] = $fields['short_preamble'];
		}
		
		if($with_options) {
			$questionPartArray = array();
			$SQLp = "SELECT qp.question_part_no, qp.question_type, qp.question_format, qp.options_type, qp.options, qp.options_value_max, qp.options_value_min, qp.options_value_default, qhl.scale_text, qhl.low_scale_text, qhl.high_scale_text, qhl.part_prompt, qhl.gap_type";
			$SQLp .= " FROM questionparts qp LEFT JOIN qoptionsheaderlocal qhl ON (qp.question_id=qhl.question_id AND qp.question_part_no=qhl.question_part_no AND qhl.loc_id='" . $this->loc_id . "') WHERE  qp.question_id=" . $question_id . " ORDER BY qp.question_part_no";
			$rsp = mysql_query($SQLp) or die($SQLp . '<br />' . mysql_error());
			$pindex = 0;
			while($rowp = mysql_fetch_array($rsp)) {
				$questionPartArray[$pindex]['question_part_no'] = $rowp['question_part_no'];
				$questionPartArray[$pindex]['question_type'] = $rowp['question_type'];
				$questionPartArray[$pindex]['question_format'] = $rowp['question_format'];
				$questionPartArray[$pindex]['options_type'] = $rowp['options_type'];
				$questionPartArray[$pindex]['options'] = $rowp['options'];
				$questionPartArray[$pindex]['options_value_max'] = $rowp['options_value_max'];
				$questionPartArray[$pindex]['options_value_min'] = $rowp['options_value_min'];
				$questionPartArray[$pindex]['options_value_default'] = $rowp['options_value_default'];
				$questionPartArray[$pindex]['scale_text'] = $rowp['scale_text'];
				$questionPartArray[$pindex]['low_scale_text'] = $rowp['low_scale_text'];
				$questionPartArray[$pindex]['high_scale_text'] = $rowp['high_scale_text'];
				$questionPartArray[$pindex]['part_prompt'] = $rowp['part_prompt'];
				$questionPartArray[$pindex]['gap_type'] = $rowp['gap_type'];
				if($rowp['question_type'] == "option" || $rowp['question_type'] == "multi") {
					$questionPartArray[$pindex]['options'] = $this->get_options($question_id, $rowp['question_part_no']);
				} else {
					$questionPartArray[$pindex]['options'] = null;
				}	
				$pindex++;
			}
			$question['questionparts'] = $questionPartArray;
		}
		return $question;
	}
	function get_options($question_id, $question_part_no=1, $with_others=true) {
		$sql = "SELECT * FROM questionparts WHERE question_id=$question_id AND question_part_no=$question_part_no LIMIT 0,1";
		$rs_part = mysql_query($sql) or die ($sql . '<br />' . mysql_error());
		$part_index = 0;
		$last_option_subheader = "";
		$question_part_fields = mysql_fetch_array($rs_part);
		$ws_url = $question_part_fields['ws_url'];
		if(strlen($ws_url) > 5) {
			$varname = '';
			$sql = "SELECT varname FROM questions WHERE question_id=$question_id LIMIT 0,1";
			$rs = mysql_query($sql) or die ($sql . '<br />' . mysql_error());
			$row = mysql_fetch_array($rs);
			if($row['varname'] != '') $varname = trim($row['varname']);

			if(strpos($ws_url, 'http://') === false) $ws_url = 'http://ws.countingopinions.com/' . $ws_url;
			$qoptionsArray = array();
			$params = '&q=1&org=' . $this->org_id;
			if($varname != '') $params .= '&vname=' . $varname;
			$responses = file_get_contents($ws_url . $params);
			$qoptionsArray = json_decode($responses, true);
			return $qoptionsArray;
		}
		$org_options = $question_part_fields['org_options'];
		$list_id = $question_part_fields['list_id'];
		if($list_id < 1) $list_id = $question_id;
		
		$sql = '';
		$org_id = $this->org_id;
		if($org_options == 1) {
			$sql = "SELECT o.option_value AS option_value,o.option_no AS option_no,ol.option_name AS option_name,ol.option_prompt AS option_prompt, ol.subheader AS subheader FROM orgqoptions o, orgqoptionslocal ol WHERE o.org_id=ol.org_id AND o.org_id=$org_id AND o.question_part_no=$question_part_no AND o.question_id=ol.question_id AND o.question_part_no=ol.question_part_no AND o.option_no=ol.option_no AND o.question_id=$question_id AND o.question_part_no=$question_part_no AND ol.loc_id='" . $this->loc_id . "' ORDER BY o.option_no";
			if($list_id > 0) {
				//get options from own org_id if exist?
				$sql = "SELECT o.option_value AS option_value,o.option_no AS option_no,ol.option_name AS option_name,ol.option_prompt AS option_prompt, '' AS subheader FROM sloptions o, sloptionslocal ol WHERE o.org_id=ol.org_id AND o.org_id=$org_id AND o.question_part_no=$question_part_no AND o.list_id=ol.list_id AND o.question_part_no=ol.question_part_no AND o.option_no=ol.option_no AND o.list_id=$list_id AND ol.loc_id='" . $this->loc_id . "' ORDER BY o.sequence";
				$rs_temp = mysql_query($sql) or die ($sql . "<br />" . mysql_error());
				if(mysql_num_rows($rs_temp) == 0) {
					//get options from org_id=0
					$sql = "SELECT o.option_value AS option_value,o.option_no AS option_no,ol.option_name AS option_name,ol.option_prompt AS option_prompt, '' AS subheader FROM sloptionslocal ol,sloptions o WHERE o.org_id=ol.org_id AND o.org_id=0 AND o.question_part_no=$question_part_no AND o.list_id=ol.list_id AND o.question_part_no=ol.question_part_no AND o.option_no=ol.option_no AND o.list_id=$list_id AND ol.loc_id='" . $this->loc_id . "' ORDER BY o.option_no";
					$rs_temp = mysql_query($sql) or die ($sql . "<br />" . mysql_error());
					if(mysql_num_rows($rs_temp) == 0) {
						//get options from other org_id
						$sql = "SELECT o.option_value AS option_value,o.option_no AS option_no,ol.option_name AS option_name,ol.option_prompt AS option_prompt, '' AS subheader FROM sloptionslocal ol,sloptions o WHERE o.org_id=ol.org_id AND o.question_part_no=$question_part_no AND o.list_id=ol.list_id AND o.question_part_no=ol.question_part_no AND o.option_no=ol.option_no AND o.list_id=$list_id AND ol.loc_id='" . $this->loc_id . "' ORDER BY o.option_no";
					}
				}	
			}
		} else {
			$sql = "SELECT o.option_value AS option_value,o.option_no AS option_no,ol.option_name AS option_name,ol.option_prompt AS option_prompt, g.group_prompt AS subheader FROM sloptionslocal ol, sloptions o LEFT JOIN sloptiongroupslocal g ON g.group_id=o.group_id AND g.loc_id='en_CA' AND g.org_id=o.org_id WHERE o.org_id=ol.org_id AND o.org_id=$org_id AND o.question_part_no=$question_part_no AND o.list_id=ol.list_id AND o.question_part_no=ol.question_part_no AND o.option_no=ol.option_no AND o.list_id=$list_id AND ol.loc_id='" . $this->loc_id . "' ORDER BY o.sequence";
			$rs_temp = mysql_query($sql) or die ($sql . "<br />" . mysql_error());
			if(mysql_num_rows($rs_temp) > 0) {
				$sql = "SELECT o.option_value AS option_value,o.option_no AS option_no,ol.option_name AS option_name,ol.option_prompt AS option_prompt, '' AS subheader FROM sloptionslocal ol,sloptions o WHERE o.org_id=ol.org_id AND o.org_id=$org_id AND o.question_part_no=$question_part_no AND o.list_id=ol.list_id AND o.question_part_no=ol.question_part_no AND o.option_no=ol.option_no AND o.list_id=$list_id AND ol.loc_id='" . $this->loc_id . "' ORDER BY o.option_no";
			} else {
				//get options from org_id=0
				$sql = "SELECT o.option_value AS option_value,o.option_no AS option_no,ol.option_name AS option_name,ol.option_prompt AS option_prompt, '' AS subheader FROM sloptionslocal ol,sloptions o WHERE o.org_id=ol.org_id AND o.org_id=0 AND o.question_part_no=$question_part_no AND o.list_id=ol.list_id AND o.question_part_no=ol.question_part_no AND o.option_no=ol.option_no AND o.list_id=$list_id AND ol.loc_id='" . $this->loc_id . "' ORDER BY o.option_no";
				$rs_temp = mysql_query($sql) or die ($sql . "<br />" . mysql_error());
				if(mysql_num_rows($rs_temp) == 0) {
					//get options from other org_id
					$sql = "SELECT o.option_value AS option_value,o.option_no AS option_no,ol.option_name AS option_name,ol.option_prompt AS option_prompt, '' AS subheader FROM sloptionslocal ol,sloptions o WHERE o.org_id=ol.org_id AND o.question_part_no=$question_part_no AND o.list_id=ol.list_id AND o.question_part_no=ol.question_part_no AND o.option_no=ol.option_no AND o.list_id=$list_id AND ol.loc_id='" . $this->loc_id . "' ORDER BY o.option_no";
				}

			}	
		}
		$rs_options = mysql_query($sql) or die ($sql . '<br />' . mysql_error());
		$qoptionsArray = array();
		$ind = 0;
		while($qoptions_fields = mysql_fetch_array($rs_options)) {
			if($with_others == false && (trim($qoptions_fields['option_prompt']) == "OTHER" || trim($qoptions_fields['option_name']) == "OTHER")) continue;
			$qoptionsArray[$ind]['option_value'] = $qoptions_fields['option_value'];
			$qoptionsArray[$ind]['option_no'] = $qoptions_fields['option_no'];
			//$qoptionsArray[$ind]['option_name'] = $qoptions_fields['option_name'];
			$qoptionsArray[$ind]['option_name'] = $qoptions_fields['option_prompt'];
			$qoptionsArray[$ind]['option_prompt'] = $qoptions_fields['option_prompt'];
			$qoptionsArray[$ind]['subheader'] = $qoptions_fields['subheader'];
			$ind++;
		}
		return $qoptionsArray;
	}
	//customer extension
	function get_custom_fields($survey_id=-1, $branch_id=0) {
		$SQL = "SELECT * FROM orgfields WHERE org_id=" . $this->org_id . " AND is_filter=1 ORDER BY field_index";
		$rs_fields = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
		if(mysql_num_rows($rs_fields) < 1) return null;
		$fieldArray = array();
		$index = 0;
		$survey_query = "";
		if($survey_id > 0) $survey_query = " AND survey_id=" . $survey_id;
		$branch_query = "";
		if($branch_id > 0) $branch_query = " AND branch_id=" . $branch_id;
		while($fields = mysql_fetch_array($rs_fields)) {
			$fieldArray[$index]['field_index'] = $fields['field_index'];
			$fieldArray[$index]['field_name'] = $fields['field_name'];
			$fieldArray[$index]['field_prompt'] = $fields['field_prompt'];
			$fieldArray[$index]['field_description'] = $fields['description'];
			$fieldArray[$index]['options'] = null;
			$fieldArray[$index]['selectedoptions'] = null;
			$optionArray = array();
			$oindex = 0;
			$SQL = "SELECT DISTINCT " . $fieldArray[$index]['field_name'] . " AS option_value  FROM respondentfields rf WHERE org_id=" . $this->org_id . $survey_query . $branch_query . " ORDER BY option_value";
			$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			while($rows = mysql_fetch_array($rs)) {
				if($rows['option_value'] == "") continue;
				$optionArray[$oindex++]['option_value'] = $rows['option_value'];
			}
			$fieldArray[$index++]['options'] = $optionArray;
		}		
		return $fieldArray;
	}	
	//customer extension
	function get_custom_surveys($branch_id=0) {
		$SQL = "SELECT DISTINCT survey_id FROM  respondentfields WHERE org_id=" . $this->org_id;
		if($branch_id > 0) $SQL .= " AND branch_id=" . $branch_id;
		$rs_fields = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
		if(mysql_num_rows($rs_fields) < 1) return null;

		$customSurveyArray = array();
		$index = 0;
		while($fields = mysql_fetch_array($rs_fields)) {
			$customSurveyArray [$index++]['survey_id'] = $fields['survey_id'];
		}		
		return $customSurveyArray;
	}
	
	function delete_respondent($respondent_id, $survey_id=-1) {	
		if($respondent_id < 0) return;

		if($survey_id < 0) {
			$SQL = "SELECT survey_id FROM respondentsurveys WHERE respondent_id=$respondent_id LIMIT 1";
			$rs = mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
			if($row = mysql_fetch_array($rs)) {
				$survey_id = $row['survey_id'];
			}	
		}
		if($survey_id < 0) return;
		
		$SQL = " DELETE FROM respondents WHERE respondent_id=$respondent_id LIMIT 1";
		mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());

		$SQL = " DELETE FROM respondentsurveys WHERE respondent_id=$respondent_id";
		mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());

		$SQL = " DELETE FROM respondentprofile WHERE respondent_id=$respondent_id LIMIT 1";
		mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
		
		$SQL = " DELETE FROM responses WHERE respondent_id=$respondent_id";
		mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());

		$SQL = " DELETE FROM multiresponses WHERE respondent_id=$respondent_id";
		mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());

		$SQL = " DELETE FROM comments WHERE respondent_id=$respondent_id";
		mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());

		$SQL = " DELETE FROM commentassignment WHERE respondent_id=$respondent_id";
		mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());

		$SQL = " DELETE FROM responsevalues WHERE respondent_id=$respondent_id";
		mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());

		$SQL = " DELETE FROM responseothers WHERE respondent_id=$respondent_id";
		mysql_query($SQL) or die ($SQL . '<br />' . mysql_error());
		
		$survey_id = $survey_id;
		$dbname = 'surveys';
		$collection_name = "survey_" . $survey_id;
		
		try {
		    // a new MongoDB connection
		    $conn = new Mongo("mongodb://10.1.172.23:27017");
			
		    // connect to test database
		    $db = $conn->$dbname;
		    //collection object
		    $collection = $db->$collection_name;
		   	if ($collection->findOne() === null) {
		    	$mongodb = 0;
		    } else {
		    	$mongodb = 1;
		    	$collection->remove(array("rid"=>intval($respondent_id)));
			}
		    $conn->close();
		} catch ( MongoConnectionException $e ) {
		    echo $e->getMessage();
		} catch ( MongoException $e ) {
		    echo $e->getMessage();
		}
	}
}	
?>