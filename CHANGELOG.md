#Changelog

## 1.20-dev
Release date: ??.??.????

* News email content order: fi, sv, en (HUB-308)
* WYSIWYG undo/redo buttons (HUB-322)


## 1.19
Release date: 12.06.2018

* Minor tweaking to caching (HUB-310)
* Use certificate for oprek integration requests (HUB-319)
* IE11: Fixed degree programme selector (HUB-324)
* User groups for themes and instructions (HUB-318)
* Autoselect user group tab (HUB-320)
* Course embedding support (HUB-315)


## 1.18
Release date: 30.04.2018

* Delete content permission removed for content editors (HUB-301)
* URL copy button for content editors (HUB-310)

## 1.17
Release date: 26.04.2018

* Truncate long news titles on front page (HUB-306)
* Master's Programmes -> Master's and Licenciate's Programmes (HUB-273)
* Added support for doctoral programmes (HUB-305)
* Separate programme specific and general news on front page (HUB-303)
* Require search text in order to improve performance (HUB-262)
* Drupal core security update (HUB-309)

## 1.16
Release date: 19.04.2018

* Drupal core and contrib module updates (HUB-283)
* Styleguide update (HUB-299)
* Display degree programme(s) in news archive (HUB-304)
* Added bulletin manager role that can manage notices (HUB-302)
* Drupal core security update (HUB-307)

## 1.15
Release date: 29.03.2018

* Styleguide update (HUB-281)
* Degree programme switcher visual changes (HUB-271)
* Prevent technical error if a referred article was removed (HUB-296)
* Typo fix in new students Finnish description (HUB-297)
* Drupal core security update (HUB-298)

## 1.14
Release date: 26.02.2018

* Fixed new students label visibility rule (HUB-280)

## 1.13
Release date: 23.02.2018

* Support for marking instructions for new students (HUB-266)
* More news items on front page (HUB-267)
* Search field info placeholder (HUB-269)
* Top content placement and visual changes (HUB-270)
* Filter news page by active degree programme (HUB-258)
* Smaller theme boxes on front page (HUB-268)
* Drupal-core update (8.4.5).

## 1.12
Release date: 20.12.2017

* Support for second level main navigation (HUB-237)
* Renamed News to Notice (HUB-253)
* Fetch avatar only when the user has Oodi UID (HUB-249)

## 1.11
Release date: 20.11.2017

* Support for new office hours API (HUB-251)


## 1.10
Release date: 13.11.2017

* Allow redirect login to happen even with logged in users (HUB-246)


## 1.9
Release date: 06.11.2017

* Changed site title (HUB-242)
* Created and configured administrator role (HUB-247)


## 1.8
Release date: 26.10.2017

* Language Centre office hours support (HUB-245)
* Drupal core 8.4 update (HUB-244)

## 1.7
Release date: 06.10.2017

* Translation update (HUB-236)
* Search both exact and partial text (HUB-243)


## 1.6
Release date: 30.08.2017

* Changed location of paragraph images (HUB-231)
* Added possibility to use UniTube video links (HUB-233)
* Added possibility to attach inline images to body fields (HUB-229)
* Changed location of paragraph videos (HUB-232)
* Added possibility to attach inline files to body fields (HUB-226)
* Changed feedback recipient email address (HUB-228)
* Cleaned up transitional degree programme handling (HUB-220)
* Added Atom+RSS news feed that support degree programme targeting (HUB-234)
* Changed text about how to add FAQ sections from original translation (HUB-222)
* Updated Drupal core (HUB-235)


## 1.5
Release date: 27.06.2017

* Added an API for getting all degree programmes (HUB-217)
* News page pagination tweaking and styling (HUB-216)
* Added a special paragraph type for displaying office hours (HUB-219)
* Updated Drupal core (HUB-227)
* Updated search title to "Instructions for students" (HUB-223)
* Fixed PHP warning of unexpected variable type when degree programmes without
  type were listed in switcher
* Switched to use one of our ported D8 module from Drupal.org


## 1.4
Release date: 23.05.2017

* Added some validation related to active degree programme detection mechanism
* Fixed node preview (HUB-211)
* Front page visual changes (HUB-198)
* List themes referencing the current instruction (HUB-210)
* Accessibility improvements (HUB-214)
* Allow a-ö (was a-z) in "My searches" filtering (HUB-215)
* Fix degree programme listing order (HUB-207)
* Use path prefix "/en" for english pages (HUB-180)
* Added possibility to filter by degree programmes to theme sections (HUB-201)


