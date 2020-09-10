import swMyAppsPage from './page/sw-my-apps-page';

export default {
    type: 'plugin',
    name: 'sw-my-apps',
    title: 'sw-connect.module.sw-my-apps.general.mainMenuItemGeneral',
    description: 'sw-connect.module.sw-my-apps.general.moduleDescription',
    icon: 'default-view-grid',
    color: '#9AA8B5',
    routePrefixPath: 'my-apps',

    components: {
        'sw-my-apps-page': swMyAppsPage,
    },

    routes: {
        index: {
            component: 'sw-my-apps-page',
            path: ':appName/:moduleName',
            props: {
                default(route) {
                    const { appName, moduleName } = route.params;
                    return {
                        appName,
                        moduleName,
                    };
                },
            },
        },
    },

    navigation: [{
        id: 'sw-my-apps',
        label:'sw-connect.module.sw-my-apps.general.mainMenuItemGeneral',
        icon: 'default-view-grid',
        color: '#9AA8B5',
        position: 100,
    }],
};

