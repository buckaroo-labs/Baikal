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


if (isset($_GET['p']) && strcmp($_GET['p'],'login')==0) {
	$include= "Hydrogen/pages/Login.php";  
		$pagetitle="Log In";
	$headline = '<h1>Log In</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'register')==0) {
	$include= "Hydrogen/pages/Register.php";  
	$pagetitle="Register";
	$headline = '<h1>Register</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'contacts')==0) {
	$include= "pages/contacts.php";  
	$pagetitle="Contacts";
	$headline = '<h1>Contacts</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'contact')==0) {
	$include= "pages/contact.php";  
	$pagetitle="Contact";
	$headline = '<h1>Contact</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'events')==0) {
	$include= "pages/events.php";  
	$pagetitle="Events";
	$headline = '<h1>Events</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'event')==0) {
	$include= "pages/event.php";  
	$pagetitle="Event";
	$headline = '<h1>Event</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'reminders')==0) {
	$include= "pages/reminders.php";  
	$pagetitle="Reminders";
	$headline = '<h1>Reminders</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'reminder')==0) {
	$include= "pages/reminder.php";  
	$pagetitle="Reminder";
	$headline = '<h1>Reminder</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'todo')==0) {
	$include= "pages/todo.php";  
	$pagetitle="To Do";
	$headline = '<h1>To Do</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'todos')==0) {
	$include= "pages/todos.php";  
	$pagetitle="To Do";
	$headline = '<h1>To Do</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'journal')==0) {
	$include= "pages/journal.php";  
	$pagetitle="Journal";
	$headline = '<h1>Journal</h1>' ;
} else {
	if (isset($_GET['menu']) && $_GET['menu']=="baikal") {
		$pagetitle="Admin";
		$headline = '<h1>Admin</h1>' ; 
	} elseif (isset($_GET['menu']) && $_GET['menu']=="sabre") {
		$pagetitle="Explorer";
		$headline = '<h1>Explorer</h1>' ; 
	} else {
		$pagetitle="Home";
		$headline = '<h1>Home</h1>' ;   
	}
}

include "Hydrogen/pgTemplate.php";

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