describe('custom modules in administration', () => {
    before(() => {
        cy.removeE2EApps()
            .cleanUpPreviousState()
            .installE2EApps([
                'productApp',
            ]);
        cy.fixture('product')
            .then((productData) => {
                return cy.createProduct(productData);
            });
    });

    it('it adds a new main menu entry', () => {
        cy.setLocaleToEnGb();
        cy.server();
        cy.login('admin');

        cy.get('.sw-admin-menu li.sw-my-apps').should('be.visible');
    });

    it('shows content of the app', () => {
        cy.setLocaleToEnGb();
        cy.server();
        cy.login('admin');

        cy.navigateAdministration('My apps', 'E2E_Product_label_en - Product module');

        cy.location().should((location) => {
            expect(location.hash).to.eq('#/my-apps/E2E_Product/external-module');
        });

        cy.get('.sw-page__main-content').within(() => {
            cy.get('iframe#app-content').should('be.visible');
        });
    });

    it('shows illustration if module could not load', () => {
        cy.setLocaleToEnGb();
        cy.server();
        cy.login('admin');

        cy.navigateAdministration('My apps', 'E2E_Product_label_en - 404 module');

        cy.location().should((location) => {
            expect(location.hash).to.eq('#/my-apps/E2E_Product/external-module-broken');
        });

        cy.get('.sw-page__main-content').within(() => {
            cy.get('.sw-loader').should('be.visible');
            cy.get('div.sw-my-apps-timeout-animation').should('be.visible');
        });
    });
});
