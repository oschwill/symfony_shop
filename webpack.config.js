// webpack.config.js
const Encore = require("@symfony/webpack-encore");
const Dotenv = require("dotenv-webpack");

Encore.setOutputPath("public/build/")
  .setPublicPath("/build")
  .addEntry("app", "./assets/app.js")
  .addEntry("external", "./assets/_externals.js")
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
  .cleanupOutputBeforeBuild()
  .enableBuildNotifications()
  .enableSourceMaps(!Encore.isProduction())
  .enableVersioning(Encore.isProduction())
  .copyFiles({
    from: "./assets/images",
    to: "images/[path][name].[ext]",
  })
  .configureBabel((config) => {
    config.plugins.push("@babel/plugin-proposal-class-properties");
  })
  .enableSassLoader()
  .enableStimulusBridge("./assets/controllers.json")
  .addPlugin(
    new Dotenv({
      path: ".env.local",
      defaults: ".env",
      systemvars: true,
      allowEmptyValues: true,
      debug: true, // Füge dies hinzu, um Debug-Informationen zu erhalten
    })
  );

module.exports = Encore.getWebpackConfig();
module.exports.mode = "development";