## 1.3
Release date: 20.04.2017

* Display a list of degree programmes when viewing instructions (HUB-192).
* Updated Drupal core to 8.3.0 (HUB-206).
* Updated JS validation, so that it is consistent with Drupal core.
* Remove heading and type labels from theme articles (HUB-188) 
* Display a list of degree programmes on each paragraph on instructions (HUB-193)
* Use summary instead of body in teasers and search results when available (HUB-185)
* Changed login and logout link titles (HUB-186)
* Removed page caching for anonymous users as it was causing issues with degree
  programme selector functionality (HUB-182).
* Fix theme teaser background image in firefox and use responsive images (HUB-205)
* Partial word search support (HUB-164).
* Sort degree programmes alphabetically (HUB-207)
* Admin content view: List content by degree programme (HUB-189)
* Add links to related themes on each instruction page (HUB-195)
* Added underline to degree programme switcher items when hovering (HUB-204)
* Added title (hover) texts to degree programme favorite widget (HUB-200)
* Feedback: User email address as the from address (HUB-190)
* Display search filters only when more than one type of result (HUB-202)
* Redirect external active degree programme calls to front page (HUB-184)
* Added paragraphs to themes (HUB-201)


## 1.2
Release date: 20.03.2017

* Changed Search results to display as a list with filter buttons (HUB-173)
* Added custom block listing page for editors (HUB-174)
* Removed soft hyphens from node title to prevent them messing up search and url aliases (HUB-183)
* Add nofollow attribute to degree programme switcher links to prevent google indexing (HUB-184)
* Translation updates (HUB-179).


## 1.1
Release date: 16.03.2017

* Fixed warning message when google analytic reports isn't authorized (HUB-36)
* Improved cache clears for search results to avoid stale results (HUB-168)
* Fix top content always linking to source translation (HUB-36)
* Changed two login/logout links into one logical menu item per menu (HUB-105)
* Updated Drupal core


## 1.0
Release date: 13.03.2017

* Fix issues with search autocomplete on mobile devices (HUB-136)
* Minor security improvements to my searches functionality (HUB-152)
* Remove theme sorting when using mobile devices (HUB-89)
* Do not prevent login when getting study rights from oprek fails (HUB-153)
* Add top content block (HUB-36)
* Fixed degree programme filtering (HUB-154)
* Removed default paragraph when creating new article or theme (HUB-160)
* Show user avatar (HUB-130)
* Added admin view for default ordering of themes (HUB-161)
* Disabled search autocomplete because of major usability issues
* Fixed search to work with paragraph titles and acccordion content (HUB-166)


## 1.0-rc0
Release date: 02.03.2017

* Added support for sorting by author on content listing (HUB-123)
* Added feature to login automatically if logged in in opintoni/opetukseni
  service (HUB-23, HUB-25, HUB-122)
* Added feature for preventing simultaneous content editing (HUB-124)
* Added automatic role (un)assignments based on groups from SAML2 (HUB-35)
* Added frequently asked questions to themes (HUB-126)
* Added tool for viewing oprek responses for student users (HUB-135)
* Added feature for logged in users to favorite degree programmes (HUB-127)
* Added up-button to bottom of page (HUB-138)
* Added feature for transitional degree programmes (HUB-141)
* Added autocomplete to search (HUB-134)
* Added per user sorting functionality to themes view (HUB-89)
* Added email address to the content lock message (HUB-124)
* Added mobile menu (HUB-146)
* Added more degree programmes (HUB-142)
* Added remember last search functionality (HUB-149)
* Added feature that synchronises students degree programmes (HUB-56, HUB-58)
* Added feature that active degree programme falls back to primary degree
  programme that is specified by oprek integration (HUB-56, HUB-58)
