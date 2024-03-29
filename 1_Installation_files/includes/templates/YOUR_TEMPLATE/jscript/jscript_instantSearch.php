<?php
/**
 * @package Instant Search Results
 * @copyright Copyright Ayoob G 2009-2011
 * @copyright Portions Copyright 2003-2006 The Zen Cart Team
 * @copyright Portions Copyright 2003 osCommerce
 * @license http://www.zen-cart.com/license/2_0.txt GNU Public License V2.0
 * @version $Id: jscript_instantSearch.js 6 2019-08-01 18:34:47Z davewest $
 */
//This jScript file is used to create our instant search box
?>
<script type="text/javascript">
//these var's will be used to maintain multiple request
  let runningRequest = false;
  var request;

//if you want to manually position the result box you can set autoPosition to false
//but make sure to provide the top and left value in instantSearch.css
  const autoPosition = true;


  let inputboxCurrent;

//checks to see if the document has loaded and is ready
  $(document).ready(function () {


    //this will apply the instant search feature to all the search boxes
    const inputBox = $('input[name="keyword"]');

    //if you want to add instant search to only a specific box then comment out the var inputBox above
    //and uncomment out the specific search box selector bellow:

    //var inputBox = $('#navMainSearch > form[name="quick_find_header"] > input[name="keyword"]');
    //var inputBox = $('#navColumnTwoWrapper > form[name="quick_find_header"] > input[name="keyword"]');
    //var inputBox = $('#searchContent > form[name="quick_find"] > input[name="keyword"]');


    //this adds a instant search container bellow the search box
    inputBox.before('<div id="resultsContainer"></div>');
    inputBox.attr('autocomplete', 'off');

    //re-position all the instant search container correctly into their places
    if (autoPosition == true) {
      inputBox.each(function (index) {
        const offset = $(this).offset();
        $(this).prev().css("left", offset.left + "px");
        $(this).prev().css("top", ($(this).outerHeight(true) + offset.top) + "px");
      });
    }


    //if the search box losses focus, then the instant search container will be hidden
    inputBox.blur(function () {
      if (inputboxCurrent) {
        const resultsContainer = inputboxCurrent.prev();
        resultsContainer.delay(300).slideUp(200);
      }
    });


    //if we resize the browser or zoom in or out of a page then the instant search container will be hidden
    $(window).resize(function () {
      if (inputboxCurrent) {
        const resultsContainer = inputboxCurrent.prev();
        resultsContainer.hide();
      }
    });


    //the user starts to enter a few characters into the search box
    inputBox.keyup(function () {

      //only the currently selected search box will be used
      inputboxCurrent = $(this);

      //assign a variable to the instant search container
      const resultsContainer = $(this).prev();

      //we capture the words that are being typed into the search box
      let searchWord = $(this).val();
      const replaceWord = searchWord;

      //we clean up the word for any unnecessary characters or double spaces
      searchWord = searchWord.replace(/^\s+/, "");
      searchWord = searchWord.replace(/  +/g, ' ');


      if (searchWord == "") {

        //if the search value entered is empty, we then hide the instant search container	
        resultsContainer.hide();

      } else {

        //if multiple requests are sent to the server, we then abort any previous request, before a new request is sent
        //this only comes in use if user is a fast typer
        if (runningRequest) {
          request.abort();
        }

        runningRequest = true;

        //we then pass on the search word to searches.php
        //searches.php will then look for all the search results 

        zcJS.ajax({
          url: 'ajax.php?act=ajaxInstantSearch&method=getSearchResults',
          data: {
            'query': searchWord
          }
        }).done(function (data) {
          if (data.length > 0) {
            let resultHtml = '';
            $.each(data, function (i, item) {
              //if any search result are found, a link will be created and placed into the instant search container
              resultHtml += '<li><a href="' + item.uri + '"><span class="alignRight">' + formatNumber(item.c) + '</span>' + highlightWord(replaceWord, item.q) + '</a></li>';
            });

            //added for the more link at the bottom uncomment if like it
            // resultHtml += '<li><a href="index.php?main_page=advanced_search_result&search_in_description=1&keyword=' + replaceWord + '"><span class="alignRight searchMore">More result...</span></a></li>';

            //fill the container with the matching products and categories
            resultsContainer.html('<ul>' + resultHtml + '</ul>');

            if (!resultsContainer.is(':visible')) {

              //auto position container if needs be
              if (autoPosition == true) {
                autoPositionContainer(inputboxCurrent, resultsContainer);
              }

              //drop down instant search box
              resultsContainer.slideDown(200);
            }

          } else {
            resultsContainer.hide();

          }
          runningRequest = false;
        });
      }
    });
  });

//this function auto positions the container
  function autoPositionContainer(inputBoxCurr, resltsContainer) {
    const offsetInput = inputBoxCurr.offset();
    const overFlow = offsetInput.left + resltsContainer.outerWidth(true);
    const winWidth = $(document).width();

    if (overFlow > winWidth) { // this checks to see if the container overflows on the right of the window
      const dif = overFlow - winWidth;

      if ((offsetInput.left - dif) < 0) {// this checks to see if the container overflows on the left of the window
        resltsContainer.css("left", 0 + "px");
      } else {
        resltsContainer.css("left", (offsetInput.left - dif) + "px");
      }
    } else {
      resltsContainer.css("left", offsetInput.left + "px");
    }
    resltsContainer.css("top", (inputBoxCurr.outerHeight(true) + offsetInput.top) + "px");
  }

  function highlightWord(findTxt, replaceTxt) {
    const f = findTxt.toLowerCase();
    const r = replaceTxt.toLowerCase();
    const regex = new RegExp('(' + f + ')', 'i');
    return r.replace(regex, '<span class="thinFont">' + f + '</span>');

  }

  function formatNumber(num) {
    return num.toString().replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");

  }
</script>