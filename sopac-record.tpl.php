<?php
/*
 * Item record display template
 */

// Set the page title
drupal_set_title(ucwords($item['title']));

// Set up some variables.
$url_prefix = variable_get('sopac_url_prefix', 'cat/seek');
$new_author_str = sopac_author_format($item['author'], $item['addl_author']);
$dl_mat_codes = in_array($item['mat_code'], $locum->csv_parser($locum_config['format_special']['download']));
$no_avail_mat_codes = in_array($item['mat_code'], $locum->csv_parser($locum_config['format_special']['skip_avail']));
$location_label = $item['loc_code'] || ($item['loc_code'] != 'none') ? $locum_config['locations'][$item['loc_code']] : '';
$note_arr = unserialize($item['notes']);

$series = trim($item['series']);
if ($split_pos = max(strpos($series, ";"), strpos($series, ":"), strpos($series, "."), 0)) {
  $series = trim(substr($series, 0, $split_pos));
}

// Get Zoom Lends copies
$zooms_avail = $item_status['callnums']['Zoom Lends DVD']['avail'] + $item_status['callnums']['Zoom Lends Book']['avail'];
$avail = $item_status['avail'] - $zooms_avail;

if ($avail > 0) {
  $reqtext = 'There ' . ($avail == 1 ? 'is' : 'are') . " currently $avail available";
}
else {
  $reqtext = 'There are no copies available';
}
if ($zooms_avail > 0) {
  //$zoom_link = l('Zoom Lends', 'catalog/browse/unusual#ZOOM', array('query' => array('lightbox' => 1), 'attributes' => array('rel' => 'lightframe')));
  $zoom_link = 'Zoom Lends';
  $reqtext .= " ($zooms_avail $zoom_link available)";
}
if ($item_status['holds'] > 0) {
  $reqtext .= ' and ' . $item_status['holds'] . ' request' . ($item_status['holds'] == 1 ? '' : 's') . " on " . $item_status['total'] . ' ' . ($item_status['total'] == 1 ? 'copy' : 'copies');
}

// Build the item availability array
if (count($item_status['items'])) {
  foreach ($item_status['items'] as $copy_status) {
    if ($copy_status['avail'] > 0) {
      $status_msg = 'Available';
    }
    else {
      $status_msg = ucwords(strtolower($copy_status['statusmsg']));
    }
    if (variable_get('sopac_multi_branch_enable', 0)) {
      $copy_status_array[] = array($copy_status['location'], $copy_status['callnum'], $locum_config['branches'][$copy_status['branch']], $status_msg);
    }
    else {
      $copy_status_array[] = array($copy_status['location'], $copy_status['callnum'], $status_msg);
    }
  }
}
?>