* Added menu links for Weboodi, course search and opinder (HUB-148)
* Added an UI for configuring SSO groups to Drupal roles mappings (HUB-139)
* Added resetting possibility to the active degree programme (HUB-99, HUB-140)
* Added fullscreen degree programme switcher in mobile (HUB-128)
* Added feature for finding content by author (HUB-132)
* Added autosubmit degree programme search (HUB-137)
* Added possibility to use multiple email addresses in news edit (HUB-144)
* Fixed empty contextual links (HUB-115)
* Fixed logout destination (HUB-116, HUB-117)
* Fixed broken revisions (HUB-150)
* Changed degree programme codes (HUB-129)
* Changed logout link to an icon link (HUB-131)
* Removed mobile input focus zoom in search (HUB-133)
* Removed input focus zoom in degree programme switcher for devices (HUB-121)
    

## 1.0-beta1
Release date: 13.02.2017

* Added front page text (HUB-98)
* Added various fixes to current topics block
* Added scrollbar (chrome + safari) to degree programme switcher (HUB-104)
* Added favicon and mobile touch icons (HUB-112)
* Added possibility to upload .tex files to file fields (HUB-118)
* Fixed overlapping news teasers in IE (HUB-114)
* Fixed error when having an unknown degree programme in cookies
* Fixed bug with html entities in teaser body (HUB-120)
* Changed login/logout menu item by mobing it to main menu (HUB-109)
* Changed degree programme switcher to group by type (HUB-119)
* Changed login destination to be the page visitor used to be in (HUB-96)
* Changed logo link to www.helsinki.fi (HUB-108)
* Removed links from news degree programme field (HUB-108)
* Removed breadcrumb (HUB-103)
* Removed degree programme from article full view (HUB-101)


## 1.0-beta0
Release date: 06.02.2017

* Added collapsing degree programme switcher when clicking header (HUB-100)
* Added styling to news full view (HUB-102)
* Added ability to login through university´s SSO service (HUB-34)
* Improved link titles to use target node title when possible (HUB-95)
* Improved styling for separating node edit actions from paragraphs (HUB-97)
* Removed ability to register new Drupal account (HUB-34)
* Removed ability to use "request new password" core feature (HUB-34)
* Removed ability to edit own account (HUB-34)


## 1.0-alpha1
Release date 30.01.2017

* Added Wysiwyg for body and paragraph body (HUB-69)
* Added support for paragraphs per translation file attachments (HUB-66)
* Added my latest searches (HUB-37)
* Added support for embedded videos (HUB-76)
* Added top content from google analytics (HUB-36)
* Changed text size in theme teaser (HUB-70)
* Changed paragraph heading size and background color (HUB-71)
* Changed site name to "Guide" on all languages (HUB-83)
* Fixed header in mobile (HUB-84)
* Improved the UX of wysiwyg tables using dark borders and padding (HUB-81)
* Improved edit mode to have paragraphs closed by default (HUB-85)
* Improved degree programmer context switching (HUB-77, HUB-73)
* Updated modules (HUB-79)
* Themes visually like articles in search results (HUB-87)
* Whole node teaser as link (HUB-88)
* Filter articles by degree programme when viewing theme nodes (HUB-86)


## 1.0-alpha0
Release date 23.01.2017

* Implemented theme that utilises University of Helsinki
 [styleguide](https://github.com/UniversityofHelsinki/styleguide) (HUB-32)
* Added feature for switching language from top right corenr (HUB-17)
* Added feature for creating articles optionally categorized by degree
  prorammes and themes (HUB-3, HUB-9, HUB-11)
* Added feature for creating structured paragraphs for articles with table of
  contents -list (HUB-62, HUB-30)
* Added feature for adding article paragraphs for specified degree programmes
  (HUB-63)
* Added feature for adding news that can be categorized by degree programmes
  (HUB-39, HUB-40)
* Added feature for viewing news either from degree programme page or from an 
  general archive list -view (HUB-43, HUB-53)
* Added feature for viewing the last changed timestamp for content (HUB-16)
* Added feature for searching content (HUB-14)
* Added feature for navigating through breadcrumb (HUB-31)
* Added feature for navigating in the site and maintain the navigation (HUB-32)
* Added feature for visitors to give feedback (HUB-38)
* Added feature for tracking statistics with Google Analytics (HUB-44)
* Added feature for providing initial/demo/example content for the site (HUB-48)
* Added feature for maintaining themes (taxonomy) and link articles to them
  (HUB-50)
* Added feature for switching degree proramme (HUB-51)
* Added feature for sending news by email (HUB-54)
* Added cookie consent disclaimer (HUB-59)
* Configured path aliaes for content (HUB-57)
* Configured Varnish purger (HUB-60)
