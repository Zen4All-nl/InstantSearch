# Instant Search v2.0.0 Beta 1
Just like googles instant search feature I’ve made one for zen cart. Instant search is a new search enhancement that shows results as you type. 

Tried and tested and works on all major browsers including smart phones. As of version 2.0.0 I only test/develop for Zen Cart &ge;1.5.6, and php &ge;7.2

This contribution is subject to version 2.0 of the GPL license, that is bundled with this package in the file LICENSE, and is available here.

## Support
These files are submitted for public distribution via the Zen Cart forum. It would be great if you can provide your feedback or support via [the support thread](https://www.zen-cart.com/showthread.php?189289-Instant-Search) at the Zen Cart forum where it can benefit all users of this add-on module.

## How It Works
 * When the page loads up jscript_instantSearch.js adds a keyup listeners to the search boxes.
 * When the user starts to key in a few letters jscript_instantSearch.js sends the data to zcAjaxInstantsearch.php.
 * zcAjaxInstantsearch.php uses sql to gather matching search results.
 * zcAjaxInstantsearch.php sends these results back to jscript_instantSearch.js.
 * jscript_instantSearch.js then creates the instant search box with the results on the web page.

 * Instant search uses the jQuery JavaScript Library.
 * You can change the style and layout of the instant search via stylesheet_instantSearch.css.
 
## Changes
since v1.0.2
- converted to the native Zen Cart Ajax function
- added some back-end configuration values
- added module version control
- converted code to latest Zen Cart standards
