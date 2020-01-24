
/**
 * Installs fixture apps on the server
 * @param {Array.<string>} apps 
 */
function installE2EApps(apps) {
    const proxyUrl = Cypress.config('cliProxyUrl');
    return cy.request({
        method: 'POST',
        url: `${proxyUrl}/install-e2e-apps`,
        Headers: {
            'content-type': 'application/json',
            accept: 'application/json',
        },
        body: { apps },
    }).its('statusCode').should('be', 204);
}

/**
 * Removes all fixture apps from the server
 */
function removeAllE2EApps() {
    const proxyUrl = Cypress.config('cliProxyUrl');

    return cy.request({
        method: 'DELETE',
        url: `${proxyUrl}/remove-e2e-apps`,
        Headers: {
            'content-type': 'application/json',
            accept: 'application/json',
        },
    }).its('statusCode').should('be', 204);
}

export default function addAppManagementCommands(Cypress) {
    Cypress.Commands.add('installE2EApps', { prevSubject: false }, installE2EApps);
    Cypress.Commands.add('removeE2EApps', { prevSubject: false }, removeAllE2EApps);
};
