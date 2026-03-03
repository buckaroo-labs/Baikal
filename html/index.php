<?php

/* The following code is meant to help you get started with configuration. 
You won't need to include it in your own application.
***CONFIG-START****/
//This file will load your app's key settings, and will in turn source the 
//  password settings, which are in a separate file not tracked in git.
//  if, when it is done, there is no value in $settings['JWT_SECRET_KEY'], then the browser
//  will be redirected to the setup page. Your application needs a unique secret key
//  to secure login tokens and sqlite database files.
require_once('settingsHydrogen.php');
if (empty($settings['JWT_SECRET_KEY'])) {
  header("Location: admin.php");
  exit;
}
/*****CONFIG-END *****/

//routes.inc.php reads the GET variables and determines what content to include
require("routes.inc.php");

//Hydrogen/pgTemplate.php handles general page layout, menus, cookies
require "Hydrogen/pgTemplate.php";

//this file will handle POST data for performing updates
if (isset($_POST['action'])) require ("api.php");

require_once("Hydrogen/db/clsDataSource.php");
?>
<!-- Main content: shift it to the right when the sidebar is visible -->
<div class="w3-main">

	<?php 
		if (isset($_GET['p']) && $_GET['p']=="login") {
			echo '<div class="w3-padding-64" style="display: grid; grid-template-columns: auto">';
		} else {
			echo '<div class="w3-padding-64" style="display: grid; grid-template-columns: auto auto">';
		}
  
       
        if(isset($include)) {
			
            include $include; 

        } else {
			include "Hydrogen/elements/LogoHeadline.php";
			echo '<div class="w3-twothird w3-container">';
			if (isset($_GET['menu']) && $_GET['menu']=="baikal") {
				echo "<p>This is " . $settings['appname']. ', a fork of <a href="https://sabre.io/baikal/" target="_blank">Ba&iuml;kal</a>, which is a calendar and contacts server built on <a href="https://sabre.io/dav/" target="_blank">sabre/dav</a>.</p>';

				echo "<p>Use the Dashboard, Users and Config links for Ba&iuml;kal&apos;s management features, logging in as <q>admin</q>.";
				echo '</div>';
			} elseif (isset($_GET['menu']) && $_GET['menu']=="sabre") {
				echo "<p>This is " . $settings['appname']. ', a fork of <a href="https://sabre.io/baikal/" target="_blank">Ba&iuml;kal</a>, which is a calendar and contacts server built on <a href="https://sabre.io/dav/" target="_blank">sabre/dav</a>.</p>';

				echo "<p>Use the DAV Home, Calendars and Contacts links for sabre/dav&apos;s folder navigation interface, logging in with your application credentials.</p>";	
				echo '</div>';
			} else {
				echo "<p>This is " . $settings['appname']. ', a fork of <a href="https://sabre.io/baikal/" target="_blank">Ba&iuml;kal</a>, which is a calendar and contacts server built on <a href="https://sabre.io/dav/" target="_blank">sabre/dav</a>.</p>';

				echo "<p>Use the Admin link for Ba&iuml;kal&apos;s management features. Use the Explorer link for sabre&apos;s folder navigation interface. Use the Contacts, Events, Reminders and Journal links for viewing your data.</p>";
				echo '</div>';
			}
        }      
        
        ?>
	<!--</div>-->
    </div> <!--end row div-->

</div> <!--end main div -->
<?php
	//Yes, it goes at the top, but it may use variables (session status) that are set by what happens in the middle -
	//   so include it at the end and then let it float to the top
	include 'Hydrogen/elements/Navbar.php';
	include "Hydrogen/elements/Footer.php";
?>
</body></html>