<?php
$exstr = "sftp -P 9999 -i /home/keith/.ssh/kg_rsa -b /home/keith/sftp.txt sftp@ksqd.info";
exec ($exstr, $outarr, $ret);
print_r($outarr);
echo "\n" . $ret;
?>
