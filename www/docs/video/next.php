<?php

include_once '../../includes/easyparliament/init.php';

$action = get_http_var('action');
$pid = intval(get_http_var('pid'));
$major = intval(get_http_var('major'));
if (!$major) {
    $major = 1;
}

if ($action == 'next' || $action=='nextneeded') {
    $gid = get_http_var('gid');
    $file = intval(get_http_var('file'));
    $time = intval(get_http_var('time'));
    $db = new ParlDB;
    $gid = "uk.org.publicwhip/$gid";
    $q = $db->query("select hdate,hpos,major from hansard where gid = :gid", array(
        ':gid' => $gid
        ))->first();
    if (!$q) {
        # Shouldn't happen, but means a bot has got the URL somehow or similar
        header('Location: /video/');
        exit;
    }
    $hdate = $q['hdate'];
    $hpos = $q['hpos'];
    $major = $q['major'];
    $q = $db->query("select gid, hpos from hansard
        where hpos>$hpos and hdate='$hdate' and major=$major
        and htype IN (12,13,14) "
        . ($action=='nextneeded'?'and video_status in (1,3)':'') . "
        ORDER BY hpos LIMIT 1")->first();
    if (!$q) {
        $PAGE->page_start();
        $PAGE->stripe_start();
        echo '<p>You appear to have reached the end of the day (or
everything after where you have just done has already been stamped).
Congratulations, now <a href="/video/">get stuck in somewhere else</a>!
;-)</p>';
        $PAGE->stripe_end();
        $PAGE->page_end();
    } else {
        $new_gid = $q['gid'];
        $new_hpos = $q['hpos'];
        if ($action=='nextneeded') {
            $q = $db->query("select adate, atime from hansard, video_timestamps
                where hansard.gid = video_timestamps.gid and deleted=0
                    and hpos<$new_hpos and hdate='$hdate' and major=$major
                    and htype IN (12,13,14) and (user_id is null or user_id!=-1)
                order by hpos desc limit 1")->first();
            $adate = $q['adate'];
            $atime = $q['atime'];
            $videodb = \MySociety\TheyWorkForYou\Utility\Video::dbConnect();
            if ($videodb) {
                $video = \MySociety\TheyWorkForYou\Utility\Video::fromTimestamp($videodb, $adate, $atime);
                $file = $video['id'];
                $time = $video['offset'];
            }
        }
        $new_gid = fix_gid_but_leave_section($new_gid);
        header('Location: /video/?from=next&file=' . $file . '&gid=' . $new_gid . '&start=' . $time);
    }
} elseif ($action == 'random' && $pid) {
    $db = new ParlDB;
    $q = $db->query("select gid from hansard
        where video_status in (1,3) and major=:major
        and htype IN (12,13,14)
        and hansard.person_id=:pid
        ORDER BY RAND() LIMIT 1", array(':major' => $major, ':pid' => $pid))->first();
    $new_gid = fix_gid_but_leave_section($q['gid']);
    header('Location: /video/?from=random&pid=' . $pid . '&gid=' . $new_gid);
} elseif ($action == 'random') {
    $db = new ParlDB;
    $q = $db->query("select gid, hpos, hdate from hansard
        where video_status in (1,3) and major=:major
        and htype IN (12,13,14)
        ORDER BY RAND() LIMIT 1", array(':major' => $major))->first();
    $gid = $q['gid'];
    /*
    $hpos = $q['hpos'];
    $hdate = $q['hdate'];
    # Look a few speeches back to see if any have been matched
    # Harder as need to check all since are /not/ done
    $q = $db->query("select gid from hansard
        where hpos<$hpos and hpos>=$hpos-5 and major=$major and hdate='$hdate'
        and htype in (12,13,14) and video_status in (5,7)
        order by hpos desc limit 1")->first();
    if ($q) {
        # Take the next speech, presumably needed
        $hpos = $q['hpos'];
        $q = $db->query("select gid from hansard
            where hpos>$hpos and major=$major and hdate='$hdate'
            and htype in (12,13,14)
            order by hpos limit 1")->first();
        $gid = $q['gid'];
    }
    */
    $gid = fix_gid_but_leave_section($gid);
    header('Location: /video/?from=random&gid=' . $gid);
} else {
    # Illegal action
}

function fix_gid_but_leave_section($gid) {
    return str_replace('uk.org.publicwhip/', '', $gid);
}
