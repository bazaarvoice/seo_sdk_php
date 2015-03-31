<?php
//Please provide cloud_key, bv_root_folder and product_id
require('bvseosdk.php');

$bv = new BV(array(
    'deployment_zone_id' => '',
    'product_id' => '',
    'cloud_key' => '',
    'current_page_url' => '',
        ));
?><!DOCTYPE html>
<html>
    <head>
        <title>BV SDK PHP Example - GetContent</title>
    </head>
    <body>
        This is a test page for Spotlights: getContent() <br>
        GetContent() will return spotlights content <br><br>

        <div id="BVRRContainer">
            <?php echo $bv->spotlights->getContent(); ?>
            <?php //echo $bv->spotlights->getAggregateRating(); ?>
            <?php //echo $bv->spotlights->getReviews();?>
        </div>
    </body>
</html>
