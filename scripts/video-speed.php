<?php

include_once "../www/includes/easyparliament/init.php";

$db = new ParlDB;

$q = $db->query('select distinct(person_id) from hansard where video_status in (5,7) and person_id>0 limit 5');
foreach ($q as $row) {
    calculate_speed($db, $row['person_id']);
}

# 1604 = Diane Abbott
#1629 = Boris
function calculate_speed($db, $spid) {
    $q = $db->query("select hpos,hdate,hansard.gid,htime,time_to_sec(atime) as atime,body from hansard,video_timestamps,epobject where hansard.gid=video_timestamps.gid and deleted=0 and video_status in (5,7) and person_id =$spid and epobject.epobject_id=hansard.epobject_id group by hansard.gid order by hansard.gid");
    $total = 0;
    $total_words = 0;
    $total_speed = 0;
    $num = 0;
    foreach ($q as $row) {
        $hpos = $row['hpos'];
        $hdate = $row['hdate'];
        $gid = $row['gid'];
        $atime = $row['atime'];
        $body = strip_tags($row['body']);
        $qq = $db->query('select time_to_sec(atime) as atime from hansard,video_timestamps where hansard.gid=video_timestamps.gid and deleted=0 and video_status in (5,7) and hdate="' . $hdate . '" and hpos=' . ($hpos+1) . ' group by video_timestamps.gid')->first();
        $next_atime = $qq['atime'];
        if (!$next_atime) {
            continue;
        }
        $duration = $next_atime - $atime;
        if ($duration<=0) {
            continue;
        }
        $words = preg_split('/\W+/', $body, -1, PREG_SPLIT_NO_EMPTY);
        $num_words = count($words);
        $speed = $num_words / ($duration/60);
        $total_speed += $speed;
        print "$gid, $num_words words, from $atime to $next_atime, duration $duration s, speed $speed wpm\n";
        $total += $duration;
        $total_words += $num_words;
        $num++;
    }

    $total_min = $total/60;

    print "Person ID $spid\n";
    print "Average length = " . ($total/$num) . "s\n";
    print "Average speed = " . ($total_words/$total_min) . "wpm\n";
    print "Average of speeds = " . ($total_speed/$num) . "wpm\n";
    print "\n";
}

