<?php
// Configure the MySQL connection  
$sql_host = "localhost";        // MySQL host connection.
$sql_user = "counting_survey";  // MySQL username.
$sql_pass = "SURvey&369";        // MySQL password.
$sql_data = "counting_survey";  // MySQL database.

$link2 = mysql_connect($sql_host, $sql_user, $sql_pass);

if (!$link2)
{
	require_once('/home/copinion/public_html/includes/maintenance.php');
    //die('Could not connect: ' . mysql_error());
    exit;
}

mysql_select_db($sql_data, $link2) or die (mysql_error());
mysql_query("SET NAMES 'utf8'");

?>
