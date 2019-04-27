<?php

  $nav_selected = "PIPLANNING";
  $left_buttons = "YES"; 
  $left_selected = "CALCULATE";

  include("./nav.php");
  include("./db_connection.php");
  global $db;


 
  echo'<!--Copies in Bears custom stylesheet-->
  <link rel="stylesheet" type="text/css" href="styleCustom.css">';
  //Checks for ART Cookie, if it is not available it will update the cookie with a default value using the artCookie function
//initializes remaining variables
$program_increment="";
$art="";
$program_increment_menu='';
$program_increment_select='';
$duration = '';
$overhead_percentage = getOverheadPercentage();
$default_total = 56;

  //Checks for ART Cookie, if it is not available it will update the cookie with a default value using the artCookie function
  if(!isset($_COOKIE['artCookie'])){
    //established finds the value to use for the ART cookie
    $art_select = setArtCookie();
  } else {
    $art_select = $_COOKIE['artCookie'];
  };

  //checks if a team has been selected. If it has not then if finds the default team name and applies it to the team variable
  if(!isset($_COOKIE['teamSelectCookie'])){
  //sets the default team name
  $team = getDefaultTeamName($art_select);
  $selected_team = getTeamID($team);
  setcookie('teamSelectCookie', $selected_team );
  } else {
    $selected_team  = $_COOKIE['teamSelectCookie'];
  };

  //finds the team id for the team name for the selected team script
  
//Function that uses json file to build ART select menu. Updates selected default with the Cookie value
$art = buildArtMenu($art_select);

//uses the pi Select Now function to identify the PI ID within the current date and adds it to the pi id select variable for the default
$program_increment_select = piSelectNow();

//capturing the pi id cookie to use for the array and build the menu list
if(isset($_COOKIE['piCookie'])){
  $program_increment = $_COOKIE['piCookie'];
  $program_increment_menu = buildPi_idMenu($program_increment);
} else {
  $program_increment=$program_increment_select;
  setcookie('piCookie', $program_increment_select);
  $program_increment_menu = buildPi_idMenu($program_increment);
};
//Function for assigning the duration variable
$duration = getDuration($program_increment_select);
//initializes the totalcapacity variable
$sql = "SELECT * FROM `capacity` WHERE program_increment='".$program_increment."' AND team_id='".$selected_team."'";
$result = $db->query($sql);
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (isset($teamcapacity)  && !isset($_POST['restore'])  && !isset($_POST['submit0'])){
      $totalcapacity = $row["total"] ;
    }else{
      $totalcapacity = $row["total"];
    }
} else {
  
  if (!isset($teamcapacity)  && !isset($_POST['restore'])  && !isset($_POST['submit0'])){
    $totalcapacity = ($default_total*6);
  }else{
    $totalcapacity = $default_total*6;
  }
};
//Function for assigning the overhead percentage
$overhead_percentage = getOverheadPercentage();
?>

<!--
form for submitting data that will be prepopulated with data from the variables
-->
<div class="right-content" >
    <div class="container">
  <form  method="POST" id="PI_form" name="PI_form">
    <table id="form_table" class="container">
      <tr>
        <td></td>
        <td>
          <input type="hidden" id="baseUrl" name="baseUrl" readonly="readonly" value="<?php echo $base_url_out; ?>">
        </td>
      </tr>
      <tr>
       <!--  <td>Agile Release Train:</td>
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
          <?php //echo $art; ?>
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
      <?php //echo $program_increment_menu; ?> -->
    </select>
  </td>
</tr>
<tr>
<td><input type="submit" id="php_button" onclick="updateEmployeeTable()" name="generate_button" class="button" value="Generate"></td>
<td></td>
</tr>
<tr>
<div style="float: right; margin-right: 10px; text-align: center; font-size: 12px;">
              <div id="capacity-calc-bignum" name="totalcap"><?php echo $totalcapacity ?></div>
              <b>Total Capacity for the Program Increment<br/>(capacity-calc-bignum $totalcap)</b>
            </div>
          </td>

