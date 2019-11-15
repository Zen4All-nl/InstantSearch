<?php

/**
 * @package Instant Search Results
 * @copyright Copyright Ayoob G 2009-2011
 * @copyright Portions Copyright 2003-2006 The Zen Cart Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 */

class zcAjaxInstantSearch extends base {

  public function getSearchResults()
  {
    global $db;

//this gets the word we are searching for. Usually from jscrip_instantSearch.js.
    $wordSearch = $_POST['query'];

// we place or results into these arrays
//$results will hold data that has the search term in the begining of the word. This will yield a better search result but the number of results will be a few.
//$resultsAddAfter will hold data that has the search term anywhere in the word. This will yield a normal search result but the number of results will be a high.
//$results has first priority over $resultsAddAfter
    $results = array();
    $resultsAddAfter = array();
    $prodResult = '';

//On-TRUE Off-FALSE switches for items within the search box
    $ProdCount = (INSTANT_SEARCH_PRODUCT_COUNT == 0 ? false : true);
    $CatCount = (INSTANT_SEARCH_CATEGORY_COUNT == 0 ? false : true);
    $sqlLimit = (int)INSTANT_SEARCH_RESULT_LIMIT;
    if (strlen($wordSearch) > 0) {

      //if the user enters less than 2 characters we would like match search results that beging with these characters
      //if the characters are greater than 2 then we would like to broaden our search results
      if (strlen($wordSearch) <= 2) {
        $wordSearchPlus = $wordSearch . "%";
      } else {
        $wordSearchPlus = "%" . $wordSearch . "%";
      }

      //first we would like to search for products that match our search word
      //we then order the search results with respect to the keyword found at the begining of each of the results
      $sqlProduct = "SELECT p.products_id, p.products_status, p.products_quantity,
                            pd.products_name
                     FROM " . TABLE_PRODUCTS . " p
                     LEFT JOIN " . TABLE_PRODUCTS_DESCRIPTION . " pd ON pd.products_id = p.products_id
                       AND pd.language_id = " . (int)$_SESSION['languages_id'] . "
                     WHERE p.products_status <> 0
                     AND ((products_name LIKE :wordSearchPlus:)
                       OR (LEFT(pd.products_name,LENGTH(:wordSearch:)) SOUNDS LIKE :wordSearch:)
                     )
                     ORDER BY field(LEFT(pd.products_name,LENGTH(:wordSearch:)), :wordSearch:) DESC, pd.products_viewed DESC
                     LIMIT " . $sqlLimit;

      //this protects use from sql injection - i think????
      $sqlProduct = $db->bindVars($sqlProduct, ':wordSearch:', $wordSearch, 'string');
      $sqlProduct = $db->bindVars($sqlProduct, ':wordSearchPlus:', $wordSearchPlus, 'string');

      $dbProducts = $db->Execute($sqlProduct);

      //this takes each item that was found in the results and places it into 2 separate arrays
      if ($dbProducts->RecordCount() > 0) {
        foreach ($dbProducts as $dbProduct) {
          $prodquantity = ($ProdCount) ? $dbProduct['products_quantity'] : '';

          $prodResult = strip_tags($dbProduct['products_name']);
          $prodInfo = zen_get_info_page($dbProduct['products_id']);
if($prodResult !=''){
          if (strtolower(substr($prodResult, 0, strlen($wordSearch))) == strtolower($wordSearch)) {
            $results[] = array(
              //we have 5 seperate variables that will be passed on to instantSearch.js
              //'q' is the result thats been found
              //'c' is the number of item within a category search (we leave this empty for product search, look at the example bellow for category search)
              //'l' is used for creating a link to the product or category
              //'pt' is used to get the product info page by product type using type_handler + '_info'
              //'pc' lets us know if the word found is a product or a category
              'q' => $prodResult,
              'c' => $prodquantity,
              'l' => $dbProduct['products_id'],
              'pt' => $prodInfo,
              'pc' => "p"
            );
          } else {
            $resultsAddAfter[] = array(
              'q' => $prodResult,
              'c' => $prodquantity,
              'l' => $dbProduct['products_id'],
              'pt' => $prodInfo,
              'pc' => "p"
            );
          }
        }}
      }

      //similar to product search but now we search witin categories
      $sqlCategories = "SELECT c.categories_id, c.categories_status,
                               cd.categories_name
                        FROM " . TABLE_CATEGORIES . " c
                        LEFT JOIN " . TABLE_CATEGORIES_DESCRIPTION . " cd ON cd.categories_id = c.categories_id
                          AND cd.language_id = " . (int)$_SESSION['languages_id'] . "
                        WHERE c.categories_status <> 0
                        AND (cd.categories_name LIKE :wordSearchPlus:)
                        ORDER BY field(LEFT(cd.categories_name,LENGTH(:wordSearch:)), :wordSearch:) DESC
                        LIMIT " . $sqlLimit;

      $sqlCategories = $db->bindVars($sqlCategories, ':wordSearch:', $wordSearch, 'string');
      $sqlCategories = $db->bindVars($sqlCategories, ':wordSearchPlus:', $wordSearchPlus, 'string');

      $dbCategories = $db->Execute($sqlCategories);

      if ($dbCategories->RecordCount() > 0) {
        foreach ($dbCategories as $dbCategory) {
          //this searches for the number of products within a category
          $products_count = ($CatCount) ? zen_count_products_in_category($dbCategory['categories_id']) : '';
          $prodResult = strip_tags($dbCategory['categories_name']);
          if($prodResult !=''){
          if (strtolower(substr($prodResult, 0, strlen($wordSearch))) == strtolower($wordSearch)) {
            $results[] = array(
              'q' => $prodResult,
              'c' => $products_count,
              'l' => $dbCategory['categories_id'],
              'pc' => 'c'
            );
          } else {
            $resultsAddAfter[] = array(
              'q' => $prodResult,
              'c' => $products_count,
              'l' => $dbCategory['categories_id'],
              'pc' => 'c'
            );
          }
        }}
      }

      //similar to categories search but now we search witin manufacturers
      $sqlManuf = "SELECT p.products_status, p.manufacturers_id,
                          m.manufacturers_name
                   FROM " . TABLE_PRODUCTS . " p,
                        " . TABLE_MANUFACTURERS . " m
                   WHERE m.manufacturers_id = p.manufacturers_id
                   AND p.products_status <> 0
                   AND (manufacturers_name LIKE :wordSearchPlus:)
                   ORDER BY field(LEFT(m.manufacturers_name,LENGTH(:wordSearch:)), :wordSearch:) DESC
                   LIMIT " . $sqlLimit;

      //this protects use from sql injection - i think????
      $sqlManuf = $db->bindVars($sqlManuf, ':wordSearch:', $wordSearch, 'string');
      $sqlManuf = $db->bindVars($sqlManuf, ':wordSearchPlus:', $wordSearchPlus, 'string');

      $dbManuf = $db->Execute($sqlManuf);

      //this takes each item that was found in the results and places it into 2 separate arrays
      if ($dbManuf->RecordCount() > 0) {
        //this searches for the number of products with same manufacturer ID
        $Manuf_count = ($CatCount) ? zen_count_products_for_manufacturer($dbManuf->fields['manufacturers_id']) : "";
        $ManufResult = strip_tags($dbManuf->fields['manufacturers_name']);
if($ManufResult !=''){
        if (strtolower(substr($ManufResult, 0, strlen($wordSearch))) == strtolower($wordSearch)) {
          $results[] = array(
            'q' => $ManufResult,
            'c' => $Manuf_count,
            'l' => $dbManuf->fields['manufacturers_id'],
            'pc' => 'm'
          );
        } else {
          $resultsAddAfter[] = array(
            'q' => $ManufResult,
            'c' => $Manuf_count,
            'l' => $dbManuf->fields['manufacturers_id'],
            'pc' => 'm'
          );
        }
      }}
    }


//we now re-sort the results so that $results has first priority over $resultsAddAfter
//if pt is null we need to not sort it or it will kill the link.
    foreach ($resultsAddAfter as &$value) {
      if ($value["pt"] == '') {
        $results[] = array(
          'q' => $value["q"],
          'c' => $value["c"],
          'l' => $value["l"],
          'pc' => $value["pc"]
        );
      } else {
        $results[] = array(
          'q' => $value["q"],
          'c' => $value["c"],
          'l' => $value["l"],
          'pt' => $value["pt"],
          'pc' => $value["pc"]
        );
      }
    }

    unset($value);

//the results are now passed onto instantSearch.js
    return([
      'results' => $results
    ]);
  }

}
