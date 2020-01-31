const WebpackAssetsManifest = require("webpack-assets-manifest");
const scssToLoad = `
  @import "@/scss/utils/load.scss";
`;
module.exports = {
  css: {
    loaderOptions: {
      sass: {
        implementation: require("sass"),
        prependData: scssToLoad,
      },
    },
  },
  configureWebpack: config => {
    config.plugins = config.plugins.concat(
      new WebpackAssetsManifest({
        output: "asset-manifest.json",
      }),
    );
  },
};
