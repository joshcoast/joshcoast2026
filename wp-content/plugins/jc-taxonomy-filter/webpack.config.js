const defaultConfig = require("@wordpress/scripts/config/webpack.config");
const path = require("path");

module.exports = {
	...defaultConfig,
	entry: {
		index: path.resolve(__dirname, "src", "index.js"),
		frontend: path.resolve(__dirname, "src", "frontend.js"),
		style: path.resolve(__dirname, "src", "style.scss"),
		editor: path.resolve(__dirname, "src", "editor.scss"),
	},
	output: {
		...defaultConfig.output,
		path: path.resolve(__dirname, "build"),
	},
};
