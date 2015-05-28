<?php

$cwd[__FILE__] = __FILE__;
if (is_link($cwd[__FILE__])) $cwd[__FILE__] = readlink($cwd[__FILE__]);
$cwd[__FILE__] = dirname($cwd[__FILE__]);


require_once($cwd[__FILE__] . "/../../citizen_science_grid/my_query.php");

function getEventsDay($connection, $species, $nest)
{
    $repd = array();
    $string = "SELECT video_2.id, video_2.start_time, video_2.species_id FROM video_2 RIGHT OUTER JOIN expert_observations ON video_2.id = expert_observations.video_id WHERE expert_observations.event_type = 'parent behavior - not in frame' AND video_2.species_id=" . $species;
    
    if(!empty($nest) && $species == 1)
    {
        $string .= " AND video_2.location_id=" . $nest;
    }
    
    $string .= " ORDER BY video_2.start_time";
    
    $query = query_wildlife_video_db($string);
    
    while($row = $query->fetch_array())
    {
        $day = explode(" ", $row['start_time']);
        
        $trigger = false;
        foreach($repd as $date=>$count)
        {
            if($day[0] == $date)
            {
                $repd[$date] = $count + 1;
                $trigger = true;
                break;
            }
        }
        
        if($trigger == false)
        {
            $repd[$day[0]] = 1;
        }
    }
    
    return $repd;
}

function getEventsDuration($connection, $species)
{
    $dura = array();
    
    $string = "SELECT exp.event_type, exp.start_time, exp.end_time FROM expert_observations AS exp LEFT OUTER JOIN video_2 AS v2 ON (v2.id = exp.video_id) WHERE exp.event_type = 'parent behavior - not in frame' AND v2.species_id = " . $species;
    
    if(!empty($nest) && $species == 1)
    {
        $string .= " AND v2.location_id=" . $nest;
    }
    
    $query = query_wildlife_video_db($string);
    
    while($row = $query->fetch_array())
    {
        $tempstart = explode(":", $row['start_time']);
        $tempend = explode(":", $row['end_time']);
        
        $durasec = $tempend[2] - $tempstart[2];
        $duramin = $tempend[1] - $tempstart[1];
        $durahour = $tempend[0] - $tempstart[0];
        
        if($durasec < 0)
        {
            $duramin = $duramin - 1;
            if($duramin < 0)
            {
                $durahour = $durahour - 1;
                if($durahour < 0)
                {
                    $durahour = 0;
                }
            
                $duramin = 60 - abs($duramin);
            }
            $durasec = 60 - abs($durasec);
        }
        
        $durahrcon = $durahour * 60;
        //Duration is in format minutes-seconds
        
        $duration = $durahrcon + $duramin . "-" . $durasec;
        
        
        $dura[count($dura)] = $duration;
        
        
        $temp = explode("-", $duration);
    }
    
    
    return $dura;
    
}

function getEventsTime($connection, $species)
{
    $time = array();
    
    $string = "SELECT exp.event_type, exp.start_time FROM expert_observations AS exp LEFT OUTER JOIN video_2 AS v2 ON (v2.id = exp.video_id) WHERE exp.event_type = 'parent behavior - not in frame' AND v2.species_id = " . $species;
    
    if(!empty($nest) && $species == 1)
    {
        $string .= " AND v2.location_id=" . $nest;
    }
    
    $query = query_wildlife_video_db($string);
    
    
    while($row = $query->fetch_array())
    {
        $time[count($time)] = $row['start_time'];
    }
    
    return $time;
}

function graphNumber($data, $species) //Outputs the data for the graph: number of recess events/day. This should be used in an ajax call to form the graphs.
{
    asort($data, floatval("SORT_NATURAL"));
    
    //Assembling array of possible event numbers
    
    $numarray = array();
    $trigger = false;
    
    foreach($data as $date=>$count)
    {
        $trigger = false;
        foreach($numarray as $num=>$daycount) //Format is num of events=>number of days with that number of event (ex: "there are 5 days that had 1 event, three days that had eight events, etc"). 
        {
            if($count == $num)
            {
                $temp = $daycount + 1;
                $numarray[$num] = $temp;
                $trigger = true;
                break;
            }
        }
        
        if($trigger == false)
        {
            $numarray[strval($count)] = 1;
        }
    
    }
    
    //Find final item in numarray
    $iterator = count($numarray);
    
    foreach($numarray as $num=>$daycount)
    {
        $iterator--;
        
        if($iterator == 0)
        {
            $final = $num;
        }
    }
    
    //NOTE: Graph type is Column Chart!!
        
    //Adding zero columns to graphing data...
    
    for($i = 1; $i < $final; $i++)
    {
        if(!array_key_exists((string)$i, $numarray))
        {
            $numarray[(string)$i] = 0;
        }
    }
    
    //Items in array now sorted.
    ksort($numarray, floatval("SORT_NATURAL"));
    
    $basestring = array();
    
    //Final formatting before json conversion
    $temp = 0;
    foreach($numarray as $num=>$daycount)
    {
        $basestring[]=array((string)$num, $daycount);
    }
    
    $item = json_encode($basestring);
    echo "<script type=\"text/javascript\">numtimeschart(" . $item . ", " . $species . ");</script>";
}

