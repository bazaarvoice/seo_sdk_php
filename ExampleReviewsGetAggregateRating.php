<?php
//Please provide cloud_key, bv_root_folder and subject_id
require('src/bvseosdk.php');
$bv = new BazaarvoiceSeo\BV(array(
  'bv_root_folder' => '',
  'subject_id' => '',
  'cloud_key' => '',
  'page_url' => ''
));
?><!DOCTYPE html>
<html>
  <head>
    <title>BV SDK PHP Example - getAggregateRating</title>
  </head>
  <body>
    This is a test page for Reviews: getAggregateRating()<br>
    This will return aggregate rating content<br><br>
    <div id="BVRRSummaryContainer">
      <?php echo $bv->reviews->getAggregateRating(); ?>
    </div>
  </body>
</html>
