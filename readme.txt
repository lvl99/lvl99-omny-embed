=== LVL99 Omny Embed ===
Contributors: lvl99
Tags: omny, embed, media
Requires at least: 3
Tested up to: 4.4.1
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Easily embed media hosted on Omny within your WordPress site. Supports omnyapp.com, omnycontent.com URLs, and the [omny] shortcode.


== Description ==

Because Omny doesn't currently support OEmbed, I've made a plugin to cURL the necessary details.

You can insert **omnyapp.com** or **omnycontent.com** URLs to clips to have generated embed HTML visible.

Alternatively, you can specify the necessary attributes through a `[omny]` shortcode.

There are some options available to set up default behaviours.


== Installation ==

1. Log in to WordPress admin to download/activate the "LVL99 Omny Embed" plugin.
2. That's it!


== Frequently Asked Questions ==

= What is Omny? =

A platform for recording and publishing podcasts to iTunes and the streaming Omny app.


== Screenshots ==

1. Configurable options


== Changelog ==

= 0.1 =
* The very first release.


== Usage ==

When composing your posts, just paste in the URL to the media hosted on Omny. This plugin will sort out the rest!

You can also use the shortcode `[omny]..[/omny]` if you wish for more customisation of your embed code.

The `..` within the shortcode is space for you to put in alternative content if the embed doesn't load properly. By default this will be a basic link to the media.

These are the available `[omny]` attributes/options:

 * `url`: The URL to the media on Omny (located on _omnyapp.com_ domain)
 * `embedUrl`: The URL to the embedded media player on Omny (located on _omnycontent.com_ domain)
 * `width`: Width of embed; available as percentage, e.g. `100%`, or pixels, e.g. `150`
 * `height`: Height of embed; available as percentage, e.g. `100%`, or pixels, e.g. `150`
 * `orgId`: The ID of the organisation on Omny, e.g. `65cc671a-66e3-45dc-98f3-a50200547d4e`
 * `programId`: The ID of the program on Omny, e.g. `7d59c4ff-f533-4daf-8005-a506000c2c15`
 * `clipId`: The ID of the clip on Omny, e.g. `a8917ca7-e171-48a2-b4fb-a57200bf0b80`


= Usage Examples =
`[omny url="http://omnyapp.com/shows/thinkergirl-the-podcast/the-summer-sessions-kristies-edition-thinkergirl-t" width="100%" height="150"]`

`[omny orgId="65cc671a-66e3-45dc-98f3-a50200547d4e" programId="7d59c4ff-f533-4daf-8005-a506000c2c15" clipId="a8917ca7-e171-48a2-b4fb-a57200bf0b80" url="http://omnyapp.com/shows/thinkergirl-the-podcast/the-summer-sessions-kristies-edition-thinkergirl-t"]
Check out our latest podcast at <a href="http://omnyapp.com/shows/thinkergirl-the-podcast/the-summer-sessions-kristies-edition-thinkergirl-t">http://omnyapp.com/shows/thinkergirl-the-podcast/the-summer-sessions-kristies-edition-thinkergirl-t</a>
[/omny]`


## Issues

* Nothing yet! Let me know via [Github](http://www.github.com/lvl99/lvl99-omny-embed).
