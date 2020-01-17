import template from './sw-page.html.twig';
import './sw-page.scss';

const {  Mixin } = Shopware;
const AppApiService = Shopware.Service('AppApiService');

export default {
    template,
    name: 'sw-page',

    data() {
        return {
            actions: null,
        };
    },

    computed: {
        params() {
            if(this.isListingPage) {
                return Object.keys(this.$parent.selection);
            } else if(this.$route.params.id) {
                return [this.$route.params.id];
            } else {
                return null;
            }
        },

        isListingPage() {
            const parentMixins = this.$parent.$options.mixins;
            const listingMixin = Mixin.getByName('listing');

            if (!parentMixins) {
                return false;
            }

            return parentMixins.find((mixin) => { return mixin === listingMixin; }) !== undefined;
        },
    },

    methods: {
        initPage() {
            this.$super('initPage');

            if (this.$route.meta.$module) {
                this.getActionButtons();
            }
        },

        getActionButtons() {
            const entity = this.module.entity;

            const view = Object.keys(this.module.routes).find((routeName) => {
                const symbol = this.module.routes[routeName].name;

                return new RegExp(`^${symbol}`).test(this.$route.name);
            });

            if(entity && view) {
                this.isLoading = true;

                AppApiService.getActionButtonsPerView(entity, view)
                    .then((actions) => {
                        this.actions = actions;
                        this.isLoading = false;
                    }).catch(() => {
                    this.isLoading = false;
                });
            }
        },
    },
};
