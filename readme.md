# LVL99 Omny Embed

Author: Matt Scheurich <matt@lvl99.com>

Repo: [http://www.github.com/lvl99/lvl99-omny-embed](http://www.github.com/lvl99/lvl99-omny-embed)


## Description

Easily embed media hosted on [Omny](http://www.omnyapp.com) within your WordPress site. Supports _omnyapp.com_ and _omnycontent.com_ URLs and the `[omny]` shortcode.


## Installation

1. Log in to WordPress admin to download and activate the "LVL99 Omny Embed" plugin.

2. That's it!


## Usage

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


### Usage Examples
```
[omny url="http://omnyapp.com/shows/thinkergirl-the-podcast/the-summer-sessions-kristies-edition-thinkergirl-t" width="100%" height="150"]
```

```
[omny orgId="65cc671a-66e3-45dc-98f3-a50200547d4e" programId="7d59c4ff-f533-4daf-8005-a506000c2c15" clipId="a8917ca7-e171-48a2-b4fb-a57200bf0b80" url="http://omnyapp.com/shows/thinkergirl-the-podcast/the-summer-sessions-kristies-edition-thinkergirl-t"]
Check out our latest podcast at <a href="http://omnyapp.com/shows/thinkergirl-the-podcast/the-summer-sessions-kristies-edition-thinkergirl-t">http://omnyapp.com/shows/thinkergirl-the-podcast/the-summer-sessions-kristies-edition-thinkergirl-t</a>
[/omny]
```


## Issues

* Nothing yet! Let me know via [Github](http://www.github.com/lvl99/lvl99-omny-embed).
