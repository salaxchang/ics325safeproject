<?php
$nav_selected = "PIPLANNING";
$left_buttons = "YES";
$left_selected = "CAPACITY";
include("./nav.php");

global $db;
?>
<!--Customer Bears style sheet-->
<link rel="stylesheet" type="text/css" href="styleCustom.css">
<h3>Capacity Calculator</h3>
<?php
$page_title = 'Capacity Calculator<';
include("./db_connection.php");

//pull BASE_URL from json file
$url_file = file_get_contents("dataFiles/url_cache.json");
$url_json = json_decode($url_file, true);
$x=count($url_json);
for($i = 0; $i < $x; $i++){
  $base_url_out = $url_json[$i]['value'];
}
//initializes remaining variables
$pi_id="";
$art="";
$pi_id_menu='';
$pi_id_select='';
$duration = '';
$overhead_percentage = '';

  //Checks for ART Cookie, if it is not available it will update the cookie with a default value using the artCookie function
  if(!isset($_COOKIE['artCookie'])){
    //established finds the value to use for the ART cookie
    $art_select = setArtCookie();
  } else {
    $art_select = $_COOKIE['artCookie'];
  };

//Function that uses json file to build ART select menu. Updates selected default with the Cookie value
$art = buildArtMenu($art_select);

//uses the pi Select Now function to identify the PI ID within the current date and adds it to the pi id select variable for the default
$pi_id_select = piSelectNow();

//capturing the pi id cookie to use for the array and build the menu list
if(isset($_COOKIE['piCookie'])){
  $pi_id = $_COOKIE['piCookie'];
  $pi_id_menu = buildPi_idMenu($pi_id);
} else {
  $pi_id=$pi_id_select;
  $pi_id_menu = buildPi_idMenu($pi_id);
};
//Function for assigning the duration variable
$duration = getDuration($pi_id_select);


//Function for assigning the overhead percentage
$overhead_percentage = getOverheadPercentage();
?>

<!--
form for submitting data that will be prepopulated with data from the variables
-->
  <form  method="POST" id="PI_form" name="PI_form">
    <table id="form_table" class="container">
      <tr>
        <td></td>
        <td>
          <input type="hidden" id="baseUrl" name="baseUrl" readonly="readonly" value="<?php echo $base_url_out; ?>">
        </td>
      </tr>
      <tr>
        <td>Agile Release Train:</td>
        <td>
          <select id="art" name="art" onchange="
          //sets art select to selected value
          var art_select = this.value;
          //sets the selected value as the cookie
          document.cookie = escape('artCookie') + '=' + escape(art_select) ;
          //updates the teams list
          getTeams(art_select);
          location.reload();
          ">
          <option value="">-- Select --</option>
          <?php echo $art; ?>
        </select>
      </td>
    </tr>
    <tr>
      <td>Agile Team:</td>
      <td>
        <select id="teams" onchange="
      //sets team_select to selected value
      var team_select = this.value;
      //sets the selected value as the cookie
      document.cookie = escape('teamSelectCookie') + '=' + escape(team_select);
      location.reload();">

      </select>
      </td>
    </tr>
    <tr>
    <td>Program Increment (PI):</td>
    <td>
      <select id="PI_ID" name="pi_id" onchange="
      //sets pi_select to selected value
      var pi_select = this.value;
      //sets the selected value as the cookie
      document.cookie = escape('piCookie') + '=' + escape(pi_select) ;
      location.reload();">
      <?php echo $pi_id_menu; ?>
    </select>
  </td>
</tr>
<tr>
<td><input type="submit" id="php_button" onclick="updateEmployeeTable()" name="generate_button" class="button" value="Generate"></td>
<td></td>
</tr>
</table>
</form><br>

<script>
//assigning the artCookie to a variable
var artCookie = getCookie('artCookie');
//running the getTeams when the window is loaded using the cookie
$( window ).on( "load", getTeams(artCookie) );
function getTeams(art_select){
  //gets values from JSON file
  $.getJSON('dataFiles/at_cache.json', function(data){
    //initializes an array to story the avilable teams
      var at_list = [];
      //for loop for adding team names to teams_list
      var x=data.length;
      for(var i=0; i < x ; i++){
        var parent = data[i].parent_name;
        if(parent == art_select){
          at_list.push(data[i].team_name);
        }
        //updates teams with the calculated list
        //document.getElementById('teams').value = at_list;
        var select = document.getElementById("teams");
        select.options.length = 0;
        for(index in at_list) {
          select.options[select.options.length] = new Option(at_list[index], index);
        }
      };
    });
  };

  //function for capturing the cookie
  function getCookie(cookieName) {
    var name = cookieName + "=";
    var decodedCookie = decodeURIComponent(document.cookie);
    var ca = decodedCookie.split(';');
    for(var i = 0; i <ca.length; i++) {
      var c = ca[i];
      while (c.charAt(0) == ' ') {
        c = c.substring(1);
      }
      if (c.indexOf(name) == 0) {
        return c.substring(name.length, c.length);
      }
    }
    return "";
  };
  </script>

  <form method="post" action="#" id="maincap">
  <?php
  //Defaulting the selected team, this will need to be updated once the table have additional values available
  $selected_team='Agile Team 1000 1';
  //settting up the pi id array for the Iteration # display
  $pi_id_array=array($pi_id."-1", $pi_id."-2" ,$pi_id."-3" ,$pi_id."-4", $pi_id."-5",$pi_id."-6",$pi_id."-IP");
  //Iteration # display
  $numberIT = 1;
  $count_piid = count($pi_id_array);
  //Loop for displaying the series of Employee table & iteration calculation placeholder
  for($i = 0; $i < $count_piid; $i++){
    echo '<h4>Iteration #' .$numberIT .': ' .$pi_id_array[$i]; 
    buildEmployeeTable($selected_team,$duration,$overhead_percentage, $pi_id_array[$i]);
    $numberIT++;
  };

  //takes the selected values and creates a json

  //$result->close();
  ?>


</form>
</td>
</tr>
</table>
</div>
</div>

<?php
include("./footer.php");
?>
