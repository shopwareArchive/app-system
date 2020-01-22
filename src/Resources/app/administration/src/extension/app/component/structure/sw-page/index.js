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
        appApiService() {
            return Shopware.Service('AppApiService');
        },

        params() {
            if(this.isListingPage) {
                return Object.keys(this.$parent.selection);
            }
            
            if(this.$route.params.id) {
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

    mounted() {
        if (this.$route.meta.$module) {
            this.getActionButtons();
        }
    },

    methods: {
        getActionButtons() {
            const entity = this.module.entity;

            const view = Object.keys(this.module.routes).find((routeName) => {
                const symbol = this.module.routes[routeName].name;

                return this.$route.name.startsWith(symbol);
            });

            if(entity && view) {
                this.isLoading = true;

                this.appApiService.getActionButtonsPerView(entity, view)
                    .then((actions) => {
                        this.actions = actions;
                        this.isLoading = false;
                    }).catch(() => {
                    this.isLoading = false;
                });
            }
        },

        runAction(actionId) {
            this.appApiService.runAction(actionId, { ids: this.params });
        },
    },
};
