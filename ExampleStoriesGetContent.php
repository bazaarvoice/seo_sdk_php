<?php
//Please provide cloud_key, deployment_zone_id and product_id
require('bvseosdk.php');
    $bv = new BV(array(
    'deployment_zone_id' => '',
    'product_id' => '',
    'cloud_key' => '',
    'content_sub_type' => 'stories_list', // either STORIES_LIST or STORIES_GRID
    'staging' => TRUE
    ));
?>

<html>
<head>
    <title>BV SDK PHP Example - GetContent</title>
</head>
<body>
    This is a test page for Stories: getContent() <br>
    GetContent() will return stories_grid content <br><br>

    <div id="BVRRContainer">
      <?php echo $bv->stories->getContent();?>
    </div>
</body>
</html>
