const productId = global.ProductFixtureService.createUuid();

describe('action buttons in administration', () => {
    before(() => {
        cy.removeE2EApps()
            .cleanUpPreviousState()
            .installE2EApps([
                'productApp',
            ]);
        cy.fixture('product')
            .then((productData) => {
                return cy.createProduct({
                    ...productData,
                    id: productId,
                });
            });
    });

    it('has actions in listing page', () => {
        cy.setLocaleToEnGb();
        cy.server();
        cy.login('admin');

        cy.navigateAdministration('Catalogues', 'Products');

        // no actions if no row is selected
        cy.get('.sw-page div.smart-bar__content .sw-context-button')
            .should('not.exist');

        // select product with name 'product as a service'
        cy.inspectDataGrid()
            .findRowWith('Name', 'product as a service')
            .setRowSelected();

        cy.route({
            method: 'post',
            url: 'api/v1/app-system/action-button/run/**',
            response: {},
        }).as('productAction');

        // run product action
        cy.actionButtons()
            .contains('Do Stuff')
            .click();

        cy.wait('@productAction').then((xhr) => {
            expect(xhr.requestBody).to.eql({ ids: [productId] });
        });
    });

    it('has actions in detail page', () => {
        cy.setLocaleToEnGb();
        cy.server();
        cy.login('admin');

        cy.navigateAdministration('Catalogues', 'Products');

        // select product with name 'product as a service'
        cy.inspectDataGrid()
            .findRowWith('Name', 'product as a service')
            .inColumn('Name', (cell) => {
                cy.wrap(cell).find('a').click();
            });

        cy.get('.sw-page .smart-bar__header h2').contains('product as a service');

        // test icon
        cy.actionButtons()
            .contains('Action product detail extern')
            .within(() => {
                cy.get('.sw-connect-action-button__icon').should('be.visible');
            });

        cy.actionButtons()
            .contains('Action product detail extern')
            .should('have.attr', 'target', '_blank')
            .find('span.icon--default-action-external')
            .should('be.visible');

        cy.route({
            method: 'post',
            url: 'api/v1/app-system/action-button/run/**',
            response: {},
        }).as('productAction');

        cy.actionButtons()
            .find('div.sw-connect-action-button')
            .contains('Action product detail')
            .click();

        cy.wait('@productAction').then((xhr) => {
            expect(xhr.requestBody).to.eql({ ids: [productId] });
        });
    });

    it('has no actions in create page', () => {
        cy.setLocaleToEnGb();
        cy.server();
        cy.login('admin');

        cy.navigateAdministration('Catalogues', 'Products');
        cy.get('.sw-button').contains('Add product').click();

        cy.get('.sw-page .smart-bar__header h2').contains('New product');
        cy.get('.sw-page div.smart-bar__content .sw-context-button')
            .should('not.exist');
    });
});
