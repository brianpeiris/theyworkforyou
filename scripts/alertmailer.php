<?php
/* 
 * Name: alertmailer.php
 * Description: Mailer for email alerts
 * $Id: alertmailer.php,v 1.34 2009-06-23 10:11:10 matthew Exp $
 */

function mlog($message) {
    print $message;
}

include_once '../www/includes/easyparliament/init.php';
ini_set('memory_limit', -1);
include_once INCLUDESPATH . 'easyparliament/member.php';

$global_start = getmicrotime();
$db = new ParlDB;

# Get current value of latest batch
$q = $db->query('SELECT max(indexbatch_id) as max_batch_id FROM indexbatch')->first();
$max_batch_id = $q['max_batch_id'];
mlog("max_batch_id: " . $max_batch_id . "\n");

# Last sent is timestamp of last alerts gone out.
# Last batch is the search index batch number last alert went out to.
if (is_file('alerts-lastsent')) {
    $lastsent = file('alerts-lastsent');
} else {
    $lastsent = array('', 0);
}

$lastupdated = trim($lastsent[0]);
if (!$lastupdated) {
    $lastupdated = strtotime('00:00 today');
}
$lastbatch = trim($lastsent[1]);
if (!$lastbatch) {
    $lastbatch = 0;
}
mlog("lastupdated: $lastupdated lastbatch: $lastbatch\n");

# Construct query fragment to select search index batches which
# have been made since last time we ran
$batch_query_fragment = "";
for ($i = $lastbatch + 1; $i <= $max_batch_id; $i++) {
    $batch_query_fragment .= "batch:$i ";
}
$batch_query_fragment = trim($batch_query_fragment);
mlog("batch_query_fragment: " . $batch_query_fragment . "\n");

if (!$batch_query_fragment) {
    mlog("No new batches since last run - nothing to run over!");
    exit;
}

# For testing purposes, specify nomail on command line to not send out emails
$nomail = false;
$onlyemail = '';
$fromemail = '';
$fromflag = false;
$toemail = '';
$template = 'alert_mailout';
for ($k = 1; $k < $argc; $k++) {
    if ($argv[$k] == '--nomail') {
        $nomail = true;
    }
    if (preg_match('#^--only=(.*)$#', $argv[$k], $m)) {
        $onlyemail = $m[1];
    }
    if (preg_match('#^--from=(.*)$#', $argv[$k], $m)) {
        $fromemail = $m[1];
    }
    if (preg_match('#^--to=(.*)$#', $argv[$k], $m)) {
        $toemail = $m[1];
    }
    if (preg_match('#^--template=(.*)$#', $argv[$k], $m)) {
        $template = $m[1];
        # Tee hee
        $template = "../../../../../../../../../../home/twfy-live/email-alert-templates/alert_mailout_$template";
    }
}

if (DEVSITE) {
    $nomail = true;
}

if ($nomail) {
    mlog("NOT SENDING EMAIL\n");
}
if (($fromemail && $onlyemail) || ($toemail && $onlyemail)) {
    mlog("Can't have both from/to and only!\n");
    exit;
}

$active = 0;
$queries = 0;
$unregistered = 0;
$registered = 0;
$sentemails = 0;

$LIVEALERTS = new ALERT;

$current = array('email' => '', 'token' => '');
$email_text = '';
$html_text = '';
$globalsuccess = 1;

# Fetch all confirmed, non-deleted alerts
$confirmed = 1; $deleted = 0;
$alertdata = $LIVEALERTS->fetch($confirmed, $deleted);
$alertdata = $alertdata['data'];

$DEBATELIST = new DEBATELIST; # Nothing debate specific, but has to be one of them

$sects = array(
    1 => 'Commons debate',
    2 => 'Westminster Hall debate',
    3 => 'Written Answer',
    4 => 'Written Ministerial Statement',
    5 => 'Northern Ireland Assembly debate',
    6 => 'Public Bill committee',
    7 => 'Scottish Parliament debate',
    8 => 'Scottish Parliament written answer',
    9 => 'London Mayoral question',
    101 => 'Lords debate',
    'F' => 'event',
    'V' => 'vote',
);
$sects_gid = array(
    1 => 'debate',
    2 => 'westminhall',
    3 => 'wrans',
    4 => 'wms',
    5 => 'ni',
    6 => 'pbc',
    7 => 'sp',
    8 => 'spwa',
    9 => 'london-mayors-questions',
    101 => 'lords',
    'F' => 'calendar',
);
$sects_search = array(
    1 => 'debate',
    2 => 'westminhall',
    3 => 'wrans',
    4 => 'wms',
    5 => 'ni',
    6 => 'pbc',
    7 => 'sp',
    8 => 'spwrans',
    9 => 'lmqs',
    101 => 'lords',
    'F' => 'future',
);
$results = array();

