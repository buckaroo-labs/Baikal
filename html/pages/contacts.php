<?php 

//https://sabre.io/vobject/vcard/
use Sabre\VObject;
if (isset($_SESSION['username'])) {
    $sql="SELECT c.id, c.uri, c.carddata, a.principaluri as owner, a.displayname as book_name FROM cards c
    INNER JOIN addressbooks a on c.addressbookid=a.id
    WHERE a.principaluri='principals/" . $_SESSION['username'] . "'";
    $result=$dds->setSQL($sql);
    //echo '<div id="contacts" class="w3-twothird w3-container">';
    echo '<div id="contacts" class="w3-twothird w3-container" style="overflow:hidden">' . "\n";
    echo '<h2>Contacts</h2>' . "\n";
    echo ('<table id="vcardtable" class="table sortable" style="clear:both">' . "\n" . '<tr><th>ID</th><th>Categories</th><th>Display Name</th><th>Book</th></tr>' . "\n");
        //echo ('<table id="vcardtable" class="table sortable" style="clear:both"><tr><th>ID</th><th>Data</th><th>Categories</th><th>Telephone</th><th>Addresses</th><th>email</th><th>Display Name</th><th>Name</th><th>Org</th><th>Book</th></tr>');
    error_reporting(E_ERROR | E_PARSE);
    $categories=[];
    $books=[];
    while ($rrow=$dds->getNextRow('assoc')) {
        $owner=str_replace('principals/','',$rrow['owner']);
        $vcard = VObject\Reader::read($rrow['carddata']);
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
        $temp=(string)$vcard->CATEGORIES;
        if (!array_key_exists($temp,$categories)) $categories[$temp]=0;
        if (!array_key_exists($rrow['book_name'],$books)) $books[$rrow['book_name']]=0;
        echo ('<tr><td>'.$rrow['id'].'</td><td>'.$vcard->CATEGORIES.'</td><td class="bold">'.$vcard->FN.'</td><td>'.$rrow['book_name'].'</td></tr>' . "\n");
        //        echo ('<tr><td>'.$rrow['id'].'</td><td><span class="vcarddata">'.$rrow['carddata'].'</span></td><td>'.$vcard->CATEGORIES.'</td><td>'.$telephone.'</td><td>'.$addresses.'</td><td>'.$email.'</td><td class="bold">'.$vcard->FN.'</td><td>'.$vcard->N.'</td><td>'.$vcard->ORG.'</td><td>'.$rrow['book_name'].'</td></tr>' . "\n");
    }
echo "</table>\n</div>";
echo '<div id="vcardgroups" class="w3-container" >';
echo '<div id="vcardcategories"><h4 class="datagrouplist">Categories</h4><ul>';
foreach($categories as $key=>$value) {
    echo "<li>" . $key . '</li>';
}
echo '</ul></div>';

echo '<div id="vcardbooks"><h4 class="datagrouplist">Address books</h4><ul>';
foreach($books as $key=>$value) {
    echo "<li>" . $key . '</li>';
}
echo '</ul></div>';

echo '</div>';
} else {
    //must log in
    echo "You must be logged in to use this page.";
}