function graphDuration($data, $species)
{
    sort($data, floatval("SORT_NATURAL")); //Data is in format "mins-secs"; sort is used because this a "normal" array and not an associative one
    
    //Convert data to pure seconds
    for($i = 0; $i < count($data); $i++)
    {
        $tempe = explode("-", $data[$i]);
        $tempm = ($tempe[0] * 60) + $tempe[1];
        $data[$i] = $tempm;
    }
    
    sort($data, floatval("SORT_NATURAL")); //Some odd quirks in how data was ordered before (as string) vs how data is ordered now (as actual numeric) needs to be rectified
    
    //Graph bounds and spacing
    $minbound = $data[0];
    $maxbound = $data[(count($data) - 1)];
    $unit = intval($maxbound / 12);
    
    //"Binning" the data
    $tempbin = array(); //Bin that is not formatted as associative...yet
    for($b = 0; $b < 13; $b++) //This will check a farther range in the last iteration than $maxbound's given value, but is of no concern
    {
        $tmin = $b * $unit;
        $tmax = ($b + 1) * $unit; //Actually worked out from an early version of the second lab in C&D (tex2.cxx)
        
        $tempbin[$b] = 0;
        
        for($i = 0; $i < count($data); $i++)
        {
            if($data[$i] >= $tmin && $data[$i] <= $tmax)
            {
                $final = $tempbin[$b] + 1;
                $tempbin[$b] = $final; //I've had odd behavior in the past...not taking chances this time
                unset($data[$i]); //Removal
                $data = array_values($data); //And removing empty space/reinitializing indices
            }
        }
    }
}

