import template from './sw-connect-action-button.html.twig';
import './sw-connect-action-button.scss';

export default {
    template,
    name: 'sw-connect-action-button',

    props: {
        action: {
            type: Object,
            required: true,
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

            this.$emit('run-app-action', this.action.id);
        },
    },
};

