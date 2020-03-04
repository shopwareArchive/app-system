/**
 * Navigates to a module in the administration
 * @param {string} themeName
 */
function activateThemeForStorefront(themeName) {
    cy.setLocaleToEnGb();
    cy.server();
    cy.login('admin');

    cy.get(`.sw-admin-menu__sales-channel-item--1 > a`).contains('Storefront');
    cy.get(`.sw-admin-menu__sales-channel-item--1`).click();
    cy.get('.sw-tabs-item').contains('Theme').click();
    cy.get('.sw-button').contains('Change theme').click();

    cy.get('.sw-theme-modal .sw-theme-list-item__title').contains(themeName).click();
    cy.get('.sw-theme-modal .sw-button--primary').contains('Save').click();

    cy.route({
        method: 'post',
        url: 'api/v1/_action/theme/**'
    }).as('themeAction');

    cy.get('.sw-modal .sw-button--primary').contains('Change theme').click();

    cy.wait('@themeAction');

    cy.get('.sw-sales-channel-detail__save-action').click();

}

export default function addThemeCommands(Cypress) {
    Cypress.Commands.add('activateThemeForStorefront', { prevSubject: false }, activateThemeForStorefront);
}
