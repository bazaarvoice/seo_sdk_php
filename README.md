### PHP SDK

The PHP SDK has the following requirements:
* PHP 5+
* curl library installed
* mbstring library installed


Follow these steps to use the PHP SDK:

1. Include the bvseosdk.php file:  	
    
    ```php
    require('bvseosdk.php');
    ```
	
2. Instantiate the bv object.
    ```php
    $bv = new BV(array(
         'bv_root_folder' => 'Main_Site-en_US',
         'subject_id' => 'product1', // must match ExternalID in the BV product feed
         'cloud_key' => 'agileville-78B2EF7DE83644CAB5F8C72F2D8C8491', // Get from the config hub. On the left panel, click "Technical Setup" > "SEO Configuration." The value will be in the "Cloud Key" field.
         'staging' => TRUE
    ));
    ```

3. Call `$bv->reviews->getContent()` to grab the product's review SEO content.  This call will return the SEO HTML as a string. This string needs to be rendered on the product page inside the `<div id="BVRRContainer"></div>`. For example: 
    ```php
    <div id="BVRRContainer">
        <?php echo $bv->reviews->getContent();?>
    </div>
    ```
4. To test this you will need to modify your HTTP user agent string to match that of a search engine. Or for testing convenience, you can add the query parameter `?bvreveal=bot` to trigger the SDK to return SEO content. `?bvreveal=debug` will also display additional debug comments in the HTML markup.

    Here is a full list of the parameters you can pass into BV class we instantiated in step 2 above


Parameter Name | Default value | Example Value(s) | Required | Notes
------------ | ------------- | ------------ | ------------ | ------------
bv_root_folder |  None | 1234-en_us | Yes | Sometimes this is also referred to as your display code. |
subject_id |  None | test1 | Yes | The subject ID needs to match the product ID you reference in your product data feed and use to power your display of UGC.|
cloud_key |  None | 2b1d0e3b86ffa60cb2079dea11135c1e | Yes | Will be provided by your Bazaarvoice team.  |
staging |  TRUE | TRUE or FALSE | No | Toggle if the SDK should pull SEO content from staging or production. |
execution_timeout | 500 | 300 | No | Integer in ms. Period of time before the BVSEO injection times out for user agents that do not match the criteria set in CRAWLER_AGENT_PATTERN. |
execution_timeout_bot | 2000 | 1000 | No | Integer in ms. Period of time before the BVSEO injection times out for user agents that match the criteria set in CRAWLER_AGENT_PATTERN. |
base_url | Current page using $_SERVER |  http://www.example.com/pdp/test1 | No | If a base URL is not provided, the current page URL will be used instead. |
page_url | None |  http://www.example.com/pdp/test1?bvstate=pg:2/ct:r' | No | You will want to provide the URL if you use query parameters or # in your URLs that you don't want Google to index. |
content_type | reviews | reviews, questions, stories, spotlights | No | You can pass content type here if needed. |
subject_type | product | product, category, entry, detail | No | You can pass subject type here if needed. |
content_sub_type | stories_list | stories_list, stories_grid | No | For stories you can pass either STORIES_LIST or STORIES_GRID content type. |
crawler_agent_pattern | msnbot, googlebot, teoma, bingbot, yandexbot, yahoo | No | Any regex valid expression | Regular expression used to determine whether or not the current request is a bot (checking against user agent header) |
include_display_integration_code |  FALSE | TRUE or FALSE | No | If you want the SDK to also include the JavaScript to power display as well.  You will need to include the bvapi.js file seperately.  |
local_seo_file_root |  None | '/home/zip/smart_seo/ | No | Local file configurations are not recommended, but may be required to overcome system limitations. If you want the SDK to also include the JavaScript to power display as well.  You will need to include the bvapi.js file seperately.  |
load_seo_file_locally |  FALSE | TRUE or FALSE | No | You will want load content from LOCAL_SEO_FILE_ROOT. Local file configurations are not recommended, but may be required to overcome system limitations. A local file system can be be fragile since Bazaarvoice is not responsible for the daily retrieval, unpacking, and distribution of SEO files. To enable local files, LOAD_SEO_FILES_LOCALLY and LOCAL_SEO_FILE_ROOT must be set.  |

To run unit tests you should first install the [phpunit](https://phpunit.de/getting-started.html) tool. Then in the folder where the bvseosdk.php file is placed run `phpunit test` to execute all of the tests or `phpunit test/<testName>` to run a particular test.
