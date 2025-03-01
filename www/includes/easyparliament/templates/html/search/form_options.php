<div class="search-page__section search-page__section--options" id="options" data-advanced="<?=$is_adv ?>">
    <div class="search-page__section__primary">
        <h2>Advanced search</h2>

        <h4>Find this exact word or phrase</h4>
        <div class="search-option">
            <div class="search-option__control">
                <input name="phrase" type="text" value="<?= _htmlentities($search_phrase) ?>" class="form-control">
            </div>
            <div class="search-option__hint">
                <p>You can also do this from the main search box by
putting exact words in quotes: like <code>"cycling"</code> or <code>"hutton
report"</code></p>
                <p>By default, we show words related to your search
term, like &ldquo;cycle&rdquo; and &ldquo;cycles&rdquo; in a search for
<code>cycling</code>. Putting the word in quotes, like <code>"cycling"</code>,
will stop this.</p>

            </div>
        </div>

        <h4>Excluding these words</h4>
        <div class="search-option">
            <div class="search-option__control">
                <input name="exclude" type="text" value="<?= _htmlentities($search_exclude) ?>" class="form-control">
            </div>
            <div class="search-option__hint">
                <p>You can also do this from the main search box by
putting a minus sign before words you don&rsquo;t want: like <code>hunting
-fox</code></p>
                <p>We also support <a href="/help/#searching">a
bunch of boolean search modifiers</a>, like <code>AND</code> and
<code>NEAR</code>, for precise searching.</p>
            </div>
        </div>

        <h4>Date range</h4>
        <div class="search-option">
            <div class="search-option__control search-option__control--date-range">
                <input name="from" type="date" value="<?= _htmlentities($search_from) ?>" class="form-control">
                <span>to</span>
                <input name="to" type="date" value="<?= _htmlentities($search_to) ?>" class="form-control">
            </div>
            <div class="search-option__hint">
                <p>You can give a <strong>start date, an end date,
or both</strong> to restrict results to a particular date range. A missing end
date implies the current date, and a missing start date implies the oldest date
we have in the system. Dates can be entered in any format you wish, e.g.
<code>3rd March 2007</code> or <code>17/10/1989</code></p>
            </div>
        </div>

        <h4>Person</h4>
        <div class="search-option">
            <div class="search-option__control">
                <input name="person" type="text" value="<?= _htmlentities($search_person) ?>" class="form-control">
            </div>
            <div class="search-option__hint">
                <p>Enter a name here to restrict results to contributions only by that person.</p>
            </div>
        </div>

        <h4>Section</h4>
        <div class="search-option">
            <div class="search-option__control">
                <select name="section">
                    <option></option>
                    <optgroup label="UK Parliament">
                        <option value="uk"<?= $search_section == 'uk' ? ' selected' : '' ?>>All UK</option>
                        <option value="debates"<?= $search_section == 'debates' ? ' selected' : '' ?>>House of Commons debates</option>
                        <option value="whall"<?= $search_section == 'whall' ? ' selected' : '' ?>>Westminster Hall debates</option>
                        <option value="lords"<?= $search_section == 'lords' ? ' selected' : '' ?>>House of Lords debates</option>
                        <option value="wrans"<?= $search_section == 'wrans' ? ' selected' : '' ?>>Written answers</option>
                        <option value="wms"<?= $search_section == 'wms' ? ' selected' : '' ?>>Written ministerial statements</option>
                        <option value="standing"<?= $search_section == 'standing' ? ' selected' : '' ?>>Bill Committees</option>
                        <option value="future"<?= $search_section == 'future' ? ' selected' : '' ?>>Future Business</option>
                    </optgroup>
                    <optgroup label="Northern Ireland Assembly">
                        <option value="ni"<?= $search_section == 'ni' ? ' selected' : '' ?>>Debates</option>
                    </optgroup>
                    <optgroup label="Scottish Parliament">
                        <option value="scotland"<?= $search_section == 'scotland' ? ' selected' : '' ?>>All Scotland</option>
                        <option value="sp"<?= $search_section == 'sp' ? ' selected' : '' ?>>Debates</option>
                        <option value="spwrans"<?= $search_section == 'spwrans' ? ' selected' : '' ?>>Written answers</option>
                    </optgroup>
                    <optgroup label="London Assembly">
                        <option value="lmqs"<?= $search_section == 'lmqs' ? ' selected' : '' ?>>Questions to the Mayor</option>
                    </optgroup>
                 </select>
            </div>
            <div class="search-option__hint">
                <p>Restrict results to a particular parliament or assembly that we cover (e.g. the Scottish Parliament), or a particular type of data within an institution, such as Commons Written Answers.</p>
            </div>
        </div>

        <h4>Column</h4>
        <div class="search-option">
            <div class="search-option__control">
                <input name="column" type="text" value="<?= _htmlentities($search_column) ?>" class="form-control">
            </div>
            <div class="search-option__hint">
                <p>If you know the actual Hansard column number of
the information you are interested in (perhaps you&rsquo;re looking up a paper
reference), you can restrict results to that; you can also use
<code>column:123</code> in the main search box.</p>
            </div>
        </div>

        <p><input type="submit" class="button" value="Search"></p>
    </div>
</div>
