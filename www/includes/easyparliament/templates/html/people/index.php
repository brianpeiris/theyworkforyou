<?php

$dissolution = MySociety\TheyWorkForYou\Dissolution::dates();
if (!count($data)) {
    if ($type == 'mps' && isset($dissolution[1])) {
        $former = 'former';
    } elseif ($type == 'msps' && isset($dissolution[4])) {
        $former = 'former';
    } elseif ($type == 'mlas' && isset($dissolution[3])) {
        $former = 'former';
    }
}

?>

<div class="full-page">
    <div class="full-page__row search-page people-list-page">

        <?php if ($type != 'peers' && count($mp_data)) { ?>

        <div class="search-page__section search-page__section--your-mp">
            <div class="search-page__section__primary">
                <div class="people-list__your-mp">
                    <div class="people-list__your-mp__header">
                      <p>
                          Based on postcode <strong><?= $mp_data['postcode'] ?></strong>
                          <a href="<?= $mp_data['change_url'] ?>">(Change postcode)</a>
                      </p>
                    <?php if ( isset( $mp_data ) && $type != 'mlas' ) { ?>
                      <h3>Your <?= $mp_data['former'] ? $mp_data['former'] . ' ' : '' ?><?= $rep_name ?> is</h3>
                    </div>
                    <a href="<?= $mp_data['mp_url'] ?>" class="people-list__person">
                    <img class="people-list__person__image" src="<?= $mp_data['image'] ?>">
                        <h2 class="people-list__person__name"><?= $mp_data['name'] ?></h2>
                        <p class="people-list__person__memberships">
                        <span class="people-list__person__constituency"><?= $mp_data['constituency'] ?></span>
                        <span class="people-list__person__party <?= slugify($mp_data['party']) ?>"><?= $mp_data['party'] ?></span>
                        </p>
                    </a>
                    <?php }
                    if ( isset($reps) ) {
                        if ( isset($mp_data) && $type != 'mlas' ) { ?>
                    <div class="people-list__your-mp__replist-header">
                        <?php } ?>
                      <h3>Your <?=$former ?> <?= $rep_plural == 'MSPs' ? 'regional ' : '' ?><?= $rep_plural ?> are</h3>
                    </div>
                        <?php foreach ( $reps as $rep ) { ?>
                    <a href="<?= $rep['mp_url'] ?>" class="people-list__person">
                    <img class="people-list__person__image" src="<?= $rep['image'] ?>">
                        <h2 class="people-list__person__name"><?= $rep['name'] ?></h2>
                        <p class="people-list__person__memberships">
                        <span class="people-list__person__constituency"><?= $rep['constituency'] ?></span>
                        <span class="people-list__person__party <?= slugify($rep['party']) ?>"><?= $rep['party'] ?></span>
                        </p>
                    </a>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        </div>

      <?php } else { ?>

        <form action="/<?= ($type == 'mlas' || $type == 'msps') ? 'postcode' : 'search' ?>/">
            <div class="search-page__section search-page__section--search">
                <div class="search-page__section__primary">
                    <p class="search-page-main-inputs">
                    <?php if ( $type == 'peers' ) { ?>
                        <label for="find-mp-by-name-or-postcode">Find <?= $rep_plural ?> by name:</label>
                    <?php } elseif ( $type == 'mlas' || $type == 'msps' ) { ?>
                        <label for="find-mp-by-name-or-postcode">Find your <?= $rep_name ?> by postcode:</label>
                    <?php } else { ?>
                        <label for="find-mp-by-name-or-postcode">Find your <?= $rep_name ?> by name or postcode:</label>
                    <?php } ?>
                        <input type="text" class="form-control" name="<?= ($type == 'mlas' || $type == 'msps') ? 'pc' : 'q' ?>" id="find-mp-by-name-or-postcode">
                        <button type="submit" class="button">Find</button>
                    </p>
                </div>
            </div>
        </form>

      <?php } ?>

        <div class="search-page__section search-page__section--results">
        <?php
        if (isset($former)) {
            if ($type == 'mps') {
            # No reps. Election period!
        ?>
            <div class="search-page__section__primary">
                During the period from the dissolution of Parliament to the general election, there are no Members of Parliament.
                <a href="/mps/?date=<?=$dissolution[1] ?>">View list of MPs as it was when Parliament was dissolved</a>
            </div>
        <?php } elseif ($type == 'msps') { ?>
            <div class="search-page__section__primary">
                During the period from the dissolution of the Scottish Parliament to the election, there are no Members of the Scottish Parliament.
                <a href="/msps/?date=<?=$dissolution[4] ?>">View list of MSPs as it was when the Scottish Parliament was dissolved</a>
            </div>
        <?php } elseif ($type == 'mlas') { ?>
            <div class="search-page__section__primary">
                During the period from the dissolution of the Northern Ireland Assembly to the election, there are no Members of the Northern Ireland Assembly.
                <a href="/mlas/?date=<?=$dissolution[3] ?>">View list of MLAs as it was when the Northern Ireland Assembly was dissolved</a>
            </div>
        <?php
            }
        } else { ?>

            <div class="search-page__section__primary">
            <h2>All <?= $rep_plural ?></h2>

                <?php if ( $type != 'peers' ) { ?>
                <ul class="search-result-display-options">
                    <?php if ( $order == 'given_name' ) { ?>
                    <li><strong>Sorted by</strong> First name</li>
                    <li>Sort by <a href="<?= $urls['by_last'] ?>">Last name</a> / <a href="<?= $urls['by_party'] ?>">Party</a></li>
                    <?php } else if ( $order == 'party' ) { ?>
                    <li><strong>Sorted by</strong> Party</li>
                    <li>Sort by <a href="<?= $urls['by_first'] ?>">First name</a> / <a href="<?= $urls['by_last'] ?>">Last name</a></li>
                    <?php } else { ?>
                    <li><strong>Sorted by</strong> Last name</li>
                    <li>Sort by <a href="<?= $urls['by_first'] ?>">First name</a> / <a href="<?= $urls['by_party'] ?>">Party</a></li>
                    <?php } ?>
                </ul>
                <?php } else { ?>
                <ul class="search-result-display-options">
                    <?php if ( $order == 'party' ) { ?>
                    <li><strong>Sorted by</strong> Party</li>
                    <li>Sort by <a href="<?= $urls['by_name'] ?>">Name</a></li>
                    <?php } else { ?>
                    <li><strong>Sorted by</strong> Name</li>
                    <li>Sort by <a href="<?= $urls['by_party'] ?>">Party</a></li>
                    <?php } ?>
                </ul>
                <?php } ?>

                <?php if ($order != 'party') { ?>
                <div class="people-list-alphabet">
                    <a href="#A">A</a>
                    <a href="#B">B</a>
                    <a href="#C">C</a>
                    <a href="#D">D</a>
                    <a href="#E">E</a>
                    <a href="#F">F</a>
                    <a href="#G">G</a>
                    <a href="#H">H</a>
                    <a href="#I">I</a>
                    <a href="#J">J</a>
                    <a href="#K">K</a>
                    <a href="#L">L</a>
                    <a href="#M">M</a>
                    <a href="#N">N</a>
                    <a href="#O">O</a>
                    <a href="#P">P</a>
                    <a href="#Q">Q</a>
                    <a href="#R">R</a>
                    <a href="#S">S</a>
                    <a href="#T">T</a>
                    <a href="#U">U</a>
                    <a href="#V">V</a>
                    <a href="#W">W</a>
                    <a href="#X">X</a>
                    <a href="#Y">Y</a>
                    <a href="#Z">Z</a>
                </div>
                <?php } ?>

                <div class="people-list">
                <?php if ($order != 'party') {
                    $current_initial = '';
                    $a_to_z_key = 'family_name';
                    if ($order == 'given_name') {
                        $a_to_z_key = 'given_name';
                    }
                }
                $initial_link = '';
                foreach ( $data as $person ) {
                    if ($order != 'party') {
                        $initial = substr( strtoupper($person[$a_to_z_key]), 0, 1);
                        if ( $initial != $current_initial ) {
                            $current_initial = $initial;
                            $initial_link = "name=\"$initial\" ";
                        } else {
                            $initial_link = "";
                        }
                    }
                ?>
                <a <?= $initial_link ?>href="/mp/<?= $person['url'] ?>" class="people-list__person">
                <noscript class="loading-lazy">
                    <img class="people-list__person__image" src="<?= $person['image'] ?>" loading="lazy" alt="">
                </noscript>
                        <h2 class="people-list__person__name"><?= ucfirst($person['name']) ?></h2>
                        <p class="people-list__person__memberships">
                        <?php if ( $person['constituency'] ) { ?>
                        <span class="people-list__person__constituency"><?= $person['constituency'] ?></span>
                        <?php } ?>
                        <span class="people-list__person__party <?= slugify($person['party']) ?>"><?= $person['party'] ?></span>
                        </p>
                    </a>
                <?php } ?>
                </div>

            </div>
            <?php } ?>

            <div class="search-page__section__secondary search-page-sidebar">
                <h2>Download data</h2>
                <p class="sidebar-item-with-icon sidebar-item-with-icon--excel">
                <a href="<?= $urls['by_csv'] ?>">Download this list as a CSV</a>
                    suitable for Excel
                </p>
                <?php if ( $type == 'mps' ) { ?>
                <style>
                .js #past-list-dates { display: none; }
                </style>
                <form method="get" action="<?= $urls['plain'] ?>" class="sidebar-item-with-icon sidebar-item-with-icon--date">
                    <p>
                        Or view a past list
                        <a class="pick-a-date" href="#past-list-dates">Pick a date</a>
                    </p>
                    <p class="past-list-dates" id="past-list-dates">
                        <a href="<?= $urls['plain'] ?>?date=2017-06-09">MPs at 2017 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=2015-05-08">MPs at 2015 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=2010-05-06">MPs at 2010 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=2005-05-05">MPs at 2005 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=2001-06-07">MPs at 2001 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1997-05-01">MPs at 1997 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1992-04-09">MPs at 1992 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1987-06-11">MPs at 1987 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1983-06-09">MPs at 1983 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1979-05-03">MPs at 1979 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1974-10-10">MPs at Oct 1974 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1974-02-28">MPs at Feb 1974 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1970-06-18">MPs at 1970 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1966-03-31">MPs at 1966 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1964-10-15">MPs at 1964 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1959-10-08">MPs at 1959 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1955-05-26">MPs at 1955 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1951-10-25">MPs at 1951 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1950-02-23">MPs at 1950 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1945-07-05">MPs at 1945 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1935-11-14">MPs at 1935 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1931-10-27">MPs at 1931 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1929-05-30">MPs at 1929 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1924-10-29">MPs at 1924 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1923-12-06">MPs at 1923 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1922-11-15">MPs at 1922 general election</a>
                        <a href="<?= $urls['plain'] ?>?date=1918-12-14">MPs at 1918 general election</a>
                        <label for="past-list-custom-date">Custom date&hellip;</label>
                        <span class="input-appended">
                            <input type="text" id="past-list-custom-date" name="date" class="form-control" placeholder="YYYY-MM-DD">
                            <input type="submit" value="Download" class="button">
                        </span>
                    </p>
                </form>

                <?php } else if ( $type == 'msps' ) { ?>
                    <p class="past-list-dates" id="past-list-dates">
                        <a href="<?= $urls['plain'] ?>?date=2011-05-05">MSPs at 2011 election</a>
                        <a href="<?= $urls['plain'] ?>?date=2007-05-03">MSPs at 2007 election</a>
                        <a href="<?= $urls['plain'] ?>?date=2003-05-01">MSPs at 2003 election</a>
                        <a href="<?= $urls['plain'] ?>?date=1999-05-06">MSPs at 1999 election</a>
                        <a href="<?= $urls['plain'] ?>?all=1">Historical list of all MSPs</a>
                    </p>
                <?php } else { ?>
                    <p class="past-list-dates" id="past-list-dates">
                    <a href="<?= $urls['plain'] ?>?all=1">Historical list of all <?= $rep_plural ?></a>
                    </p>
                <?php } ?>
            </div>
        </div>

    </div>
</div>
