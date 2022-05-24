const path = require( 'path' );
const defaults = require("@wordpress/scripts/config/webpack.config");

module.exports = {
    ...defaults,
	externals: {
		"react": "React",
		"react-dom": "ReactDOM"
	},
    ...{
        entry: {
            "topic": './assets/js/src/topic.js',
        },
		output: {
			filename: '[name].js',
			path: path.resolve( process.cwd(), 'assets/js/build' ),
		},
    }
}
