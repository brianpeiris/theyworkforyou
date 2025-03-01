<?php
# vim:sw=4:ts=4:et:nowrap

/*
SEARCHENGINE class 2004-05-26
francis@flourish.org

Example usage:

        include_once INCLUDESPATH."easyparliament/searchengine.php";

        $searchengine = new SEARCHENGINE($searchstring);
        $description = $searchengine->query_description();
        $short_description = $searchengine->query_description_short();

        $count = $searchengine->run_count();

        // $first_result begins at 0
        $searchengine->run_search($first_result, $results_per_page);
        $gids = $searchengine->get_gids();
        $relevances = $searchengine->get_relevances();

        $bestpos = $searchengine->position_of_first_word($body);
        $extract = $searchengine->highlight($extract);

*/

if (defined('XAPIANDB') AND XAPIANDB != '') {
    if (file_exists('/usr/share/php/xapian.php')) {
        include_once '/usr/share/php/xapian.php';
    } else {
        twfy_debug('SEARCH', '/usr/share/php/xapian.php does not exist');
    }
}

class SEARCHENGINE {
    public $valid = false;
    public $error;

    public function __construct($query) {
        if (!defined('XAPIANDB') || !XAPIANDB)
            return null;

        global $xapiandb, $PAGE;
        if (!$xapiandb) {
            if (strstr(XAPIANDB, ":")) {
                //ini_set('display_errors', 'On');
                list ($xapian_host, $xapian_port) = explode(":", XAPIANDB);
                twfy_debug("SEARCH", "Using Xapian remote backend: " . $xapian_host . " port " . $xapian_port);
                $xapiandb_remote = remote_open($xapian_host, intval($xapian_port));
                $xapiandb = new XapianDatabase($xapiandb_remote);
            } else {
                $xapiandb = new XapianDatabase(XAPIANDB);
            }
        }
        $this->query = $query;
        if (!isset($this->stemmer)) $this->stemmer = new XapianStem('english');
        if (!isset($this->enquire)) $this->enquire = new XapianEnquire($xapiandb);
        if (!isset($this->queryparser)) {
            $this->queryparser = new XapianQueryParser();
            $this->datevaluerange = new XapianDateValueRangeProcessor(1);
            $this->queryparser->set_stemmer($this->stemmer);
            $this->queryparser->set_stemming_strategy(XapianQueryParser::STEM_SOME);
            $this->queryparser->set_database($xapiandb);
            $this->queryparser->set_default_op(Query_OP_AND);
            $this->queryparser->add_boolean_prefix('speaker', 'S');
            $this->queryparser->add_boolean_prefix('major', 'M');
            $this->queryparser->add_boolean_prefix('date', 'D');
            $this->queryparser->add_boolean_prefix('batch', 'B');
            $this->queryparser->add_boolean_prefix('segment', 'U');
            $this->queryparser->add_boolean_prefix('department', 'G');
            $this->queryparser->add_boolean_prefix('party', 'P');
            $this->queryparser->add_boolean_prefix('column', 'C');
            $this->queryparser->add_boolean_prefix('gid', 'Q');
            $this->queryparser->add_valuerangeprocessor($this->datevaluerange);
        }

        # Force words to lower case
        $this->query = preg_replace_callback('#(department|party):.+?\b#i', function($m) {
            return strtolower($m[0]);
        }, $this->query);

        // Any characters other than this are treated as, basically, white space
        // (apart from quotes and minuses, special case below)
        // The colon is in here for prefixes speaker:10043 and so on.
        $this->wordchars = "A-Za-z0-9,.'&:_\x80-\xbf\xc2-\xf4";
        $this->wordcharsnodigit = "A-Za-z0-9'&_\x80-\xbf\xc2-\xf4";

        // An array of normal words.
        $this->words = array();
        // All quoted phrases, as an (array of (arrays of words in each phrase)).
        $this->phrases = array();
        // Items prefixed with a colon (speaker:10024) as an (array of (name, value))
        $this->prefixed = array();

        // Split words up into individual words, and quoted phrases
        preg_match_all('/(' .
            '"|' . # match either a quote, or...
            '(?:(?<![' .$this->wordchars. '])-)?' . # optionally a - (exclude)
            # if at start of word (i.e. not preceded by a word character, in
            # which case it is probably a hyphenated-word)
            '['.$this->wordchars.']+' . # followed by a string of word-characters
            ')/', $this->query, $all_words);
        if ($all_words) {
            $all_words = $all_words[0];
        } else {
            $all_words = array();
        }
        $in_quote = false;
        $from = ''; $to = '';
        foreach ($all_words as $word) {
            if ($word == '"') {
                $in_quote = !$in_quote;
                if ($in_quote) array_push($this->phrases, array());
                if (!$in_quote && !count($this->phrases[count($this->phrases) - 1])) {
                    array_pop($this->phrases);
                }
                continue;
            }
            if ($word == '') {
                continue;
            }

            if (strpos($word, ':') !== false) {
                $items = explode(":", strtolower($word));
                $type = $items[0];
                if (substr($type, 0, 1)=='-') $type = substr($type, 1);
                $value = strtolower(join(":", array_slice($items,1)));
                if ($type == 'section') {
                    $newv = $value;
                    if ($value == 'debates' || $value == 'debate') $newv = 1;
                    elseif ($value == 'whall' || $value == 'westminster' || $value == 'westminhall') $newv = 2;
                    elseif ($value == 'wrans' || $value == 'wran') $newv = 3;
                    elseif ($value == 'wms' || $value == 'statements' || $value == 'statement') $newv = 4;
                    elseif ($value == 'lordsdebates' || $value == 'lords') $newv = 101;
                    elseif ($value == 'ni' || $value == 'nidebates') $newv = 5;
                    elseif ($value == 'pbc' || $value == 'standing') $newv = 6;
                    elseif ($value == 'sp') $newv = 7;
                    elseif ($value == 'spwrans' || $value == 'spwran') $newv = 8;
                    elseif ($value == 'lmqs') $newv = 9;
                    elseif ($value == 'uk') $newv = array(1,2,3,4,6,101);
                    elseif ($value == 'scotland') $newv = array(7,8);
                    elseif ($value == 'future') $newv = 'F';
                    if (is_array($newv)) {
                        $newv = 'major:' . join(' major:', $newv);
                    } else {
                        $newv = "major:$newv";
                    }
                    $this->query = str_ireplace("$type:$value", $newv, $this->query);
                } elseif ($type == 'groupby') {
                    $newv = $value;
                    if ($value == 'debates' || $value == 'debate') $newv = 'debate';
                    if ($value == 'speech' || $value == 'speeches') $newv = 'speech';
                    $this->query = str_ireplace("$type:$value", '', $this->query);
                    array_push($this->prefixed, array($type, $newv));
                } elseif ($type == 'from') {
                    $from = $value;
                } elseif ($type == 'to') {
                    $to = $value;
                }
            } elseif (strpos($word, '-') !== false) {
            } elseif ($in_quote) {
                array_push($this->phrases[count($this->phrases) - 1], strtolower($word));
            } elseif (strpos($word, '..') !== false) {
            } elseif ($word == 'OR' || $word == 'AND' || $word == 'XOR' || $word == 'NEAR') {
            } else {
                array_push($this->words, strtolower($word));
            }
        }
        if ($from && $to) {
            $this->query = str_ireplace("from:$from", '', $this->query);
            $this->query = str_ireplace("to:$to", '', $this->query);
            $this->query .= " $from..$to";
        } elseif ($from) {
            $this->query = str_ireplace("from:$from", '', $this->query);
            $this->query .= " $from..".date('Ymd');
        } elseif ($to) {
            $this->query = str_ireplace("to:$to", '', $this->query);
            $this->query .= " 19990101..$to";
        }

        # Merged people
        $db = new ParlDB;
        $merged = $db->query('SELECT * FROM gidredirect WHERE gid_from LIKE :gid_from', array(':gid_from' => "uk.org.publicwhip/person/%"));
        foreach ($merged as $row) {
            $from_id = str_replace('uk.org.publicwhip/person/', '', $row['gid_from']);
            $to_id = str_replace('uk.org.publicwhip/person/', '', $row['gid_to']);
            $this->query = preg_replace("#speaker:($from_id|$to_id)#i", "(speaker:$from_id OR speaker:$to_id)", $this->query);
        }

        twfy_debug("SEARCH", "prefixed: " . var_export($this->prefixed, true));

        twfy_debug("SEARCH", "query -- ". $this->query);
        $flags = XapianQueryParser::FLAG_BOOLEAN | XapianQueryParser::FLAG_LOVEHATE |
            XapianQueryParser::FLAG_WILDCARD | XapianQueryParser::FLAG_SPELLING_CORRECTION;
        $flags = $flags | XapianQueryParser::FLAG_PHRASE;
        try {
            $query = $this->queryparser->parse_query($this->query, $flags);
        } catch (Exception $e) {
            # Nothing we can really do with a bad query
            $this->error = _htmlspecialchars($e->getMessage());

            return null;
        }

        $this->enquire->set_query($query);

        # Now parse the parsed query back into a query string, yummy

        $qd = $query->get_description();
        twfy_debug("SEARCH", "queryparser original description -- " . $qd);
        $qd = substr($qd, 6, -1); # Strip "Query()" around description
        $qd = preg_replace('#@[0-9]+#', '', $qd); # Strip position variable
        # Date range
        $qd = preg_replace_callback('#VALUE_RANGE 1 (\d+) (\d+)#', function($m) {
            return preg_replace("#(\d{4})(\d\d)(\d\d)#", '$3/$2/$1', $m[1])
                . ".." . preg_replace("#(\d{4})(\d\d)(\d\d)#", '$3/$2/$1', $m[2]);
        }, $qd);
        # Replace phrases with the phrase in quotes
        preg_match_all('#\(([^(]*? PHRASE [^(]*?)\)#', $qd, $m);
        foreach ($m[1] as $phrase) {
            $phrase_new = preg_replace('# PHRASE \d+#', '', $phrase);
            #$this->phrases[] = preg_split('#\s+#', $phrase_new);
            $qd = str_replace("($phrase)", '"'.$phrase_new.'"', $qd);
        }
        preg_match_all('#\(([^(]*? NEAR [^(]*?)\)#', $qd, $m);
        foreach ($m[1] as $mm) {
            $mmn = preg_replace('# NEAR \d+ #', ' NEAR ', $mm);
            $qd = str_replace("($mm)", "($mmn)", $qd);
        }
        # Awesome regexes to get rid of superfluous matching brackets
        $qd = preg_replace('/( \( ( (?: (?>[^ ()]+) | (?1) ) (?: [ ](?:AND|OR|XOR|FILTER|NEAR[ ]\d+|PHRASE[ ]\d+)[ ] (?: (?>[^ ()]+) | (?1) ) )*  ) \) ) [ ] (FILTER|AND_NOT)/x', '$2 $3', $qd);
        $qd = preg_replace('/(?:FILTER | 0 [ ] \* ) [ ] ( \( ( (?: (?>[^ ()]+) | (?1) ) (?: [ ](?:AND|OR|XOR)[ ] (?: (?>[^ ()]+) | (?1) ) )*  ) \) )/x', '$2', $qd);
        $qd = preg_replace('/(?:FILTER | 0 [ ] \* ) [ ] ( [^()] )/x', '$1', $qd);
        $qd = str_replace('AND ', '', $qd); # AND is the default
        $qd = preg_replace('/^ ( \( ( (?: (?>[^()]+) | (?1) )* ) \) ) $/x', '$2', $qd);
        # Other prefixes
        $qd = preg_replace('#\bU(\d+)\b#', 'segment:$1', $qd);
        $qd = preg_replace('#\bC(\d+)\b#', 'column:$1', $qd);
        $qd = preg_replace('#\bQ(.*?)\b#', 'gid:$1', $qd);
        $qd = preg_replace_callback('#\bP(.*?)\b#', function($m) {
            global $parties;
            $pu = ucfirst($m[1]);
            return "party:" . (isset($parties[$pu]) ? $parties[$pu] : $m[1]);
        }, $qd);
        $qd = preg_replace('#\bD(.*?)\b#', 'date:$1', $qd);
        $qd = preg_replace('#\bG(.*?)\b#', 'department:$1', $qd); # XXX Lookup to show proper name of dept
        if (strstr($qd, 'M1 OR M2 OR M3 OR M4 OR M6 OR M101')) {
            $qd = str_replace('M1 OR M2 OR M3 OR M4 OR M6 OR M101', 'section:uk', $qd);
        } elseif (strstr($qd, 'M7 OR M8')) {
            $qd = str_replace('M7 OR M8', 'section:scotland', $qd);
        }
        $qd = preg_replace_callback('#\bM(\d+)\b#', function($m) {
            global $hansardmajors;
            $title = isset($hansardmajors[$m[1]]["title"]) ? $hansardmajors[$m[1]]["title"] : $m[1];
            return "in the '$title'";
        }, $qd);
        $qd = preg_replace('#\bMF\b#', 'in Future Business', $qd);

        # Replace stemmed things with their unstemmed terms from the query
        $used = array();
        preg_match_all('#Z[^\s()]+#', $qd, $m);
        foreach ($m[0] as $mm) {
            $iter = $this->queryparser->unstem_begin($mm);
            $end = $this->queryparser->unstem_end($mm);
            while (!$iter->equals($end)) {
                $tt = $iter->get_term();
                if (!in_array($tt, $used)) break;
                $iter->next();
            }
            $used[] = $tt;
            $qd = preg_replace('#' . preg_quote($mm, '#') . '#', $tt, $qd, 1);
        }

        # Speakers
        foreach ($merged as $row) {
            $from_id = str_replace('uk.org.publicwhip/person/', '', $row['gid_from']);
            $to_id = str_replace('uk.org.publicwhip/person/', '', $row['gid_to']);
            $qd = str_replace("(S$from_id OR S$to_id)", "S$to_id", $qd);
            $qd = str_replace("S$from_id OR S$to_id", "S$to_id", $qd);
        }

        preg_match_all('#S(\d+)#', $qd, $m);
        foreach ($m[1] as $mm) {
            $member = new MEMBER(array('person_id' => $mm));
            $name = $member->full_name();
            $qd = str_replace("S$mm", "speaker:$name", $qd);
        }

        # Simplify display of excluded words
        $qd = preg_replace('#AND_NOT ([a-z0-9"]+)#', '-$1', $qd);
        preg_match_all('#AND_NOT \((.*?)\)#', $qd, $m);
        foreach ($m[1] as $mm) {
            $mmn = '-' . join(' -', explode(' OR ', $mm));
            $qd = str_replace("AND_NOT ($mm)", $mmn, $qd);
        }

        foreach ($this->prefixed as $items) {
            if ($items[0] == 'groupby') {
                if ($items[1] == 'debate') {
                    $qd .= ' grouped by debate';
                } elseif ($items[1] == 'speech') {
                    $qd .= ' showing all speeches';
                } else {
                    $PAGE->error_message("Unknown group by '$items[1]' ignored");
                }
            }
        }

        $this->query_desc = trim($qd);

        #print 'DEBUG: ' . $query->get_description();
        twfy_debug("SEARCH", "words: " . var_export($this->words, true));
        twfy_debug("SEARCH", "phrases: " . var_export($this->phrases, true));
        twfy_debug("SEARCH", "queryparser description -- " . $this->query_desc);

        $this->valid = true;
    }

