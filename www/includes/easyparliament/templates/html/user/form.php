<div class="full-page legacy-page static-page"> <div class="full-page__row"> <div class="panel">        <div class="stripe-side">
                <?php if (isset($errors['db'])) { ?>
                <p class="error">
                    <?= $errors['db'] ?>
                </p>
                <?php } ?>

            <div class="main">
            <?php if ($facebook_user) { ?>
              <h1>Edit your details</h1>
              <form method="post" action="/user/index.php">
                <?php if (isset($errors['postcode'])) { ?>
                <p class="error">
                    <?= $errors['postcode'] ?>
                </p>
                <?php } ?>

                <br style="clear: left;">&nbsp;<br>
                <div class="row">
                <span class="label"><label for="postcode">Your UK postcode:</label></span>
                <span class="formw"><input type="text" name="postcode" id="postcode" value="<?= _htmlentities($postcode) ?>" maxlength="10" size="10" class="form"> <small>Optional and not public</small></span>
                </div>

                <p>
                We use this to show you information about your MP.
                </p>

                <div class="row">
                <span class="formw"><input type="submit" class="submit" value="Update details"></span>
                </div>

                <input type="hidden" name="submitted" value="true">

                <input type="hidden" name="pg" value="edit">
              </form>
            <?php } else { ?>
              <?php if (isset($showall) && $showall == true && isset($user_id)) { ?>
              <h1>Edit the user&rsquo;s details</h1>
              <?php } else { ?>
              <h1>Edit your details</h1>
              <?php } ?>

              <form method="post" action="/user/index.php">
                <?php if (isset($errors['firstname'])) { ?>
                <p class="error">
                    <?= $errors['firstname'] ?>
                </p>
                <?php } ?>

                <div class="row">
                <span class="label"><label for="firstname">Your first name:</label></span>
                <span class="formw"><input type="text" name="firstname" id="firstname" value="<?= _htmlentities($firstname) ?>" maxlength="255" size="30" class="form"></span>
                </div>

                <?php if (isset($errors['lastname'])) { ?>
                <p class="error">
                    <?= $errors['lastname'] ?>
                </p>
                <?php } ?>

                <div class="row">
                <span class="label"><label for="lastname">Your last name:</label></span>
                <span class="formw"><input type="text" name="lastname" id="lastname" value="<?= _htmlentities($lastname) ?>" maxlength="255" size="30" class="form"></span>
                </div>

                <?php if (isset($errors['email'])) { ?>
                <p class="error">
                    <?= $errors['email'] ?>
                </p>
                <?php } ?>

                <div class="row">
                <span class="label"><label for="em">Email address:</label></span>
                <span class="formw"><input type="text" name="em" id="em" value="<?= _htmlentities($email) ?>" maxlength="255" size="30" class="form"></span>
                </div>

                <?php if (isset($errors['password'])) { ?>
                <p class="error">
                    <?= $errors['password'] ?>
                </p>
                <?php } ?>

                <div class="row">
                &nbsp;<br><small>To change your password enter a new one twice below (otherwise, leave blank).</small>
                </div>
                <div class="row">
                <span class="label"><label for="password">Password:</label></span>
                <span class="formw"><input type="password" name="password" id="password" value="" maxlength="30" size="20" class="form"> <small>At least six characters</small></span>
                </div>

                <?php if (isset($errors['password2'])) { ?>
                <p class="error">
                    <?= $errors['password2'] ?>
                </p>
                <?php } ?>

                <div class="row">
                <span class="label"><label for="password2">Repeat password:</label></span>
                <span class="formw"><input type="password" name="password2" id="password2" value="" maxlength="30" size="20" class="form"></span>
                </div>

                <?php if (isset($errors['postcode'])) { ?>
                <p class="error">
                    <?= $errors['postcode'] ?>
                </p>
                <?php } ?>

                <br style="clear: left;">&nbsp;<br>
                <div class="row">
                <span class="label"><label for="postcode">Your UK postcode:</label></span>
                <span class="formw"><input type="text" name="postcode" id="postcode" value="<?= _htmlentities($postcode) ?>" maxlength="10" size="10" class="form"> <small>Optional and not public</small></span>
                </div>

                <?php if (isset($errors['url'])) { ?>
                <p class="error">
                    <?= $errors['url'] ?>
                </p>
                <?php } ?>

                <div class="row">
                <span class="label"><label for="url">Your website:</label></span>
                <span class="formw"><input type="text" name="url" id="url" value="<?= _htmlentities($url) ?>" maxlength="255" size="20" class="form"> <small>Optional and public</small></span>
                </div>

                <?php if (isset($showall) && $showall == true) { ?>
                  <?php if (isset($errors['status'])) { ?>
                  <p class="error">
                      <?= $errors['status'] ?>
                  </p>
                  <?php } ?>

                  <div class="row">
                  <span class="label">Security status:</span>
                  <span class="formw"><select name="status">
                  <?php
                  foreach ($statuses as $n => $status_name) { ?>
                    <option value="<?= $status_name ?>"<?= $status_name == $status ? ' selected' : '' ?>>
                      <?= $status_name ?>
                    </option>
                  <?php } ?>
                  </select></span>
                  </div>

                  <div class="row">
                  <span class="label"><label for="confirmed">Confirmed?</label></span>
                  <span class="formw"><input type="checkbox" name="confirmed[]" id="confirmed" value="true"
                    <?= isset($confirmed) && $confirmed == true ? 'checked' : '' ?>
                  ></span>
                  </div>

                  <div class="row">
                  <span class="label"><label for="deleted">"Deleted"?</label></span>
                  <span class="formw"><input type="checkbox" name="deleted[]" id="deleted" value="true"
                    <?= isset($deleted) && $deleted == true ? 'checked' : '' ?>
                  > <small>(No data will actually be deleted.)</small></span>
                  </div>

                <?php } ?>

                <div class="row">
                &nbsp;<br>Do you want to receive the monthly newsletter from mySociety, with news on TheyWorkForYou and our other projects?
                </div>

                <?php if (isset($errors['optin'])) { ?>
                <p class="error">
                    <?= $errors['optin'] ?>
                </p>
                <?php } ?>

                <div class="row">
                <span class="formw"><input type="radio" name="optin" id="optintrue" value="true" <?= $optin == 'Yes' ? ' checked' : '' ?>> <label for="optintrue">Yes</label><br>
                <input type="radio" name="optin" id="optinfalse" value="false" <?= $optin == 'No' ? ' checked' : '' ?>> <label for="optinfalse">No</label></span>
                </div>

                <div class="row">
                <span class="formw"><input type="submit" class="submit" value="Update details"></span>
                </div>

                <input type="hidden" name="submitted" value="true">
                <input type="hidden" name="pg" value="<?= _htmlentities($pg) ?>">

                <?php if (isset($showall) && $showall == true && isset($user_id)) { ?>
                    <input type="hidden" name="u" value="<?= _htmlentities($user_id) ?>">
                <?php } ?>

              </form>
            <?php } ?>
            </div>
            <div class="sidebar">&nbsp;</div>
            <div class="break"></div>
        </div>
     </div>
   </div>
</div>
