<?php 
/*This file will serve as the basis for pages listing contacts, events, journal entries and tasks 

include this file after setting the following:
$sql for main listing:  must return object primary key as id, principaluri as owner, calendar/book displayname as subfolder_name, calendar/book id as subfolder_id, and calendar/card data as objdata
 ? $tablename for subfolder listing ('calendarinstances' or 'addressbooks')
$headline for H2 content
$pagevar for filtering on subfolder
$pagevar2 for individual vobject display
$componenttype (only for VCALENDAR objects)
$thead array for table headers
$tdata two-by-n array for table data source:
    ['V']['FN'] indicates get ->FN from vobject
    ['V']['N'] indicates get ->N from vobject
    ['Q']['subfoldername'] indicates to get value of 'subfoldername' key from SQL row result
    ['Q']['id'] indicates to get 'id' from SQL row result
    ...
$tdata_xform two-by-n array for table data transformations
    ['link']['url'] indicates to link to the given url. URL must contain at least one GET variable appended using "?". A GET variable of "id" will be appended using "&", using the id value from the SQL row result.
    ['datetimeformat'][''] indicates to reformat as a date/time
    [''][''] indicates no change.

    defaults follow:
*/
if (!isset($pagevar)) $pagevar="folder";
if (!isset($pagevar2)) $pagevar2="file";
if (!isset($headline)) {
    $headline="Folder";
    $componenttype='VEVENT';
}
if (!isset($thead)) $thead=array("ID", "Summary", "Start", "Calendar");
if (!isset($tdata)) {
        $tdata[0]=array('Q','id');
        $tdata[1]=array('V','SUMMARY');
        $tdata[2]=array('V','DTSTART');
        $tdata[3]=array('Q','subfolder_name');
}
if (!isset($tdata_xform)) {
    $tdata_xform[0]=array('link','index.php?p=event');
    $tdata_xform[1]=array('','');
    $tdata_xform[2]=array('datetimeformat','');
}
$and='';
if (isset($_GET['folderid']) && is_numeric($_GET['folderid'])) {
    $folderid=$_GET['folderid'];
    $and=" AND i.id=" . $folderid;
}
if (isset($_GET['category']) && $_GET['category']==htmlspecialchars($_GET['category'])) {
    $category=$_GET['category'];
}

if (isset($_GET['todostatus']) && $_GET['todostatus']==htmlspecialchars($_GET['todostatus'])) {
    $todostatus=$_GET['todostatus'];
}

