=== Related Post ===
	Contributors: PickPlugins
	Donate link: http://pickplugins.com
	Tags: related post, related posts, related content, inline related post,  similar posts
	Requires at least: 3.8
	Tested up to: 5.8
	Stable tag: 2.0.36
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html

	Display Related Post under post by taxonomy and terms.

== Description ==


**Related Post** plugin is one of the most powerful plugin to display related post under post content on single post or page or custom post types, its also support to display related post under excerpt on archive pages. you can conditionally choose to display related content under excerpt or content by archive pages like tag, category, date, author, search page and custom taxonomy pages.

### Related Post by http://pickplugins.com

* [Documentation! &raquo;](https://www.pickplugins.com/documentation/related-post/?ref=wordpress.org)
* [Live Demo! &raquo;](https://www.pickplugins.com/demo/related-post/?ref=wordpress.org)
* [Buy Pro! &raquo;](https://www.pickplugins.com/item/related-post-for-wordpress/?ref=wordpress.org)

**Tutorials**

* [How to install & setup](https://www.youtube.com/watch?v=9SZKa0QYgsc)
* [Display on archive pages](https://www.youtube.com/watch?v=tXBLwC3PQBI)
* [Customize elements](https://www.youtube.com/watch?v=_kWh4mP-eso)
* [Manually selected post](https://www.youtube.com/watch?v=5G7o_zFKUhE)
* [Customize column count](https://www.youtube.com/watch?v=qudCJcqjlCk)
* [Related post as slider layout](https://www.youtube.com/watch?v=KUtBCyFoARk)
* [Related posts as list layout](https://www.youtube.com/watch?v=uo2v9U9kUCc)
* [Display on popups (Premium) ](https://www.youtube.com/watch?v=siMFvhy95Wo)
* [Display custom html after elements (Premium) ](https://www.youtube.com/watch?v=pztzF9R2yRQ)
* [Customize link target (Premium) ](https://www.youtube.com/watch?v=qFZPMoqEHxs)


**Related post under every paragraph**

You can choose paragraph position like first, second, third and before last paragraph to display related post.

**Before & After content and excerpt**

Related post plugin allows you to display related content link before and after the content, this feature also available for excerpt under archive pages.

**Related post under archive page**

You can display related post under various archive pages like home page, front page, blog page, date, search, author, year, date, month and etc.

**Related post by post types**

You can choose custom post types to display Related post under single page.

**Manual Post selection**
You can choose manually picked the post to display as related post for each post.

**Advance query**
You can set max number post to display and choose query order and orderby parameters, related posts query based on current post category, tags and custom taxonomies.

**Ready layout**

There is 3 different type layout currently available to display related post, you can choose grid, slider and list style layout. you can set custom width for items and margin, padding, text align.

**Sortable post elements**

You can sort post elements like post title, post thumbnail and excerpt as you want.

**Hide any elements**

You can hide or display post elements like post title, thumbnail or excerpt.

**Customize elements style**

You can set custom font size, font color, padding, margin for post title, post excerpt and set custom height for post thumbnail, select custom thumbnail size and etc. you can also write custom CSS for each elements.

**Track click**

You can enable tracking click on related post, this will help you understand which post getting more attention from related posts. you will see top 10 post from stats page.

### Premium Features

**Popup related post**
You can display related post on popups under single blog post or custom post types,

**Popup positions**
you can set 8 ready position for popups

**Popup custom delay**
You can set custom delay to display popup, so popup will be hidden until certain amount of time and then it will display.

**Popup display on scroll**
You can choose to display popup based on scroll down, popup will be hidden until certain amount of scroll down.

**Popup display scroll down to article**
Popup will display when user scroll down to reached end of the article class or content.

**Popup display scroll down to page**
Popup will display when user scroll down to reached end of the page or footer.

**Custom HTML after each elements**
You can display custom HTML under each elements like post title, post excerpt and post thumbnails. you can also display 3rd party shrotcode as output after each elements.

**Link Target**
You can set custom link target for each elements link like post title, post thumbnail and read more link, you can set _blank, _parent or etc for each link.

**Display via shortcode**

You can display related post any where via shortcode by using on your theme files
`
<?php echo do_shortcode( '[related_post post_id=""]' ); ?>
`



== Installation ==

1. Install as regular WordPress plugin.<br />
2. Go your plugin setting via WordPress dashboard and find "<strong>Related Post</strong>" activate it.<br />

After activate plugin you will see "Related Post" menu at left side on WordPress dashboard.<br />

short-code inside content for fixed post id you can use anywhere inside content.

`[related_post]`

Short-code inside loop by dynamic post id you can use anywhere inside loop on .php files.

`
<?php
echo do_shortcode( '[related_post post_id=""]' );
?>
`

== Screenshots ==

1. screenshot-1
2. screenshot-2
3. screenshot-3
4. screenshot-4
5. screenshot-5
6. screenshot-6
7. screenshot-7


== Changelog ==

	= 2.0.36 =
    * 2021-07-26 fix - html/font awesome icon saving issue fixed.

	= 2.0.35 =
    * 2021-06-25 fix - post types headline text issue fixed.

	= 2.0.34 =
    * 2021-04-20 fix - generic function name issue fixed.

	= 2.0.33 =
    * 2021-04-16 fix - eval function removed.

	= 2.0.32 =
    * 2021-04-13 fix - security issue updated.

	= 2.0.31 =
    * 2020-07-29 fix - excerpt related post display issue fixed.
    * 2020-07-29 add - added custom CSS and scripts options.



	= 2.0.30 =
    * 2020-07-26 fix - headline text output issue fixed.

	= 2.0.29 =
    * 2020-07-24 fix - headline custom CSS issue fixed.
    * 2020-07-24 add - advance display by post types.


	= 2.0.28 =
    * 2020-06-26 add - manually selected post any post types


	= 2.0.27 =
    * 2020-06-16 remove - remove popup for reviews.

	= 2.0.26 =
    * 2020-05-01 fix - translation for headline and read more added.
    * 2020-05-01 fix - sanitize global variables



	= 2.0.25 =
    * 2020-04-22 fix - manually selected post issue fixed.

	= 2.0.24 =
    * 2020-03-05 fix - font-awesome icon issue for slider navigation


	= 2.0.23 =
    * 2020-02-28 fix - minor php error issue fixed.

	= 2.0.22 =
    * 2020-02-26 add - Font awesome version load option added.

	= 2.0.21 =
    * 2020-02-11 fix - Slider loop option saving issue fixed.

	= 2.0.20 =
    * 2020-02-05 fix - slider column issue for mobile device fixed.

	= 2.0.19 =
    * 26/01/2020 add - help and support added on settings page sidebar

	= 2.0.18 =
    * 26/01/2020 add - added tutorials under help & support tabs


	= 2.0.17 =
    * 25/01/2020 update - remove shortcode tabs, move to help & support tabs


	= 2.0.16 =
    * 20/01/2020 update - responsive issue fixed for settings page

	= 2.0.15 =
    * 20/01/2020 update - missing slider options added
    * 20/01/2020 add - added help & support menu.

	= 2.0.14 =
    * 19/01/2020 update - update default value for settings.

	= 2.0.13 =
    * 16/01/2020 fix - manually added post query issue fixed.

	= 2.0.12 =
    * 16/01/2020 add - added help menu.
    * 16/01/2020 fix - fixed minor post query issue.
    * 16/01/2020 add - translation file added


	= 2.0.11 =
    * 15/01/2020 add - add new filter hook for link attributes
    * 15/01/2020 fix - fixed the issue displaying related post on custom taxonomy

	= 2.0.10 =
    * 14/01/2020 add - custom font size, color and custom css for headline text

	= 2.0.9 =
    * 14/01/2020 fix - data update issue for undefined index issue fixed.
    * 14/01/2020 fix - added reset post query after loop.


	= 2.0.8 =
    * 12/01/2020 add - data update process added.

	= 2.0.7 =
    * 12/01/2020 update - re-write plugin

	= 2.0.6 =
    * 23/10/2018 add - added orderby post__in option


	= 2.0.5 =
    * 21/10/2018 add - added order and orderby option


	= 2.0.4 =
    * 01/07/2018 fix - Empty variable issue check fixed.

	= 2.0.3 =
    * 19/12/2018 fix - Undefined index issue fixed.

	= 2.0.2 =
    * 20/03/2016 add - Stats to click on related post.

	= 2.0.1 =
    * 19/03/2016 fix - title link issue fixed.
    * 19/03/2016 fix - Thumbnail link issue fixed.    

	= 2.0.0 =
    * 18/03/2016 add - Re-written plugin.

	= 1.5 =
    * 04/07/2015 fix - conflict with "post grid" plugin fixed.	
    
	= 1.4 =
    * 02/04/2015 add - Display Related Post automatically .	
    * 02/04/2015 add - display related post for custom post.
    * 02/04/2015 add - Display automatically  only post types selection.   
    
	= 1.3 =
	
    * 29/12/2014 add - Upload custom 404 thumbnail image.
    
	= 1.2 =
	
    * 21/12/2014 add - Maximum number of post to display.

	= 1.1 =
	
    * 19/11/2014 add - Default empty thumb.
    * 19/11/2014 add - Headling text for "Related Post".
    * 19/11/2014 add - Font size for post title.
    * 19/11/2014 add - Font color for post title.
    
	= 1.0 =
	
    * 09/11/2014 Initial release.
