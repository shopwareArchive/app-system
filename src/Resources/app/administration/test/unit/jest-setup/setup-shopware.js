const adminContext = {
    apiContext: {
        host: 'www.shopware-test.de',
        port: '80',
        scheme: 'http',
        schemeAndHttpHost: 'http://www.shopware-test.de:80',
        uri: 'http://www.shopware-test.de:80/admin',
        basePath: '',
        pathInfo: '/admin',
        liveVersionId: '0fa91ce3e96a4bc2be4bd9ce752c3425',
        systemLanguageId: '2fbb5fe2e29a4d70aa5854ce7ce3e20b',
        apiVersion: 1,
    },
    appContext: {
        features: [],
        firstRunWizard: 'true',
        systemCurrencyId: 'b7d2554b0ce847cd82f3ac9bd1c0dfca',
    },
};

module.exports = (() => {
    require('babel-plugin-require-context-hook/register')();
    const Shopware = require('src/core/shopware');
    const connect = require('connect/core').default;

    global.Shopware = Shopware;

    require('src/app/main');
    Shopware.Application
        .initState()
        .registerConfig(adminContext)
        .initializeFeatureFlags();
    connect.install(Shopware);
})();
