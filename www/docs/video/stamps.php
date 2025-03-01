<?php

include_once '../../includes/easyparliament/init.php';

date_default_timezone_set('Europe/London');
$videodb = \MySociety\TheyWorkForYou\Utility\Video::dbConnect();

$gid = get_http_var('gid');

$db = new ParlDB;
$q = $db->query("select subsection_id,adate,atime from hansard, video_timestamps
    where hansard.gid = video_timestamps.gid and hansard.gid = :gid
        and deleted=0 and (user_id is null or user_id!=-1)", array(
        ':gid' => "uk.org.publicwhip/$gid"
        ))->first();
$subsection_id = $q['subsection_id'];
$adate = $q['adate'];
$atime = $q['atime'];
$video = \MySociety\TheyWorkForYou\Utility\Video::fromTimestamp($videodb, $adate, $atime);
if (!$video) {
    exit;
}

$start = date('H:i:s', strtotime($video['broadcast_start']. ' GMT'));
$end = date('H:i:s', strtotime($video['broadcast_end'] . ' GMT'));

$q = $db->query("select video_timestamps.gid, adate, atime, time_to_sec(timediff(atime, '$start')) as timediff,
        time_to_sec(timediff(atime, '$end')) as timetoend
    from hansard, video_timestamps
    where hansard.gid = video_timestamps.gid and subsection_id=$subsection_id
    and (user_id is null or user_id!=-1) and deleted=0 order by atime, hpos");

header('Content-Type: text/xml');

$gids = array();
$file_offset = 0;
print '<stamps>';
foreach ($q as $row) {
    $gid = str_replace('uk.org.publicwhip/', '', $row['gid']);
    if (isset($gids[$gid])) {
        continue;
    }
    $timetoend = $row['timetoend'] - $file_offset;
    if ($timetoend>0) {
        $video = \MySociety\TheyWorkForYou\Utility\Video::fromTimestamp($videodb, $row['adate'], $row['atime']);
        $new_start = date('H:i:s', strtotime($video['broadcast_start']. ' GMT'));
        $file_offset += timediff($new_start, $start);
        $start = $new_start;
        $end = date('H:i:s', strtotime($video['broadcast_end'] . ' GMT'));
    }
    $timediff = $row['timediff'] - $file_offset;
    if ($timediff>=0) {
        print "<stamp><gid>$gid</gid><file>$video[id]</file><time>$timediff</time></stamp>\n";
    }
    $gids[$gid] = true;
}
print '</stamps>';

function timediff($a, $b) {
    return substr($a, 0, 2)*3600 + substr($a, 3, 2)*60 + substr($a, 6, 2)
        - substr($b, 0, 2)*3600 - substr($b, 3, 2)*60 - substr($b, 6, 2);

}
