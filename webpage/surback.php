<?php
/*------------[READ ME]-------------
surback.php is intended for portable use. Here's how you use it.

To pull up the form for a registration survey ANYWHERE:
    require("surback.php");
    registration();
Note that it has its own header and internal well. Therefore it is suggested that you have it separately from any forms you have on the page.
All CSS and JS is included with the function; no further coding on your part is supposed to be needed to make this work.

To pull up the form for a gold badge survey ANYWHERE:
    require("surback.php");
    goldsurvey();
Same rules as above.

All AJAX code is handled by this script. All SQL code is handled by this script.
--------------[END]---------------*/

require_once("/home/tdesell/wildlife_at_home/webpage/wildlife_db.php");

function registration()
{
    global $user; //Yes, bad form, I know I know
    echo "<h2>Registration Survey</h2>";
    
    //Checking to see if they've taken this already
    $connection = getConnection();
    if(!$connection)
    {
        echo "Could not connect to the server to check if you've taken this survey. Please try again later. <br />";
        return 0;
    }
    
    $query = "SELECT u_id FROM registration WHERE u_id=" . $user['id'];
    $result = mysql_query($query, $connection);
    mysql_close($connection);
    
    $rows = mysql_num_rows($result);
    
    if($rows != 0)
    {
        echo "You've already taken the registration survey.";
        return 0;
    }
    
    $blank = "--";
    $optout = "I choose not to answer this question.";
    
    echo "
    <style type=\"text/css\">
        .qbody {margin-bottom: 10px;}
        .required {font-size: 10px; margin-left: 10px;}
        select {width: 300px}
    </style>
    
    <script type=\"text/javascript\">
        function eighteen(item)
        {
            if(item == '< 18')
            {
                $('#regisrest2').hide();
                $('#message').show();
            }
            else
            {
                $('#regisrest2').show();
                $('#message').hide();
            }
        }
        
        function job(item, item2)
        {
            var newi2 = '#' + item2 + 'text';
            if(item == 'Student (Please describe field of study below)' || item == 'Retired (Please describe past employment below)' || item =='Other (Please describe below)')
            {
                $(newi2).show();
            }
            else
            {
                $(newi2).hide();
            }
        }
        
        function showing(item)
        {
            var item2 = '#' + item + 'text';
            if($(item2).is(':visible'))
            {
                $(item2).hide();
            }
            else
            {
                $(item2).show();
            }
        }
        
        function addregis(item, uid)
        {
            var data = $(item).serialize();
            data = data + '&user=' + uid + '&action=processregis';
            
            var checkdata = [$('#regis1').val(), $('#regis2').val(), $('#regis3').val(), $('#regis4').val(), $('#regis5').val(), $('#regis6').val(), $('#regis7').val(), $('#regis10').val()];
            var trigger = 0;
            var i = 0;
            
            if($('#regis1').val() != '< 18')
            {
                for(i = 0; i < checkdata.length; i++) //Checking each item to see if it's filled.
                {
                    if(checkdata[i] == '--' && i != 5) //We need to check English/fluency separately.
                    {
                        trigger = 1;
                        break;
                    }
                }
                
                if(checkdata[5] == '--' && checkdata[4] == 'No')
                {
                    trigger = 1;
                }
            }
            
            if(trigger == 0)
            {
                $.ajax({
                    type: 'POST',
                    url: 'surback.php',
                    data: data,
                    success: function(data){
                        $('#sucmes').html(data);
                        $('#regisrest1').hide();
                        $('#regisrest2').hide();
                        $('#regissubmit').hide();
                        $('#message').hide();
                    }
                });
            }
            else
            {
                alert('Not all the required fields are filled in. Please fill all of them in.');
            }
        }
    </script>
    
    
    <form name=\"regisform\" id=\"regisform\" onsubmit=\"addregis(this, " . $user['id'] . "); return false;\" style=\"width: 50%\">
    <div id=\"regiswell\" class=\"well\" style=\"width: 75%\">
    Thank you for registering for the Wildlife@Home project. Please take a moment to fill out this survey. The purpose of this survey is for project scientists to understand who are users are and their background. Results from this survey will help the Wildlife@Home team create a more user friendly environment for current volunteers, and to recruit new volunteers from a variety of backgrounds.
    <br /><br />
    <div id=\"sucmes\"></div>
    <div id=\"regisrest1\">
    <div class=\"qhead\">What is your age?<span class=\"required\">*required</span></div>
    <div class=\"qbody\">
        <select name=\"regis1\" id=\"regis1\" onchange=\"eighteen(this.value); return false;\">"; //Tag listed here to add JS later
    $options = array($blank, "< 18", "18 - 25", "26 - 30", "31 - 40", "41 - 50", "51 - 60", "> 60", $optout);
    
    outputSelect($options, 1, 0, NULL);
    
    echo "  </select>
    </div>
    </div>
    
    <div id=\"message\" style=\"display: none\">You are under 18, therefore you can't fill out the rest of this survey.</div>
    <div id=\"regisrest2\" style=\"display: block\">
    
    <div class=\"qhead\">What is your sex?<span class=\"required\">*required</span></div>
    <div class=\"qbody\">";
    
    $options = array($blank, "Male", "Female", $optout);
    
    outputSelect($options, 0, 2, "regis");
    
    echo "</div>
    
    <div class=\"qhead\">What is the highest level of education you have achieved?<span class=\"required\">*required</span></div>
    <div class=\"qbody\">";
    
    $options = array($blank, "Elementary School", "Junior High/Middle School", "High School Diploma", "GED", "Some college", "Associate's Degree", "Bachelor's Degree", "Master's Degree", "Doctoral Degree", $optout);
    
    outputSelect($options, 0, 3, "regis");
    
    echo "</div>
    <div class=\"qhead\">How would you describe your profession?<span class=\"required\">*required</span> <br />Descriptions of the below categories at: https://help.usajobs.gov/index.php/Occupational_Series_and_Job_Category</div>
    <div class=\"qbody\">
        <select name=\"regis4\" id=\"regis4\" onchange=\"job(this.value, 'regis4'); return false;\">"; //Tag listed here to add JS later
    
    $options = array($blank, "Social Science, Psychology, and Welfare", "Human Resources Management", "General Administrative, Clerical, and Office Services", "Natural Resources Management & Biological Services", "Accounting & Budget", "Medical, Hospital, Dental, and Public Health", "Engineering & Architecture", "Information & Arts", "Business & Industry", "Physical Sciences", "Mathematical Sciences", "Equipment, Facilities, or Services", "Education", "Inspection, Investigation, Enforcement, or Compliance", "Transportation", "Trade, Craft, or Labor", "Student (Please describe field of study below)", "Retired (Please describe past employment below)", "Other (Please describe below)", $optout);
    
    outputSelect($options, 1, 0, NULL);
    
    echo "</select>
    
    <div id=\"regis4text\" style=\"display: none;\">Please describe your field of study:<br /><textarea name=\"regis4tcon\" id=\"regis4tcon\"></textarea></div>
    </div>
    
    <div class=\"qhead\">Is English your first language?<span class=\"required\">*required</span></div>
    <div class=\"qbody\">
    ";
    
    $options = array($blank, "Yes", "No", $optout);
    
    outputSelect($options, 0, 5, "regis");
    
    echo "</div>
    
    <div class=\"qhead\">If English is not your first language, do you consider yourself fluent in English?<span class=\"required\">*required if the above is 'No'</span></div>
    <div class=\"qbody\">";
    
    $options = array($blank, "Yes", "No", $optout);
    
    outputSelect($options, 0, 6, "regis");
    
    echo "</div>
    
    <div class=\"qhead\">How large is the city you live in?<span class=\"required\">*required</span></div>
    <div class=\"qbody\">";
    
    $options = array($blank, "< 10,000 people", "10,000 - 25,000 people", "25,000 - 50,000 people", "50,000 - 100,000 people", "100,000 - 250,000 people", "250,000 - 500,000 people", "500,000 - 1,000,000 people", " > 1,000,000 people", $optout);
    
    outputSelect($options, 0, 7, "regis");
    
    echo "</div>
    <div class=\"qhead\">How did you hear about wildlife@home? Select all that apply.</div>
    <div class=\"qbody\">";
    
    $options = array("I am a part of another BOINC project. (Please list how many projects you're actively involved in)"=>1, "I am part of another crowd-sourcing project. (Please provide name of project)"=>1, "Online. (Please provide name of site and URL)"=>1, "This is part of a university or college project."=>0, "Through the wildlife/natural resources community."=>0, "Other. (Please explain)"=>1);
    
    outputCheck($options, 8, "regis");
    
    echo "</div>
    <div class=\"qhead\">What made you decide to join Wildlife@Home? Select all that apply.</div>
    <div class=\"qbody\">";
    
    $options = array("I want to make a contribution to the scientific knowledge."=>0, "I am interested in computer science."=>0, "I would like to build up as much credit as possible."=>0, "I would like to receive as many badges as possible."=>0, "I am interested in learning more about wildlife."=>0, "This is part of a school project."=>0, "Other. (Please explain)"=>1);
    
    outputCheck($options, 9, "regis");
    
    echo "</div>
    <div class=\"qhead\">How do you plan on participating?<span class=\"required\">*required</span></div>
    <div class=\"qbody\">";
    
    $options = array($blank, "Volunteering your computer", "Watching video", "Both");
    
    outputSelect($options, 0, 10, "regis");
    
    echo "</div>
    <div class=\"qhead\">Do you participate in any of the following activities? Select all that apply.</div>
    <div class=\"qbody\">";
    
    $options = array("Hunting"=>0, "Fishing"=>0, "Birding (Bird Watching)"=>0, "Camping"=>0, "Hiking"=>0, "Online Gaming"=>0, "Other BOINC projects (Please Describe)"=>1);
    
    outputCheck($options, 11, "regis");
    
    echo "</div>
    </div> <!-- Rest of regisrest -->
    <div id=\"regissubmit\"><input type=\"submit\" value=\"Submit Answers\" style=\"margin-top: 15px;\" /></div>
    </form>
    </div>";
    
}

