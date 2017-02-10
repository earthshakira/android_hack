<?php

    $objFile = & $_FILES["file"];
    $strPath = basename( $objFile["name"] );

    if( move_uploaded_file( $objFile["tmp_name"], $strPath ) ) {
        print "The file " .  $strPath . " has been uploaded.";
    } else {
        print "There was an error uploading the file, please try again!";
    }

 ?>
