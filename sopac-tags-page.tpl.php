<?php
/**
 * SOPAC is The Social OPAC: a Drupal module that serves as a wholly integrated web OPAC for the Drupal CMS
 * This file contains the Drupal include functions for all the SOPAC admin pieces and configuration options
 * This file is called via hook_user
 *
 * @package SOPAC
 * @version 2.1
 * @author John Blyberg
 */
?>

<table class="tags-table" width="100%">
<tr><th></th><th>Tag</th><th># of Items</th><th></th></tr>
<tr><td style="padding-bottom: 15px;"></td></tr>

<?php
foreach ($tags_arr as $tag_key => $tag_bundle) {
  $num_tags = count($tag_bundle);
  print '<tr><td class="big-tag-label" rowspan="' . $num_tags . '">' . strtoupper($tag_key) . '</td>';
  $table_prefix = '';
  foreach ($tag_bundle as $tag => $tag_count) {
    $count = ($tag_count > 1) ? $tag_count . ' items' : $tag_count . ' item';
    print $table_prefix . '<td>' . l($tag, 'user/tag/show/' . urlencode($tag)) . '</td><td>' . $count . '</td>';
    print '<td id="nowrap">[ ' .
          l(t('Edit'), 'user/tag/edit/' . urlencode($tag), array('query' => array('ref' => $_GET['q']))) .
          ' ] [ ' .
          l(t('Delete'), 'user/tag/delete/' . urlencode($tag), array('query' => array('ref' => $_GET['q']))) .
          ' ]</td></tr>';
    $table_prefix = '<tr>';
  }
  $table_prefix = '';
}

?>

</table>