function runOperations($event_type, $data)
{
    if(empty($data))
    {
        echo "<h5>There is no data for this set of statistics.</h5>";
        return 0;
    }
    
    if($event_type == "per-day")
    {
        asort($data, floatval("SORT_NATURAL"));
        
        $iteration = 0; //For the minimum, median, and maximum
        $total = 0; //For the mean
        $split = (intval(count($data) / 2)); //For the median, first quartile and third quartile
        $splitfirst = (intval($split / 2)); //Iteration for first quartile
        $splitthird = ($split + (intval($split / 2))); //Iteration for third quartile
        
        foreach($data as $date=>$count)
        {
            if(empty($iteration))
            {
                $minimum = $count;
            }
            else if($iteration == $splitfirst)
            {
                $quartilefirst = $count;
            }
            else if($iteration == $split)
            {
                $median = $count;
            }
            else if($iteration == $splitthird)
            {
                $quartilethird = $count;
            }
            else if($iteration == (count($data) - 1))
            {
                $maximum = $count;
            }
            $total = $total + $count;
            $iteration++;
        }
        
        $mean = round($total / count($data), 2);
        //The following is for the calculation of the standard deviation
        
        $ongoing = 0; //The total of the squared numbers
        foreach($data as $date=>$count)
        {
            $temp = pow(($count - $mean), 2);
            $ongoing = $ongoing + $temp;
        }
        $standard = sqrt(($ongoing / count($data)));
        
        //Now calculating 95% confidence interval
        
        $conflow = ($mean - (1.96 * ($standard/sqrt(count($data))))); //Lower endpoint
        $confhigh = ($mean + (1.96 * ($standard/sqrt(count($data))))); //Higher endpoint
        
        if(empty($minimum))
        {
            $minimum = "N/A";
        }
        if(empty($quartilefirst))
        {
            $quartilefirst = "N/A";
        }
        if(empty($median))
        {
            $median = "N/A";
        }
        if(empty($quartilethird))
        {
            $quartilethird = "N/A";
        }
        if(empty($maximum))
        {
            $maximum = "N/A";
        }

        echo "<h5>Minimum: " . $minimum . "</h5>
        <h5>First Quartile: " . $quartilefirst . "</h5>
        <h5>Median: " . $median . "</h5>
        <h5>Third Quartile: " . $quartilethird . "</h5>
        <h5>Maximum: " . $maximum . "</h5>
        <h5>Standard Deviation: " . round($standard, 2) . "</h5>
        <h5>Confidence Interval (95%) Low Endpoint: " . round($conflow, 2) . "</h5>
        <h5>Mean: " . $mean . "</h5>
        <h5>Confidence Interval (95%) High Endpoint: " . round($confhigh, 2) . "</h5>
        ";
        
    }
    else if($event_type == "duration")
    {
        sort($data, floatval("SORT_NATURAL"));
        
        $total = 0; //For the mean
        $split = (intval(count($data) / 2)); //For the median, first quartile and third quartile
        $splitfirst = (intval($split / 2)); //Iteration for first quartile
        $splitthird = ($split + (intval($split / 2))); //Iteration for third quartile
        
        //Calculating median, first quartile, and third quartile. Also converting to seconds and adding for mean
        for($i = 0; $i < count($data); $i++)
        {
            if($i == 0)
            {
                $minimum = $data[$i];
            }
            else if($i == $splitfirst)
            {
                $quartilefirst = $data[$i];
            }
            else if($i == $split)
            {
                $median = $data[$i];
            }
            else if($i == $splitthird)
            {
                $quartilethird = $data[$i];
            }
            else if(!$data[$i + 1])
            {
                $maximum = $data[$i];
            }
            
            //Converting to seconds and adding
            $temp = explode("-", $data[$i]);
            $contosec = ($temp[0] * 60) + $temp[1];
            $total = $total + $contosec;
        }
        
        $minimum = explode("-", $minimum);
        $quartilefirst = explode("-", $quartilefirst);
        $median = explode("-", $median);
        $quartilethird = explode("-", $quartilethird);
        $maximum = explode("-", $maximum);
        $meanmin = intval(($total / count($data))/ 60);
        $meansec = ($total / count($data)) % 60;
        
        //Calculating standard deviation. Since $meanmin is an int, I'll be using that for consistency.
        
        //Converting to seconds
        $meanassec = ($meanmin * 60) + $meansec;
        
        $ongoing = 0; //Total of the squared numbers
        for($i = 0; $i < count($data); $i++)
        {
            $datatemp = explode("-", $data);
            $totaltemp = ($datatemp[0] * 60) + $datatemp[1];
            
            $temp = pow(($totaltemp - $meanassec), 2);
            $ongoing = $ongoing + $temp;
        }
        $standard = sqrt(($ongoing / count($data))); //Still in seconds
        $standardmin = intval($standard / 60); //Convert to minutes
        $standardsec = $standard % 60; //Getting seconds
        
        //Calcualting 95% confidence interval. Will be in seconds
        
        $conflow = ($meanassec - (1.96 * ($standard/sqrt(count($data))))); //Lower endpoint
        $confhigh = ($meanassec + (1.96 * ($standard/sqrt(count($data))))); //Higher endpoint
        
        $conflowasmin = intval($conflow / 60);
        $conflowassec = $conflow % 60;
        
        $confhighasmin = intval($confhigh / 60);
        $confhighassec = $confhigh % 60;
        
        
        
        echo "<h5>Minimum: " . $minimum[0] . "min " . $minimum[1] . "sec </h5>
        <h5>First Quartile: " . $quartilefirst[0] . "min " . $quartilefirst[1] . "sec </h5>
        <h5>Median: " . $median[0] . "min " . $median[1] . "sec </h5>
        <h5>Third Quartile: " . $quartilethird[0] . "min " . $quartilethird[1] . "sec </h5>
        <h5>Maximum: " . $maximum[0] . "min " . $maximum[1] . "sec </h5>
        <h5>Standard Deviation: " . $standardmin . "min " . $standardsec . "sec </h5>
        <h5>Confidence Interval (95%) Low Endpoint: " . $conflowasmin . "min " . $conflowassec . "sec</h5>
        <h5>Mean: " . $meanmin . "min " . $meansec . "sec </h5>
        <h5>Confidence Interval (95%) High Endpoint: " . $confhighasmin . "min " . $confhighassec . "sec</h5>
        ";
    }
    else if($event_type == "time")
    {
        sort($data, floatval("SORT_NATURAL"));
        
        /*The following are for the mean*/
        $totalhr = 0;
        $totalmin = 0;
        $totalsec = 0;
        /*[/mean variables]*/
        
        $split = (intval(count($data) / 2)); //For the median, first quartile and third quartile
        $splitfirst = (intval($split / 2)); //Iteration for first quartile
        $splitthird = ($split + (intval($split / 2))); //Iteration for third quartile
        
        for($i = 0; $i < count($data); $i++)
        {
            if($i == 0)
            {
                $minimum = $data[$i];
            }
            else if($i == $splitfirst)
            {
                $quartilefirst = $data[$i];
            }
            else if($i == $split)
            {
                $median = $data[$i];
            }
            else if($i == $splitthird)
            {
                $quartilethird = $data[$i];
            }
            else if(!$data[$i + 1])
            {
                $maximum = $data[$i];
            }
            
            //Splitting time at semicolons and adding
            $temp = explode(":", $data[$i]);
            $totalhr = intval($temp[0]) + $totalhr;
            $totalmin = intval($temp[1]) + $totalmin;
            $totalsec = intval($temp[2]) + $totalsec;
        }
        
        //Calculating mean
        $meanhr = intval($totalhr / count($data));
        $meanmin = intval($totalmin / count($data));
        $meansec = intval($totalsec / count($data));
        
        //Calculating standard deviation
        $ongoinghr = 0;
        $ongoingmin = 0;
        $ongoingsec = 0;
        
        for($i = 0; $i < count($data); $i++)
        {
            $temp = explode(":", $data[$i]);
            
            $ongoinghr = intval(pow(($temp[0] - $meanhr), 2)) + $ongoinghr;
            $ongoingmin = intval(pow(($temp[1] - $meanmin), 2)) + $ongoingmin;
            $ongoingsec = intval(pow(($temp[2] - $meansec), 2)) + $ongoingsec;
        }
        
        $stdhr = intval(sqrt(($ongoinghr / count($data))));
        $stdmin = intval(sqrt(($ongoingmin / count($data))));
        $stdsec = intval(sqrt(($ongoingsec / count($data))));
        
        //Calculating 95% confidence interval
        //Low endpoint
        $conflowhr = intval($meanhr - (1.96 * ($stdhr/sqrt(count($data)))));
        $conflowmin = intval($meanmin - (1.96 * ($stdmin/sqrt(count($data)))));
        $conflowsec = intval($meansec - (1.96 * ($stdsec/sqrt(count($data)))));
        
        //High endpoint
        $confhighhr = intval($meanhr + (1.96 * ($stdhr/sqrt(count($data)))));
        $confhighmin = intval($meanmin + (1.96 * ($stdmin/sqrt(count($data)))));
        $confhighsec = intval($meansec + (1.96 * ($stdsec/sqrt(count($data)))));
        
        echo "<h5>Minimum: " . $minimum . " </h5>
        <h5>First Quartile: " . $quartilefirst . " </h5>
        <h5>Median: " . $median . " </h5>
        <h5>Third Quartile: " . $quartilethird . " </h5>
        <h5>Maximum: " . $maximum . " </h5>
        <h5>Standard Deviation: " . $stdhr . ":" . $stdmin . ":" . $stdsec . " </h5>
        <h5>Confidence Interval (95%) Low Endpoint: " . $conflowhr . ":" . $conflowmin . ":" . $conflowsec . " </h5>
        <h5>Mean: " . $meanhr . ":" . $meanmin . ":" . $meansec . " </h5>
        <h5>Confidence Interval (95%) High Endpoint: " . $confhighhr . ":" . $confhighmin . ":" . $confhighsec . "</h5>
        ";
    }
}

