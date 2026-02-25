<?php
$mysqli = new mysqli("db","davuser","davpassword","dav");
$commands = file_get_contents('/var/www/Core/Resources/Db/MySQL/db.sql');   
$mysqli->multi_query($commands);