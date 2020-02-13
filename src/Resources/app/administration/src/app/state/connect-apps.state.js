export default {
    namespaced: true,

    state() {
        return {
            apps: [],
        };
    },

    getters: {
        navigation(state) {
            return state.apps.reduce((previousValue, app) => {
                previousValue.push(...getNavigationForApps(app));
                return previousValue;
            }, []);
        },
    },

    mutations: {
        setApps(state, apps)  {
            state.apps = apps;
        },
    },

    actions: {
        fetchAppModules({ commit }) {
            const appModulesService = Shopware.Service('AppModulesService');
            return appModulesService.fetchAppModules().then((modules) => {
                commit('setApps', modules);
            });
        },
    },
};

function getNavigationForApps(app) {
    const locale = Shopware.State.get('session').currentLocale;
    const fallbackLocale = Shopware.Context.app.fallbackLocale;

    const appLabel = app.label[locale] || app.label[fallbackLocale];

    return app.modules.map((adminModule) => {
        const moduleLabel = adminModule.label[locale] || adminModule.label[fallbackLocale];

        return {
            id: `app-${app.name}-${adminModule.name}`,
            path: 'sw.my.apps.index',
            params: { appName: app.name, moduleName: adminModule.name },
            label: {
                translated: true,
                label: `${appLabel} - ${moduleLabel}`,
            },
            color: '#9AA8B5',
            parent: 'sw-my-apps',
            children: [],
        };
    });
}
