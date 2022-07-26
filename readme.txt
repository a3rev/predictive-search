=== Predictive Search ===

Contributors: a3rev, nguyencongtuan
Tags: WordPress search, Predictive Search, Live Search, Elementor Search
Requires at least: 5.6
Tested up to: 6.1
Stable tag: 1.2.0
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

Predictive Search for WordPress gives your site visitors an awesome search experience delivering stunning 'live' search results.

== DESCRIPTION ==

WP Predictive Search engine delivers users instant and changing results as they type in the dropdown Users can click through from the dropdown to the 'All Results Search page' that features endless scroll.

= KEY FEATURES =

* Search objects include Post name, Page name, Post Categories and Post Tags
* Works with any WordPress Theme, Classic or Blockor Page Builder.
* Add Search Box via, Widget, Shortcode, PHP tag or Block.
* Fully compatible with Full Site Editor.
* Predictive Search engine delivers 100% accurate results, 100% of the time.
* Super-fast results even on sites with 1,000's of posts and pages
* Results show in search box dropdown as soon as you start to type.
* Full in plugin search box and search results dropdown style and layout options
* Click through to see all search results on a designated page (features endless scroll)
* Pretty URL for the All Search Results pages.

= DOCUMENTATION =

We have published complete and comprehensive Predictive Search documentation. [Visit the docs site.](https://docs.a3rev.com/wordpress/predictive-search/)


= PREDICTIVE SEARCH BOX =

* Set Placeholder text that shows in the search box on front end - example 'Type your search here'
* Choose which objects- Posts, Pages, Post Categories or Post tags should be included in the search
* Set the number of results to show for each object type e.g. Post 6
* When there are more results than can show in the dropdown a link is added to the dropdown footer to see all results
* Set order that the search object shows in the dropdown and on the All Results Search page.
* Option to show just post, page title in results
* Option to show or not show Post or page feature image thumbnail with results
* Option to show or not show description extract with results including the length in characters
* Option show or not show Product prices 


= SEARCH BOX STYLE & MOBILE =
There are 2 customizable templates built in - Widget Template and Header Template. Style the Search Box and results dropdown then apply the template required from the Predictive Search Widget.


= ALL SEARCH RESULTS PAGE =

* On install the plugin auto creates a Predictive Search page (with shortcode inserted)
* Predictive Search Results Block. Chose to create Search Results content with Predictive Search Block
* If using Shortcode have the option to use the Theme template or Plugins built-in template 
* Built-in template has option to show results in List format or Grid view 
* All results search page results show with endless scroll feature

= EXCLUDE FROM SEARCH RESULTS =

* Exclude any post from showing in Predictive Search results
* Exclude any page from showing in Predictive Search Results
* Exclude any or all Post category or Tags from showing in Predictive Search Results

= SPECIAL CHARACTERS =

* Special Characters near match. Query strings that CONTAINS a special character eg d'E return all matches found for d'e and de.
* Special Characters Prepend and Append near match e.g. Query (Purple) will return all matches found for (Purple) and Purple.
* Option to turn Special Characters Function ON or OFF
* Option to select any Special Characters used on your site. Results returned with or without special character input

= SINGULAR / PLURAL RESULT(s) =

* Returns results when user adds plural (s) to search term - the s is ignored e.g. bike and bikes will both return the same results

= TECHNICAL FEATURES =

* Predictive Search Database is auto updated each time a Post or Page is created, updated or deleted
* Manual Database Sync option to manually sync Predictive Search database with WordPress databases if required
* No Conflict. Can be used in conjunction with any other Search plugin without conflicts occurring
* Results are cached not on your server but on the user's machine via Backbone.localStorage.js (saving you on bandwidth)
* NO-CACHE option - should be turned on when testing, OFF when not.

= FOREVER FREE =

WP Predictive Search is fully functioned and provided completely free. 


= CONTRIBUTE =

When you download WP Predictive Search, you join our the a3rev Software community. Regardless of if you are a WordPress beginner or experienced developer if you are interested in contributing to the future development of Predictive Search or any of our other plugins on Github head over to the WP Predictive Search  [GitHub Repository](https://github.com/a3rev/predictive-search) to find out how you can contribute.
Want to add a new language? You can contribute via [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/predictive-search)

== SCREENSHOTS ==

Predictive Search WordPress


== INSTALLATION ==

= Minimum Requirements =

* PHP version 7.4 or greater is recommended
* MySQL version 5.6 or greater is recommended

== Changelog ==

= 1.2.0 - 2022/12/19 =
* This feature release Adds a Shortcode option box to the settings tab with a shortcode generator plus a Default Font option to the typography control and removes the fontawsome lib and replaces font icons with SVG.
* Feature - Add Predictive Search Box Shortcode creation from the Settings tab, Shortcode Option Box
* Feature - Add Create Shortcode button with Pop up form and shortcode generator
* Feature - Convert icon from font awesome to SVG
* Feature - Update styling for new SVG icons
* Plugin Framework - Remove fontawesome lib
* Plugin Framework - Update typography control from plugin framework to add support for Default value
* Plugin Framework - Default value will get fonts set in the theme.
* Plugin Framework - Change generate typography style for change on typography control

= 1.1.2 - 2022/11/16 =
* This maintenance release fixes Post Types (Post, Page, Product) number showing as 6 results, when set to 0, in search boxes added by Shortcode and PHP tag.
* Tweak - Remove use dashicons on frontend and replace with svg icon for faster script load
* Fix - Shortcode, when set Post Types (Post, Page, Product) number of items to show as 0, it shows 0 products, not 6.
* Fix - PHP Tag, when set Post Types (Post, Page, Product) number of items to show as 0, it shows 0 products, not 6.

= 1.1.1 - 2022/10/27 =
* Tweak - this maintenance release has compatibility for WordPress 6.1.0 plus including tweaks for Classic and Blocks theme all results page content creation methods.
* Tweak - Tested for compatibility with WordPress major version 6.1.0
* Tweak - Run Check to establish if the activate theme is Classic or Blocks theme. 
* Tweak - If Classic Theme detected remove option to create All Results Page content from PS Blocks
* Tweak - If Classic Theme detected only show shortcode option for creating All Results page content.
* Tweak - If Blocks Theme detected add PS Search Results Part Template in FSE
* Tweak - If Blocks Theme detected add All Results Blocks

= 1.1.0 - 2022/10/14 =
* This release adds new developer action hooks, plus support for post status filters, a theme template Get fix and a security patch.  
* Dev - Add wpps_shortcode_theme_display action hook
* Dev - Add wpps_popup_tpl_item_desc_after action hook
* Dev - Add wpps_popup_tpl_item_category_before action hook
* Dev - Update to support post status instead of only support the Publish post status
* Fix - Update search results script to get and show the correct Theme template
* Security - This release has a patch for a security vulnerability

= 1.0.1 - 2022/09/05 =
* This the first maintenance release contains 3 bug fixes. 
* Fix - Use sanitize_text_field instead of sanitize_key for search_in parameter so the number of items and order Display work correct in the dropdown of Search
* Fix - Show correct position of Category dropdown on frontend like set from block
* Fix - Show correct of search mobile icon block on frontend

== Upgrade Notice ==

= 1.2.0 =
This feature release Adds a Shortcode option box to the settings tab with a shortcode generator plus a Default Font option to the typography control and removes the fontawsome lib and replaces font icons with SVG.

= 1.1.2 =
This maintenance release fixes Post Types (Post, Page, Product) number showing as 6 results, when set to 0, in search boxes added by Shortcode and PHP tag.

= 1.1.1 =
this maintenance release has compatibility for WordPress 6.1.0 plus including tweaks for Classic and Blocks theme all results page content creation methods.

= 1.1.0 =
This release adds new developer action hooks, plus support for post status filters, a theme template Get fix and a security patch.

= 1.0.1 =
This the first maintenance release contains 3 bug fixes.

