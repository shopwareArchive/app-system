const shopwareE2ePlugin = require('@shopware-ag/e2e-testsuite-platform/cypress/plugins');

const setUpUrls = (on, config) => {
    const url = `${config.env.schema}://${config.env.host}`;

    config.baseUrl = `${url}:${config.env.port}`;
    config.cliProxyUrl = `${url}:${config.env.cliProxy.port}`;
};

module.exports = (on, config) => {
    shopwareE2ePlugin(on, config);
    setUpUrls(on, config);

    return config;
};