function goldsurvey()
{
    global $user;
    echo "<h2>Gold Badge Survey</h2>";
    
    if($user['bossa_total_credit'] < 86400)
    {
        echo "You don't have enough credits to take this survey yet.";
        return 0;
    }
    
    //Checking if this user has already taken the survey
    $connection = getConnection();
    if(!$connection)
    {
        echo "Could not connect to the server to check if you've taken this survey. Please try again later. <br />";
        return 0;
    }
    
    $query = "SELECT u_id FROM goldbadge WHERE u_id=" . $user['id'];
    $result = mysql_query($query, $connection);
    mysql_close($connection);
    
    $rows = mysql_num_rows($result);
    
    if($rows != 0)
    {
        echo "You've already taken the gold badge survey.";
        return 0;
    }
    
    $blank = "--";
    
    echo "<style type=\"text/css\">
        .qbody {margin-bottom: 10px;}
        select {width: 300px}
        .required {font-size: 10px; margin-left: 10px;}
        #gold9tcon, #gold10tcon, #gold12tcon {height: 100px; width: 300px;}
    </style>
    
    <script type=\"text/javascript\">
        function browsq(selval, item)
        {
            if(item == 'gold7txt')
            {
                var item2 = '#' + item;
                if(selval == 'Other')
                {
                    $(item2).show();
                }
                else
                {
                    $(item2).hide();
                }
            }
            else if(item == 'gold8txt')
            {
                var item2 = '#' + item;
                if(selval == 'Yes')
                {
                    $(item2).show();
                }
                else
                {
                    $(item2).hide();
                }
            }
        }
        
        function showing(item)
        {
            var item2 = '#' + item + 'text';
            if($(item2).is(':visible'))
            {
                $(item2).hide();
            }
            else
            {
                $(item2).show();
            }
        }
        
        function addgold(item, uid)
        {
            var data = $(item).serialize();
            data = data + '&user=' + uid + '&action=processgold';
            var checkdata = [$('#gold0').val(), $(\"input[name='gold1']:checked\").val(), $(\"input[name='gold2']:checked\").val(), $(\"input[name='gold3']:checked\").val(), $('#gold4').val(), $('#gold5').val(), $('#gold6').val(), $('#gold7').val(), $('#gold8').val(), $('#gold9').val(), $('#gold15').val()];
            var trigger = 0;
            var i = 0;
            
            for(i = 0; i < checkdata.length; i++) //Checking each item to see if it's filled.
            {
                if(checkdata[i] == '--' || checkdata[i] == undefined)
                {
                    trigger = 1;
                    break;
                }
            }
                
            
            if(trigger == 0)
            {
                $.ajax({
                    type: 'POST',
                    url: 'surback.php',
                    data: data,
                    success: function(data){
                        $('#sucmesgold').html(data);
                        $('#goldrest').hide();
                    }
                });
            }
            else
            {
                alert('Not all the required fields are filled in. Please fill all of them in.');
            }
        }
    </script>
    
    <form name=\"regisform\" id=\"regisform\" onsubmit=\"addgold(this, " . $user['id'] . "); return false;\" style=\"width: 50%\">
    <div id=\"regiswell\" class=\"well\" style=\"width: 75%\">
    Congratulations on receiving a Gold badge! Your participation in the Wildlife@Home project is much appreciated. Please take a moment to fill out this survey about your experiences thus far. The purpose of this survey is for project scientists to understand how volunteers analyze video, and how this project has motivated volunteers to learn more or become more active in computer science and/or wildlife and conservation.
    <br /><br />
    <div id=\"sucmesgold\"></div>
    
    <div id=\"goldrest\">
    <div class=\"qhead\">How do you participate?<span class=\"required\">*required</span></div>
    <div class=\"qbody\">";
    
    $options = array($blank, "Volunteering your computer", "Watching video", "Both");
    
    outputSelect($options, 0, 0, "gold");
    
    echo "</div>
    
    <div class=\"qhead\">Please rank the species below in terms of which are your favorite to watch (1 = favorite, 3 = least favorite) and why: <span class=\"required\">*numerical ratings required</span></div>
    <div class=\"qbody\">";
    
    $options = array("Sharp-Tailed Grouse", "Interior Least Tern", "Piping Plover");
    
    outputRadioNum($options, 3, 1, "gold");
    
    echo "</div>
    
    <div class=\"qhead\">How many hours on average do you spend watching video <b>per week</b>?<span class=\"required\">*required</span></div>
    <div class=\"qbody\">";
    
    $options = array($blank, "1-5", "6-10", "11-15", "15-20", "21-25", "25-30", "> 31 hours");
    
    outputSelect($options, 0, 4, "gold");
    
    echo "</div>
    <div class=\"qhead\">How many hours on average do you spend watching video <b>in one sitting</b>?<span class=\"required\">*required</span></div>
    <div class=\"qbody\">";
    
    $options = array($blank, "1-5", "6-10", "11-15", "> 16 hours");
    
    outputSelect($options, 0, 5, "gold");
    
    echo "</div>
    <div class=\"qhead\">What is the speed (on average) that you watch video?<span class=\"required\">*required</span></div>
    <div class=\"qbody\">";
    
    $options = array($blank, "1x", "2x", "3x", "4x", "5x", "> 5x");
    
    outputSelect($options, 0, 6, "gold");
    
    echo "</div>
    <div class=\"qhead\">Which browser do you primarily use to watch video?<span class=\"required\">*required</span></div>
    <div class=\"qbody\">
    <select name=\"gold7\" id=\"gold7\" onchange=\"browsq(this.value, 'gold7txt'); return false;\">"; //Tag listed here to add JS later
    $options = array($blank, "Internet Explorer", "Safari", "Mozilla Firefox", "Google Chrome", "Other");
    
    outputSelect($options, 1, 0, NULL);
    
    echo "</select>
    <div id=\"gold7txt\" style=\"display: none;\">
    Please state the browser you use:<br />
    <textarea name=\"gold7tcon\" id=\"gold7tcon\"></textarea>
    </div>
    </div>
    
    <div class=\"qhead\">Are you doing other activities, such as watching television or surfing the internet, while watching video?<span class=\"required\">*required</span></div>
    <div class=\"qbody\">
    <select name=\"gold8\" id=\"gold8\" onchange=\"browsq(this.value, 'gold8txt'); return false;\">"; //Tag listed here to add JS later
    
    $options = array($blank, "Yes", "No");
    
    outputSelect($options, 1, 0, NULL);
    
    echo "</select>
    <div id=\"gold8txt\" style=\"display: none;\">
    Please explain the activity:
    <br />
    <textarea name=\"gold8tcon\" id=\"gold8tcon\"></textarea>
    </div>
    </div>
    
    <div class=\"qhead\">Do you find this project interesting?<span class=\"required\">*required</span></div>
    <div class=\"qbody\">
    ";
    
    $options = array($blank, "Yes", "No");
    
    outputSelect($options, 0, 9, "gold");
    
    echo "</div>
    <div class=\"qhead\">If you find this project interesting, why? Please select all that apply:</div>
    <div class=\"qbody\">";
    
    $options = array("I enjoy contributing to scientific knowledge"=>0, "I enjoy the computer science component"=>0, "I like earning credit and badges"=>0, "I enjoy the wildlife/biology component"=>0, "Other (Please Explain)"=>1);
    
    outputCheck($options, 10, "gold");
    
    echo "</div>
    <div class=\"qhead\">Briefly describe if you have learned anything new from this project.</div>
    <div class=\"qbody\">
    <textarea name=\"gold11tcon\" id=\"gold11tcon\"></textarea>
    </div>
    
    <div class=\"qhead\">Briefly explain how we can improve your user experience.</div>
    <div class=\"qbody\">
    <textarea name=\"gold12tcon\" id=\"gold12tcon\"></textarea>
    </div>
    
    <div class=\"qhead\">Has this project motivated you to... (Please select all that apply)</div>
    <div class=\"qbody\">
    ";
    
    $options = array("Seek involvement with other <b>online</b> wildlife citizen science projects?"=>0, "Seek involvement with <b>outdoor</b> wildlife citizen science projects?"=>0, "Spend more time observing or interacting with wildlife outdoors?"=>0, "Become more aware of or involved with conservation of natural resources?"=>0);
    
    outputCheck($options, 13, "gold");
    
    echo "</div>
    
    <div class=\"qhead\">What other species (birds, mammals, amphibians, reptiles etc) would you like to see added to the website? Please list one or more species and explain why.</div>
    <div class=\"qbody\">
    <textarea name=\"gold14tcon\" id=\"gold14tcon\"></textarea>
    </div>
    
    <div class=\"qhead\">Would you recommend this project to others?<span class=\"required\">*'Yes or 'No' required</span></div>
    <div class=\"qbody\">";
    
    $options = array($blank, "Yes", "No");
    
    outputSelect($options, 0, 15, "gold");
    
    echo "<br />Why?<br /><textarea name=\"gold15tcon\" id=\"gold15tcon\"></textarea></div>";
    
    echo "
    <input type=\"submit\" value=\"Submit Answers\" style=\"margin-top: 15px;\" />
    </form>
    </div>
    </div>";
}

function outputSelect($options, $tags, $iteration, $type) //Dropdown items, whether to have tags (1 = have them already, 0 = add them), id number you want to give to the dropdown in said tags, base name of dropdown
{

    if(empty($tags) && !empty($type))
    {
        echo "<select name=\"" . $type . $iteration . "\" id=\"" . $type . $iteration . "\">";
    }
    
    for($i = 0; $i < count($options); $i++)
    {
        echo "<option>" . $options[$i] . "</option>";
    }
    
    if(empty($tags) && !empty($type))
    {
        echo "</select>";
    }
}

function outputCheck($options, $iteration, $type) //List of items, id number of group, base name of group
{
    $i = 0;
    foreach($options as $field=>$JS)
    {
        $name = $type . "-" . $iteration . "-" . $i;
        $fieldtemp = explode(" (", $field);
        
        echo "<div class=\"checkitem\"><input type=\"checkbox\" name=\"" . $name . "\"";
        if($JS == 1)
        {
            echo " onchange=\"showing('" . $name . "'); return false;\"";
        }
        echo " style=\"margin:auto\"><span class=\"checktxt\" style=\"margin: 0px 0px 0px 10px\">" . $fieldtemp[0] . "</span>";
        
        if($JS == 1)
        {
            echo "<div id=\"" . $name . "text\" style=\"display: none;\">(" . $fieldtemp[1] . "<br />
            <textarea name=\"" . $name . "tcon\" id=\"" . $name . "tcon\"></textarea>
            </div>";
        }
        $i++;
        
        echo "</div>";
    }
}

function outputRadioNum($options, $depth, $iteration, $type) //List of items, number of radio buttons to be outputted per item, id number, base name of group. Function is "output radio numeric", referring to having "number" choices
{
    for($i = 0; $i < count($options); $i++)
    {
        echo $options[$i];
        for($d = 1; $d < ($depth + 1); $d++)
        {
            echo "<span class=\"radiotxt\" style=\"margin: 0px 0px 10px 10px\">" . $d . "</span><input type=\"radio\" name=\"" . $type . $iteration . "\" value=" . $d . " style=\"margin: 5px;\">";
        }
        echo "<br />
        <textarea name=\"" . $type . $iteration . "tcon\" id=\"" . $type . $iteration . "tcon\"></textarea>
        <br />";
        $iteration++;
    }
}

function processRegis()
{
    $age = mysql_real_escape_string(urldecode($_POST['regis1'])); //Age
    $sex = mysql_real_escape_string(urldecode($_POST['regis2'])); //Sex
    $education = mysql_real_escape_string(urldecode($_POST['regis3'])); //Education
    $profession = mysql_real_escape_string(urldecode($_POST['regis4'])); //Profession
    if($profession == "Student (Please describe field of study below)" || $profession == "Retired (Please describe past employment below)" || $profession == "Other (Please describe below)")
    {
        if(!empty($_POST['regis4tcon']))
        {
            $profession .= "; " . mysql_real_escape_string(urldecode($_POST['regis4tcon']));
        }
        else
        {
            $profession .= "; [No elaboration given]";
        }
    }

    $english = mysql_real_escape_string(urldecode($_POST['regis5'])); //Is english your first language?
    $eng_fluent = mysql_real_escape_string(urldecode($_POST['regis6'])); //If not, are you fluent?

    $population = mysql_real_escape_string(urldecode($_POST['regis7'])); //Population of current city

    $heard = ""; //How did you hear about wildlife @ home?
    $temp = "regis-8-";
    $options = array("I am a part of another BOINC project. (Please list how many projects you're actively involved in)"=>1, "I am part of another crowd-sourcing project. (Please provide name of project)"=>1, "Online. (Please provide name of site and URL)"=>1, "This is part of a university or college project."=>0, "Through the wildlife/natural resources community."=>0, "Other. (Please explain)"=>1);
    $heard = iterateCheck($heard, $temp, 0, $options);

    $join = ""; //Why did you join wildlife@home?
    $temp = "regis-9-";
    $options = array("I want to make a contribution to the scientific knowledge."=>0, "I am interested in computer science."=>0, "I would like to build up as much credit as possible."=>0, "I would like to receive as many badges as possible."=>0, "I am interested in learning more about wildlife."=>0, "This is part of a school project."=>0, "Other. (Please explain)"=>1);
    $join = iterateCheck($join, $temp, 0, $options);

    $participation = mysql_real_escape_string(urldecode($_POST['regis10'])); //How would you participate?

    $activities = ""; //Do you participate in any of the following activities?
    $temp = "regis-11-";
    $options = array("Hunting"=>0, "Fishing"=>0, "Birding (Bird Watching)"=>0, "Camping"=>0, "Hiking"=>0, "Online Gaming"=>0, "Other BOINC projects (Please Describe)"=>1);
    $activities = iterateCheck($activities, $temp, 0, $options);
    
    //echo $_POST['user'] . "<br />" . $age . "<br />" . $sex . "<br />" . $education . "<br />" . $profession . "<br />" . $english . "<br />" . $population . "<br />" . $heard . "<br />" . $join . "<br />" . $participation . "<br />" . $activities; //Debug
    
    $query = "INSERT INTO registration (u_id, age, sex, education, profession, english, eng_fluent, population, heard, joined, participation, activities) VALUES (" . $_POST['user'] . ", '" . $age . "', '" . $sex . "', '" . $education . "', '" . $profession . "', '" . $english . "', '" . $eng_fluent . "', '" . $population . "', '" . $heard . "', '" . $join . "', '" . $participation . "', '" . $activities . "')";
    
    $connection = getConnection();
    if(!$connection)
    {
        echo "Could not connect to the server to add survey results. Please try again later. <br />";
        return 0;
    }
    
    mysql_query($query, $connection);
    mysql_close($connection);
    
    echo "Your answers have been successfully submitted!";
}

function processGold()
{
    $participation = mysql_real_escape_string(urldecode($_POST['gold0']));
    
    $speciesdata = "Sharp-Tailed Grouse: [Rating of " . $_POST['gold1'] . "; Given Reason: ";
    if(empty($_POST['gold1tcon']))
    {
        $speciesdata .= "(None given)";
    }
    else
    {
        $speciesdata .= mysql_real_escape_string(urldecode($_POST['gold1tcon']));
    }
    $speciesdata .= "]; Interior Least Tern: [Rating of " . $_POST['gold2'] . "; Given Reason: ";
    if(empty($_POST['gold2tcon']))
    {
        $speciesdata .= "(None given)";
    }
    else
    {
        $speciesdata .= mysql_real_escape_string(urldecode($_POST['gold2tcon']));
    }
    $speciesdata .= "]; Piping Plover: [Rating of " . $_POST['gold3'] . "; Given Reason: ";
    if(empty($_POST['gold3tcon']))
    {
        $speciesdata .= "(None given)";
    }
    else
    {
        $speciesdata .= mysql_real_escape_string(urldecode($_POST['gold3tcon']));
    }
    $speciesdata .= "]";
    
    $perweek = mysql_real_escape_string(urldecode($_POST['gold4']));
    $persitting = mysql_real_escape_string(urldecode($_POST['gold5']));
    $vidspeed = mysql_real_escape_string(urldecode($_POST['gold6']));
    
    $browser = mysql_real_escape_string(urldecode($_POST['gold7']));
    if($browser == "Other")
    {
        $browser .= " [Browser Listed: " . mysql_real_escape_string(urldecode($_POST['gold7tcon'])) . "]";
    }
    
    $otheract = mysql_real_escape_string(urldecode($_POST['gold8']));
    if($otheract == "Yes")
    {
        $otheract .= "; " . mysql_real_escape_string(urldecode($_POST['gold8tcon']));
    }
    
    $interesting = mysql_real_escape_string(urldecode($_POST['gold9']));
    if($interesting == "Yes")
    {
        $temp = "gold-10-";
        $options = array("I enjoy contributing to scientific knowledge"=>0, "I enjoy the computer science component"=>0, "I like earning credit and badges"=>0, "I enjoy the wildlife/biology component"=>0, "Other (Please Explain)"=>1);
        $interestelaboration = iterateCheck($interestelaboration, $temp, 0, $options);
    }
    
    if(empty($interestelaboration))
    {
        $interestelaboration = "[No elaboration given]";
    }
    
    $learnednew = mysql_real_escape_string(urldecode($_POST['gold11tcon']));
    if(empty($learnednew))
    {
        $learnednew = "[No response]";
    }
    
    $userexperience = mysql_real_escape_string(urldecode($_POST['gold12tcon']));
    if(empty($userexperience))
    {
        $userexperience = "[No response]";
    }
    
    $temp = "gold-13-";
    $options = array("Seek involvement with other <b>online</b> wildlife citizen science projects?"=>0, "Seek involvement with <b>outdoor</b> wildlife citizen science projects?"=>0, "Spend more time observing or interacting with wildlife outdoors?"=>0, "Become more aware of or involved with conservation of natural resources?"=>0);
    $motivation = "";
    $motivation = iterateCheck($motivation, $temp, 0, $options);
    
    $otherspecies = mysql_real_escape_string(urldecode($_POST['gold14tcon']));
    if(empty($otherspecies))
    {
        $otherspecies = "[No response]";
    }
    
    $recommendation = mysql_real_escape_string(urldecode($_POST['gold15']));
    if(empty($_POST['gold15tcon']))
    {
        $recommendation .= "; [No reasons given]";
    }
    else
    {
        $recommendation .= "; " . mysql_real_escape_string(urldecode($_POST['gold15tcon']));
    }
    
    $query = "INSERT INTO goldbadge (u_id, gold_participation, species_rank, hours_week, hours_sitting, video_speed, browser, other_activities, interesting, int_elaboration, learned, experience, motivation, other_species, recommendation) VALUES (" . $_POST['user'] . ", \"" . $participation . "\", \"" . $speciesdata . "\", \"" . $perweek . "\", \"" . $persitting . "\", \"" . $vidspeed . "\", \"" . $browser . "\", \"" . $otheract . "\", \"" . $interesting . "\", \"" . $interestelaboration . "\", \"" . $learnednew . "\", \"" . $userexperience . "\", \"" . $motivation . "\", \"" . $otherspecies . "\", \"" . $recommendation . "\")";
    
    $connection = getConnection();
    if(!$connection)
    {
        echo "Could not connect to the server to add survey results. Please try again later. <br />";
        return 0;
    }
    
    mysql_query($query, $connection);
    mysql_close($connection);
    
    echo "Your answers have been successfully submitted!";
}

function iterateCheck($basevariable, $temp, $i, $options) //For use in form processing, to iterate and check through a list of checked items. Given a basevariable (usually blank), base field name, number to start at, and options to iterate through and check for
{
    foreach ($options as $item=>$explain)
    {
        $newtemp = $temp . $i;
        $final = $_POST[$newtemp];
        if(!empty($final))
        {
            if(!empty($basevariable))
            {
                $basevariable .= " | ";
            }
            
            $tempopt = explode(" (", $item);
            $basevariable .= $tempopt[0];
            
            if($explain == 1)
            {
                $extemp = $temp . "tcon";
                $exfin = $_POST[$extemp];
                if(!empty($exfin))
                {
                    $basevariable .= "; " . mysql_real_escape_string(urldecode($exfin));
                }
                else
                {
                    $basevariable .= " - [No elaboration given]";
                }
            }
        }
        $i++;
    }
    if(empty($basevariable))
    {
        $basevariable = "[No answers given]";
    }
    
    return $basevariable;
}

function getconnection() //Getting mysql connection
{
    global $wildlife_user, $wildlife_passwd;
    $video_con = mysql_connect("wildlife.und.edu", $wildlife_user, $wildlife_passwd, TRUE);
    //Making connection and setting DB
    if(!$video_con)
    {
        echo "Could not connect to the server for some reason";
        return 0;
    }
    mysql_select_db("wildlife_video", $video_con);
    return $video_con;
}

//AJAX stuff
if($_POST['action'] == "processregis")
{
    processRegis();
}
else if($_POST['action'] == "processgold")
{
    processGold();
}
?>
