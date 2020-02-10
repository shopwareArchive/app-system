function actionButtons() {
    cy.get('.sw-page div.smart-bar__content .sw-context-button').then((contextButton) => {
        if (!contextButton.hasClass('is--active')) {
            cy.wrap(contextButton).click();
        }

        return cy.get('.sw-context-button__menu-popover');
    });
}

export default function addActionButtonCommands(Cypress) {
    Cypress.Commands.add('actionButtons', { prevSubject: false }, actionButtons);
};
