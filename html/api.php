<?php

//This file will parse incoming POST data, call the appropriate functions from 
// another file to handle the data changes, and then as appropriate for context
// will do data output or set variables indicating what the including script should do next

if(isset($_POST['action'])) {
  //use case is for this file being included from index.php
  //handle any data POSTed to index.php along with $_POST['action']
  //generally expect an action, an object type, one or more attributes, and an ID
  if (!isset($_SESSION['username'])) reject("Unauthenticated user");
  if (!isset($_POST['type'])) reject("Unspecified type");
  switch ($_POST['action']) {
    case 'create':
      //code block
      break;
    case 'update':
      //code block;
      break;
    case 'delete':
      //code block
      break;
    default:
      //code block
  }
  //so far, we have only been validating the requested action. now we review any validation errors
  //before performing the action
  if (!isset($errorOutput)) {
    
  }
} else {
  //handle requests sent directly to this URL via GET, POST, PUT, PATCH, or DELETE
  //check content-type header?
  //json_decode(file_get_contents('php://input'),$bool); ?
}

function reject($reason) {
      $errorOutput=$reason;
}