    public function query_description_internal($long) {
        if (!defined('XAPIANDB') || !XAPIANDB) {
            return '';
        }
        if (!$this->valid) {
            return '[bad query]';
        }

        return $this->query_desc;
    }

    // Return textual description of search
    public function query_description_short() {
        return $this->query_description_internal(false);
    }

    // Return textual description of search
    public function query_description_long() {
        return $this->query_description_internal(true);
    }

    // Return stem of a word
    public function stem($word) {
        return $this->stemmer->apply(strtolower($word));
    }

    public function get_spelling_correction() {
         if (!defined('XAPIANDB') || !XAPIANDB)
            return null;

        $qd = $this->queryparser->get_corrected_query_string();
        return $qd;
    }

    // Perform partial query to get a count of number of matches
    public function run_count($first_result, $results_per_page, $sort_order='relevance') {
        if (!defined('XAPIANDB') || !XAPIANDB)
            return null;

        $start = getmicrotime();

        switch ($sort_order) {
            case 'date':
            case 'newest':
                $this->enquire->set_sort_by_value(0, true);
                break;
            case 'oldest':
                $this->enquire->set_sort_by_value(0, false);
                break;
            case 'created':
                $this->enquire->set_sort_by_value(2, false);
            default:
                //do nothing, default ordering is by relevance
                break;
        }

        // Set collapsing and sorting
        global $PAGE;
        $collapsed = false;
        if (preg_match('#(speaker|segment):\d+#', $this->query)) {
            $collapsed = true;
        }
        foreach ($this->prefixed as $items) {
            if ($items[0] == 'groupby') {
                $collapsed = true;
                if ($items[1] == 'speech')
                    ; // no collapse key
                elseif ($items[1] == 'debate')
                    $this->enquire->set_collapse_key(3);
                else
                    $PAGE->error_message("Unknown group by '$items[1]' ignored");
            }
        }

        // default to grouping by subdebate, i.e. by page
        if (!$collapsed)
            $this->enquire->set_collapse_key(3);

        /*
        XXX Helping to debug possible Xapian bug
        foreach (array(0, 50, 100, 200, 300, 400, 460) as $fff) {
            foreach (array(0, 100, 300, 500, 1000) as $cal) {
                print "get_mset($fff, 20, $cal): ";
                $m = $this->enquire->get_mset($fff, 20, $cal);
                print $m->get_matches_estimated(). ' ';
                print $m->get_matches_lower_bound() . ' ';
                print $m->get_matches_upper_bound() . "\n";
            }
        }
        */

        #$matches = $this->enquire->get_mset(0, 500);
        $this->matches = $this->enquire->get_mset($first_result, $results_per_page, 100);
        // Take either: 1) the estimate which is sometimes too large or 2) the
        // size which is sometimes too low (it is limited to the 500 in the line
        // above).  We get the exact mset we need later, according to which page
        // we are on.
        #if ($matches->size() < 500) {
            #$count = $matches->size();
        #} else {
            $count = $this->matches->get_matches_estimated();
        #    print "DEBUG bounds: ";
        #    print $this->matches->get_matches_lower_bound();
        #    print ' - ';
        #    print $this->matches->get_matches_upper_bound();
        #}

        $duration = getmicrotime() - $start;
        twfy_debug ("SEARCH", "Search count took $duration seconds.");

        return $count;
    }

