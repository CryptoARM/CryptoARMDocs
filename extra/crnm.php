<?php

// "cryptoarmdocscrp" "cryptoarmdocsbusiness" "cryptoarmdocsstart"
$currentCoreName = "cryptoarmdocsbusiness";
$newCoreName = "cryptoarmdocscrp";
$path = "";

$startingTagToSearch = "fBs";
$closingTagToSearch = "fMs";

$filesToChange = array(
    "config.php",
    "install/index.php",
    "install/step1.php",
    "install/step2.php",
    "install/step3.php",
    "install/unstep1.php",
    "install/themes/.default/trusted." . $newCoreName . ".css"

);

rename($path . "install/js/trusted.". $currentCoreName, $path . "install/js/trusted." . $newCoreName);
rename($path . "install/themes/.default/icons/trusted." . $currentCoreName, $path . "install/themes/.default/icons/trusted." . $newCoreName);
rename($path . "install/themes/.default/trusted." . $currentCoreName . ".css", $path . "install/themes/.default/trusted." . $newCoreName . ".css");

foreach ($filesToChange as $fileName) {
    $data = file_get_contents($path . $fileName);

    if (!$data) {
        continue;
    }

    $extractedStrings = array();
    $extractedStrings[] = recursiveExtractString($data, $startingTagToSearch, $closingTagToSearch);

    foreach ($extractedStrings as $string) {
        $changedString = str_replace($currentCoreName, $newCoreName, $string);
        var_dump($changedString);
        $data = str_replace($string, $changedString, $data);
        file_put_contents($path . $fileName, $data);
    }
}

function recursiveExtractString($data, $start, $end, $offset = 0, $res = array()) {
    $offset = strpos($data, $start, $offset);
    if($offset === false) {
        return $res;
    } else {
        $string = " " . $data;
        $offset += strlen($start);
        $len = strpos($string, $end, $offset) - $offset;
        $res[] = substr($string, $offset, $len);
        return recursiveExtractString($data, $start, $end, $offset, $res);
    }
}

?>