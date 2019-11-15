<?php

/**
 * 2_0_0.php
 *
 * @copyright Copyright 2003-2018 Zen Cart Development Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version Author: Zen4All
 */

if (!zen_page_key_exists('configInstantSearch') && (int)$configuration_group_id > 0) {
  zen_register_admin_page('configInstantSearch', 'BOX_CONFIGURATION_INSTANT_SEARCH', 'FILENAME_CONFIGURATION', 'gID=' . $configuration_group_id, 'configuration', 'Y', $configuration_group_id);
}

$db->Execute("INSERT INTO " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added, set_function)
              VALUES ('Product Count', 'INSTANT_SEARCH_PRODUCT_COUNT', '0', 'Show the number of products found', " . $configuration_group_id . ", 1, now(), 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'false\'), array(\'id\'=>\'1\', \'text\'=>\'true\')),'),
                     ('Category Count', 'INSTANT_SEARCH_CATEGORY_COUNT', '1', 'Show the number of products found in a category', " . $configuration_group_id . ", 2, now(), 'zen_cfg_select_drop_down(array(array(\'id\'=>\'0\', \'text\'=>\'false\'), array(\'id\'=>\'1\', \'text\'=>\'true\')),'),
                     ('Number of results', 'INSTANT_SEARCH_RESULT_LIMIT', '4', 'How many results should be shown in the drop-down. This number is for each Product, Category, and manufacture result. So if set to 4 a maximum of 12 items will be shown in the drop-down\r\nToo long and its useless\r\nDefault: 4', " . $configuration_group_id . ", 3, now(), NULL);");