function runRoutine($species, $nest) //Function to run all of the functions and output the stats, given a species id and nest site
{
    $connection = getconnection();
    $repd = getEventsDay($connection, $species, $nest);
    $dura = getEventsDuration($connection, $species, $nest);
    $time = getEventsTime($connection, $species, $nest);
    
    echo "<div id=\"perdaystats\" class=\"well\">
    <div class=\"row-fluid\">
    <h3>Stats for Recess Events per Day</h3>
    <div class=\"datatable\" id=\"perdaydt\">
    <div id=\"perdaydtcol1\"></div>
    <div id=\"perdaydtcol2\"></div>
    <div id=\"perdaydata\">
    ";

    runOperations("per-day", $repd);
    
    echo "</div><div id=\"perdaygraphcon\">
    <div id=\"perdaygraph\">";
    graphNumber($repd, $species);
    echo "</div></div>
    
    </div>";

    echo "</div>
    </div>";
    
    echo "<div id=\"durationstats\" class=\"well\">
    <div class=\"row-fluid\">
    <h3>Stats for Recess Event Duration</h3>";
    
    runOperations("duration", $dura);
    
    echo "<div id=\"duragraph\">";
    graphDuration($dura, $species);
    echo "</div>";
    
    echo "</div>
    </div>";
    
    echo "<div id=\"timestats\" class=\"well\">
    <div class=\"row-fluid\">
    <h3>Stats for Recess Event Time of Day</h3>";
    
    runOperations("time", $time);
    
    echo "</div>
    </div>";
}

//Code to be executed via AJAX

$switch = $_POST['action'];
$species = $_POST['species'];
$nest = $_POST['nestsite'];

if(empty($species))
{
    $species = 1;
}

if(!empty($switch) && $switch == "goforit")
{
    runRoutine($species, $nest);
}

?>
