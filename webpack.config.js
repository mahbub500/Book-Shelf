const path = require("path");

module.exports = (env, argv) => {
    return {
        mode: argv.mode || "development",
        entry: {
            tailwind: path.resolve(__dirname, "Asstes/common/css/tailwind.css"),

        },
        output: {
            filename: "[name].bundle.js",
            path: path.resolve(__dirname, "build"),
        },
        module: {
            rules: [
               
                {
                    test: /\.css$/,
                    use: [
                        "style-loader",
                        "css-loader",
                        {
                            loader: "postcss-loader",
                            options: {
                                postcssOptions: {
                                    ident: "postcss",
                                    plugins: [
                                        require("tailwindcss"),
                                        require("autoprefixer"),
                                    ],
                                },
                            },
                        },
                    ],
                },
            ],
        },
        resolve: {
            extensions: [".js", ".jsx"],
        },
        externals: {
            react: "React",
            "react-dom": "ReactDOM",
            "@wordpress/blocks": ["wp", "blocks"],
            "@wordpress/block-editor": ["wp", "blockEditor"],
            "@wordpress/element": ["wp", "element"],
        },
        devtool: "source-map",
    };
};
