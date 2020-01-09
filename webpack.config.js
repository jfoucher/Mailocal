var Encore = require('@symfony/webpack-encore');

Encore
  .setOutputPath('public/builds/')
  .setPublicPath('/builds')
    .disableSingleRuntimeChunk()
  // this will be your app!
  .addEntry('app', './assets/js/app.js')
  .autoProvidejQuery()
  .enableSourceMaps(!Encore.isProduction())
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  // You need sass loader!
  .enableSassLoader()
;

module.exports = Encore.getWebpackConfig();
