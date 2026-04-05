<?php 

//https://sabre.io/vobject/vcard/
use Sabre\VObject;

function saveimage($data, $photofullpath) {
    // open the output file for writing
    if (!$ifp = fopen($photofullpath,'wb')) {
        debug( "Cannot open file ($photofullpath)");
        //exit;
        return false;
    } 

    if (fwrite($ifp,  $data) === FALSE) {
                debug( "Cannot write to file ($photofullpath)");
                //exit;
                return false;
    } else {
                debug( "Photo data written to file ($photofullpath)");
         
    }
    // clean up the file resource
    fclose( $ifp ); 

    return true; 
}

/*
supported properties:
FN
N
ORG
CATEGORIES
TITLE
ADR
EMAIL
PHONE
PHOTO
NOTE
?attachments
*/
if (isset($_SESSION['username'])) {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $contactid=$_GET['id'];
    } else {
        $contactid=0;
    }
    $columns=" c.id, c.uri, c.carddata, a.principaluri as owner, a.displayname as book_name ";
    $from=" FROM cards c INNER JOIN addressbooks a on c.addressbookid=a.id ";
    $where=" WHERE a.principaluri='principals/" . $_SESSION['username'] . "' and c.id=" . $contactid;
    $sql="SELECT " . $columns . $from . $where;
    $result=$dds->setSQL($sql);

    error_reporting(E_ERROR | E_PARSE);
    while ($rrow=$dds->getNextRow('assoc')) {
        $vcard = VObject\Reader::read($rrow['carddata']);
        $vcardsm =VObject\Reader::read($rrow['carddata']);
        unset($vcardsm->PHOTO);

        echo '<div id="Contact" class="w3-twothird w3-container" style="overflow:hidden">';
        echo '<h2 id="ContactName">' . $vcard->FN . '</h2>';
        echo ('<table id="vcardtable" class="table sortable" style="clear:both">' . "\n" . '<tr><th>ID</th><th>Categories</th><th>Organization</th><th>Book</th></tr>' . "\n");
        $telephone='';
        foreach($vcard->TEL as $tel) {
            $telephone.="Phone";
            if ($tel['TYPE']) {
                $telephone .= " (" . strtolower($tel['TYPE']) . ")";
            }
            $telephone .= ": " .$tel . ": <BR>\n";
        }
        $email='';
        foreach($vcard->EMAIL as $eml) {
            $email.="email";
            if ($eml['TYPE']) {
                $email .= " (" . strtolower($eml['TYPE']) . ")";
            }
            $email .= ": ". $eml . "<BR>\n";
        }
        $addresses="";
        foreach($vcard->ADR as $adr) {
            $addresses.="Address";
            if ($adr['TYPE']) {
                $addresses .= " (" . strtolower($adr['TYPE']) . ")";
            }
            $addresses .= ": " . str_replace(";","|",$adr) . "<BR>\n";
        }
        echo ('<tr><td>'.$rrow['id'].'</td><td>'.rtrim($vcard->CATEGORIES,";").'</td><td class="bold">'.rtrim((string) $vcard->ORG,",").'</td><td>'.$rrow['book_name'].'</td></tr>' . "\n");

        echo "</table>\n";
        $data=$vcardsm->serialize();
        $data=str_replace("\n","<br>\n",$data);
        echo '<p><span class="vcarddata">'.$data .'</span></p></div>';
        
        $photopath='';
        if (isset($vcard->PHOTO) && isset($vcard->UID)) {
            $decoded=$vcard->PHOTO;
            $photopath='contactphotos/' . $vcard->UID . '.' . strtolower($vcard->PHOTO['TYPE']);
            $photofullpath= __DIR__ . '/../' . $photopath;
            if(saveimage($decoded,$photofullpath)) echo '<img src="' . $photopath . '" class="contactphoto">';

        }
    }

} else {
    //must log in
    echo "You must be logged in to use this page.";
}