</td></tr>
</table>
</form><br>
</div>
</div>
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
  console.log("PI Cookie: " + getCookie('piCookie'));
  console.log("ART Cookie: "+getCookie('artCookie'));
  console.log("Team Cookie: " + getCookie('teamSelectCookie'));
  </script>
  <?php
  $sequenceArray = array();
  $iterationArray = array();
  date_default_timezone_set('America/Chicago');
  //updated sql so select values matched availabe column names
  $sql = "SELECT sequence, PI_id as program_increment, iteration_id as iteration , sequence
  FROM `cadence`
  WHERE PI_id in (SELECT  PI_id
  FROM `cadence`
  WHERE start_date <= DATE(NOW())
  AND end_date >= DATE(NOW())
  order by sequence);";
  $result = $db->query($sql);
  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $program_increment = $row["program_increment"];
    $iteration = $row["iteration"];
    $sequence = $row["sequence"];
    $result->close();
  } else {
    echo "No Available Iterations available for Today's date";
    $result->close();

    $sql = "SELECT *
        FROM
        (	SELECT MIN(start_date) as start_date, MAX(end_date) as end_date
          FROM cadence
          WHERE start_date <= DATE(NOW())
          OR end_date >= end_date >= DATE(NOW())
          GROUP BY program_increment
        ) as PI
        WHERE PI.start_date <= DATE(NOW())
        AND PI.end_date >= DATE(NOW());";
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $start_date = $row["start_date"];
      $end_date = $row["end_date"];
    } else {
      //echo "In-between Program Increments";
    }
    $result->close();
  }


  echo '<script>console.log('.$sequence.');</script>';
  //checks if there is a current team selected. If not it uses the artCookie to find the $selected_team
  if (isset($_POST['current-team-selected'])) {
      $selected_team = $_POST['current-team-selected'];

  } ;

  if (isset($_POST['showNext'])) {
    $sequence++;
    echo '<script>console.log("Show Next: " + "'.$sequence.'");</script>';
    echo '<script>console.log("Program Increment: " + "'.$program_increment.'");</script>';
    
    $sql = "SELECT sequence, PI_id as program_increment, iteration_id as iteration 
            FROM `cadence`
            WHERE sequence='".$sequence."';";
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      $program_increment = $row["program_increment"];
      $iteration = $row["iteration"];
      $sequence = $row["sequence"];
      $result->close();
    } else {
      $sql = "SELECT sequence, PI_id as program_increment, iteration_id as iteration 
              FROM `cadence`
              WHERE PI_id='".$program_increment."'
              ORDER BY sequence limit 1;";
      $result = $db->query($sql);
      if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $program_increment = $row["program_increment"];
        $iteration = $row["iteration"];
        $sequence = $row["sequence"];
        $result->close();
    }
  }
  ///////////////////////////////////////////////////////////////////////////////////////////////////////
  echo '<script>console.log("Program Increment: " + "'.$iteration.'");</script>';
    $sql = "SELECT * FROM `capacity` where team_id='".$selected_team."' AND program_increment='".$program_increment."';";
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
      $default_data = false;
      //$default_total = ($row["iteration_1"] + $row["iteration_2"] + $row["iteration_3"] + $row["iteration_4"]+ $row["iteration_5"] + $row["iteration_6"] + $row["iteration_IP"]);
    } else {
      $default_data = true;
      
      $sql = "SELECT * FROM `membership` where team_name = (select team_name from trains_and_teams where team_id = '".$selected_team."' and art_name = '".$art_name."' LIMIT 1) ;";
      $result = $db->query($sql);
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

          if ($row["role"] == "SM") {
            $velType = "SCRUM_MASTER_ALLOCATION";
          } else if ($row["role"] == "PO") {
            $velType = "PRODUCT_OWNER_ALLOCATION";
          } else  {
            $velType = "AGILE_TEAM_MEMBER_ALLOCATION";
          }

          $sql2 = "SELECT * FROM `preferences` WHERE name='".$velType."';";
          $result2 = $db->query($sql2);

          if ($result2->num_rows > 0) {

              $row2 = $result2->fetch_assoc();
              $default_total += $row2["value"];

          }
        }
      }
    }
  }
  if (isset($_POST['select-team'])) {
    $selected_team = $_POST['select-team'];

    $sql = "SELECT * FROM `capacity` where team_id='".$selected_team."' AND program_increment='".$program_increment."';";
    $result = $db->query($sql);
    if ($result->num_rows > 0) {
    } else {
      $default_data = true;
      $default_total = ($defaul_total * 5) + 28;

      $sql = "SELECT * FROM `membership` where team_name = (select team_name from trains_and_teams where team_id = '".$selected_team."' and art_name = '".$art_name."' LIMIT 1);";
      $result = $db->query($sql);
      if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {

          if ($row["role"] == "SM") {
            $velType = "SCRUM_MASTER_ALLOCATION";
          } else if ($row["role"] == "PO") {
            $velType = "PRODUCT_OWNER_ALLOCATION";
          } else  {
            $velType = "AGILE_TEAM_MEMBER_ALLOCATION";
          }

          $sql2 = "SELECT * FROM `preferences` WHERE name='".$velType."';";
          $result2 = $db->query($sql2);

          if ($result2->num_rows > 0) {

              $row2 = $result2->fetch_assoc();
              $default_total += $row2["value"];

          }
        }
      }
    }
  }
  if (!isset($_POST['select-team']) && !isset($_POST['current-team-selected'])) {
    $sql = "SELECT team_id FROM `capacity` where program_increment='".$program_increment."' LIMIT 1;";
    $result = $db->query($sql);

    if ($result->num_rows > 0) {

        $row = $result->fetch_assoc();
        $selected_team = $row["team_id"];
    }
  }

  $sql5 = "SELECT * FROM `cadence` WHERE PI_id='".$program_increment."';";
  $result5 = $db->query($sql5);
  if ($result5->num_rows > 0) {
      $row5 = $result5->fetch_assoc();
      $duration = $row5["duration"];
  }
  $sql6 = "SELECT * FROM `preferences` WHERE name='OVERHEAD_PERCENTAGE';";
  $result6 = $db->query($sql6);
  if ($result6->num_rows > 0) {
      $row6 = $result6->fetch_assoc();
      $overhead_percentage = $row6["value"];
  }

  if (isset($_POST['submit0'])) {
    $iterationcapacity = 0;
    for ($x=0; $x < count($_POST['rownum']); $x++){
      $teamcapacity[$_POST['rownum'][$x]] = round(($duration-$_POST['daysoff'][$x])*((100-$overhead_percentage)/100)*($_POST['velocity'][$x]/100));
      $iterationcapacity += $teamcapacity[$_POST['rownum'][$x]];
      $daysoff[$_POST['rownum'][$x]] = $_POST['daysoff'][$x];
      $velocity[$_POST['rownum'][$x]] = $_POST['velocity'][$x];
    }
    $sqliter = "UPDATE `capacity` SET iteration_".substr($iteration, -1)."='".$iterationcapacity."' WHERE program_increment='".$program_increment."' AND team_id='".$selected_team."';";
    $result_iter = $db->query($sqliter);
    $sqlinc = "SELECT (iteration_1 + iteration_2 + iteration_3 + iteration_4 + iteration_5 + iteration_6) as new_total FROM `capacity` WHERE program_increment='".$program_increment."' AND team_id='".$selected_team."';";
    $result_inc = $db->query($sqlinc);
    if ($result_inc->num_rows > 0) {
        $rowinc = $result_inc->fetch_assoc();
        $pi_capacity = $rowinc["new_total"];
      }
    $sqlup = "UPDATE `capacity` SET total='$pi_capacity' WHERE program_increment='".$program_increment."' AND team_id='".$selected_team."';";
    $result_up = $db->query($sqlup);

    // keep velocity and days off value changes
    $iterationcapacity = 0;
    for ($x=0; $x < count($_POST['rownum']); $x++){
      $teamcapacity[$_POST['rownum'][$x]] = round(($duration-$_POST['daysoff'][$x])*((100-$overhead_percentage)/100)*($_POST['velocity'][$x]/100));
      $iterationcapacity += $teamcapacity[$_POST['rownum'][$x]];
      $daysoff[$_POST['rownum'][$x]] = $_POST['daysoff'][$x];
      $velocity[$_POST['rownum'][$x]] = $_POST['velocity'][$x];
    }
  }

  ?>

