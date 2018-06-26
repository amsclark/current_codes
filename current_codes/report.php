<?php 

/*********************************/
/* 2017 Alex Clark       */
/*********************************/

chdir('../../');

require_once ('pika-danio.php'); 
pika_init();

require_once('pikaMisc.php');


$report_title = "Current Codes Report";
$report_name = "current_codes";

$tablenames = array();

$base_url = pl_settings_get('base_url');
if(!pika_report_authorize($report_name)) {
	$main_html = array();
	$main_html['base_url'] = $base_url;
	$main_html['page_title'] = $report_title;
	$main_html['nav'] = "<a href=\"{$base_url}/\">Pika Home</a>
    				  &gt; <a href=\"{$base_url}/reports/\">Reports</a> 
    				  &gt; $report_title";
	$main_html['content'] = "You are not authorized to run this report";

	$buffer = pl_template('templates/default.html', $main_html);
	pika_exit($buffer);
}
$usersortorder = pl_grab_post('usersort');

	require_once ('app/lib/plHtmlReportTable.php');
	require_once ('app/lib/plHtmlReport.php');
	$t = new plHtmlReport();

$t->set_title($report_title);

echo "<!DOCTYPE HTML>\n";
echo "<html>\n";
echo "<head>\n";
echo "<title>Current Codes Report</title>\n";
echo "<link rel=\"stylesheet\" href=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css\">\n";
echo "<script src=\"https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js\"></script>\n";
echo "<script src=\"https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js\"></script>\n";
echo "<link rel=\"stylesheet\" href=\"../../css/custom.css\">\n";
echo "<script>\n";
echo 'function filterTableOn(tableName,filterName,column) {' . PHP_EOL;
echo 'console.log(tableName.id);' . PHP_EOL;
echo 'console.log(filterName.id);' . PHP_EOL;
echo 'var input, filter, table, tr, td, i;' . PHP_EOL;
echo 'input = document.getElementById(filterName.id);' . PHP_EOL;
echo 'filter = input.value.toUpperCase();' . PHP_EOL;
echo 'table = document.getElementById(tableName.id);' . PHP_EOL;
echo 'tr = table.getElementsByTagName("tr");' . PHP_EOL;

echo 'for (i = 0; i < tr.length; i++) {' . PHP_EOL;
echo '   td = tr[i].getElementsByTagName("td")[column];' . PHP_EOL;
echo '   if (td) {' . PHP_EOL;
echo '     if (td.innerHTML.toUpperCase().indexOf(filter) == 0) {' . PHP_EOL;
echo '       tr[i].style.display = "";' . PHP_EOL;
echo '     } else {' . PHP_EOL;
echo '       tr[i].style.display = "none";' . PHP_EOL;
echo '     }' . PHP_EOL;
echo '   } ' . PHP_EOL;
echo ' }' . PHP_EOL;
echo '}' . PHP_EOL;
echo "</script>\n";
echo "</head>\n";
echo "<body>\n";


// build the SQL statement, based on user input
$menu_prefix = "menu_%";
$sql = "show tables like '$menu_prefix'"; 



				
// execute the SQL statement, format the results, and add to the table object
$result = mysql_query($sql) or trigger_error();

//Let's create our report navigation menu
echo "<a name=\"top\"></a>\n";
echo "<h1>Current Codes</h1>\n";
echo "<div class=\"fivecolumns\">\n";
while(($row = mysql_fetch_row($result))) {
    echo "<a href=\"#$row[0]\">" . $row[0] . "</a><br>\n";
}
echo "<a href=\"#users\">" . "Users" . "</a><br>\n";
echo "<a href=\"#referrals\">" . "Referral Agencies" . "</a><br>\n";
echo "</div>\n";

//we want to loop through from the beginning of the result set again now that we created our menu
mysql_data_seek($result, 0);

