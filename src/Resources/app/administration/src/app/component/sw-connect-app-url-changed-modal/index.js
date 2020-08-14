import template from './sw-connect-app-url-changed-modal.html.twig';
import './sw-connect-app-url-changed-modal.scss';

const { Service } = Shopware;

export default {
    name: 'sw-connect-app-url-changed-modal',
    template,

    mixins: [Shopware.Mixin.getByName('notification')],

    props: {
        urlDiff: {
            type: Object,
            required: true,
        },
    },

    data() {
        return {
            strategies: [],
            selectedStrategy: null,
            isLoading: true,
        };
    },

    computed: {
        appUrlChangeService() {
            return Service('AppUrlChangeService');
        },
    },

    created() {
        this.appUrlChangeService
            .fetchResolverStrategies()
            .then((strategies) => {
                this.strategies = strategies;
                this.selectedStrategy = strategies[0];
            })
            .then(() => this.isLoading = false);
    },

    methods: {
        closeModal() {
            this.$emit('modal-close');
        },

        isSelected({ name }) {
            return !!this.selectedStrategy && this.selectedStrategy.name === name;
        },

        getStrategyLabel({ name }) {
            return this.$tc(`sw-connect.component.sw-connect-app-url-changed-modal.${name}.name`);
        },

        getStrategyDescription({ name }) {
            return this.$tc(`sw-connect.component.sw-connect-app-url-changed-modal.${name}.description`);
        },

        getActiveStyle({ name }) {
            return {
                'sw-connect-app-url-changed-modal__content-migration-strategy--active': name === this.selectedStrategy.name,
            };
        },

        confirm() {
            this.appUrlChangeService.resolveUrlChange(this.selectedStrategy)
                .then(() => {
                    this.createNotificationSuccess({
                        message: this.$tc('sw-connect.component.sw-connect-app-url-changed-modal.success'),
                    });
                })
                .catch(() => {
                    this.createNotificationError({
                        message: this.$tc('sw-connect.component.sw-connect-app-url-changed-modal.error'),
                    });
                })
                .then(this.closeModal);
        },
    },
};