$outof = count($alertdata);
$start_time = time();
foreach ($alertdata as $alertitem) {
    $active++;
    $email = $alertitem['email'];
    if ($onlyemail && $email != $onlyemail) {
        continue;
    }
    if ($fromemail && strcasecmp($email, $fromemail) > 0) {
        $fromflag = true;
    }
    if ($fromemail && !$fromflag) {
        continue;
    }
    if ($toemail && strcasecmp($email, $toemail) > 0) {
        continue;
    }
    $criteria_raw = $alertitem['criteria'];
    if (preg_match('#\bOR\b#', $criteria_raw)) {
        $criteria_raw = "($criteria_raw)";
    }
    $criteria_batch = $criteria_raw . " " . $batch_query_fragment;

    if ($email != $current['email']) {
        if ($email_text) {
            write_and_send_email($current, $email_text, $html_text, $template);
        }
        $current['email'] = $email;
        $current['token'] = $alertitem['alert_id'] . '-' . $alertitem['registrationtoken'];
        $email_text = '';
        $html_text = '';
        $q = $db->query('SELECT user_id FROM users WHERE email = :email', array(
            ':email' => $email
            ))->first();
        if ($q) {
            $user_id = $q['user_id'];
            $registered++;
        } else {
            $user_id = 0;
            $unregistered++;
        }
        mlog("\nEMAIL: $email, uid $user_id; memory usage : " . memory_get_usage() . "\n");
    }

    $data = null;
    if (!isset($results[$criteria_batch])) {
        mlog("  ALERT $active/$outof QUERY $queries : Xapian query '$criteria_batch'");
        $start = getmicrotime();
        $SEARCHENGINE = new SEARCHENGINE($criteria_batch);
        #mlog("query_remade: " . $SEARCHENGINE->query_remade() . "\n");
        $args = array(
            's' => $criteria_raw, # Note: use raw here for URLs, whereas search engine has batch
            'threshold' => $lastupdated, # Return everything added since last time this script was run
            'o' => 'c',
            'num' => 1000, // this is limited to 1000 in hansardlist.php anyway
            'pop' => 1,
            'e' => 1 # Don't escape ampersands
        );
        $data = $DEBATELIST->_get_data_by_search($args);
        # add to cache (but only for speaker queries, which are commonly repeated)
        # Don't cache, it's very quick, and we'd prefer low memory usage
        #if (preg_match('#^speaker:\d+$#', $criteria_raw, $m)) {
        #    mlog(", caching");
        #    $results[$criteria_batch] = $data;
        #}
        #        unset($SEARCHENGINE);
        $total_results = $data['info']['total_results'];
        $queries++;
        mlog(", hits " . $total_results . ", time " . (getmicrotime()-$start) . "\n");
    } else {
        mlog("  ACTION $active/$outof CACHE HIT : Using cached result for '$criteria_batch'\n");
        $data = $results[$criteria_batch];
    }

    # Divisions
    if (preg_match('#^speaker:(\d+)$#', $criteria_raw, $m)) {
        $pid = $m[1];
        $q = $db->query('SELECT * FROM persondivisionvotes pdv JOIN divisions USING(division_id)
            WHERE person_id=:person_id AND pdv.lastupdate >= :time', array(
                'person_id' => $pid,
                ':time' => date('Y-m-d H:i:s', $lastupdated),
            ));
        foreach ($q as $row) {
            $vote = $row['vote'];
            $num = $row['division_number'];
            $teller = '';
            if (strpos($vote, 'tell') !== false) {
                $teller = ', as a teller';
                $vote = str_replace('tell', '', $vote);
            }
            if ($vote == 'absent') {
                continue;
            }
            $text = "Voted ";
            if ($vote == 'aye' && $row['yes_text']) {
                $text .= "($vote$teller) " . $row['yes_text'];
            } elseif ($vote == 'no' && $row['no_text']) {
                $text .= "($vote$teller) " . $row['no_text'];
            } else {
                $text .= "$vote$teller";
            }
            $text .= " (division #$num; result was <b>" . $row['yes_total'] . '</b> aye, <b>' . $row['no_total'] . ' no</b>)';
            $data['rows'][] = [
                'parent' => [
                    'body' => $row['division_title'],
                ],
                'extract' => $text,
                'listurl' => '/divisions/' . $row['division_id'],
                'major' => 'V',
                'hdate' => $row['division_date'],
                'hpos' => $row['division_number'],
            ];
        }
    }

    if (isset($data['rows']) && count($data['rows']) > 0) {
        usort($data['rows'], 'sort_by_stuff'); # Sort results into order, by major, then date, then hpos
        $o = array(); $major = 0; $count = array(); $total = 0;
        $any_content = false;
        foreach ($data['rows'] as $row) {
            if ($major !== $row['major']) {
                $count[$major] = $total; $total = 0;
                $major = $row['major'];
                $o[$major] = ['text' => '', 'html' => ''];
                $k = 3;
            }
            #mlog($row['major'] . " " . $row['gid'] ."\n");

            # Due to database server failure and restoring from day
            # old backup, 17th January 2012 is being newly
            # inserted, but has already been alerted upon. So
            # manually now stop anything from before 18th January
            # 2012 from sending an email alert again.
            if ($row['hdate'] < '2012-01-18') {
                continue;
            }

            if (isset($sects_gid[$major])) {
                $q = $db->query('SELECT gid_from FROM gidredirect WHERE gid_to = :gid_to', array(
                    ':gid_to' => 'uk.org.publicwhip/' . $sects_gid[$major] . '/' . $row['gid']
                    ));
                if ($q->rows() > 0) {
                    continue;
                }
            }

            --$k;
            if ($major == 'V' || $k >= 0) {
                $any_content = true;
                $parentbody = text_html_to_email($row['parent']['body']);
                $body_text = text_html_to_email($row['extract']);
                $body_html = $row['extract'];
                if (isset($row['speaker']) && count($row['speaker'])) {
                    $body_text = $row['speaker']['name'] . ': ' . $body_text;
                    $body_html = '<strong style="font-weight: 900;">' . $row['speaker']['name'] . '</strong>: ' . $body_html;
                }
                $body_html = '<p style="font-size: 16px;">' . $body_html . '</p>';

                $body_text = wordwrap($body_text, 72);
                $o[$major]['text'] .= $parentbody . ' (' . format_date($row['hdate'], SHORTDATEFORMAT) . ")\nhttps://www.theyworkforyou.com" . $row['listurl'] . "\n";
                $o[$major]['text'] .= $body_text . "\n\n";
                $o[$major]['html'] .= '<a href="https://www.theyworkforyou.com' . $row['listurl'] . '"><h2 style="line-height: 1.2; font-size: 17px; font-weight: 900;">' . $parentbody . '</h2></a> <span style="margin: 16px 0 0 0; display: block; font-size: 16px;">' . format_date($row['hdate'], SHORTDATEFORMAT) . '</span>';
                $o[$major]['html'] .= $body_html . "\n\n";
            }
            $total++;
        }
        $count[$major] = $total;

        if ($any_content) {
            # Add data to email_text/html_text
            $desc = trim(html_entity_decode($data['searchdescription']));
            $desc = trim(preg_replace(['#\(B\d+( OR B\d+)*\)#', '#B\d+( OR B\d+)*#'], '', $desc));
            foreach ($o as $major => $body) {
                if ($body['text']) {
                    $heading_text = $desc . ' : ' . $count[$major] . ' ' . $sects[$major] . ($count[$major] != 1 ? 's' : '');
                    $heading_html = $desc . ' : <strong>' . $count[$major] . '</strong> ' . $sects[$major] . ($count[$major] != 1 ? 's' : '');

                    $email_text .= "$heading_text\n" . str_repeat('=', strlen($heading_text)) . "\n\n";
                    if ($count[$major] > 3 && $major != 'V') {
                        $email_text .= "There are more results than we have shown here. See more:\nhttps://www.theyworkforyou.com/search/?s=" . urlencode($criteria_raw) . "+section:" . $sects_search[$major] . "&o=d\n\n";
                    }
                    $email_text .= $body['text'];

                    $html_text .= '<hr style="height:2px;border-width:0; background-color: #f7f6f5; margin: 30px 0;">';
                    $html_text .= '<p style="font-size:16px;">' . $heading_html . '</p>';
                    if ($count[$major] > 3 && $major != 'V') {
                        $html_text .= '<p style="font-size:16px;">There are more results than we have shown here. <a href="https://www.theyworkforyou.com/search/?s=' . urlencode($criteria_raw) . "+section:" . $sects_search[$major] . '&o=d">See more</a></p>';
                    }
                    $html_text .= $body['html'];
                }
            }
        }
    }
}
if ($email_text) {
    write_and_send_email($current, $email_text, $html_text, $template);
}

