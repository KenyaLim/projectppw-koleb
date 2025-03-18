<!DOCTYPE html>
<html>
<body>

<?php
$array = array("berenang", "berladang", "berbincang", "bermain", "berniaga");
foreach ($array as $i => $val){
	if ($i%2==0){
    	echo "<strong>$val</strong>";
    }else{
    	echo "<i>$val</i>";	
    }
    
    if ($i < count($array)-1){
    	echo ", ";
    }else{
    	echo ".";
    }
}
?>

</body>
</html>
