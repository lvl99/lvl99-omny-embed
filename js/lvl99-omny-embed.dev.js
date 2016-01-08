/*
 * LVL99 Omny Embed
 */
(function(window) {

  // Needs jQuery
  var $ = window.jQuery;

  // Omny Embed
  var lvl99 = window.lvl99 || {};
  lvl99.OmnyEmbed = {
    /*
    Fetches the embedded media's info from a URL and returns as an array
    @TODO CORS disables this, so maybe once Omny have Access-Control-Origin-Allowed then it'll be good to use

    @method fetchMediaInfo
    @since 0.1.0
    @param {Object} mediaInfo
    @returns {String}
    */
    fetchMediaInfo: function ( url, successCb, errorCb ) {
      console.log( 'fetchMediaInfo', url );

      // No URL given
      if ( !url ) return;

      $.ajax({
        url: url,
        success: function (data, textStatus, xhr) {
          var $data = $(data);

          // The og:video meta tag has the link to the audio
          var omnyMediaUrl = $data.find('meta[property="og:video"]');

          // Not there? Abort
          if ( !omnyMediaUrl ) {
            if ( typeof errorCb === 'function' )
              errorCb.apply( this, [ 'no media url found', xhr ]);
            return; // Error
          }

          // console.log( 'found media url', omnyMediaUrl );

          // Split media URL to get orgId, programId and clipId
          // @example: https://www.omnycontent.com/d/clips/65cc671a-66e3-45dc-98f3-a50200547d4e/7d59c4ff-f533-4daf-8005-a506000c2c15/028daa9c-1633-483c-afe4-a572007e0519/video.mp4?utm_source=Omny+Radio+(Facebook+Player)&amp;utm_medium=Video
          var omnyMediaUrlInfo = omnyMediaUrl.replace(/https?\:\/\/(www\.)?/i, '').split('/'); // Remove protocol and www domain before splitting

          // console.log( 'omnyMediaUrlInfo', omnyMediaUrlInfo );

          if ( omnyMediaUrlInfo.length < 6 ) return; // Error (doesn't match minimum component length)
          if ( !omnyMediaUrlInfo[0].match('omnycontent') ) return; // Error (doesn't reference omny content)
          if ( !omnyMediaUrlInfo[1].match('d') ) return; // Error (doesn't match expected 'd')
          if ( !omnyMediaUrlInfo[2].match('clips') ) return; // Error (doesn't match expected 'clips')

          // Collate the media info
          var omnyMediaInfo = {
            orgId:     omnyMediaUrlInfo[3],
            programId: omnyMediaUrlInfo[4],
            clipId:    omnyMediaUrlInfo[5],
            url:       url,
            embedUrl:  'https://www.omnycontent.com/w/player/?orgId='+omnyMediaUrlInfo[3]+'&amp;programId='+omnyMediaUrlInfo[4]+'&amp;clipId='+omnyMediaUrlInfo[5]+'&amp;source=LVL99+Omny+Embed+(WordPress+AJAX)'
          };

          console.log( 'omnyMediaInfo', omnyMediaInfo );

          if ( typeof successCb === 'function' )
            successCb.apply( this, [ omnyMediaInfo ]);
        },
        error: function (err, xhr) {
          // alert( "Couldn't find or access the Omny media to embed. Please ensure it is accessible online and that its settings are not set to 'Private' before trying again.");
          if ( typeof errorCb === 'function' )
            errorCb.apply( this, [ err, xhr ]);
        }
      });
    },

    /*
    Generates the embed code for the media

    @method getEmbedCode
    @since 0.1.0
    @param {Mixed} urlOrMediaInfo
    @returns {String}
    */
    getEmbedCode: function ( urlOrMediaInfo ) {
      var mediaInfo = {};

      // Object: mediaInfo
      if ( typeof urlOrMediaInfo == 'object' ) {
        mediaInfo = urlOrMediaInfo;

      // String: url to fetch mediaInfo first
      } else {

      }

      // Build the embed string
      // @example: <iframe src="https://www.omnycontent.com/w/player/?orgId=65cc671a-66e3-45dc-98f3-a50200547d4e&amp;programId=7d59c4ff-f533-4daf-8005-a506000c2c15&amp;clipId=a8917ca7-e171-48a2-b4fb-a57200bf0b80&amp;source=wordpress" width="100%" height="150px" frameborder="0" style="opacity: 1; visibility: visible;"></iframe>
      return '<iframe src="'+mediaInfo.embedUrl+'" width="100%" height="150" style="opacity: 1; visibility: visible;"><a href="'+mediaInfo.url+'" target="_blank">Listen: '+mediaInfo.url+'</a></iframe>';
    },

    /*
    Initialise the Omny Embed items

    @method init
    @returns {Void}
    */
    init: function () {
      var $omnyEmbeds = $('.lvl99-omny-embed');
      if ( $omnyEmbeds.length > 0 ) {
        $omnyEmbeds.each( function (i, item) {
          // Fetch the media data
          var $item = $(item),
              itemId = $item.attr('id') || new Date().getTime(),
              itemUrl = $item.attr('data-url');

          // Skip any items that already have iframes within them
          if ( $item.find('iframe').length === 0 ) {
            // Set a unique ID (if not already set)
            $item.attr('id', itemId );

            // Fetch the media info via AJAX
            lvl99.OmnyEmbed.fetchMediaInfo( itemUrl, function ( mediaInfo ) {
              var itemEmbedCode = lvl99.OmnyEmbed.getEmbedCode( mediaInfo );

              // Set the contents of the item to the embed code
              $('.lvl99-omny-embed[data-url="'+mediaInfo.url+'"]').html( itemEmbedCode );
            });
          }
        });
      }
    }

  };

  lvl99.OmnyEmbed.init();

  // TinyMCE plugin
  var tinymce = window.tinymce;
  if ( tinymce && tinymce.hasOwnProperty('create') ) {
    tinymce.create('tinymce.plugins.LVL99OmnyEmbed', {
      init : function(ed, url) {
        // Add the button
        ed.addButton('omny_embed', {
          title: 'Omny Embed',
          cmd:   'omny_embed',
          image: url + '/media/lvl99_omny_embed.png'
        });

        // Add the command
        ed.addCommand( 'omny_embed', function () {
          var url = prompt( 'Enter the URL to the Omny Media you wish to generate embed code for');

          if ( url )
            lvl99.OmnyEmbed.fetchMediaInfo( url, function (return_text) {
              ed.execCommand('mceInsertContent', 0, return_text);
            }, function () {
              ed.execCommand('mceInsertContent', 0, '[omny_embed url="'+url+'"]');
            });
        });
      },
   
      getInfo : function() {
        return {
          longname:  'LVL99 Omny Embed',
          author:    'Matt Scheurich',
          authorurl: 'http://www.lvl99.com/',
          infourl:   'http://www.lvl99.com/',
          version:   '0.1.0'
        };
      }
    });
   
    // Register plugin
    tinymce.PluginManager.add( 'LVL99OmnyEmbed', tinymce.plugins.LVL99OmnyEmbed );
  }
})(window);