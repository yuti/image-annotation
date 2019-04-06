=== demon image annotation ===
Contributors: demonisblack
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=HBKHFYS86E99Q&lc=MY&item_name=demon%20Image%20Annotation%20Plugin&item_number=dia_plugin&currency_code=MYR&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tags: comment,comments,image,images,note,notes,annotation,image annotation,dannychoo,facebook,tag,flickr
Author URI: http://www.superwhite.cc/
Plugin URI: http://www.superwhite.cc/demon/image-annotation-plugin
Requires at least: 2.5
Tested up to: 4.9.4
Stable tag: 3.8

Allows you to add textual annotations to images by select a region of the image and then attach a textual description.

== Description ==

This plugin allows you to add textual annotations to images by select a region of the image and then attach a textual description, the concept of annotating images with user comments.
Integration with JQuery Image Annotation from Chris (http://www.flipbit.co.uk/jquery-image-annotation.html) with PHP support from GitHub (http://github.com/stas/jquery-image-annotate-php-fork).

<h3>Live Demo:</h3>

[http://www.superwhite.cc/demon/image-annotation-plugin](http://www.superwhite.cc/demon/image-annotation-plugin "http://www.superwhite.cc/demon/image-annotation-plugin")

<h3>Needs Your Support:</h3>

It is hard to continue development and support for this free plugin without contributions from users like you. If you enjoy using demon Image Annotation and find it useful, please consider making a donation. Your donation will help encourage and support the plugin's continued development and better user support. [Donate](https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=HBKHFYS86E99Q&lc=MY&item_name=demon%20Image%20Annotation%20Plugin&item_number=dia_plugin&currency_code=MYR&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted "Donate")

<h3>Some features:</h3>

* Option to approve, edit and remove image notes in admin page.
* Preview image annotation in admin page.
* Auto insert unique id attribute for all the images for image note.
* Option to allow image annotation for login user who can moderate comment only
* Gravatar in the notes
* Option to sync with wordpress comments.
* Option to show thumbnail in comment list.
* 'Mouseover to load notes' on top of every image note (editable).
* 'Link' on top of every image note if hyperlink image (editable).

== Installation ==

<h3>Installation</h3>
<ol>
<li>Put the plugin folder into [wordpress_dir]/wp-content/plugins/</li>
<li>Go into the WordPress admin interface and activate the plugin</li>
<li>Choose the settings you want in demon-image-annotation settings.</li>
</ol>

<h3>How to use</h3>
<ol>
<li>
<p>First enter div wrapper <strong>id</strong> or <strong>class</strong> in settings where your post content appear, or else the plugin can't find the wrapper to start.</p>
<strong>Example (.entrybody)</strong><br />
<code>
&lt;div class="entrybody&gt;<br />
&nbsp;&nbsp;&nbsp; &lt;?php the_content(); ?&gt;<br />
&lt;/div&gt;</code>
</li>
<li>
<p>To embed annotations and comments on images, your img tag must have id attribute value start with <strong>‘img-‘</strong>, this plugin already did the trick if you enable <strong>Auto Generate Image ID</strong> option.</p>
</li>

<li>
<p>
If you wish to add an id attribute maunally, here is the guide on how to insert id attribute to img tag.<br />
- First disable <strong>Auto Generate Image ID</strong> option<br />
- Add an id attribute start with <strong>‘img-‘</strong> follow by unique id to img tag.<br />
- All the images must have unique and different id or else you will get the same comments.
</p>
<strong>Example (img-4774005463)</strong><br />
<code>
&lt;img id=&quot;img-4774005463&quot; src=&quot;http://farm5.static.flickr.com/4121/4774005463_3837b6de44_o.jpg&quot; width=&quot;900&quot; height=&quot;599&quot; alt=&quot;Image Annotation Plugin&quot; /&gt;
</code>
</li>

<li>
<p>
Decide the option for <strong>Wordpress Comments</strong> setting.
</p>
<p>
<strong>Sync with wordpress comments:</strong><br/>
- image note sync with wordpress comment database<br/>
- modified comment will auto update both database<br/>
- deleted comment from wordpress comment will not sync, have to delete manually in image notes table list.<br />
- new image note from annoymous will auto add into wordpress comment as waiting approval.<br/>
- the image note only publish when the comment is approve.<br/><br/>

<strong>Not sync with wordpress comments:</strong><br/>
- standalone image note database.<br/>
- new image note will publish without approval.
</p>
<p>Pls note if you switch the option, the comments added with previous option will not load.</p>
</li>
</ol>

<h3>Usage:</h3>
<ol>
<li>
<p><strong>Disable Add Note button:</strong><br/>
Add an addable attribute with value “false” to disable the add note button, but image notes still viewable.<br/>
Login User who can Moderate Comments still able to see Add button option.
</p>
<code>
&lt;img id=&quot;img-4774005463&quot; addable=&quot;false&quot; src=&quot;http://farm5.static.flickr.com/4121/4774005463_3837b6de44_o.jpg&quot; width=&quot;900&quot; height=&quot;599&quot; alt=&quot;Image Annotation Plugin&quot; /&gt;
</code>
</li>

<li>
<p><strong>Exclude image:</strong><br/>
Add an exclude attribute to disable image annotation function.</p>
<code>
&lt;img exclude id=&quot;img-4774005463&quot; src=&quot;http://farm5.static.flickr.com/4121/4774005463_3837b6de44_o.jpg&quot; width=&quot;900&quot; height=&quot;599&quot; alt=&quot;Image Annotation Plugin&quot; /&gt;
</code>
</li>

<li>
<p><strong>Comments thumbnail:</strong><br/>
To add thumbnails to your comments list manually, just add the php code below in your comment callback function.</p>
<code>
&lt;?php if (function_exists(&#39;dia_thumbnail&#39;)) {
	dia_thumbnail($comment-&gt;comment_ID);
}?&gt;
</code>
</li>
</ol>

== Frequently Asked Questions ==

[Visit the site for more questions or help.](http://www.superwhite.cc/demon/image-annotation-plugin "Visit the site for more questions or help.")

== Other Notes ==
<ol>
<li>There's a new method to exlcude image annotation after version 3, but previous version method id="img-exclude" still work. </li>
<li>Image preview for admin editing is only support version 3 and above, image note added with previous version will not support.</li>
</ol>

== Screenshots ==

1. Demonstration of demon image annotation.
2. Demonstration of demon image annotation.
3. Image annotation settings.
4. Image annotation table list.
5. Image annotation editing.

== Changelog ==
= 3.8   =
* Replace mysql_real_escape_string() with esc_sql()

= 3.7   =
* Added admin plugin tab
* Updated CSS buttons
* Fixed auto update Wordpress Comments database issue
* Fixed dashboard menu count issue

= 3.6   =
* Fixed image description


= 3.5   =
* Fixed image numbering issue


= 3.4   =
* Fixed post ID issue

= 3.3   =
* Fixed auto update Wordpress Comments database issue
* Fixed notes overlap issue
* Fixed notes not resize according to image size
* Added image notes count number to admin bar and menu

= 3.2   =
* Modifiend database column
* Fixed backslash in image note
* Remove auto generate ID and post ID in jQuery
* Replace auto generate ID and post ID with filter function
* Option to disable numbering
* Option to auto resize image annotation to fit content max width
* Image annotation able to show on home page

= 3.1   =
* Fixed Annotation jQuery.

= 3.0   =
* Fixed HTTPS issue
* Fixed wordpress comment synced with image note data.
* Update database column.
* Update admin image notes table list.
* Update jQuery version to 2.1.1
* Update jQuery UI version to 1.11.2
* Add Image preview in admin image notes table list.
* Add edit image button in wordpress comment table list.
* Add submenu to admin page
* Add comments maxlength setting
* Able to manage settings base on roles and capabilities
* Able to approve, edit and remove image notes base on roles and capabilities
* Able to add new line for image notes
* Able to restore comment from Trash in admin image notes table list.

= 2.5.4   =
* Thumbnail resize fixed.
* Thumbnail preloader.

= 2.5.2   =
* Fixed addable for admin only.

= 2.5   =
* Fixed image annotation script.
* Fixed php JSON data.
* Fixed not workin in ie.
* New admin table list.

= 2.4.8   =
* Fixed MD5 not working.

= 2.4.7   =
* Fixed bugs.

= 2.4.6   =
* Fixed jquery conflict.

= 2.4.5   =
* Fixed missing add button.

= 2.4.4   =
* Added approve and unapprove button for selected image notes.
* Fixed table prefix issue

= 2.4.3   =
* Fixed on table name issue.
* Fixed pop up error while saving.
* Fixed image notes not loading (when comment or image note is not approve yet).
* Show error occured message when loading image notes timeout.
* Add option to remove HTML image tags.

= 2.4.1   =
* Fixed on Image Notes Tab not display in Safari browser.

= 2.4   =
* Fixed on Chrome and IE browsers.

= 2.3   =
* Fixed return and new line issue that cause image note stop loading.

= 2.2   =
* New image note as waiting approval even it is not sync with wordpress comment.
* Fixed image note not loading with special characters.
* Image note settings now is display for admin only.
* Customize default avatar for image note author gravatar.

= 2.1   =
* Rounded border.
* Add list of image notes in admin page.
* Add option to approve, edit and delete image notes.
* Add option to change mouseover description and image hyperlink name.
* Add option to lnclude post id in every auto insert images id.
* Fix issue of database prefix is not wp_.
* move author to top.

= 2.0   =
* Admin page
* Auto insert id attribute start with "img-".
* Add notes to your uploaded pictures and embed pictures.
* Add author gravatar on notes.
* Add option to show image notes not only in single page but other pages such as home and archives.
* Add option which enable user to disable or enable noted image for admin only or every user.
* Add option which enable user to disable or enable WordPress commenting system.
* Add option which enable user to disable or enable noted image thumbnail at comment list.
* Add description on top of every image note 'Mouseover to load notes'.
* Add link on top of every image note if hyperlink image.

= 1.2   =
* Delete comments
* Comment thumbnail hover

= 1.1   =
* Fix note overlap
* Image note user addable