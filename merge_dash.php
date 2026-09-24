<?php
$admin = file_get_contents('resources/views/software/_utils/dashboarad_inquiry_statistics.blade.php');
$emp = file_get_contents('resources/views/software/_utils/_emp_part.tmp');
// strip BOM if present
$emp = preg_replace('/^\xEF\xBB\xBF/', '', $emp);
file_put_contents('resources/views/software/_utils/dashboarad_inquiry_statistics.blade.php', rtrim($admin) . "\n\n" . ltrim($emp));
unlink('resources/views/software/_utils/_emp_part.tmp');
echo "merged ok\n";
