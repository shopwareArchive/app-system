function createProduct(productData) {
    return cy.request({
        method: 'POST',
        url: 'api/oauth/token',
        headers: {
            'content-type': 'application/json',
            accept: 'application/json',
        },
        body: {
            'grant_type': 'password',
            'client_id': 'administration',
            'scopes': 'write',
            'username': 'admin',
            'password': 'shopware',
        },
    }).then(({ body }) => {
        return cy.request({
            method: 'POST',
            url: '/api/v1/product',
            headers: {
                'content-type': 'application/json',
                accept: 'application/json',
                Authorization: `Bearer ${body['access_token']}`,

            },
            body: productData,
        });
    }).should((response) => {
        if (response.status !== 204) {
            cy.log(response.body);
        }
        expect(response.status).to.equals(204);
    });
}

export default function addProductCommand(Cypress)  {
    Cypress.Commands.add('createProduct', { prevSubject: false }, createProduct);
};
