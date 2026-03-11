<?php
require_once("lib/functions.php");
require_once("Hydrogen/lib/Debug.php");
//This file will parse incoming POST data, call the appropriate functions from 
// another file to handle the data changes, and then as appropriate for context
// will do data output or set variables indicating what the including script should do next
$object = new stdClass();

if(isset($_POST['action'])) {
  //use case is for this file being included from index.php
  //handle any data POSTed to index.php along with $_POST['action']
  //generally expect an action, an object type, one or more attributes, and an ID

  debug("Responding to POST data");
  if (!isset($_SESSION['username'])) goto errors;
  if (!isset($_POST['type'])) goto errors;
  if ($_POST['action']!='create' && $_POST['type']!='list' && !isset($_POST['id']) && !isset($_POST['ID'])) goto errors;
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
        case 'list':
          //code
          break;
        default:
          //code          
      }
      break;
    case 'togglestatus':
      //code
      switch ($_POST['type']) {
        case 'VTODO':
          //if complete, mark incomplete; vice versa
          if (isset($_POST['id']) && is_numeric($_POST['id'])) $object=new VTODO($_POST['id']);
          $object->toggle();
          $object->save();
          break;
        default:
      break;
      }
    case 'reset':
      //code
      switch ($_POST['type']) {
        case 'list':
          //reset all completed tasks in list
          if(isset($_GET['category'])) {
            $categoryname=htmlspecialchars($_GET['category']);
            //loop through all VTODOS in user's calendar having calendar uri of "lists." If VTODO category matches and has COMPLETED property, remove it and set STATUS to OPEN  
            resetTaskList($categoryname,$_SESSION['username']);
          }
          break;
        default:
      break;
      }
    case 'update':
            //code block;
      switch ($_POST['type']) {
        case 'VTODO':
          //code
          //$object=new VTODO($_POST['id']);
          break;
        case 'VEVENT':
          //this needs to be more specific when the VEVENT class is written
          //$object=new VCALENDAR($_POST['id']);
          break;
        case 'VJOURNAL':
          //this needs to be more specific when the VJOURNAL class is written
          //$object=new VCALENDAR($_POST['id']);
          break;
        case 'recurrence':
          //expect the following POST data:
          //StartDate, StartTime, Recurrence, recur_units, recur_scale, recur_float,
          //EndDate, EndTime, GraceTime, grace_units, grace_scale,
          //Alarms, alarm_interval_units, alarm_interval_scale, passive_units, passive_scale.
          //Any further POST data not implemented at this time.
          include("recurrencepost.inc.php");

          break;
        default:
          //code          
      }
      break;
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
        case 'list':
          //code
          break;
        default:
          //code          
      }
      break;

    default:
      //code block
  }

  if ($_POST['action']=='create' || $_POST['action']=='update') {
    if ($_POST['type']!='recurrence') {
      debug("Setting vobject properties");
      $object->setProperties($_POST);
      debug("Saving vobject");
      $object->save();
    }
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
      debug($reason);
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
goto noerrors;
errors:
debug("Error encountered in POST data");
noerrors:
if (!isset($_SESSION['username'])) reject("Unauthenticated user");
if (!isset($_POST['type'])) reject("Unspecified type");
 if ($_POST['action']!='create' && $_POST['type']!='list' && !isset($_POST['id']) && !isset($_POST['ID']))  reject("Unspecified ID");