<!-- begin item record -->
<div class="itemrecord">

  <!-- begin left-hand column -->
  <div class="item-left">

    <!-- Cover Image -->
    <?php
    if (!module_exists('covercache')) {
      if (strpos($item['cover_img'], 'http://') !== FALSE) {
        $cover_img = $item['cover_img'];
      }
      else {
        $cover_img = base_path() . drupal_get_path('module', 'sopac') . '/images/nocover.png';
      }
      $cover_img = '<img class="item-cover" width="200" src="' . $cover_img . '">';
    }
    print $cover_img;
    ?>

    <!-- Ratings -->
    <?php
    if (variable_get('sopac_social_enable', 1)) {
      print '<div class="item-rating">';
      print theme_sopac_get_rating_stars($item['bnum']);
      print '</div>';
    }
    ?>

    <!-- Item Details -->
    <ul>
      <?php
      if ($item['pub_info']) {
        print '<li><b>Published:</b> ' . $item['pub_info'] . '</li>';
      }
      if ($item['pub_year']) {
        print '<li><b>Year Published:</b> ' . $item['pub_year'] . '</li>';
      }
      if ($item['series']) {
        print '<li><b>Series:</b> ' . l($item['series'], $url_prefix . '/search/series/' . urlencode($series)) . '</li>';
      }
      if ($item['edition']) {
        print '<li><b>Edition:</b> ' . $item['edition'] . '</li>';
      }
      if ($item['descr']) {
        print '<li><b>Description:</b> ' . nl2br($item['descr']) . '</li>';
      }
      if ($item['stdnum']) {
        print '<li><b>ISBN/Standard #:</b>' . $item['stdnum'] . '</li>';
      }
      if ($item['lang']) {
        print '<li><b>Language:</b> ' . $locum_config['languages'][$item['lang']] . '</li>';
      }
      if ($item['mat_code']) {
        print '<li><b>Format:</b> ' . $locum_config['formats'][$item['mat_code']] . '</li>';
      }
      ?>
    </ul>

    <!-- Additional Credits -->
    <?php
    if ($item['addl_author']) {
      print '<h3>Additional Credits</h3><ul>';
      $addl_author_arr = unserialize($item['addl_author']);
      foreach ($addl_author_arr as $addl_author) {
        $addl_author_link = $url_prefix . '/search/author/%22' . urlencode($addl_author) .'%22';
        print '<li>' . l($addl_author, $addl_author_link) . '</li>';
      }
      print '</ul>';
    }
    ?>

    <!-- Subject Headings -->
    <?php
    if ($item['subjects']) {
      print '<h3>Subjects</h3><ul>';
      $subj_arr = unserialize($item['subjects']);
      if (is_array($subj_arr)) {
        foreach ($subj_arr as $subj) {
          $subjurl = $url_prefix . '/search/subject/%22' . urlencode($subj) . '%22';
          print '<li>' . l($subj, $subjurl) . '</li>';
        }
      }
      print '</ul>';
    }
    ?>

    <!-- Tags -->
    <?php
    if (variable_get('sopac_social_enable', 1)) {
      print '<h3>Tags</h3>';
      $block = module_invoke('sopac','block','view', 4);
      print $block['content'];
    }
    ?>

  <!-- end left-hand column -->
  </div>


  <!-- begin right-hand column -->
  <div class="item-right">

    <!-- Supressed record notification -->
    <?php
      if ($item['active'] == '0') {
        print '<div class="suppressed">This Record is Suppressed</div>';
      }
    ?>

    <div class="item-main">
    <!-- Item Format Icon -->
    <ul class="item-format-icon">
      <li><img src="<?php print base_path() . drupal_get_path('module', 'sopac') . '/images/' . $item['mat_code'] . '.png' ?>"></li>
      <li style="margin-top: -2px;"><?php print wordwrap($locum_config['formats'][$item['mat_code']], 8, '<br />'); ?></li>
    </ul>

    <!-- Actions -->
    <ul class="item-actions">
      <?php
      if ($item_status['libuse'] > 0 && $item_status['libuse'] == $item_status['total']) { ?>
        <li class="button">Library Use Only</li>
      <?php } else if (in_array($item['loc_code'], $no_circ) || in_array($item['mat_code'], $no_circ)) { ?>
            <li class="button red">Not Requestable</li>
      <?php }
      else {
        print sopac_put_request_link($item['bnum'], 1, 0, $locum_config['formats'][$item['mat_code']]);
      }
      if ($user->uid) {
        include_once('sopac_user.php');
        print sopac_put_list_links($item['bnum']);
      }
      ?>
    </ul>

    <!-- Item Title -->
    <h1>
      <?php
      print ucwords($item['title']);
      if ($item['title_medium']) {
        print " $item[title_medium]";
      }
      ?>
    </h1>

    <!-- Item Author -->
    <?php
    if ($item['author']) {
      $authorurl = $url_prefix . '/search/author/' . $new_author_str;
      print '<h3>by ' . l($new_author_str, $authorurl) . '</h3>';
    }
    $avail_class = ($item_status['avail'] ? "request-avail" : "request-unavail");
    print '<p class="item-request ' . $avail_class . '">' . $reqtext . '</p>';
    ?>
    </div>

    <!-- Where to find it -->
    <div class="item-avail-disp">
      <h2>Where To Find It</h2>
      <?php
      if ($item_status['callnums']) {
        if (count($item_status['callnums']) > 10) {
          print '<p>Call number: <strong>' . $item['callnum'] . '</strong> (see all copies below for individual call numbers)</p>';
        } else {
          print '<p>Call number: <strong>' . implode(", ", array_keys($item_status['callnums'])) . '</strong></p>';
        }
      }

      if (count($item_status['items']) && !$no_avail_mat_codes) {
        if ($item_status['avail']) {
          // Build list of locations
          $locations = array();
          foreach ($item_status['items'] as $itemstat) {
            if ($itemstat['avail']) {
              $locations[$itemstat['loc_code']] = $itemstat['location'];
            }
          }
          $locations = implode(', ', $locations);

          print "<p>Available Copies: <strong>$locations</strong></p>";
        }

        print '<div><fieldset class="collapsible collapsed"><legend>Show All Copies (' . count($item_status['items']) . ')</legend><div>';
        if (variable_get('sopac_multi_branch_enable', 0)) {
          print theme('table', array("Location", "Call Number", "Branch", "Item Status"), $copy_status_array);
        }
        else {
          print theme('table', array("Location", "Call Number", "Item Status"), $copy_status_array);
        }
        print '</div></fieldset></div>';
      }
      elseif ($item['download_link']) {
        print '<div class="item-request">';
        print '<p>' . l(t('Download this Title'), $item['download_link'], array('attributes' => array('target' => '_new'))) . '</p>';
        print '</div>';
      }
      else {
        if (!$no_avail_mat_codes) {
          print '<p>No copies found.</p>';
        }
      }
      if (count($item_status['orders'])) {
        print '<p>' . implode("</p><p>", $item_status['orders']) . '</p>';
      }
      ?>
    </div>

    <!-- Notes / Additional Details -->
    <?php
    if (is_array($note_arr)) {
      print '<div id="item-notes">';
      print '<h2>Additional Details</h2>';
      foreach($note_arr as $note) {
        print '<p>' . $note . '</p>';
      }
      print '</div>';
    }
    ?>

    <!-- Syndetics / Review Links -->
    <?php
    if ($item['review_links']) {
      print '<div id="item-syndetics">';
      print '<h2>Reviews &amp; Summaries</h2>';
      print '<ul>';
      foreach ($item['review_links'] as $rev_title => $rev_link) {
        $rev_link = explode('?', $rev_link);
        print '<li>' . l($rev_title, $rev_link[0], array('query' => $rev_link[1], 'attributes' => array('target' => '_new'))) . '</li>';
      }
      print '</ul></div>';
    }
    ?>

    <!-- Community / SOPAC Reviews -->
    <div id="item-reviews">
      <h2>Community Reviews</h2>
      <?php
      if (count($rev_arr)) {
        foreach ($rev_arr as $rev_item) {
          print '<div class="hreview">';
          print '<h3 class="summary">' . l($rev_item['rev_title'], 'review/view/' . $rev_item['rev_id'], array('attributes' => array('class' => 'fn url'))) . '</h3>';
          if ($rev_item['uid']) {
            $rev_user = user_load(array('uid' => $rev_item['uid']));
            print '<p class="review-byline">submitted by <span class="review-author">' . l($rev_user->name, 'review/user/' . $rev_item['uid']) . ' on <abbr class="dtreviewed" title="' . date("c", $rev_item['timestamp']) . '">' . date("F j, Y, g:i a", $rev_item['timestamp']) . '</abbr></span>';
            if ($user->uid == $rev_item['uid']) {
              print ' &nbsp; [ ' .
                    l(t('delete'), 'review/delete/' . $rev_item['rev_id'], array('attributes' => array('title' => 'Delete this review'), 'query' => array('ref' => $_GET['q']))) .
                    ' ] [ ' .
                    l(t('edit'), 'review/edit/' . $rev_item['rev_id'], array('attributes' => array('title' => 'Edit this review'), 'query' => array('ref' => $_GET['q']))) .
                    ' ]';
            }
            print '</p>';
          }
          print '<div class="review-body description">' . nl2br($rev_item['rev_body']) . '</div></div>';
        }
      }
      else {
        print '<p>No reviews have been written yet.  You could be the first!</p>';
      }
      print $rev_form ? $rev_form : '<p>' . l(t('Login'), 'user/login', array('query' => array('destination' => $_GET['q']))) . ' to write a review of your own.</p>';
      ?>
    </div>

    <!-- Google Books Preview -->
    <div id="item-google-books">
      <div class="item-google-prev">
        <script type="text/javascript" src="http://books.google.com/books/previewlib.js"></script>
          <script type="text/javascript">
            var w=document.getElementById("item-google-books").offsetWidth;
            var h=(w*1.3);
            GBS_insertEmbeddedViewer('ISBN:<?php print $item['stdnum']; ?>',w,h);
          </script>
      </div>
    </div>

  <!-- end right-hand column -->
  </div>

<!-- end item record -->
</div>
