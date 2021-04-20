const path = require('path');

module.exports = {
    devServer: {
        contentBase: path.resolve(__dirname, './src')
    },
    entry: path.resolve(__dirname, './src/index.js'),
    output: {
        path: path.resolve(__dirname, 'assets'),
        filename: 'wcfm-feature.js'
    },
    module: {
        rules: [
            {
                test: /\.js$/,
                use: 'babel-loader',
                exclude: '/node_modules/'
            }
        ]
    }
};