<div class="right-content" >
    <div class="container">

      <h3 style=" color: #01B0F1; font-weight: bold;">Capacity Calculations for the Agile Team</h3>

      <table width="95%">
        <tr>
          <td width="25%" style="vertical-align: top; font-weight: bold; color: #01B0F1; line-height: 130%; font-size: 18px;">
            <form method="post" action="#">
            Agile Release Train: &emsp; <br/>
            Agile Team: &emsp; <br/>
            Program Increment (PI): &emsp; <br/>
          </td>
          <td  style="vertical-align: top; font-weight: bold; line-height: 130%;  font-size: 18px;" width="25%">
            
          <select id="art" name="art" onchange="
            //sets art select to selected value
            var art_select = this.value;
            //sets the selected value as the cookie
            document.cookie = escape('artCookie') + '=' + escape(art_select) ;
            //updates the teams list
            getTeams(art_select);
            location.reload();">
            <option value="">-- Select --</option>
            <?php echo $art; ?>
          </select>
          <br/>
            <select name="select-team" onchange="      
            //sets team_select to selected value
            var team_select = this.value;
            //sets the selected value as the cookie
            document.cookie = escape('teamSelectCookie') + '=' + escape(team_select);
            location.reload();" >
              <?php
              $sql = "SELECT DISTINCT c.team_id, c.team_name FROM capacity c, trains_and_teams t where c.program_increment='".$program_increment."' and c.team_id = t.team_id and t.parent_name = '".$art_select."';";
              //checks if there is a selected team in the cookie variable. If there is it will update the detault to the cookie value
              if(isset($_COOKIE['teamSelectCookie'])){
                $selected_team = $_COOKIE['teamSelectCookie'];
              }
              $result = $db->query($sql);

              if ($result->num_rows > 0) {

                  while ($row = $result->fetch_assoc()) {
                    if ( trim($selected_team) == trim($row["team_id"]) ) {
                      echo '<option value="'.$row["team_id"].'" selected>'.$row["team_name"].'</option>';
                    }else{
                      echo '<option value="'.$row["team_id"].'">'.$row["team_name"].'</option>';
                    }

                  }
              }
              ?>
            </select>
            <br/>
          <select id="PI_ID" name="pi_id" onchange="
          //sets pi_select to selected value
          var pi_select = this.value;
          //sets the selected value as the cookie
          document.cookie = escape('piCookie') + '=' + escape(pi_select) ;
          location.reload();">
          <?php echo $program_increment_menu; ?>
          </select>

          </form><br/>
          <?php

            //Creates an array of the active sequences and iterations
  if ($result = $db->query("SELECT sequence, iteration_id as iteration FROM `cadence` WHERE PI_id ='".$_COOKIE['piCookie']."';")) {
    $rows = array();
    while($row = $result->fetch_array()) {
      $sequenceArray[]=$row["sequence"];
      $iterationArray[]=$row["iteration"];
    }
};
          $count_iteration = count($iterationArray);
  //Loop for displaying the series of Employee table & iteration calculation placeholder
  for($i = 0; $i < $count_iteration; $i++){
    creatTables($program_increment, $selected_team, $iterationArray[$i], $sequenceArray[$i], $overhead_percentage);
  };

          



          function creatTables($program_increment, $selected_team, $iteration, $sequence, $overhead_percentage){
            getTotalCapacity($program_increment, $selected_team, $sequence);
            ///////////////////////////Funtion Start/////////////////////////////////////////////////////////
            $current_sequence = 'current-sequence'.$sequence;
            if (isset($_POST[$current_sequence])) {
              $sequence = $_POST[$current_sequence];
          
            };

            $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
            $db->set_charset("utf8");
            $duration = getDuration($program_increment);

           echo '<tr><td>&nbsp;&nbsp;Iteration (I): &nbsp;</td><td>'.$iteration.'</td></tr>';
           echo '<tr><td>&nbsp;&nbsp;No. of Days in Iteration: &nbsp;</td><td>'.$duration.'</td></tr>';
           echo '<tr><td>&nbsp;&nbsp;Overhead Percentage: &nbsp;</td><td>'.$overhead_percentage.'%</td></tr>';
            //echo "&nbsp;".$program_increment."<br/>";

            echo '<td width="50%"  style="font-weight: bold;">';
            $sql = "SELECT * FROM `capacity` WHERE program_increment='".$program_increment."' AND team_id='".$selected_team."';";
            $result = $db->query($sql);

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();

                if (isset($teamcapacity)  && !isset($_POST['restore'.$sequence])  && !isset($_POST['submit0'])){
                  $icapacity = array_sum($teamcapacity);
                  $totalcapacity = $row["total"] + ($icapacity - $row["iteration_".substr($iteration, -1)]);
                }else{
                  //this is where the problem is<-Fixed by adding column iteration_P to the capacity table
                  $icapacity = $row["iteration_".substr($iteration, -1)];
                  $totalcapacity = $row["total"];
                }

            } else {
              if (isset($teamcapacity)  && !isset($_POST['restore'.$sequence])  && !isset($_POST['submit0'])){
                $icapacity = array_sum($teamcapacity);
                $totalcapacity = ($default_total*6) + ($icapacity - $default_total);
              }else{
                $icapacity = $default_total;
                $totalcapacity = $default_total*6;
              }
            }
       
             ?>
            <tr>
            <td></td>
            <td>
            <div style="float: left; text-align: center; font-size: 12px;">
              <div id="capacity-calc-bignum" name="icap"><?php echo $icapacity ?></div>
              Total Capacity for this Iteration <br/>(capacity-calc-bignum $icapacity)
            </div>
          </td>
        </tr>
        <tr>
          <td colspan="3">

        <form method="post" action="#" id="maincap<?php echo $sequence; ?>">
        <table id="<?php echo $sequence; ?>" cellpadding="2px" cellspacing="0" border="0" class="capacity-table"
             width="100%" style="width: 100%; clear: both; font-size: 15px; margin: 8px 0 15px 0">

          <thead>

          <tr id="capacity-table-first-row">

              <th id="capacity-table-td">Last Name</th>
              <th id="capacity-table-td">First Name</th>
              <th id="capacity-table-td">Role</th>
              <th id="capacity-table-td">% Velocity Available</th>
              <th id="capacity-table-td">Days Off <br/><p style="font-size: 9px;">(Vacation / Holidays / Sick Days)</p></th>
              <th id="capacity-table-td">Story Points</th>

          </tr>

          </thead>

          <tbody>


          <?php

          $sql = "SELECT last_name, first_name, role FROM `membership`
                  JOIN `employees` on (membership.polarion_id = employees.number)
                  JOIN `trains_and_teams` on (membership.team_name = trains_and_teams.team_name)
                  WHERE trains_and_teams.team_id = '".$selected_team."';";

          $result = $db->query($sql);


          if ($result->num_rows > 0) {

              // output data of each
              $rownum = 0;
              while ($row = $result->fetch_assoc()) {

                if ($row["role"] == "SM") {
                  $velocityType = "SCRUM_MASTER_ALLOCATION";
                } else if ($row["role"] == "PO") {
                  $velocityType = "PRODUCT_OWNER_ALLOCATION";
                } else  {
                  $velocityType = "AGILE_TEAM_MEMBER_ALLOCATION";
                }

                $sql2 = "SELECT * FROM `preferences` WHERE name='".$velocityType."';";
                $result2 = $db->query($sql2);

                if ($result2->num_rows > 0) {

                    $row2 = $result2->fetch_assoc();

                }
                if (isset($teamcapacity[$rownum]) && !isset($_POST['restore'.$sequence]) && isset($_POST['submit0'])){
                  $storypts = $teamcapacity[$rownum];
                }else{
                  $storypts = round(($duration-0)*((100-$overhead_percentage)/100)*($row2["value"]/100));
                }
                $valueForJS = $row2["value"];
                if (isset($daysoff[$rownum]) && !isset($_POST['restore'.$sequence])  && isset($_POST['submit0'])){
                  $doff = $daysoff[$rownum];
                } else {
                  $doff = 0;
                }
                if (isset($velocity[$rownum]) && !isset($_POST['restore'.$sequence]) && isset($_POST['submit0'])){
                  $vel = $velocity[$rownum];
                } else {
                  $vel = $row2["value"];
                }

                  echo
                  "<tr>
                      <td id='capacity-table-td' style='font-weight:500;'>" . $row["last_name"] . "</td>
                      <td id='capacity-table-td' style='font-weight:500;'>" . $row["first_name"] . "</td>
                      <td id='capacity-table-td' style='font-weight:500;'>" . $row["role"] . "</td>
                      <td id='capacity-table-td' style='font-weight:500; text-align: center;'><input id='autoin' class='capacity-text-input' type='text' name='velocity[]' value='" . $vel . "' onchange='autoLoad();' /> %</td>
                      <td id='capacity-table-td' style='font-weight:500; text-align: center;'><input id='autoin2' class='capacity-text-input' type='text' name='daysoff[]' value='".$doff."' onchange='autoLoad();' /></td>
                      <td id='capacity-table-td' style='font-weight:500; text-align: center;  background: #e9e9e9;'><input id='story' class='capacity-text-input' type='text' name='storypoints[]' value='".$storypts."' readonly='readonly' style='border: 0;  background: #e9e9e9;' />&nbsp;pts</td>
                      <input type='hidden' name='rownum[]' value='".$rownum."'/>
                  </tr>";
                  $rownum++;
              }
          } else {
            echo "<tr><td colspan='6' id='capacity-table-td'  style='text-align: center; font-weight: bold; padding: 20px 0 20px 0'>";
              print "NO TEAM MEMBERS ASSIGNED TO TEAM \"".$selected_team."\"";
              echo "</td></tr>";
          }

          $result->close();
          

          echo '</tbody>';

          echo '<tfoot>';

          echo '</tfoot>';

      echo '</table>';
      echo '<input type="submit" id="capacity-button-blue" name="submit0" value="Submit">
      <input type="submit" id="capacity-button-blue" name="restore'.$sequence.'" value="Restore Defaults">
      <input type="submit" id="capacity-button-blue" name="showNext" value="Show Next Iteration">
        <input type="hidden" name="current-team-selected" value="'.$selected_team.'">
        <input type="hidden" name="current-sequence"'.$sequence.' value='.$sequence.'">
      </form>

      <script type="text/javascript">

      $(document).ready(function () {

          $(\'#'.$sequence.'\').DataTable({
              paging: false,
              searching: false,
              infoCallback: false
          });

      });

      function autoForm() {
        document.getElementById(\'maincap'.$sequence.'\').submit();
      }

      function autoLoad() {
        var velocity = $("input[name=\'velocity[]\']")
            .map(function(){return $(this).val();}).get();
        var daysoff = $("input[name=\'daysoff[]\']")
            .map(function(){return $(this).val();}).get();
        var rownum = $("input[name=\'rownum[]\']")
            .map(function(){return $(this).val();}).get();

        var overhead = "'.$overhead_percentage.'";
        var duration = "'.$duration.'";
        var value = "'.$valueForJS.'";
        var totalcap_old = "'.$totalcapacity.'";
        var icap_old = "'.$icapacity.'";
        var icap = 0;

        for (var i in rownum) {
            var storypts = Math.round( ( duration - daysoff[i] ) * ( ( 100-overhead ) / 100 ) * ( velocity[i] / 100 ) );
            $("input[name=\'storypoints[]\']").eq(i).val(storypts);
            icap += storypts;
        }

        document.getElementsByName("icap")[0].innerHTML = icap;
          var capdiff = icap - icap_old;
          var tcap = parseInt(capdiff) + parseInt(totalcap_old);
          document.getElementsByName("totalcap")[0].innerHTML = tcap;
      }

  </script>';
      ///////////////////////////Funtion End/////////////////////////////////////////////////////////
        };
