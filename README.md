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
bv_root_folder |  None | 1234-en_us | Yes | For PRR customers the root folder is the display code. For Conversations customers unique root folders are created for each deployment zone and locale. |
subject_id |  None | test1 | Yes | The subject ID needs to match the product ID you reference in your product data feed and use to power your display of UGC.|
cloud_key |  None | 2b1d0e3b86ffa60cb2079dea11135c1e | Yes | Will be provided by your Bazaarvoice team.  |
testing |  FALSE | TRUE or FALSE | No | If set `TRUE` the SDK will pull SEO content from QA server rather than production. |
staging |  FALSE | TRUE or FALSE | No | If set `TRUE` the SDK will pull SEO content from staging rather than production. |
execution_timeout | 500 | 300 | No | Integer in ms. Period of time before the BVSEO injection times out for user agents that do not match the criteria set in `crawler_agent_pattern`. |
execution_timeout_bot | 2000 | 1000 | No | Integer in ms. Period of time before the BVSEO injection times out for user agents that match the criteria set in `crawler_agent_pattern`. |
base_url | Current page using $_SERVER |  http://www.example.com/pdp/test1 | No | If a base URL is not provided, the current page URL will be used instead. |
page_url | None |  http://www.example.com/pdp/test1?bvstate=pg:2/ct:r' | No | Provide the URL if using query parameters or fragments in your URLs that Google should not index. |
content_type | reviews | reviews, questions, stories, spotlights | No | Provide the content type here if needed. |
subject_type | product | product, category, entry, detail | No | Provide the subject type here if needed. |
content_sub_type | stories_list | stories_list, stories_grid | No | If `content_type` is set to `stories` then pass either `stories_list` or `stories_grid` as the content subtype. |
crawler_agent_pattern | msnbot, googlebot, teoma, bingbot, yandexbot, yahoo | `msnbot|google` | Provide a regular expression to check the user agent header value. This is used to determine whether or not the current request is made by a search engine crawler. |
include_display_integration_code |  FALSE | TRUE or FALSE | No | Set `TRUE` to include the Javascript that powers the display. The `bvapi.js` file must be included separately. |
local_seo_file_root |  None | '/home/zip/smart_seo/' | No | If using a local file configuration, provide the absolute path to the unzipped directory of Smart SEO content. |
load_seo_file_locally |  FALSE | TRUE or FALSE | No | Set `TRUE` to load content from the local directory specified in `local_seo_file_root`. Local file configurations are not recommended, but may be required to overcome system limitations. A local file system can be be fragile since Bazaarvoice is not responsible for the daily retrieval, unpacking, and distribution of SEO files. To enable local files, both `load_seo_file_locally` and `local_seo_file_root` must be set.  |

To run unit tests you should first install the [phpunit](https://phpunit.de/getting-started.html) tool. Then in the folder where the bvseosdk.php file is placed run `phpunit test` to execute all of the tests or `phpunit test/<testName>` to run a particular test.
