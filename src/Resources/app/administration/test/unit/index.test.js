describe('index.js', () => {
    test('It registers services after install method', () => {
        const swaas = require('connect/core/index.js').default;
        
        swaas.install(Shopware);
        expect(Shopware.Service('AppActionButtonService')).toBeDefined();
    });
});