    // Perform the full search...
    public function run_search($first_result, $results_per_page, $sort_order='relevance') {
        $start = getmicrotime();

        #$matches = $this->enquire->get_mset($first_result, $results_per_page);
        $matches = $this->matches;
        $this->gids = array();
        $this->created = array();
        $this->collapsed = array();
        $this->relevances = array();
        $iter = $matches->begin();
        $end = $matches->end();
        while (!$iter->equals($end)) {
            $relevancy = $iter->get_percent();
            $weight    = $iter->get_weight();
            $collapsed = $iter->get_collapse_count();
            $doc       = $iter->get_document();
            $gid       = $doc->get_data();
            if ($sort_order == 'created') {
                array_push($this->created, join('', unpack('N', $doc->get_value(2)))); # XXX Needs fixing
            }
            twfy_debug("SEARCH", "gid: $gid relevancy: $relevancy% weight: $weight");
            array_push($this->gids, "uk.org.publicwhip/".$gid);
            array_push($this->collapsed, $collapsed);
            array_push($this->relevances, $relevancy);
            $iter->next();
        }
        $duration = getmicrotime() - $start;
        twfy_debug ("SEARCH", "Run search took $duration seconds.");
    }
    // ... use these to get the results
    public function get_gids() {
        return $this->gids;
    }
    public function get_relevances() {
        return $this->relevances;
    }
    public function get_createds() {
        return $this->created;
    }