while(($row =  mysql_fetch_row($result))) {
    echo "<div class=\"container\">\n";
    echo "<a name=\"$row[0]\"></a>\n";
    echo "<h3>" . $row[0] . "</h3>\n";
    echo "<a href=\"#top\">Back to top</a>\n";
    echo '&nbsp;&nbsp;' . PHP_EOL;
    echo '<input type="text" id="';
    echo "$row[0]Filter";
    echo '" onkeyup="filterTableOn(';
    echo "$row[0]Table";
    echo ',';
    echo "$row[0]Filter";
    echo ',0)" placeholder="Filter by Code">';
    echo '&nbsp;&nbsp;' . PHP_EOL;
    echo '<input type="text" id="';
    echo "$row[0]FilterM";
    echo '" onkeyup="filterTableOn(';
    echo "$row[0]Table";
    echo ',';
    echo "$row[0]FilterM";
    echo ',1)" placeholder="Filter by Meaning">' . PHP_EOL; 
    echo "<table class=\"table table-striped table-bordered\" id=\"$row[0]Table\">\n";
    
    echo "<tr><th>Code</th><th>Meaning</th></tr>\n";
    $sql_table_list = "select * from $row[0]";
    $result_table_list = mysql_query($sql_table_list) or trigger_error();
    while(($result_table_row = mysql_fetch_row($result_table_list))) {	
    	echo "<tr><td>" . $result_table_row[0] . "</td><td>" . $result_table_row[1] . "</td></tr>" . PHP_EOL;
	}
    echo "</table>\n";
    echo "</div>\n";
}

$sql2 = "select user_id, concat(`first_name`, ' ', `last_name`), enabled from users order by enabled desc";
switch ($usersortorder) {
  case "idnum":
    $sql2 .= ", user_id";
    break;
  case "last":
    $sql2 .= ", last_name";
    break;
  default:
} 
$result2 = mysql_query($sql2) or trigger_error();

echo "<div class=\"container\">\n";
echo "<a name=\"users\"></a>\n";
echo "<h3>users</h3>\n";
echo "<a href=\"#top\">Back to top</a>\n";

echo '&nbsp;&nbsp;' . PHP_EOL;
echo '<input type="text" id="';
echo "FilterU";
echo '" onkeyup="filterTableOn(';
echo "usersTable";
echo ',';
echo "FilterU";
echo ',0)" placeholder="Filter by User ID">' . PHP_EOL;
echo '&nbsp;&nbsp;' . PHP_EOL;
echo '<input type="text" id="';
echo "FilterN";
echo '" onkeyup="filterTableOn(';
echo "usersTable";
echo ',';
echo "FilterN";
echo ',1)" placeholder="Filter by Name">' . PHP_EOL;

echo "<table class=\"table table-striped table-bordered\" id=\"usersTable\">\n";
echo "<tr><th>User_id</th><th>Name</th><th>Enabled</th></tr>\n";
while(($row2 = mysql_fetch_row($result2))) {
    echo "<tr><td>" . $row2[0] . "</td><td>" . $row2[1] . "</td><td>" . $row2[2] . "</td></tr>\n";
}
echo "</table>\n";
echo "</div>\n";


//Get all the Referral Agency Names and contact ID numbers
$sql3 = "select distinct first_name, last_name, contact_id from (select first_name, last_name, contact_id from (select contacts.first_name, contacts.last_name, conflict.contact_id, conflict.relation_code from conflict left join contacts on contacts.contact_id = conflict.contact_id) t where relation_code=50) u";
$result3 = mysql_query($sql3) or trigger_error();

echo "<div class=\"container\">\n";
echo "<a name=\"referrals\"></a>\n";
echo "<h3>Referral Agencies</h3>\n";
echo "<a href=\"#top\">Back to top</a>\n";

echo '&nbsp;&nbsp;' . PHP_EOL;
echo '<input type="text" id="';
echo "FilterC";
echo '" onkeyup="filterTableOn(';
echo "referralsTable";
echo ',';
echo "FilterC";
echo ',2)" placeholder="Filter by Contact ID">' . PHP_EOL;
echo '&nbsp;&nbsp;' . PHP_EOL;
echo '<input type="text" id="';
echo "FilterT";
echo '" onkeyup="filterTableOn(';
echo "referralsTable";
echo ',';
echo "FilterT";
echo ',1)" placeholder="Filter by Last/Agency Name">' . PHP_EOL;

echo "<table class=\"table table-striped table-bordered\" id=\"referralsTable\">\n";
echo "<tr><th>First Name</th><th>Last/Agency Name</th><th>Contact ID</th></tr>\n";

while (($row3 = mysql_fetch_row($result3))) {
    echo "<tr><td>" . $row3[0] . "</td><td>" . $row3[1] . "</td><td>" . $row3[2] . "</td></tr>\n";
}
echo "</table>\n";
echo "</div>\n";
echo "</body>\n";
echo "</html>\n";

exit();

?>
