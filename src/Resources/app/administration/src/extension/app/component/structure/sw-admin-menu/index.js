export default {
    name: 'sw-admin-menu',

    computed: {
        appEntries() {
            return Shopware.State.getters['connect-apps/navigation'];
        },
    },

    watch: {
        appEntries() {
            this.updateAppEntries();
        },

        mainMenuEntries() {
            this.updateAppEntries();
        },
    },

    methods: {
        updateAppEntries() {
            const entryIndex = this.mainMenuEntries.findIndex((entry) => entry.id === 'sw-my-apps');

            if (entryIndex < 0) {
                return;
            }

            const myAppsEntry = this.mainMenuEntries[entryIndex];
            const newEntries = this.getNewEntries(myAppsEntry.children, this.appEntries);

            myAppsEntry.children =  [...myAppsEntry.children, ...newEntries];

            this.mainMenuEntries[entryIndex] = { ...myAppsEntry };
        },

        getNewEntries(actualNavigationEntries, appNavigationEntries) {
            return appNavigationEntries.filter((appNavigationEntry) => {
                return !actualNavigationEntries.some((actualEntry) => {
                    return actualEntry.path === appNavigationEntry.path;
                });
            });
        },
    },
};