    // Puts HTML highlighting round all the matching words in the text
    public function highlight($body) {
        if (!defined('XAPIANDB') || !XAPIANDB)
            return $body;

        $stemmed_words = array_map(array($this, 'stem'), $this->words);
        if (is_array($body)) {
            foreach ($body as $k => $b) {
                $body[$k] = $this->highlight_internal($b, $stemmed_words);
            }

            return $body;
        } else {
            return $this->highlight_internal($body, $stemmed_words);
        }
    }

    private $specialchars = array('&lt;', '&gt;', '&quot;', '&amp;');
    private $specialchars_upper = array('&LT;', '&GT;', '&QUOT;', '&AMP;');

    public function highlight_internal($body, $stemmed_words) {
        if (!defined('XAPIANDB') || !XAPIANDB)
            return $body;

        # Does html_entity_decode without the htmlspecialchars
        $body = str_replace($this->specialchars, $this->specialchars_upper, $body);
        $body = mb_convert_encoding($body, "UTF-8", "HTML-ENTITIES");
        $body = str_replace($this->specialchars_upper, $this->specialchars, $body);
        $splitextract = preg_split('/(<[^>]*>|[0-9,.]+|['.$this->wordcharsnodigit.']+)/', $body, -1, PREG_SPLIT_DELIM_CAPTURE);
        $hlextract = "";
        foreach ($splitextract as $extractword) {
            if (preg_match('/^<[^>]*>$/', $extractword)) {
                $hlextract .= $extractword;
                continue;
            }
            $endswithamp = '';
            if (substr($extractword, -1) == '&') {
                $extractword = substr($extractword, 0, -1);
                $endswithamp = '&';
            }
            $hl = false;
            $matchword = $this->stem($extractword);
            foreach ($stemmed_words as $word) {
                if ($word == '') continue;
                if ($matchword == $word) {
                    $hl = true;
                    break;
                }
            }
            if ($hl) {
                $hlextract .= "<span class=\"hi\">$extractword</span>$endswithamp";
            } else {
                $hlextract .= $extractword . $endswithamp;
            }
        }
        $body = preg_replace("#</span>\s+<span class=\"hi\">#", " ", $hlextract);

        // Contents will be used in preg_replace() to highlight the search terms.
        $findwords = array();
        $replacewords = array();

        /*
        XXX OLD Way of doing it, doesn't work too well with stemming...
        foreach ($this->words as $word) {
            if (ctype_digit($word)) {
                array_push($findwords, "/\b($word|" . number_format($word) . ")\b/");
            } else {
                array_push($findwords, "/\b($word)\b/i");
            }
            array_push($replacewords, "<span class=\"hi\">\\1</span>");
            //array_push($findwords, "/([^>\.\'])\b(" . $word . ")\b([^<\'])/i");
            //array_push($replacewords, "\\1<span class=\"hi\">\\2</span>\\3");
        }
        */

        foreach ($this->phrases as $phrase) {
            $phrasematch = join($phrase, '[^'.$this->wordchars.']+');
            array_push($findwords, "/\b($phrasematch)\b(?!(?>[^<>]*>))/i");
            $replacewords[] = "<span class=\"hi\">\\1</span>";
        }

        // Highlight search phrases.
        $hlbody = preg_replace($findwords, $replacewords, $body);

        return $hlbody;
    }

