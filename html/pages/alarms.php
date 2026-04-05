<?php 
//mostly this will use the defaults in folder.php
$pagevar="alarms";
$pagevar2="alarm";
//$componenttype='VEVENT';

$thead=array("ID", "Summary", "Start", "Alarm","Calendar");

$tdata[0]=array('Q','id');
$tdata[1]=array('V','SUMMARY');
$tdata[2]=array('V','DTSTART');
$tdata[3]=array('A','TRIGGER');
$tdata[4]=array('Q','subfolder_name');

$tdata_xform[0]=array('link','index.php?p=alarm');
$tdata_xform[1]=array('','');
$tdata_xform[2]=array('datetimeformat','');

//include("pages/folder.php");

$and='';
if (isset($_GET['folderid']) && is_numeric($_GET['folderid'])) {
    $folderid=$_GET['folderid'];
    $and=" AND i.id=" . $folderid;
}
if (isset($_GET['category']) && $_GET['category']==htmlspecialchars($_GET['category'])) {
    $category=$_GET['category'];
}
$columns=" c.id, c.uri, c.calendardata as objdata, i.principaluri as owner, i.displayname as subfolder_name, i.id as subfolder_id, c.componenttype ";
$from=" FROM calendarobjects c INNER JOIN calendarinstances i on c.calendarid=i.id ";
$where=" WHERE i.uri NOT IN ('lists','projecttime','recurring')";
//if(isset($componenttype)) $and .=" AND  c.componenttype='".$componenttype."'";
$and .="  AND i.principaluri='principals/" . $_SESSION['username'] . "'";
$sql="SELECT " . $columns . $from . $where . $and;

use Sabre\VObject;
if (isset($_SESSION['username'])) {
    $dds->setMaxRecs(9999);
    $result=$dds->setSQL($sql);
    echo '<div id="folderitemsdiv" class="w3-twothird w3-container" style="overflow:hidden">' . "\n";
    echo '<h2>'.$headline.'</h2>' . "\n";
    echo ('<table id="folderitems" class="table sortable" style="clear:both">' . "\n") ; 
    echo ('<tr><th>'. implode('</th><th>',$thead) .'</th></tr>' . "\n");

    error_reporting(E_ERROR | E_PARSE);
    $categories=[];
    $subfolders=[]; //calendars or addressbooks
    while ($rrow=$dds->getNextRow('assoc')) {
        $owner=str_replace('principals/','',$rrow['owner']);
        try {
            unset ($vobj);
            $vobj = VObject\Reader::read($rrow['objdata'], VObject\Reader::OPTION_FORGIVING);
        } catch (Exception $e) {
            debug("(alarms.php) Vobject reader exception: " . $e->getMessage());
        }
        if (isset($vobj)) {
                
            if(isset($componenttype)) {
                $temp=(string)$vobj->{$componenttype}->CATEGORIES;
            } else $temp=(string)$vobj->CATEGORIES;

            $temp2=str_replace(" ","",$temp);
            $temp2=str_replace("&","-",$temp2);
            if (!array_key_exists($temp,$categories)) $categories[$temp]=0;
            if (!array_key_exists($rrow['subfolder_name'],$subfolders)) $subfolders[$rrow['subfolder_name']]=$rrow['subfolder_id'];
            $hidden="";
            if(isset($category) && $category!=$temp) $hidden=' style="display:none" ';
            echo ('<tr '. $hidden .'class="vobject '.str_replace(","," ",$temp2). '">'); 
            
            for ($i=0; $i<count($tdata); $i++) {
                if(isset($vobj->{$rrow['componenttype']}->VALARM) ) {
                    echo '<td>';
                        if($tdata[$i][0]=='Q') {
                            $celldata=$rrow[$tdata[$i][1]];
                        } elseif ($tdata[$i][0]=='V') {
                            $celldata=$vobj->{$rrow['componenttype']}->{$tdata[$i][1]};
                        } else {
                            $celldata=$vobj->{$rrow['componenttype']}->VALARM->{$tdata[$i][1]};
                        }
                        
                        if (count($tdata_xform) >$i) {
                            if($tdata_xform[$i][0]=="link") {
                                $celldata= '<a href="'. $tdata_xform[$i][1] . '&id=' . $rrow['id'] . '">' . $celldata . '</a>'; 
                            } elseif ($tdata_xform[$i][0]=="datetimeformat") {
                                if(isset($celldata)) {
                                    //assuming that $celldata is a Sabre VObject property that supports this
                                    $celldata=$celldata->getDateTime();
                                    $celldata= displayFormatDateTime($celldata);
                                }
                            } 
                        } 
                    //if(!isset($category) || $category==$temp) 
                        echo $celldata;
                    echo '</td>';
                }
            }

            echo ('</tr>' . "\n");
        }
    }
echo ("</table>\n</div>");
}