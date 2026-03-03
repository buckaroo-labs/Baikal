<?php
require_once "lib/clsVTODO.php";

class Reminder extends VTODO {
    //Recurrence management functions apply here

    //Class hierarchy: DAVObject->VCALENDAR->VTODO->Reminder

} //end class

function testit() {
    $r=new Reminder();
    echo $r->serialize();
}
