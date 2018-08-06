const Path = require('path');
const webpack = require('webpack');
// Import the core config
const webpackConfig = require('@silverstripe/webpack-config');
const {
    resolveJS,
    externalJS,
    moduleJS,
    pluginJS,
    moduleCSS,
    pluginCSS,
} = webpackConfig;

const ENV = process.env.NODE_ENV;
const PATHS = {
    // your node_modules folder name, or full path
    MODULES: 'node_modules',
    // relative path from your css files to your other files, such as images and fonts
    FILES_PATH: '../',
    // the root path, where your webpack.config.js is located.
    ROOT: Path.resolve(),
    // the root path to your javascript source files
    SRC: Path.resolve('client/src'),
    // the root path to your javascript dist files
    DIST: Path.resolve('client/dist'),
    // thirdparty folder containing copies of packages which wouldn't be available on NPM
    THIRDPARTY: 'thirdparty',

};

const config = [
    {
        name: 'js',
        entry: [
            `${PATHS.SRC}/js/clubmaster.js`
        ],
        output: {
            path: PATHS.DIST,
            filename: 'js/[name].js',
            //filename: 'js/clubmaster.js'
        },
        devtool: (ENV !== 'production') ? 'source-map' : '',
        resolve: resolveJS(ENV, PATHS),
        externals: externalJS(ENV, PATHS),
        module: moduleJS(ENV, PATHS),
        plugins: pluginJS(ENV, PATHS),
    },
    {
        name: 'css',
        entry: [
            `${PATHS.SRC}/styles/clubmaster.scss`
        ],
        output: {
            path: PATHS.DIST,
            filename: 'styles/[name].css',
            //filename: 'styles/clubmaster.css'
        },
        devtool: (ENV !== 'production') ? 'source-map' : '',
        module: moduleCSS(ENV, PATHS),
        plugins: pluginCSS(ENV, PATHS),
    }

];

module.exports = config;
