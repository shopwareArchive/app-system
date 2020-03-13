const productId = global.ProductFixtureService.createUuid();

describe('app theme system for storefront', () => {
    before(() => {
        cy.removeE2EApps()
            .cleanUpPreviousState()
            .installE2EApps([
                'themeApp',
            ]);
        cy.fixture('product')
            .then((productData) => {
                cy.searchViaAdminApi({
                    endpoint: 'sales-channel',
                    data: {
                        field: 'name',
                        value: 'Storefront',
                    },
                }).then((result) => {
                    return cy.createProduct({
                        ...productData,
                        id: productId,
                        visibilities: [
                            {
                                salesChannelId: result.id,
                                visibility: 30,
                            },
                        ],
                        categories: [
                            {
                                id: result.attributes.navigationCategoryId,
                            },
                        ],
                    });
                });
            });
    });

    it('CSS changes have effect if theme is active', () => {
        cy.activateThemeForStorefront('SwagTheme');

        cy.visit('/');

        cy.get('body')
            .should('have.css', 'background-color', 'rgb(255, 0, 0)');

        cy.get('.product-box')
            .should('be.visible')
            .should('have.css', 'background-color', 'rgb(0, 0, 0)');

        let alertText = '';
        cy.on('window:alert', (txt) => {
            alertText = txt;
        });

        cy.scrollTo('bottom')
            .wait(1)
            .then(() => {
                expect(alertText).to.match(/seems like there's nothing more to see here./);
            });
    });

    it('CSS changes have no effect if theme is deactivated', () => {
        cy.activateThemeForStorefront('Shopware default theme');

        cy.visit('/');

        cy.get('body')
            .should('not.have.css', 'background-color', 'rgb(255, 0, 0)');

        cy.get('.product-box')
            .should('be.visible')
            .should('not.have.css', 'background-color', 'rgb(0, 0, 0)');

        let alerted = false;
        cy.on('window:alert', () => {
            alerted = true;
        });

        cy.scrollTo('bottom')
            .wait(1)
            .then(() => {
                expect(alerted).to.equal(false);
            });
    });
});
