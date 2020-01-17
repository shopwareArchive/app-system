import template from './sw-connect-action-button.html.twig';
import './sw-connect-action-button.scss';

const AppApiService = Shopware.Service('AppApiService');

export default {
    template,
    name: 'sw-connect-action-button',

    props: {
        action: {
            type: Object,
            required: true,
        },
        params: {
            type: Array,
            required: false,
            default: null,
        },
    },

    computed: {
        buttonLabel() {
            const currentLocale = Shopware.State.get('session').currentLocale;
            const fallbackLocale = Shopware.Context.app.fallbackLocale;

            return this.action.label[currentLocale] || this.action.label[fallbackLocale] || '';
        },

        openInNewTab() {
            return !!this.action.openNewTab;
        },

        linkData() {
            if (this.openInNewTab) {
                return {
                    target: '_blank',
                    href: this.action.url,
                };
            }

            return {};
        },
    },

    methods: {
        runAction() {
            if (this.openInNewTab) {
                return;
            }

            AppApiService.runAction(this.action.id, { 'itemIds': this.params });
        },
    },
};

