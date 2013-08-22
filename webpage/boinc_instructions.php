<?php

require_once('/projects/wildlife/html/inc/util.inc');

require_once('/home/tdesell/wildlife_at_home/webpage/navbar.php');
require_once('/home/tdesell/wildlife_at_home/webpage/footer.php');
require_once('/home/tdesell/wildlife_at_home/webpage/wildlife_db.php');
require_once('/home/tdesell/wildlife_at_home/webpage/my_query.php');

$bootstrap_scripts = file_get_contents("/home/tdesell/wildlife_at_home/webpage/bootstrap_scripts.html");

echo "
<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>Wildlife@Home: BOINC Instructions</title>

    <!-- For bootstrap -->
    $bootstrap_scripts

    <script type='text/javascript' src='user_video_list.js'></script>

    <style>
    body {
        padding-top: 60px;
    }

    @media (max-width: 979px) {
        body {
            padding-top: 0px;
        }
    }

        .well {
           position: relative;
           margin: 15px 5px;
           padding: 39px 19px 14px;
           *padding-top: 19px;
           border: 1px solid #ddd;
           -webkit-border-radius: 4px;
           -moz-border-radius: 4px;
           border-radius: 4px; 
        }

        .tab {
           position: absolute;
           top: -1px;
           left: -1px;
           padding: 3px 7px;
           font-size: 14px;
           font-weight: bold;
           background-color: #f5f5f5;
           border: 1px solid #ddd;
           color: #606060; 
           -webkit-border-radius: 4px 0 4px 0;
           -moz-border-radius: 4px 0 4px 0;
           border-radius: 4px 0 4px 0;
        }

        .title {
            text-align: center;
           position: absolute;
           top: -1px;
           left: -1px;
           width: 100%;
           padding: 3px 0px 0px 0px;
           font-size: 14px;
           font-weight: bold;
           background-color: #f5f5f5;
           border: 1px solid #ddd;
           color: #606060; 
           -webkit-border-radius: 4px 4px 0px 0px;
           -moz-border-radius: 4px 4px 0px 0px;
           border-radius: 4px 4px 0px 0px;
        }
    </style>
";


echo "
</head>
<body>";


$active_items = array(
                    'home' => '',
                    'watch_video' => '',
                    'message_boards' => '',
                    'preferences' => '',
                    'about_wildlife' => '',
                    'community' => 'active'
                );

print_navbar($active_items);

echo "
    <div class='well well-large' style='padding-top:5px; padding-bottom:5px'>
        <div class='row-fluid'>
            <div class='span12'>
                <h3>Wildlife@Home and BOINC</h3>
                <p>Wildlife@Home uses the <a href='http://boinc.berkeley.edu'>Berkeley Open Infrastructure for Network Computing (BOINC)</a> for volunteer computing. You can download and install BOINC, attach to our project, and volunteer your computer to aid us in using computer vision algorithms to find out what is happening in the video gathered by our field biologists. Eventually, we will use these volunteered computers to filter this video, so that the video we show to our users contains mostly interesting events.
                </p>
            </div>
        </div>
    </div>

    <div class='well well-large' style='padding-top:5px'>
        <div class='row-fluid'>
            <div class='span12'>
                <h3>Instructions</h3>
                <ul>
                <li>  If you're already running BOINC, select Attach to Project. If not, <a target='_new' href='http://boinc.berkeley.edu/download.php'>download, install and run BOINC</a>. </li>
                <li> When prompted, enter <b>http://volunteer.cs.und.edu/wildlife/</b></li>
                <li> If you're running a command-line or pre-5.0 version of BOINC, <a href='create_account_form.php'>create an account</a> first. </li>
                <li> If you have any problems, <a href='http://boinc.berkeley.edu/help.php'>get help here</a>. </li>
                </ul>

            </div>
        </div>
    </div>

    <div class='well well-large' style='padding-top:5px'>
        <div class='row-fluid'>
            <div class='span12'>
                <h3>Rules and Policies</h3>

                <h4>Run Wildlife@Home only on authorized computers</h4>
                    <p>Run Wildlife@Home only on computers that you own, or for which you have obtained the owner's permission. Some companies and schools have policies that prohibit using their computers for projects such as Wildlife@Home.</p>

                <h4>How Wildlife@Home will use your computer</h4>
                    <p>When you run Wildlife@Home on your computer, it will use part of the computer's CPU power, disk space, and network bandwidth. You can control how much of your resources are used by Wildlife@Home, and when it uses them.</p>
                    <p>The work done by your computer contributes to the goals of Wildlife@Home, as described on its web site. The application programs may change from time to time.</p>

                <h4>Privacy policy</h4>
                    <p>Your account on Wildlife@Home is identified by a name that you choose. This name may be shown on the Wildlife@Home web site, along with a summary of the work your computer has done for Wildlife@Home. If you want to be anonymous, choose a name that doesn't reveal your identity.</p>
                    <p>If you participate in Wildlife@Home, information about your computer (such as its processor type, amount of memory, etc.) will be recorded by Wildlife@Home and used to decide what type of work to assign to your computer. This information will also be shown on Wildlife@Home's web site. Nothing that reveals your computer's location (e.g. its domain name or network address) will be shown.</p>
                    <p>To participate in Wildlife@Home, you must give an address where you receive email. This address will not be shown on the Wildlife@Home web site or shared with organizations. Wildlife@Home may send you periodic newsletters; however, you can opt out at any time.</p>
                    <p>Private messages sent on the Wildlife@Home web site are visible only to the sender and recipient.  Wildlife@Home does not examine or police the content of private messages.  If you receive unwanted private messages from another Wildlife@Home user, you may add them to your <a href='edit_forum_preferences_form.php'>message filter</a>.  This will prevent you from seeing any public or private messages from that user. </p>
                    <p>If you use our web site forums you must follow the <a href=moderation.php>posting guidelines</a>.  Messages posted to the Wildlife@Home forums are visible to everyone, including non-members.  By posting to the forums, you are granting irrevocable license for anyone to view and copy your posts. </p>
                <h4>Is it safe to run Wildlife@Home?</h4></p>
                    <p>Any time you download a program through the Internet you are taking a chance: the program might have dangerous errors, or the download server might have been hacked. Wildlife@Home has made efforts to minimize these risks. We have tested our applications carefully. Our servers are behind a firewall and are configured for high security. To ensure the integrity of program downloads, all executable files are digitally signed on a secure computer not connected to the Internet.</p>
                    <p>The applications run by Wildlife@Home may cause some computers to overheat. If this happens, stop running Wildlife@Home or use a <a href='download_network.php'>utility program</a> that limits CPU usage.</p>
                    <p>Wildlife@Home was developed by the Wildlife@Home team at the University of North Dakota. BOINC was developed at the University of California.</p>

                <h4>Liability</h4>
                    <p>Wildlife@Home and AstroInformatics Group assume no liability for damage to your computer, loss of data, or any other event or condition that may occur as a result of participating in Wildlife@Home.</p>

                <h4>Other BOINC projects</h4>
                    <p>Other projects use the same platform, BOINC, as Wildlife@Home. You may want to consider participating in one or more of these projects. By doing so, your computer will do useful work even when Wildlife@Home has no work available for it.</p>
                    <p>These other projects are not associated with Wildlife@Home, and we cannot vouch for their security practices or the nature of their research. Join them at your own risk.</p>



            </div>
        </div>
    </div>
";


print_footer();

echo "
</body>
</html>
";

?>
