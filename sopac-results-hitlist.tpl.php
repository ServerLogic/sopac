<?php
/*
 * Theme template for SOPAC hitlist
 *
 */

// Prep some stuff here
$new_author_str = sopac_author_format($locum_result['author'], $locum_result['addl_author']);
$url_prefix = variable_get('sopac_url_prefix', 'cat/seek');
global $user;
if (!module_exists('covercache')) {
  if (strpos($locum_result['cover_img'], 'http://') !== FALSE) {
    $cover_img = $locum_result['cover_img'];
  }
  else {
    $cover_img = base_path() . drupal_get_path('module', 'sopac') . '/images/nocover.png';
  }
  $cover_img = '<img width="100" src="' . $cover_img . '">';
  $cover_img = l($cover_img,
                 variable_get('sopac_url_prefix', 'cat/seek') . '/record/' . $locum_result['bnum'],
                 array('html' => TRUE, 'alias' => TRUE));
}
$list_display = strpos($locum_result['namespace'], 'list') !== FALSE;

// Get Zoom Lends copies
$zooms_avail = $locum_result['status']['callnums']['Zoom Lends DVD']['avail'] + $locum_result['status']['callnums']['Zoom Lends Book']['avail'];
$avail = $locum_result['status']['avail'] - $zooms_avail;

if ($avail > 0) {
  $availtext = 'There ' . ($avail == 1 ? 'is' : 'are') . " currently $avail available";
}
else {
  $availtext = 'There are no copies available';
}
if ($zooms_avail > 0) {
//  $zoom_text = l('Zoom Lends', 'catalog/browse/unusual', array('alias' => TRUE, 'fragment' => 'ZOOM', 'query' => array('lightbox' => 1), 'attributes' => array('rel' => 'lightframe')));
  $zoom_text = 'Zoom Lends';
  $availtext .= " ($zooms_avail $zoom_text " . ' available)';
}
if ($locum_result['status']['avail']) {
  $availtext .= ":";
}
if ($locum_result['status']['holds'] > 0) {
  $reqtext = $locum_result['status']['holds'] . ' request' . ($locum_result['status']['holds'] == 1 ? '' : 's') . " on " . $locum_result['status']['total'] . ' ' . ($locum_result['status']['total'] == 1 ? 'copy' : 'copies');
}

?>
  <tr class="hitlist-item <?php if($locum_result['status']['avail']) print "available"; ?>">
    <td class="hitlist-number"><?php print $result_num; ?></td>
    <td class="hitlist-cover">
      <?php print $cover_img; ?>
    </td>
    <td class="hitlist-info">
      <?php
        if ($locum_result['active'] == '0') {
          print '<div class="suppressed">This Record is Suppressed</div>';
        }
      ?>
      <ul>
        <li class="hitlist-title">
          <strong><?php print l(ucwords($locum_result['title']), $url_prefix . '/record/' . $locum_result['bnum'],array('alias' => TRUE)); ?></strong>
          <?php
          if ($locum_result['title_medium']) {
            print "[$locum_result[title_medium]]";
          }
          ?>
        </li>
        <li>
        <?php
          print l($new_author_str, $url_prefix . '/search/author/' . urlencode($new_author_str),array('alias' => TRUE));
        ?>
        </li>
        <li><?php print $locum_result['pub_info']; ?></li>
        <?php if ($locum_result['callnum']) {
          ?><li><?php print t('Call number: '); ?><strong><?php print l($locum_result['callnum'], $url_prefix . '/search/callnum/"' . urlencode($locum_result['callnum']) .'"',array('alias' => TRUE)); ?></strong></li><?php
        }
        elseif (count($locum_result['avail_details'])) {
          ?><li><?php print t('Call number: '); ?><strong><?php print key($locum_result['avail_details']); ?></strong></li><?php
        } ?>
        <?php if ($locum_result['sort'] == 'catalog_newest') { ?>
        <li><strong>Added on <?php echo date('m-d-Y', strtotime($locum_result['bib_created'])); ?></strong></li>
        <?php } ?>
        <?php if ($list_display) { ?>
        <li><strong>Added to list</strong> on
        <?php
          // Don't display timestamp if it's exactly midnight (Checkout History)
          echo str_replace(', 12:00 am', '', date("F j, Y, g:i a", strtotime($locum_result['tag_date'])));
        ?>
        </li>
        <?php } ?>
        <ul class="hitlist-avail">
          <li class="hitlist-subtitle"><?php print $availtext; ?></li>
          <?php
            if ($locum_result['status']['avail']) {
              // Build list of locations
              $locations = array();
              foreach ($locum_result['status']['items'] as $item) {
                if ($item['avail']) {
                  $locations[$item['loc_code']] = $item['location'];
                }
              }
              print '<li>' . implode(', ', $locations) . '</li>';
            }
            if ($reqtext) {
              print '<li class="hitlist-subtitle">' . $reqtext . '</li>';
            }
          ?>
        </ul>
    <?php
      if ($locum_result['review_links']) {
        print '<li class="button hassub">Reviews &amp; Summaries (' .
              count($locum_result['review_links']) . ')<ul class="submenu" id="rev_' . $locum_result['bnum'] . '">';
        foreach ($locum_result['review_links'] as $rev_title => $rev_link) {
          $rev_link = explode('?', $rev_link);
          print '<li>' . l($rev_title, $rev_link[0], array('query' => $rev_link[1], 'attributes' => array('html' => TRUE, 'target' => "_new", 'alias' => TRUE))) . '</li>';
        }
        print '</ul><span></span></li>';
      }
    ?>
    </ul>
    </td>
    <td class="hitlist-actions">
      <ul>
        <?php
          if ($locum_result['status']['libuse'] > 0 && $locum_result['status']['libuse'] == $locum_result['status']['total']) { ?>
            <li class="button">Library Use Only</li>
        <?php } else if (in_array($locum_result['loc_code'], $no_circ) || in_array($locum_result['mat_code'], $no_circ)) { ?>
            <li class="button red">Not Requestable</li>
        <?php }
          else {
            print sopac_put_request_link($locum_result['bnum'],
                                         $locum_result['status']['avail'],
                                         $locum_result['status']['holds'],
                                         $locum_config['formats'][$locum_result['mat_code']]);
          }
          if ($user->uid) {
            include_once('sopac_user.php');
            print sopac_put_list_links($locum_result['bnum'], $list_display);
          }
          if ($list_display && $locum_result['uid'] == $user->uid) {
            // PART OF A LIST, SHOW ADDITIONAL ACTIONS
            $list_id = intval(str_replace('list', '', $locum_result['namespace']));
            $value = $locum_result['value'];
            $bnum = $locum_result['bnum'];
            if (!$locum_result['freeze']) {
              print '<li class="button green">' . l('Move to Top of List', "user/listmovetop/$list_id/$value", array('alias' => TRUE)) . '</li>';
            }
            print '<li class="button red">' . l('Remove from List', "user/listdelete/$list_id/$bnum", array('alias' => TRUE)) . '</li>';
          }
        ?>
      </ul>
    </td>
    <td class="hitlist-format-icon">
      <img src="<?php print base_path() . drupal_get_path('module', 'sopac') . '/images/' . $locum_result['mat_code'] . '.png' ?>">
      <br />
      <?php print wordwrap($locum_config['formats'][$locum_result['mat_code']], 8, '<br />'); ?>
    </td>
  </tr>
