// import axios from 'axios';

const adminContext = {
    apiContext: {
        host: '',
        port: '',
        scheme: '',
        schemeAndHttpHost: '',
        uri: '',
        basePath: '',
        pathInfo: '',
        liveVersionId: '',
        systemLanguageId: '',
    },
    appContext: {
        features: [],
        firstRunWizard: 'true',
        systemCurrencyId: null,
    },
};

module.exports = (() => {
    jest.mock('axios');

    require('babel-plugin-require-context-hook/register')();
    const Shopware = require('src/core/shopware');
    global.Shopware = Shopware;

    require('src/app/main');
    Shopware.Application
        .initState()
        .registerConfig(adminContext)
        .initializeFeatureFlags();
})();
