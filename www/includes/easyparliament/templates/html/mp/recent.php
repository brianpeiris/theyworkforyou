<?php
include_once INCLUDESPATH . "easyparliament/templates/html/mp/header.php";
?>

<div class="full-page">
    <div class="full-page__row">
        <div class="full-page__unit">
            <div class="person-navigation">
                <ul>
                    <li><a href="<?= $member_url ?>">Overview</a></li>
                    <li><a href="<?= $member_url ?>/votes">Voting Record</a></li>
                    <li class="active"><a href="<?= $member_url ?>/recent">Recent Votes</a></li>
                </ul>
            </div>
        </div>
        <div class="person-panels">
            <div class="primary-content__unit">
                <?php include('_covid19_panel.php'); ?>

                <?php if ($party == 'Sinn Féin' && in_array(HOUSE_TYPE_COMMONS, $houses)): ?>
                <div class="panel">
                    <p>Sinn F&eacute;in MPs do not take their seats in Parliament.</p>
                </div>
                <?php endif; ?>

                <?php

                $displayed_votes = false;
                $show_all = true;
                $current_date = '';
                $sidebar_links = array();

                if ( isset($divisions) && $divisions ) {
                    if ($has_voting_record) {
                        foreach ($divisions as $division) {
                          $displayed_votes = true;

                          if ($current_date != $division['date']) {
                            if ($current_date != '' ) {
                              print('</ul></div>');
                            }
                            $current_date = $division['date'];
                            $sidebar_links[] = $division['date'];
                            ?>
                          <div class="panel" id="<?= strftime('%Y-%m-%d', strtotime($division['date'])) ?>">
                            <h2><?= strftime('%e %b %Y', strtotime($division['date'])) ?></h2>
                             <ul class="vote-descriptions policy-votes">
                          <?php }
                          include('_division_description.php');
                        }
                        echo('</div>');
                    }
                } ?>

                <?php if (!$displayed_votes) { ?>
                    <div class="panel">
                        <p>This person has not voted recently.</p>
                    </div>
                <?php }
                include('_vote_footer.php'); ?>
            </div>

            <div class="sidebar__unit in-page-nav">
                <ul>
                  <?php foreach($sidebar_links as $date) { ?>
                    <li>
                        <a href="#<?= strftime('%Y-%m-%d', strtotime($date)) ?>">
                            <?= strftime('%e %b %Y', strtotime($date)) ?>
                        </a>
                    </li>
                  <?php } ?>
                </ul>
            </div>
        </div>
    </div>
</div>
