const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const CssMinimizerPlugin = require('css-minimizer-webpack-plugin');
const RemoveEmptyScriptsPlugin = require('webpack-remove-empty-scripts');

module.exports = (env, argv) => {
  const isProduction = argv.mode === 'production';
  const envSourceMap = env?.sourcemap === true || env?.sourcemap === 'true';
  const processSourceMap = process.env.SOURCE_MAPS === 'true';
  const enableSourceMaps = !isProduction || envSourceMap || processSourceMap;

  return {
    entry: {
      style: './src/styles/style.scss',
      'theme-arcade': './src/styles/theme-arcade.scss',
      'theme-stripes': './src/styles/theme-stripes.scss',
    },
    output: {
      path: path.resolve(__dirname, 'assets/css'),
      filename: '[name].js',
      clean: false,
    },
    module: {
      rules: [
        {
          test: /\.s?css$/i,
          use: [
            MiniCssExtractPlugin.loader,
            {
              loader: 'css-loader',
              options: {
                sourceMap: enableSourceMaps,
                url: false,
              },
            },
            {
              loader: 'postcss-loader',
              options: {
                sourceMap: enableSourceMaps,
              },
            },
            {
              loader: 'sass-loader',
              options: {
                sourceMap: enableSourceMaps,
              },
            },
          ],
        },
      ],
    },
    plugins: [
      new RemoveEmptyScriptsPlugin(),
      new MiniCssExtractPlugin({
        filename: '[name].min.css',
      }),
    ],
    optimization: {
      minimize: isProduction,
      minimizer: [
        '...',
        new CssMinimizerPlugin(),
      ],
    },
    devtool: enableSourceMaps ? 'source-map' : false,
  };
};