use Sabre\VObject;
if (isset($_SESSION['username'])) {


    $columns=" c.id, c.uri, c.calendardata as objdata, i.principaluri as owner, i.displayname as subfolder_name, i.id as subfolder_id ";
    $from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
    if(!isset($where)) $where=" WHERE i.uri NOT IN ('lists','projecttime','recurring')";
    if(isset($componenttype)) $and .=" AND  c.componenttype='".$componenttype."'";
    $and .="  AND i.principaluri='principals/" . $_SESSION['username'] . "'";
    if(!isset($sql)) $sql="SELECT " . $columns . $from . $where . $and;

    $dds->setMaxRecs(9999);
    $result=$dds->setSQL($sql);
    echo '<div id="folderitemsdiv" class="w3-twothird w3-container" style="overflow:hidden">' . "\n";
    echo '<h2>'.$headline.'</h2>' . "\n";
    echo ('<table id="folderitems" class="table sortable" style="clear:both">' . "\n") ; 
    echo ('<tr><th>'. implode('</th><th>',$thead) .'</th></tr>' . "\n");

    error_reporting(E_ERROR | E_PARSE);
    $categories=[];
    $todostatuses=[];
    $subfolders=[]; //calendars or addressbooks
    $orgs=[];
    //Adding new variables for PHP sorting of DB results.
    $sortme=[];
    $datacount=0;
    //buffer the TR elements being created row-by-row as SQL results are fetched; possibly throw them away 
    //  and instead use array elements being created row-by-row
    ob_start();
    while ($rrow=$dds->getNextRow('assoc')) {
        $owner=str_replace('principals/','',$rrow['owner']);
        try {
            unset ($vobj);
            $vobj = VObject\Reader::read($rrow['objdata'], VObject\Reader::OPTION_FORGIVING);
        } catch (Exception $e) {
            debug("Vobject reader exception: " . $e->getMessage());
        }
        if (isset($vobj)) {
            $status="";
            if(isset($componenttype)) {
                if ($componenttype=='VTODO') $status="status_unknown";
                $temp=(string)$vobj->{$componenttype}->CATEGORIES;
                if ($vobj->{$componenttype}->STATUS) {
                    $status="status_" .$vobj->{$componenttype}->STATUS; 
                    $k=(string) $vobj->{$componenttype}->STATUS;
                    if (!array_key_exists($k,$todostatuses)) $todostatuses[$k]=0; 
                }
            } else $temp=(string)$vobj->CATEGORIES;
            $temp=rtrim($temp,";");
            $temp2=str_replace(" ","",$temp);
            $temp2=str_replace("&","-",$temp2);
            $temp3=(string)$vobj->ORG;
            $temp3=rtrim($temp3,";");
            $temp3=rtrim($temp3," ");
            $temp4=str_replace(" ","-",$temp3);
            $temp4=str_replace("'","-",$temp4);
            $temp4="org_" . str_replace("&","-",$temp4);
            if (!array_key_exists($temp,$categories)) $categories[$temp]=0;
            if (!array_key_exists($rrow['subfolder_name'],$subfolders)) $subfolders[$rrow['subfolder_name']]=$rrow['subfolder_id'];
            if (!array_key_exists($temp3,$orgs)) $orgs[$temp3]=0;
            $hidden="";
            if(isset($category) && $category!=$temp) $hidden=' style="display:none" ';
            if(isset($todostatus) && $vobj->{$componenttype}->STATUS && $todostatus!=$vobj->{$componenttype}->STATUS) $hidden=' style="display:none" ';
            //filter out completed/cancelled items by default
            if(!isset($todostatus) && $vobj->{$componenttype}->STATUS && $vobj->{$componenttype}->STATUS=="COMPLETED") $hidden=' style="display:none" ';
            if(!isset($todostatus) && $vobj->{$componenttype}->STATUS && $vobj->{$componenttype}->STATUS=="CANCELLED") $hidden=' style="display:none" ';
            
            echo ('<tr '. $hidden .'class="vobject ' . $status . " " .str_replace(","," ",$temp2). " " . str_replace(","," ",$temp4).'">'); 
            $sortme[$datacount]['hidden']=$hidden;
            $sortme[$datacount]['status']=$status;
            $sortme[$datacount]['categories']=$temp2;
            $sortme[$datacount]['org']=$temp4;


            for ($i=0; $i<count($tdata); $i++) {
                //if(!isset($category) || $category==$temp) {
                    echo '<td>';
                    if($tdata[$i][0]=='Q') {
                        $celldata=$rrow[$tdata[$i][1]];
                    } elseif ($tdata[$i][0]=='V') {
                        if(isset($componenttype)) {

                            //05-APR-2026: DTSTART is not required per RFC5545. DTSTAMP is.
                            //We will compensate here for VJOURNAL items having no DTSTART
                            if($componenttype=='VJOURNAL' && $tdata[$i][1]=='DTSTART'  && !($vobj->VJOURNAL->DTSTART)) {
                                $celldata=$vobj->VJOURNAL->DTSTAMP;
                            } else $celldata=$vobj->{$componenttype}->{$tdata[$i][1]};

                        } else $celldata=$vobj->{$tdata[$i][1]};
                    } else {
                        $celldata='';
                    }
                    $celldata=rtrim($celldata,";");
                    if (count($tdata_xform) >$i) {
                    if($tdata_xform[$i][0]=="link") {
                        $celldata= '<a href="'. $tdata_xform[$i][1] . '&id=' . $rrow['id'] . '">' . $celldata . '</a>'; 
                    } elseif ($tdata_xform[$i][0]=="datetimeformat") {
                        if(isset($celldata)) {
                            //Hoping that $celldata is a Sabre VObject property that supports this
                            $celldata=$celldata->getDateTime();
                            $celldata= displayFormatDateTime($celldata);
                        }
                    } 
                    } 
                    //if(!isset($category) || $category==$temp) 
                    echo $celldata;
                    $sortme[$datacount][$tdata[$i][1]]=$celldata;
                    echo '</td>';
                //}
            }

            echo ('</tr>' . "\n");
            $datacount++;
        } //reader success
    }
/*two options at this point: 
1) flush the output buffer and display the table HTML already calculated,    or
2) discard the buffer, sort the array data and output that as HTML instead
*/
$content='';
if (!$resort) {
    $content=ob_get_contents();
    ob_end_clean();
} else {
    ob_end_clean();
    $sortorder = SORT_ASC;
    if(isset($sortme[0]) && array_key_exists('FN',$sortme[0])) {
        $array_key_for_multisort="FN";
    } elseif (isset($sortme[0]) && array_key_exists('DTSTART',$sortme[0])) {
        $array_key_for_multisort="DTSTART";
        $sortorder = SORT_DESC;
    } else {
        $array_key_for_multisort="id";
    }
    usort($sortme,"keysort");
    for ($j=0; $j<$datacount; $j++) {

        echo ('<tr '. $sortme[$j]['hidden'] .'class="vobject ' . $sortme[$j]['status'] . " " .str_replace(","," ",$sortme[$j]['categories']). " " . str_replace(","," ",$sortme[$j]['org']).'">'); 
        for ($i=0; $i<count($tdata); $i++) {
            echo "<td>" . $sortme[$j][$tdata[$i][1]] . '</td>';
        }
        echo ('</tr>' . "\n");


    }

}

echo $content;
echo "</table>\n</div>";

echo '<div id="vobjgroups" class="w3-container" >';

if (count($categories)>1) {
echo '<h4 class="datagrouplist">Categories</h4><table id="vobjcategories">
<tr><th style="padding-right:20px;">Link</th><th>Toggle</th></tr>';
ksort($categories);
foreach($categories as $key=>$value) {
    $keyid=str_replace(" ","",$key);
    $keyid=str_replace("&","-",$keyid);
    $status="active";
    if(isset($category) && $category!=$key) $status="";
    if (strlen($key)>0 && !strpos($key,",")) echo '<tr><td><a style="text-decoration:none;" href="index.php?p='.$pagevar .'&category='. $keyid .'"><span class="enlargeonhover">🔗</span></a></td><td id="' . $keyid . '" class="vcardcategory '.$status.'">' . $key . '</td></tr>';
}
echo '</table>';
}

if (count($todostatuses)>0) {
echo '<h4 class="datagrouplist">Statuses</h4><table id="vobjstatuses">
<tr><th style="padding-right:20px;">Link</th><th>Toggle</th></tr>';
ksort($todostatuses);
foreach($todostatuses as $key=>$value) {
    $status="active";
    if(isset($todostatus) && $todostatus!=$key) $status="";
    if(!isset($todostatus) && $key=="COMPLETED")  $status="";
    if(!isset($todostatus) && $key=="CANCELLED")  $status="";
    if (strlen($key)>0 ) echo '<tr><td><a style="text-decoration:none;" href="index.php?p='.$pagevar .'&todostatus='. $key .'"><span class="enlargeonhover">🔗</span></a></td><td id="status_' . $key . '" class="vtodostatus '.$status.'">' . $key . '</td></tr>';
}
echo '</table><br>';
}

if (!isset($folderid) && $pagevar!="time" && count($subfolders)>0) {
echo '<div id="vsubfolders"><h4 class="datagrouplist">Subfolders</h4><ul>';
ksort($subfolders);
foreach($subfolders as $key=>$value) {
    $keyid=str_replace(" ","",$key);
    $keyid=str_replace("&","-",$keyid);
    $liClassAndID=' class="subfoldername"';
    if(isset($folderid) && $folderid==$rrow['id'])  {
        $liClassAndID .= ' id="activeSubfolderName" ';
    }
    echo '<li '. $liClassAndID .'"><a href="index.php?p='. $pagevar .'&folderid=' . $value . '">' . $key . '</a></li>';
}
echo '</ul></div>';
}
if (count($orgs)>1) {
echo '<div id="vobjorgs"><h4 class="datagrouplist">Organizations</h4><ul>';
ksort($orgs);
foreach($orgs as $key=>$value) {
    $keyid=str_replace(" ","-",$key);
    $keyid=str_replace("'","",$keyid);
    $keyid=str_replace("&","-",$keyid);
    if (strlen($key)>0 && !strpos($key,",")) echo '<li id="org_' . $keyid . '" class="vobjorg">' . $key . '</li>';
}
echo '</ul></div>';
}
echo '</div>';
echo '<script src="js/category-filter.js"></script>';
} else {
    //must log in
    echo "<p>You must be logged in to use this page.</p>";
}