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

function base64_to_jpeg($base64_string, $photofullpath) {
    // open the output file for writing
    if (!$ifp = fopen($photofullpath,'wb')) {
        debug( "Cannot open file ($photofullpath)");
        //exit;
        return false;
    } 
    // split the string on commas
    // $data[ 0 ] == "data:image/jpeg;base64"
    // $data[ 1 ] == <actual base64 string>
    $data = explode( ',', $base64_string );

    if (fwrite($ifp,  base64_decode( $data[ count($data)-1 ] ) ) === FALSE) {
                debug( "Cannot write to file ($photofullpath)");
                //exit;
                return false;
    } else {
                debug( "Photo data written to file ($photofullpath)");
         
    }
    // clean up the file resource
    fclose( $ifp ); 

    return Strue; 
}

if (isset($_SESSION['username'])) {
    if (isset($_GET['id']) && is_numeric($_GET['id'])) {
        $contactid=$_GET['id'];
    } else {
        $contactid=0;
    }
    $columns=" c.id, c.uri, c.carddata, a.principaluri as owner, a.displayname as book_name ";
    $from=" FROM cards c INNER JOIN addressbooks a on c.addressbookid=a.id ";
    $where=" WHERE a.principaluri='principals/" . $_SESSION['username'] . "' and c.id=" . $contactid;
    $sql="SELECT count(*) " . $from . $where;
    $resultcount=$dds->getInt($sql);
    $dds->setMaxRecs(9999);
    $sql="SELECT " . $columns . $from . $where;
    $result=$dds->setSQL($sql);

    error_reporting(E_ERROR | E_PARSE);
    while ($rrow=$dds->getNextRow('assoc')) {
        $vcard = VObject\Reader::read($rrow['carddata']);
 
    echo '<div id="Contacts" class="w3-twothird w3-container" style="overflow:hidden">';

        echo ('<table id="vcardtable" class="table sortable" style="clear:both">' . "\n" . '<tr><th>ID</th><th>Categories</th><th>Display Name</th><th>Book</th></tr>' . "\n");
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
        echo ('<tr><td>'.$rrow['id'].'</td><td>'.$vcard->CATEGORIES.'</td><td class="bold">'.$vcard->FN.'</td><td>'.$rrow['book_name'].'</td></tr>' . "\n");

        echo "</table>\n";
        $data=str_replace("\n","<br>\n",$rrow['carddata']);
        echo '<p><span class="vcarddata">'.$data.'</span></p></div>';
               $photopath='';
        if (isset($vcard->PHOTO) && isset($vcard->UID)) {
            //$photo=base64_decode($vcard->PHOTO);
            //https://www.php.net/manual/en/function.base64-decode.php
            //$decoded = "";
            //for ($i=0; $i < ceil(strlen($vcard->PHOTO)/256); $i++)
            //$decoded = $decoded . base64_decode(substr($vcard->PHOTO,$i*256,256));

            //OMG was it really this easy all the time?
            $decoded=$vcard->PHOTO;
            $photopath='contactphotos/' . $vcard->UID . '.' . strtolower($vcard->PHOTO['TYPE']);
            $photofullpath= __DIR__ . '/../' . $photopath;
            if(saveimage($decoded,$photofullpath)) echo '<img src="' . $photopath . '" class="contactphoto">';
            //https://stackoverflow.com/questions/15153776/convert-base64-string-to-an-image-file
            //if(base64_to_jpeg($decoded,$photofullpath)) echo '<img src="' . $photopath . '" style="max-width:400px;">';

        }
    }

} else {
    //must log in
    echo "You must be logged in to use this page.";
}