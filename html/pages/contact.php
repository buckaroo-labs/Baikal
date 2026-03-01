<?php 

//https://sabre.io/vobject/vcard/
use Sabre\VObject;
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
    echo '<div id="Contacts" class="w3-twothird w3-container" style="overflow:hidden">';

    error_reporting(E_ERROR | E_PARSE);
    while ($rrow=$dds->getNextRow('assoc')) {
        $vcard = VObject\Reader::read($rrow['carddata']);

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

    }

} else {
    //must log in
    echo "You must be logged in to use this page.";
}