mlog("\n");

$sss = "Active alerts: $active\nEmail lookups: $registered registered, $unregistered unregistered\nQuery lookups: $queries\nSent emails: $sentemails\n";
if ($globalsuccess) {
    $sss .= 'Everything went swimmingly, in ';
} else {
    $sss .= 'Something went wrong! Total time: ';
}
$sss .= (getmicrotime() - $global_start) . "\n\n";
mlog($sss);
if (!$nomail && !$onlyemail) {
    $fp = fopen('alerts-lastsent', 'w');
    fwrite($fp, time() . "\n");
    fwrite($fp, $max_batch_id);
    fclose($fp);
    mail(ALERT_STATS_EMAILS, 'Email alert statistics', $sss, 'From: Email Alerts <fawkes@dracos.co.uk>');
}
mlog(date('r') . "\n");

function _sort($a, $b) {
    if ($a > $b) {
        return 1;
    } elseif ($a < $b) {
        return -1;
    }
    return 0;
}

function sort_by_stuff($a, $b) {
    # Always have votes first.
    if ($a['major'] == 'V' && $b['major'] != 'V') {
        return -1;
    } elseif ($b['major'] == 'V' && $a['major'] != 'V') {
        return 1;
    }

    # Always have future business second..
    if ($a['major'] == 'F' && $b['major'] != 'F') {
        return -1;
    } elseif ($b['major'] == 'F' && $a['major'] != 'F') {
        return 1;
    }

    # Otherwise sort firstly by major number (so Commons before NI before SP before Lords)
    if ($ret = _sort($a['major'], $b['major'])) {
        return $ret;
    }

    # Then by date (most recent first for everything except future, which is the opposite)
    if ($a['major'] == 'F') {
        if ($ret = _sort($a['hdate'], $b['hdate'])) {
            return $ret;
        }
    } else {
        if ($ret = _sort($b['hdate'], $a['hdate'])) {
            return $ret;
        }
    }

    # Lastly by speech position within a debate.
    if ($a['hpos'] == $b['hpos']) {
        return 0;
    }
    return ($a['hpos'] > $b['hpos']) ? 1 : -1;
}

