<?php

//This file will parse incoming POST data, call the appropriate functions from 
// another file to handle the data changes, and then as appropriate for context
// will do data output or set variables indicating what the including script should do next
$object = new stdClass();

if(isset($_POST['action'])) {
  //use case is for this file being included from index.php
  //handle any data POSTed to index.php along with $_POST['action']
  //generally expect an action, an object type, one or more attributes, and an ID
  if (!isset($_SESSION['username'])) goto errors;
  if (!isset($_POST['type'])) goto errors;
  if ($_POST['action']!='create' && !isset($_POST['id'])) goto errors;
  loadClassHierarchy();

  switch ($_POST['action']) {
    case 'create':
      //code block
      switch ($_POST['type']) {
        case 'VTODO':
          //code
          debug("Creating new VTODO");
          if (isset($_POST['parenturi'])) {
            $parenturi=htmlspecialchars($_POST['parenturi']);
          } else {
            $parenturi='default';
          }
          if (isset($_POST['title'])) {
            $summary=htmlspecialchars($_POST['title']);
          } else {
            $summary='New To Do';
          }
          $object=new VTODO(0,$summary,$parenturi);
          break;
        case 'VEVENT':
          //code
          break;
        default:
          //code          
      }
      break;
    case 'update':
    case 'delete':
      //code block;
      switch ($_POST['type']) {
        case 'VTODO':
          //code
          $object=new VTODO($_POST['id']);
          break;
        case 'VEVENT':
          //this needs to be more specific when the VEVENT class is written
          $object=new VCALENDAR($_POST['id']);
          break;
        case 'VJOURNAL':
          //this needs to be more specific when the VJOURNAL class is written
          $object=new VCALENDAR($_POST['id']);
          break;
        default:
          //code          
      }
      break;

    default:
      //code block
  }

  if ($_POST['action']=='create' || $_POST['action']=='update') {
    debug("Setting vobject properties");
    $object->setProperties($_POST);
    debug("Saving vobject");
    $object->save();
  } elseif ($_POST['action']=='delete') {
    $object->delete();
  }

} else {
  //handle requests sent directly to this URL via GET, POST, PUT, PATCH, or DELETE
  //check content-type header?
  //json_decode(file_get_contents('php://input'),$bool); ?
}

function reject($reason) {
      $errorOutput=$reason;
}

function loadClassHierarchy() {
  global $object;
  switch ($_POST['type']) {
    case 'VTODO':
      //code
      require_once ("lib/clsVTODO.php");
      break;
    case 'VEVENT':
      //code
      break;
    default:
      //code
              
  }
}

errors:
if (!isset($_SESSION['username'])) reject("Unauthenticated user");
if (!isset($_POST['type'])) reject("Unspecified type");
if ($_POST['action']!='create' && !isset($_POST['id'])) reject("Unspecified ID");