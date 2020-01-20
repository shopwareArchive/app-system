const emptyActionButtonList = {
    status: 200,
    statusText: 'OK',
    data: {
        actions: [],
    },
};

const actionButtons = {
    status: 200,
    statusText: 'OK',
    data: {
        actions: [
            {
                action: 'doStuffWithProducts',
                app: 'SwagApp',
                id: '28e76437999b41d0b8e87e9aab44e41b',
                label: {
                    'en-GB': 'Do Stuff',
                },
                openNewTab: false,
                url: 'https://swag-test.com/do-stuff',
            },
        ],
    },
};

const malformedList = {
    status: 200,
    statusText: 'OK',
    data: [
        {
            action: 'doStuffWithProducts',
            app: 'SwagApp',
            id: '28e76437999b41d0b8e87e9aab44e41b',
            label: {
                'en-GB': 'Do Stuff',
            },
            openNewTab: false,
            url: 'https://swag-test.com/do-stuff',
        },
    ],
};

const emptyResponse = {
    status: 200,
    statusText: 'OK',
    data: [],
};

export default {
    emptyActionButtonList,
    malformedList,
    actionButtons,
    emptyResponse,
};
