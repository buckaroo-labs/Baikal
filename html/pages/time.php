<?php 

//mostly this will use the defaults in folder.php
/*
$pagevar="time";
$pagevar2="timerecord";
$componenttype='VTODO';

$tdata_xform[0]=array('link','index.php?p=timerecord');
$tdata_xform[1]=array('','');
$tdata_xform[2]=array('datetimeformat','');

$where=" WHERE i.uri ='projecttime'";
$thead=array("ID", "Summary", "Start", "End", "Project");

$tdata[0]=array('Q','id');
$tdata[1]=array('V','SUMMARY');
$tdata[2]=array('V','DTSTART');
$tdata[3]=array('V','COMPLETED');
$tdata[4]=array('V','CATEGORIES');


include("pages/folder.php");

What if we did it just like lists?
*/
//https://sabre.io/vobject/vcard/
use Sabre\VObject;
if (isset($_SESSION['username'])) {
   
    function newItemForm() {
        global $categoryname;
        global $categories;
        echo '<div id="newitem">';
        if (!isset($categoryname)) { 
            echo '<h4 class="formheader">New Item</h4>';
            echo '    <form action="index.php?p=time" method="POST" id="newitemform">';
        } else {
            echo '    <form action="index.php?p=time&category='.$categoryname.'" method="POST" id="newitemform">';
        }
        echo '  <input type="hidden" name="action" value="create">
                <input type="hidden" name="type"  value="VTODO">
                <input type="hidden" name="parenturi" value="projecttime">';
        if (!isset($categoryname)) echo '        <label for="title">Work Summary:</label><br>';
        echo '        <input type="text" name="title"  value="">';
                if (!isset($categoryname)) {
                    echo '<br><label for="CATEGORIES">Project:</label><br>
                        <input type="text" name="CATEGORIES"  value=""><br>';
                    echo '    </form><br>
                        <button type="submit" form="newitemform" value="Submit">Submit</button>
                    </div>';
                } else {
                    echo '<input type="hidden" name="CATEGORIES"  value="'.$categoryname.'">';
                    echo '<input type="submit" value="Add new">   </form>  
                    </div>';
                }
                

        if (!isset($categoryname)) {
            echo '<div class="w3-container" id="listcategories">
            <h4 class="datagrouplist">Projects</h4>
            <ul>';
            ksort($categories);
            foreach($categories as $key=>$value) {
                $keyid=str_replace(" ","",$key);
                $keyid=str_replace("'","",$keyid);
                $keyid=str_replace("&","-",$keyid);
                if (strlen($key)>0 && !strpos($key,",")) echo '<li id="' . $keyid . '" class="vlistcat"><a href="index.php?p=time&category=' . $key . '">' . $key . '</a></li>';
            }
            echo '</ul></div>';
        }
    } //newItemForm

    $columns=" c.id, c.uri, c.calendardata, i.principaluri as owner, i.displayname as calendarname ";
    $from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
    $where=" WHERE i.uri='projecttime' and i.principaluri='principals/" . $_SESSION['username'] . "'";
    $sql="SELECT count(*) " . $from . $where;
    $resultcount=$dds->getInt($sql);
    $dds->setMaxRecs(9999);
    $sql="SELECT " . $columns . $from . $where;
    $result=$dds->setSQL($sql);
    echo '<div id="ListItems" class="w3-container" style="overflow:hidden">';

    if(isset($_GET['category'])) {
        $categoryname=htmlspecialchars($_GET['category']);
        $headline=ucwords($categoryname);
    } else {
        $headline="&#128348; Time Entries";
    }



    echo '<h2>'.$headline.'</h2>';
    if (isset($categoryname)) {
        echo newItemForm();
        echo ('<table id="vtodotable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Summary</th><th>Start</th><th>Actions</th></tr>');
    } else {
        echo ('<table id="vtodotable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Summary</th><th>Project</th><th>Actions</th></tr>');
    }

    error_reporting(E_ERROR | E_PARSE);
            $categories=[];
    while ($rrow=$dds->getNextRow('assoc')) {
        $vcalendar = VObject\Reader::read($rrow['calendardata'], VObject\Reader::OPTION_FORGIVING);

        foreach($vcalendar->VTODO as $vtodo) {
            $starttime='';
            $endtime='';
            $summary= (string)$vtodo->SUMMARY;
            if ($vtodo->DTSTART) {
                $dtstart = $vtodo->DTSTART->getDateTime();
            } else {
                $dtstart = $vtodo->DTSTAMP->getDateTime();
            }
            //$starttime= $dtstart->format(\DateTime::W3C);
            $starttime=displayFormatDateTime($dtstart);
            if ( $vtodo->DTEND) {
                $dtend = $vtodo->DTEND->getDateTime();
                //$endtime= $dtend->format(\DateTime::W3C);
                $endtime=displayFormatDateTime($dtend);
            }
            if ($vtodo->CATEGORIES) {
                $category=(string)$vtodo->CATEGORIES;
                $categories[$category]=0;
            } else {
                $category='';
            }
            if ($vtodo->STATUS) $status="status_" .$vtodo->STATUS; else $status="status_unknown";
            if ($vtodo->COMPLETED) $checked=" checked"; else $checked="";
            
            if (isset($categoryname)) {
                $append="&category=" .$categoryname;
                if(strpos($category,$categoryname)!==false) $echo=true; else $echo=false;
            } else {
                $append="";
                $echo=true;
                }
            $field3=$category;
            if (isset($categoryname)) $field3=$starttime;
            if ($echo) echo ('<tr class="' . $status . ' listitem_tr '. $category .'"><td><a href="index.php?p=reminder&id='.$rrow['id'].'">' .$rrow['id']. '</a></td><td>'.$summary.'</td><td>'.$field3.' </td><td><form style="float:left" method="POST" action="index.php?p=time'.$append .'" id="taskstatusform_'.$rrow['id'].'"><input type="hidden" name="type" value="VTODO"><input type="hidden" name="action" value="togglestatus"><input type="hidden" name="id" value="'.$rrow['id'].'"><input onChange="this.form.submit()" id="taskstatus_'.$rrow['id'].'" class="taskstatusbox" type="checkbox" ' .$checked .' /></form> <form method="POST" action="index.php?p=time'.$append .'" onClick="this.submit()" class="itemdeleteform" id="deletetask_'.$rrow['id'].'"><input type="hidden" name="type" value="VTODO"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="'.$rrow['id'].'"><label >🗑️</label></form></td></tr>'); 
       
        } 



    }
    echo "</table>\n</div>";
    echo "<div id='rhs'>";
    if (!isset($categoryname)) echo newItemForm();
    echo "</div>"; //rhs

} else {
    //must log in
    echo "You must be logged in to use this page.";
}
