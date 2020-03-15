import template from './sw-page.html.twig';
import './sw-page.scss';

const {  Mixin } = Shopware;

export default {
    template,
    name: 'sw-page',

    data() {
        return {
            actions: null,
        };
    },

    computed: {
        appActionButtonService() {
            return Shopware.Service('AppActionButtonService');
        },

        params() {
            if (this.isListingPage) {
                return Object.keys(this.$parent.selection);
            }
            
            if (this.$route.params.id) {
                return [this.$route.params.id];
            }
            
            return [];
        },

        isListingPage() {
            const parentMixins = this.$parent.$options.mixins;
            const listingMixin = Mixin.getByName('listing');

            if (!parentMixins) {
                return false;
            }

            return parentMixins.find((mixin) => { return mixin === listingMixin; }) !== undefined;
        },

        areActionsAvailable() {
            return !!this.actions && this.actions.length > 0
                && this.params.length > 0;
        },
    },

    watch: {
        $route: {
            immediate: true,
            async handler(newVal, oldVal) {
                if (!oldVal) {
                    this.actions = await this.getActionButtons(newVal);
                    return;
                }

                if (this.didViewChange(newVal, oldVal)) {
                    this.actions = await this.getActionButtons(newVal);
                }
            },
        },
    },

    methods: {
        async getActionButtons(newRoute) {
            const module = newRoute.meta.$module;
            if (!module) {
                return [];
            }

            const entity = module.entity;
            const view = this.getViewForRoute(newRoute);

            if (!entity || !view) {
                return [];
            }

            try {
                this.isLoading = true;
                return await this.appActionButtonService.getActionButtonsPerView(entity, view);
            } finally {
                this.isLoading = false;
            }
        },

        runAction(actionId) {
            this.appActionButtonService.runAction(actionId, { ids: this.params });
        },

        getViewForRoute(route) {
            const module = route.meta.$module;

            if (!module) {
                return undefined;
            }

            return Object.keys(module.routes).find((routeName) => {
                const symbol = module.routes[routeName].name;

                return route.name.startsWith(symbol);
            });
        },

        didViewChange(newRoute, oldRoute) {
            const oldEntity = oldRoute.meta.$module ? oldRoute.meta.$module.entity : undefined; 
            const newEntity = newRoute.meta.$module ? newRoute.meta.$module.entity : undefined;

            if (oldEntity !== newEntity) {
                return true;
            }

            const oldView = this.getViewForRoute(oldRoute);
            const newView = this.getViewForRoute(newRoute);

            return oldView !== newView;
        },

    },
};
