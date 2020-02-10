function cleanUpPreviousState() {
    const proxyUrl = Cypress.config('cliProxyUrl');
    return cy.request({
        method: 'DELETE',
        url: `${proxyUrl}/cleanup`,
        Headers: {
            'content-type': 'application/json',
            accept: 'application/json',
        },
    }).should((res) => {
        const body = JSON.parse(res.body);
        const dbName = body.message.match(/.* --port='\d+' '(.*)'.*/)[1];
        expect(res.status).to.equal(200);
        expect(body.code).to.equal(0);
        expect(dbName).to.equal('shopware_e2e');
    });
}

export default function addSystemCommands(Cypress) {
    Cypress.Commands.overwrite('cleanUpPreviousState', cleanUpPreviousState);
};
