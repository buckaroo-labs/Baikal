<?php

debug("determining page routing");
if (isset($_GET['p']) && strcmp($_GET['p'],'login')==0) {
	$include= "Hydrogen/pages/Login.php";  
	$pagetitle="Log In";
	$headline = '<h1>Log In</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'about')==0) {
	$include= "pages/about.php";  
	$pagetitle="About";
	$headline = '<h1>About</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'help')==0) {
	$include= "pages/help.php";  
	$pagetitle="Help";
	$headline = '<h1>Help</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'register')==0) {
	$include= "Hydrogen/pages/Register.php";  
	$pagetitle="Register";
	$headline = '<h1>Register</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'Account')==0) {
	$include= "pages/account.php";  
	$pagetitle="Account";
	$headline = '<h1>Account</h1>' ;
	$login_required=true;
}elseif (isset($_GET['p']) && strcmp($_GET['p'],'contacts')==0) {
	$include= "pages/contacts.php";  
	$pagetitle="Contacts";
	$headline = '<h1>🪪 Contacts</h1>' ;
	$login_required=true;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'time')==0) {
	$include= "pages/time.php";  
	$pagetitle="Time";
	$headline = '<h1>&#128348; Time Entry</h1>' ;
	$login_required=true;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'contact')==0) {
	$include= "pages/contact.php";  
	$pagetitle="Contact";
	$headline = '<h1>Contact</h1>' ;
	$login_required=true;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'events')==0) {
	$include= "pages/events.php";  
	$pagetitle="Events";
	$headline = '<h1>&#128198; Events</h1>' ;
	$login_required=true;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'folder')==0) {
	$include= "pages/folder.php";  
	$pagetitle="Folder";
	$headline = '<h1>Folder</h1>' ;
	$login_required=true;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'event')==0) {
	$include= "pages/event.php";  
	$pagetitle="Event";
	$headline = '<h1>Event</h1>' ;
	$login_required=true;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'reminders')==0) {
	$include= "pages/reminders.php";  
	$pagetitle="Reminders";
	$headline = '<h1>🔄 Reminders</h1>' ;
	$login_required=true;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'reminder')==0) {
	$include= "pages/reminder.php";  
	$pagetitle="Reminder";
	$headline = '<h1>Reminder</h1>' ;
	$login_required=true;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'tasks')==0) {
	$include= "pages/tasks.php";  
	$pagetitle="Tasks";
	$headline = '<h1>☑ Tasks</h1>' ;
	$login_required=true;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'journal')==0) {
	$include= "pages/journal.php";  
	$pagetitle="Journal";
	$headline = '<h1>📖 Journal</h1>' ;
	$login_required=true;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'entry')==0) {
	$include= "pages/entry.php";  
	$pagetitle="Journal Entry";
	$headline = '<h1>Journal Entry</h1>' ;
	$login_required=true;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'lists')==0) {
	$include= "pages/lists.php";  
	$pagetitle="Lists";
	$headline = '<h1>📋 To-Do Lists</h1>' ;
	$login_required=true;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'alarms')==0) {
	$include= "pages/alarms.php";  
	$pagetitle="Alarms";
	$headline = '<h1>&#9200; Alarms</h1>' ;
	$login_required=true;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'alarm')==0) {
	$include= "pages/alarm.php";  
	$pagetitle="Alarm";
	$headline = '<h1>&#9200; Alarm</h1>' ;
	$login_required=true;
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
