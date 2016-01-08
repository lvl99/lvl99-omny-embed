var gulp        = require('gulp'),
    gutil       = require('gulp-util'),
    browserify  = require('browserify'),
    buffer      = require('vinyl-buffer'),
    concat      = require('gulp-concat'),
    del         = require('del'),
    less        = require('gulp-less'),
    prefix      = require('gulp-autoprefixer'),
    rename      = require('gulp-rename'),
    source      = require('vinyl-source-stream'),
    sourcemaps  = require('gulp-sourcemaps'),
    transform   = require('vinyl-transform'),
    uglify      = require('gulp-uglify'),
    uglifycss   = require('gulp-uglifycss'),
    watch       = require('gulp-watch'),
    watchify    = require('watchify');

// Fix gulp's error handling
// See: https://github.com/gulpjs/gulp/issues/71
var origSrc = gulp.src;
gulp.src = function () {
    return fixPipe(origSrc.apply(this, arguments));
};
function fixPipe(stream) {
  var origPipe = stream.pipe;
  stream.pipe = function (dest) {
    arguments[0] = dest.on('error', function (error) {
      var nextStreams = dest._nextStreams;
      if (nextStreams) {
        nextStreams.forEach(function (nextStream) {
          nextStream.emit('error', error);
        });
      } else if (dest.listeners('error').length === 1) {
        throw error;
      }
    });
    var nextStream = fixPipe(origPipe.apply(this, arguments));
    (this._nextStreams || (this._nextStreams = [])).push(nextStream);
    return nextStream;
  };
  return stream;
}

function Bundler( src, file, dest, destFile ) {
  var self = this;

  // Properties
  self.src = src;
  self.file = file;
  self.dest = dest;
  self.destFile = destFile
  self.watchify = watchify( browserify( self.src + '/' + self.file, watchify.args ) );

  // Method
  self.bundle = function () {
    return self.watchify.bundle()
      .on( 'error', gutil.log.bind( gutil, 'Browserify Error' ) )
      .pipe( source(self.destFile) )
      // Sourcemaps
        // .pipe( buffer() )
        // .pipe( sourcemaps.init({ loadMaps: true }) )
        // .pipe( sourcemaps.write('./') )
      //
      .pipe( gulp.dest(self.dest) );
  };

  // Events
  self.watchify.on( 'update', self.bundle );
  self.watchify.on( 'log', gutil.log );
}

// JavaScript bundlers
var app = new Bundler( './js', 'lvl99-omny-embed.dev.js', './js', 'lvl99-omny-embed.js' );
gulp.task('app', app.bundle);


/*
 * Task recipes
 */

gulp.task('default', ['build', 'watch']);
gulp.task('watch', ['watchfiles']);
gulp.task('build', ['less', 'app']);
gulp.task('compress', ['compressjs', 'compresscss']);
gulp.task('production', ['build', 'compress']);


/*
 * Tasks
 */

// JS dependencies (ones which browserify doesn't include)
gulp.task('jsdependencies', function () {
  // Normal
  return gulp.src(['./lib/js/**/*.js'])
    .pipe( concat('dependencies.js') )
    .pipe( gulp.dest('./js') );
});

// LESS
gulp.task('less', function () {
  return gulp.src(['./css/lvl99-omny-embed.less'])
    .pipe( less() )
    .pipe( prefix({
      browsers: [ 'last 2 versions', '> 1%' ],
      cascade:  true
    }) )
    .pipe( gulp.dest('./css') );
});

// Compress for production
gulp.task('compressjs', ['jsdependencies', 'app'], function () {
  return gulp.src(['./js/lvl99-omny-embed.js'/*,
                   './js/dependencies.js'*/])
    .pipe( uglify() )
    .pipe( rename({
      extname: '.min.js'
    }) )
    .pipe( gulp.dest('./js') );
});

gulp.task('compresscss', ['less'], function () {
  return gulp.src(['./css/lvl99-omny-embed.css',
                   '!./css/*.min.css']) // Ignore already minified files
    .pipe( uglifycss() )
    .pipe( rename({
      extname: '.min.css'
    }) )
    .pipe( gulp.dest('./css') );
});

// Watch Files
gulp.task('watchfiles', function () {
  var watchcss    = gulp.watch( './css/**/*.less', ['less'] ),
      watchjs     = gulp.watch( './lib/js/**/*.js', ['jsdependencies'] );
});