    // Find the position of the first of the search words/phrases in $body.
    public function position_of_first_word($body) {
        $lcbody = ' ' . html_entity_decode(strtolower($body)) . ' '; // spaces to make regexp mapping easier
        $pos = -1;

        // look for phrases
        foreach ($this->phrases as $phrase) {
            $phrasematch = join($phrase, '[^'.$this->wordchars.']+');
            if (preg_match('/([^'.$this->wordchars.']' . $phrasematch . '[^A-Za-z0-9])/', $lcbody, $matches))
            {
                $wordpos = strpos( $lcbody, $matches[0] );
                if ($wordpos) {
                    if ( ($wordpos < $pos) || ($pos==-1) ) {
                        $pos = $wordpos;
                    }
                }
            }
        }
        if ($pos != -1) return $pos;

        $splitextract = preg_split('/([0-9,.]+|['.$this->wordcharsnodigit.']+)/', $lcbody, -1, PREG_SPLIT_DELIM_CAPTURE);
        $stemmed_words = array_map(array($this, 'stem'), $this->words);
        foreach ($splitextract as $extractword) {
            $extractword = preg_replace('/&$/', '', $extractword);
            if (!$extractword) continue;
            $wordpos = strpos($lcbody, $extractword);
            if (!$wordpos) continue;
            foreach ($stemmed_words as $word) {
                if ($word == '') continue;
                $matchword = $this->stem($extractword);
                if ($matchword == $word && ($wordpos < $pos || $pos==-1)) {
                    $pos = $wordpos;
                }
            }
        }
        // only look for earlier words if phrases weren't found
        if ($pos != -1) return $pos;

        foreach ($this->words as $word) {
            if (ctype_digit($word)) $word = '(?:'.$word.'|'.number_format($word).')';
            if (preg_match('/([^'.$this->wordchars.']' . $word . '[^'.$this->wordchars. '])/', $lcbody, $matches)) {
                $wordpos = strpos( $lcbody, $matches[0] );
                if ($wordpos) {
                    if ( ($wordpos < $pos) || ($pos==-1) ) {
                        $pos = $wordpos;
                    }
                }
            }
        }
        // only look for something containing the word (ie. something stemmed, but doesn't work all the time) if no whole word was found
        if ($pos != -1) return $pos;

        foreach ($this->words as $word) {
            if (ctype_digit($word)) $word = '(?:'.$word.'|'.number_format($word).')';
            if (preg_match('/(' . $word . ')/', $lcbody, $matches)) {
                $wordpos = strpos( $lcbody, $matches[0] );
                if ($wordpos) {
                    if ( ($wordpos < $pos) || ($pos==-1) ) {
                        $pos = $wordpos;
                    }
                }
            }
        }

        if ($pos == -1)
            $pos = 0;

        return $pos;
    }
}

global $SEARCHENGINE;
$SEARCHENGINE = null;
