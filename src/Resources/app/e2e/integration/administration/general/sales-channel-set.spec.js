describe('general', () => {
    it('uses ', () => {
        cy.setLocaleToEnGb();
        cy.server();
        cy.login('admin');

        cy.get('.sw-admin-menu__sales-channel-item--1 > a').contains('Storefront').click();

        cy.get('.sw-card')
            .contains('div.sw-card__title', 'Domains')
            .parent('.sw-card')
            .within(() => {
                cy.inspectDataGrid()
                    .findRowsWith('URL', Cypress.config('baseUrl'))
                    .should('exist');
            });

        cy.visit('/');
        cy.title().should('equal', 'Catalogue #1');
    });
});
