const mix               = require('laravel-mix');
const glob              = require('glob');

mix.js('src/js/kitchen.js', 'js');

const sassSettings = {
  precision: 10,
};

mix.sass('src/scss/kitchen.scss', 'css', sassSettings);

mix.setPublicPath('assets');
mix.setResourceRoot('/assets/');

// mix.copyDirectory('src/images', 'assets/images');
mix.copyDirectory('src/sounds', 'assets/sounds');

mix.disableNotifications();

mix.options({
  autoprefixer: false,
});

mix.webpackConfig({
  output: {
    publicPath: '/assets/',
    chunkFilename: 'js/[name].bundle.js?v=[chunkhash:8]',
  },
  stats: {
    assets: false,
    chunks: false,
    hash: false,
  },
});
