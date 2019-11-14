<?php

/**
 * @package Instant Search Results
 * @copyright Copyright Ayoob G 2009-2011
 * @copyright Portions Copyright 2003-2006 The Zen Cart Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: searches.php 6 2019-08-01 18:34:47Z davewest $
 */


/**
 * 
 * @global type $db
 * @param integer $manufacturers_id
 * @param boolean $include_inactive
 * @return integer Return the number of products for manufacturer
 */
function zen_count_products_for_manufacturer($manufacturers_id, $include_inactive = false)
{
  global $db;
  $products_count = 0;
  if ($include_inactive == true) {
    $products_query = "select count(products_id) as total
                       from " . TABLE_PRODUCTS . "
                       where manufacturers_id = '" . (int)$manufacturers_id . "'";
  } else {
    $products_query = "select count(products_id) as total
                       from " . TABLE_PRODUCTS . "
                       where  manufacturers_id = '" . (int)$manufacturers_id . "'
                       and products_status = '1'";
  }
  $products = $db->Execute($products_query);
  $products_count += $products->fields['total'];


  return $products_count;
}

?>
