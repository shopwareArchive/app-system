/**
 * Navigates to a module in the administration
 * @param {string} mainNavigation 
 * @param {string} subNavigation 
 */
function navigateAdministration(mainNavigation, subNavigation) {
    cy.get('aside.sw-admin-menu').within(($adminMenu) => {
        // ensure admin menu is expanded
        if ($adminMenu.hasClass('is--collapsed')) {
            cy.get('button.sw-admin-menu__toggle').click();
        }
    });

    cy.get('li.sw-admin-menu__navigation-list-item').contains(mainNavigation).click();

    if (subNavigation) {
        cy.get('li.sw-admin-menu__navigation-list-item').contains(subNavigation).click();
    }
}

/**
 * Opens settings module in administration
 */
function openSettings() {
    cy.navigateAdministration('Settings');
}

export default function addNavigationCommands(Cypress) {
    Cypress.Commands.add('navigateAdministration', { prevSubject: false }, navigateAdministration);
    Cypress.Commands.add('openSettings', { prevSubject: false }, openSettings);
}

