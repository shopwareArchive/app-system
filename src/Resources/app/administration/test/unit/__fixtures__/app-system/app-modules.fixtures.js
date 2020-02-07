
const emptyModuleList = {
    status: 200,
    statusText: 'OK',
    data: {
        modules: [],
    },
};

const appsAndModules = {
    status: 200,
    statusText: 'OK',
    data: {
        modules: [
            {
                name: 'E2E_Product',
                label: {
                    'de-DE': 'E2E_Product_label_de',
                    'en-GB':'E2E_Product_label_en',
                },
                modules: [
                    {
                        name: 'external-module',
                        label: {
                            'de-DE': 'Produktmodul',
                            'en-GB':'Product module',
                        },
                        source: 'http://localhost:8005/show-app-action',
                    },
                    {
                        name: 'external-module-broken',
                        label: {
                            'de-DE': '404 Modul',
                            'en-GB':'404 module',
                        },
                        source: 'http://localhost:8005/somewhere',
                    },
                ],
            },
            {
                name: 'SwagApp',
                label: {
                    'de-DE': 'Swag App Test',
                    'en-GB': 'Swag App Test',
                },
                modules: [
                    {
                        name: 'first-module',
                        label: {
                            'de-DE': 'Mein erstes eigenes Modul',
                            'en-GB': 'My first own module',
                        },
                        source: 'https://test.com',
                    },
                    {
                        name: 'second-module',
                        label: {
                            'en-GB': 'My second module',
                        },
                        source:'https://test.com/second',
                    },
                ],
            },
        ],
    },
};

const malformedModulesList = {
    status: 200,
    statusText: 'OK',
    data: [
        {
            name: 'first-module',
            label: {
                'de-DE': 'Mein erstes eigenes Modul',
                'en-GB': 'My first own module',
            },
            source: 'https://test.com',
        },
        {
            name: 'second-module',
            label: {
                'en-GB': 'My second module',
            },
            source:'https://test.com/second',
        },
    ],
};

export default {
    emptyModuleList,
    appsAndModules,
    malformedModulesList,
};