function write_and_send_email($current, $text, $html, $template) {
    global $globalsuccess, $sentemails, $nomail, $start_time;

    $text .= '====================';
    $sentemails++;
    mlog("SEND $sentemails : Sending email to $current[email] ... ");
    $d = array('to' => $current['email'], 'template' => $template);
    $m = array(
        'DATA' => $text,
        '_HTML_' => $html,
        'MANAGE' => 'https://www.theyworkforyou.com/D/' . $current['token'],
    );
    if (!$nomail) {
        $success = send_template_email($d, $m, true, true); # true = "Precedence: bulk", want bounces
        mlog("sent ... ");
        # sleep if time between sending mails is less than a certain number of seconds on average
        # 0.25 is number of seconds per mail not to be quicker than
        if (((time() - $start_time) / $sentemails) < 0.25) {
            mlog("pausing ... ");
            sleep(1);
        }
    } else {
        mlog($text);
        $success = 1;
    }
    mlog("done\n");
    if (!$success) {
        $globalsuccess = 0;
    }
}

function text_html_to_email($s) {
    $s = preg_replace('#</?(i|b|small)>#', '', $s);
    $s = preg_replace('#</?span[^>]*>#', '*', $s);
    $s = str_replace(
        array('&#163;', '&#8211;', '&#8212;', '&#8217;', '<br>'),
        array("\xa3", '-', '-', "'", "\n"),
        $s
    );
    return $s;
}

