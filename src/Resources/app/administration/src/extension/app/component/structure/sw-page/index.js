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
            handler(newVal, oldVal) {
                if (!oldVal) {
                    this.getActionButtons();
                    return;
                }

                if (this.didViewChange(newVal, oldVal)) {
                    this.getActionButtons();
                }
            },
        },
    },

    methods: {
        getActionButtons() {
            const module = this.$route.meta.$module;
            if (!module) {
                return;
            }

            const entity = module.entity;

            const view = this.getViewForRoute(this.$route);

            if (entity && view) {
                this.isLoading = true;

                this.appActionButtonService.getActionButtonsPerView(entity, view)
                    .then((actions) => {
                        this.actions = actions;
                        this.isLoading = false;
                    }).catch(() => {
                    this.isLoading = false;
                });
            }
        },

        runAction(actionId) {
            this.appActionButtonService.runAction(actionId, { ids: this.params });
        },

        getViewForRoute(route) {
            const module = route.meta.$module;
            return Object.keys(module.routes).find((routeName) => {
                const symbol = module.routes[routeName].name;

                return route.name.startsWith(symbol);
            });
        },

        didViewChange(newRoute, oldRoute) {
            if (newRoute.meta.$module.entity !== oldRoute.meta.$module.entity) {
                return true;
            }

            const oldView = this.getViewForRoute(oldRoute);
            const newView = this.getViewForRoute(newRoute);

            return oldView !== newView;
        },
    },
};
