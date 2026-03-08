<?php

debug("determining page routing");
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
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'folder')==0) {
	$include= "pages/folder.php";  
	$pagetitle="Folder";
	$headline = '<h1>Folder</h1>' ;
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
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'tasks')==0) {
	$include= "pages/tasks.php";  
	$pagetitle="Tasks";
	$headline = '<h1>To Do</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'journal')==0) {
	$include= "pages/journal.php";  
	$pagetitle="Journal";
	$headline = '<h1>Journal</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'entry')==0) {
	$include= "pages/entry.php";  
	$pagetitle="Journal Entry";
	$headline = '<h1>Journal Entry</h1>' ;
} elseif (isset($_GET['p']) && strcmp($_GET['p'],'lists')==0) {
	$include= "pages/lists.php";  
	$pagetitle="Lists";
	$headline = '<h1>To-Do Lists</h1>' ;
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
