module.exports = {
    filenameHashing: false,
    publicPath: process.env.NODE_ENV === 'production'
        ? './'
        : '/',

    // publicPath: '',
    // publicPath: process.env.VUE_APP_BASE_URL,
    outputDir: "dist",
    lintOnSave: true,
    runtimeCompiler: false,
    productionSourceMap: false,
}
// https://angela52799.medium.com/vue-cli-3-%E9%85%8D%E7%BD%AE%E8%88%87%E9%9D%9C%E6%85%8B%E6%AA%94%E5%BC%95%E7%94%A8%E8%B7%AF%E5%BE%91-82c101ad601f