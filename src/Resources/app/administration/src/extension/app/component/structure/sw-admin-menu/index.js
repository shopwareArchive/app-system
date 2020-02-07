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
            const myAppsEntryIndex = this.mainMenuEntries.findIndex((entry) => entry.id === 'sw-my-apps');
            if (myAppsEntryIndex <= -1) {
                return;
            }

            this.mainMenuEntries[myAppsEntryIndex].children = this.appEntries;
            this.mainMenuEntries[myAppsEntryIndex] = { ...this.mainMenuEntries[myAppsEntryIndex] };
        },
    },
};
