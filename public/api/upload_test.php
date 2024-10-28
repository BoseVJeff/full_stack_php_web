<?php
foreach ($_FILES as $key => $value) {
    echo $key." : <br>";
    foreach ($value as $key => $val) {
        echo "    ".$key." : ".$val."<br>";
    }
}

    $uploaddir = '../../uploads/';
    $uploadfile = $uploaddir . basename($_FILES['file']['name']);

    if(move_uploaded_file($_FILES['file']['tmp_name'],$uploadfile)) {

        echo "Uploaded ".basename($_FILES['file']['name'])." to ".$uploaddir." with size ".$_FILES['file']['size']." bytes";
    } else {
        echo "Error uploading file!";
    }
    
    ?>