?>

      </td>
      </tr>
      </table>

    </div>
    </div>

   
  <?php 
      //function for returning the default team name for a given ART
      function getDefaultTeamName($art_name){
          $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
          $db->set_charset("utf8");
          $sql = "SELECT DISTINCT team_name FROM trains_and_teams where type = 'AT'  and parent_name = '".$art_name."' ORDER BY parent_name LIMIT 1;";
          $result = $db->query($sql);
          if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $team_name = $row["team_name"];
          }
          return $team_name;
      };
      //function for returning the team id for a given team name
      function getTeamID($team){
          $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
          $db->set_charset("utf8");
          $team_id= '';
          $sql = "SELECT DISTINCT team_id FROM trains_and_teams where team_name = '".$team."' ORDER BY parent_name LIMIT 1;";
          $result = $db->query($sql);
          if ($result->num_rows > 0) {
              $row = $result->fetch_assoc();
              $team_id= $row["team_id"];
          }
          return $team_id;
      }

      function getTotalCapacity($program_increment, $selected_team, $sequence){
     
        $db = new mysqli(DATABASE_HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_DATABASE);
        $sql = "SELECT * FROM `capacity` WHERE program_increment='".$program_increment."' AND team_id='".$selected_team."'";
        $result = $db->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            if (isset($teamcapacity)  && !isset($_POST['restore'.$sequence])  && !isset($_POST['submit0'])){
              $icapacity = array_sum($teamcapacity);
              $totalcapacity = $row["total"] + ($icapacity - $row["iteration_".substr($iteration, -1)]);
            }else{
              $icapacity = $row["iteration_".substr($iteration, -1)];
              $totalcapacity = $row["total"];
            }
        } else {
          
          if (!isset($teamcapacity)  && !isset($_POST['restore'.$sequence])  && !isset($_POST['submit0'])){
            $icapacity = array_sum($teamcapacity);
            $totalcapacity = ($default_total*6) + ($icapacity - $default_total);
          }else{
            $icapacity = $default_total;
            $totalcapacity = $default_total*6;
          }
        }
      }
      ;
?>
<?php include("./footer.php"); ?>