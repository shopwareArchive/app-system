describe('Test administration is available', () => {
    it('open administration', () =>{
        cy.visit('/admin');
        cy.title().should('eq', 'Login | Shopware Administration');
    })
});
