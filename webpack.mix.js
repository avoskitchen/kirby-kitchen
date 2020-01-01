const mix = require('laravel-mix');

mix.js('src/js/kitchen.js', 'js');
mix.sass('src/scss/kitchen.scss', 'css');

mix.setPublicPath('assets');
mix.setResourceRoot('/assets/');

mix.disableNotifications();

mix.webpackConfig({
  output: {
    publicPath: '/assets/',
    chunkFilename: 'js/[name].bundle.js?v=[chunkhash:8]',
  },
  watchOptions: {
    ignored: /node_modules/,
  },
  stats: {
    assets: false,
    chunks: false,
    hash: false,
  },
});
