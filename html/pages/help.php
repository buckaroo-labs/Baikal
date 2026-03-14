<?php 
	include "Hydrogen/elements/LogoHeadline.php";
    if (isset($_SESSION['username'])) $uname=$_SESSION['username']; else $uname='username';

	echo '<div class="w3-twothird w3-container">';
    echo '<h4 class="reverse">Client Connection</h4>';
    if (!isset($_SESSION['username'])) {
        echo '<p>In the example URLs below, replace <q>username</q> with your own username.</p>';
    }

    //https://stackoverflow.com/questions/4503135/php-get-site-url-protocol-http-vs-https#14270161
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    $baseURL=$_SERVER['HTTP_HOST'];
    //this is unnecessary
    //if ($_SERVER['SERVER_PORT']!=80 && $_SERVER['SERVER_PORT']!=443) $baseURL .=":" . $_SERVER['SERVER_PORT'];
    $davfile=substr($_SERVER['REQUEST_URI'],0,strpos($_SERVER['REQUEST_URI'],"index.php"));
    $davfile.= "dav.php";
    $calendarsURL=$protocol . $baseURL . $davfile . '/calendars/' . $uname . '/';
    $addrbooksURL=$protocol . $baseURL . $davfile . '/addressbooks/' . $uname . '/';
    $principalURL=$protocol . $baseURL . $davfile . '/principals/' . $uname . '/';
    echo '<h5>Mozilla Thunderbird</h5>';
    echo '<p>To use your calendars, connect to '. $calendarsURL .' .</p>'; 
    echo '<p>To use your address books, connect to '. $addrbooksURL .' .</p>'; 
    echo '<p> </p>';
     echo '<h5>iOS</h5>';
    echo 'Connect to '. $principalURL .' .</p><br>'; 
    

    echo '<h4 class="reverse">Data management</h4>';
    echo '<p>The <a target="_blank" href="https://winscp.net/eng/index.php">WinSCP</a> application (for Windows) may be helpful in uploading VCF or ICS file data to the server. Use any of the URLs above, and select WebDAV as your file protocol.</p>';
    echo '</div>';
    
   
    
