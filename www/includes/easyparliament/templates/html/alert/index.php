<div class="full-page">
    <div class="full-page__row">

      <?php if ( $message ) { ?>
        <div class="alert-section alert-section--feedback">
            <div class="alert-section__primary">
                <h3><?= $message['title'] ?></h3>
                <p class="alert-results">
                    <?= $message['text'] ?>
                </p>
            </div>
        </div>
      <?php } ?>

      <?php if ( $results ) { ?>
        <div class="alert-section alert-section--feedback">
            <div class="alert-section__primary">
              <?php if ( $results == 'alert-confirmed' ) { ?>
                <h3>Your alert has been confirmed</h3>
                <p>
                    You will now receive email alerts for the following criteria:
                </p>
                <ul><?= _htmlspecialchars($criteria) ?></ul>
                <p>
                    This is normally the day after, but could conceivably
                    be later due to issues at our or parliament.uk&rsquo;s end.
                </p>

                <!-- Google Code for TWFY Alert Signup Conversion Page -->
                <script type="text/javascript">
                /* <![CDATA[ */
                var google_conversion_id = 1067468161;
                var google_conversion_language = "en";
                var google_conversion_format = "3";
                var google_conversion_color = "ffffff";
                var google_conversion_label = "HEeDCOvPjmQQgYuB_QM";
                var google_remarketing_only = false;
                /* ]]> */
                </script>
                <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
                </script>
                <noscript>
                <div style="display:inline;">
                <img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/1067468161/?label=HEeDCOvPjmQQgYuB_QM&amp;guid=ON&amp;script=0"/>
                </div>
                </noscript>

              <?php } elseif ( $results == 'alert-suspended' ) { ?>
                <h3>Alert suspended</h3>
                <p>
                    You can reactivate the alert at any time, from the sidebar below.
                </p>

              <?php } elseif ( $results == 'alert-resumed' ) { ?>
                <h3>Alert resumed</h3>
                <p>
                    You will now receive email alerts on any day when there
                    are entries in Hansard that match your criteria.
                </p>

              <?php } elseif ( $results == 'alert-deleted' ) { ?>
                <h3>Alert deleted</h3>
                <p>
                    You will no longer receive this alert.
                </p>

              <?php } elseif ( $results == 'all-alerts-deleted' ) { ?>
                <h3>All alerts deleted</h3>
                <p>
                    You will no longer receive any alerts.
                </p>

              <?php } elseif ( $results == 'alert-fail' ) { ?>
                <h3>Hmmm, something&rsquo;s not right</h3>
                <p>
                    The link you followed to reach this page appears to be incomplete.
                </p>
                <p>
                    If you clicked a link in your alert email you may
                    need to manually copy and paste the entire link to
                    the 'Location' bar of the web browser and try again.
                </p>
                <p>
                    If you still get this message, please do
                    <a href="mailto:<?= str_replace('@', '&#64;', CONTACTEMAIL) ?>">email us</a>
                    and let us know, and we'll help out!
                </p>

              <?php } elseif ( $results == 'alert-added' ) { ?>
                <h3>Your alert has been added</h3>
                <p>
                    You will now receive email alerts on any day when
                    <?= _htmlspecialchars($criteria) ?> in parliament.
                </p>

              <?php } elseif ( $results == 'alert-confirmation' ) { ?>
                <h3>We&rsquo;re nearly done&hellip;</h3>
                <p>
                    You should receive an email shortly which will contain a link.
                    You will need to follow that link to confirm your email address
                    and receive future alerts. Thanks.
                </p>

              <?php } elseif ( $results == 'alert-exists' ) { ?>
                <h3>You&rsquo;re already subscribed to that!</h3>
                <p>
                    It&rsquo;s good to know you&rsquo;re keen though.
                </p>

              <?php } elseif ( $results == 'alert-already-signed' ) { ?>
                <h3>We&rsquo;re nearly done</h3>
                <p>
                    You should receive an email shortly which will contain a link.
                    You will need to follow that link to confirm your email address to receive the alert. Thanks.
                </p>

              <?php } elseif ( $results == 'alert-fail' ) { ?>
                <h3>Alert could not be created</h3>
                <p>
                    Sorry, we were unable to create that alert. Please
                    <a href="mailto:<?= str_replace('@', '&#64;', CONTACTEMAIL) ?>">let us know</a>.
                    Thanks.
                </p>

              <?php } ?>
            </div>
        </div>
      <?php } ?>

      <?php
          if(
              $members ||
              (isset($constituencies) && count($constituencies) > 0) ||
              ($alertsearch)
          ) {
              /* We need to disambiguate the user's instructions */
              $member_options = false;
      ?>
        <div class="alert-section alert-section--disambiguation">
            <div class="alert-section__primary">

              <?php if ($members) {
                  $member_options = true; ?>
                <h3>Sign up for alerts when people matching <i><?= _htmlspecialchars($alertsearch) ?></i> speaks</h3>
                <ul>
                  <?php
                    foreach ($members as $row) {
                  ?>
                    <li>
                        <form action="<?= $actionurl ?>" method="post">
                            <input type="hidden" name="t" value="<?= _htmlspecialchars($token) ?>">
                            <input type="hidden" name="email" value="<?= _htmlspecialchars($email) ?>">
                            <input type="hidden" name="pid" value="<?= $row['person_id'] ?>">
                            When
                            <?= member_full_name($row['house'], $row['title'], $row['given_name'], $row['family_name'], $row['lordofname'] ) ?>
                            <?php if ($row['constituency']) { ?>
                                (<?= $row['constituency'] ?>)
                            <?php } ?>
                            speaks.
                            <input type="submit" class="button small" value="Subscribe"></form>
                        </form>
                    </li>
                  <?php } ?>
                </ul>
              <?php } ?>

              <?php if (isset($constituencies) && count($constituencies) > 0) {
                  $member_options = true; ?>
                <h3>Sign up for alerts when MPs for constituencies matching <i><?= _htmlspecialchars($alertsearch) ?></i> speaks</h3>
                <ul>
                <?php foreach ($constituencies as $constituency => $member) { ?>
                    <li>
                        <form action="<?= $actionurl ?>" method="post">
                            <input type="hidden" name="t" value="<?= _htmlspecialchars($token) ?>">
                            <input type="hidden" name="email" value="<?= _htmlspecialchars($email) ?>">
                            <input type="hidden" name="pid" value="<?= $member->person_id() ?>">
                            <?= $member->full_name() ?>
                            (<?= _htmlspecialchars($constituency) ?>)
                            <input type="submit" class="button small" value="Subscribe"></form>
                        </li>
                <?php } ?>
                </ul>
              <?php } ?>

              <?php if ($alertsearch) {
                if ( $member_options ) { ?>
                <h3>Sign up for alerts for topics</h3>
                <?php } else { ?>
                <h3>Great! Can you just confirm what you mean?</h3>
                <?php } ?>
                <ul>
                    <li>
                        <form action="<?= $actionurl ?>" method="post">
                            <input type="hidden" name="t" value="<?= _htmlspecialchars($token) ?>">
                            <input type="hidden" name="email" value="<?= _htmlspecialchars($email) ?>">
                            <input type="hidden" name="keyword" value="<?= _htmlspecialchars($alertsearch) ?>">
                            Receive alerts when <?= _htmlspecialchars($alertsearch_pretty) ?>
                            <input type="submit" class="button small" value="Subscribe">
                        </form>
                      <?php if ( isset($mistakes['multiple']) ) { ?>
                        <em class="error">
                            You have used a comma in your search term &ndash;
                            are you sure this is what you want? You cannot
                            sign up to multiple search terms using a comma
                            &ndash; either use OR, or create a separate alert
                            for each individual term.
                        </em>
                      <?php } ?>
                      <?php if ( isset($mistakes['postcode_and']) ) { ?>
                        <em class="error">
                            You have used a postcode and something else in your
                            search term &ndash; are you sure this is what you
                            want? You will only get an alert if all of these
                            are mentioned in the same debate.
                          <?php if (isset($member_alertsearch)) { ?>
                            Did you mean to get alerts for when your MP
                            <?= $scottish_text ?> mentions something instead?
                            If so maybe you want to subscribe to&hellip;
                          <?php } ?>
                        </em>
                      <?php } ?>
                    </li>

                  <?php if (isset($member_alertsearch)) { ?>
                    <li>
                        <form action="<?= $actionurl ?>" method="post">
                            <input type="hidden" name="t" value="<?= _htmlspecialchars($token) ?>">
                            <input type="hidden" name="email" value="<?= _htmlspecialchars($email) ?>">
                            <input type="hidden" name="keyword" value="<?= _htmlspecialchars($member_alertsearch) ?>">
                            Mentions of [<?= _htmlspecialchars($member_displaysearch) ?>] by <?= $mp_display_text . $member->full_name() ?>
                            <input type="submit" class="button small" value="Subscribe">
                        </form>
                    </li>
                  <?php } ?>


                  <?php if (isset($scottish_alertsearch)) { ?>
                    <li>
                        <form action="<?= $actionurl ?>" method="post">
                            <input type="hidden" name="t" value="<?= _htmlspecialchars($token) ?>">
                            <input type="hidden" name="email" value="<?= _htmlspecialchars($email) ?>">
                            <input type="hidden" name="keyword" value="<?= _htmlspecialchars($scottish_alertsearch) ?>">
                            Mentions of [<?= _htmlspecialchars($member_displaysearch) ?>] by your MSP, <?= $scottish_member->full_name() ?>
                            <input type="submit" class="button small" value="Subscribe">
                        </form>
                    </li>
                  <?php } ?>
                </ul>
              <?php } ?>
            </div>
        </div>
      <?php } ?>

        <div class="alert-section">
            <div class="alert-section__secondary">
              <?php if ($email_verified) { ?>

                  <?php if ( $alerts ) { ?>
                    <?php include('_list.php'); ?>
                  <?php } ?>

                  <?php if ( $current_mp ) { ?>
                    <h3>Your MP alert</h3>
                    <ul class="alerts-manage__list">
                        <li>
                            You are not subscribed to an alert for your
                            current MP, <?=$current_mp->full_name() ?>.
                            <form action="<?= $actionurl ?>" method="post">
                                <input type="hidden" name="t" value="<?=_htmlspecialchars($token)?>">
                                <input type="hidden" name="pid" value="<?= $current_mp->person_id() ?>">
                                <input type="submit" class="button small" value="Subscribe">
                            </form>
                        </li>
                    </ul>
                  <?php } ?>

              <?php } else { ?>
                <p>
                    If you <a href="/user/?pg=join">join</a> or
                    <a href="/user/login/?ret=%2Falert%2F">sign in</a>,
                    you can suspend, resume and delete your email alerts
                    from your profile page.
                </p>
                <p>
                    Plus, you won&rsquo;t need to confirm your email address
                    for every alert you set.
                </p>
              <?php } ?>
            </div>

            <div class="alert-section__primary">

              <?php if ($pid) { ?>
                <h3>
                    Sign up for an alert when <?= $pid_member->full_name() ?>
                    <?php if ($pid_member->constituency()) { ?>
                    (<?= _htmlspecialchars($pid_member->constituency()) ?>)
                    <?php } ?>
                    speaks.
                </h3>
              <?php } elseif ($keyword) { ?>
                <h3>
                    Sign up for an alert when <?= _htmlspecialchars($display_keyword) ?>.
                </h3>
              <?php } elseif ($alertsearch) { ?>
                <h3>Not quite right? Search again to refine your email alert.</h3>
              <?php } else { ?>
                <h3>Request a new TheyWorkForYou email alert</h3>
              <?php } ?>

                <form action="<?= $actionurl ?>" method="post" class="alert-page-main-inputs">
                  <?php if (!$email_verified) { ?>
                    <p>
                      <?php if (isset($errors["email"]) && $submitted) { ?>
                        <span class="alert-page-error"><?= $errors["email"] ?></span>
                      <?php } ?>
                        <input type="email" class="form-control" placeholder="Your email address" name="email" id="email" value="<?= _htmlentities($email) ?>">
                    </p>
                  <?php } ?>

                    <p>
                      <?php if ($pid) { ?>
                        <input type="text" class="form-control" name="alertsearch" id="alertsearch" disabled="disabled"
                            value="<?= $pid_member->full_name() ?><?php if ($pid_member->constituency()) { ?> (<?= _htmlspecialchars($pid_member->constituency()) ?>)<?php } ?>">
                      <?php } elseif ($keyword) { ?>
                        <input type="text" class="form-control" name="alertsearch" id="alertsearch" disabled="disabled" value="<?= _htmlspecialchars($display_keyword) ?>">
                      <?php } else { ?>
                        <input type="text" class="form-control" placeholder="Search term, postcode, or MP name" name="alertsearch" id="alertsearch" value="<?= _htmlentities($search_text) ?>">
                      <?php } ?>
                        <input type="submit" class="button" value="<?= ($pid || $keyword) ? 'Subscribe' : 'Search' ?>">
                    </p>

                    <input type="hidden" name="t" value="<?= _htmlspecialchars($token) ?>">
                    <input type="hidden" name="submitted" value="1">

                  <?php if ($pid) { ?>
                    <input type="hidden" name="pid" value="<?= _htmlspecialchars($pid) ?>">
                  <?php } ?>
                  <?php if ($keyword) { ?>
                    <input type="hidden" name="keyword" value="<?= _htmlspecialchars($keyword) ?>">
                  <?php } ?>

                  <?php if ($sign) { ?>
                    <input type="hidden" name="sign" value="<?= _htmlspecialchars($sign) ?>">
                  <?php } ?>

                  <?php if ($site) { ?>
                    <input type="hidden" name="site" value="<?= _htmlspecialchars($site) ?>">
                  <?php } ?>
                </form>

              <?php if (!$pid && !$keyword) { ?>
                <div class="alert-page-search-tips">
                    <h3>Search tips</h3>
                    <p>
                        To be alerted on an exact <strong>phrase</strong>,
                        be sure to put it in quotes. Also use quotes
                        around a word to avoid stemming (where &lsquo;horse&rsquo;
                        would also match &lsquo;horses&rsquo;).
                    </p>
                    <p>
                        You should only enter <strong>one term per alert</strong>
                        &ndash; if you wish to receive alerts on more than one
                        thing, or for more than one person, simply fill in this
                        form as many times as you need, or use boolean OR.
                    </p>
                    <p>
                        For example, if you wish to receive alerts whenever
                        the words <i>horse</i> or <i>pony</i> are mentioned in
                        Parliament, please fill in this form once with the word
                        <i>horse</i> and then again with the word <i>pony</i>
                        (or you can put <i>horse OR pony</i> with the OR in capitals).
                        Do not put <i>horse, pony</i> as that will only sign you up
                        for alerts where <strong>both</strong> horse and pony are mentioned.
                    </p>
                </div>

                <div class="alert-page-search-tips">

                    <h3>Step by step guides</h3>
                    <p>
                        The mySociety blog has a number of posts on signing up for
                        and managing alerts:
                    </p>

                    <ul>
                        <li><a href="https://www.mysociety.org/2014/07/23/want-to-know-what-your-mp-is-saying-subscribe-to-a-theyworkforyou-alert/">How to sign up for alerts on what your MP is saying</a>.</li>
                        <li><a href="https://www.mysociety.org/2014/09/01/well-send-you-an-email-every-time-your-chosen-word-is-mentioned-in-parliament/">How to sign up for alerts when your chosen word is mentioned</a>.</li>
                        <li><a href="https://www.mysociety.org/2014/09/04/how-to-manage-your-theyworkforyou-alerts/">Managing email alerts</a>, including how to stop or suspend them.</li>
                    <ul>
                </div>
              <?php } ?>
            </div>

        </div>

    </div>
</div>
