module.exports = {
    extends: '../common/.eslintrc.js',
    env: {
        browser: true,
        es6: true,
    },

    globals: {
        Shopware: true
    },

    parserOptions: {
        ecmaVersion: 6,
        sourceType: 'module'
    }
};
