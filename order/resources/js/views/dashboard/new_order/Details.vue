<template>
    <div 
        v-for="component in $store.getters.getOrderedDetailsComponents" 
        :value="component" 
        :key="component.sequence"
    >
        <component v-if="component.show" :is="component.name"></component>
    </div>
</template>

<script>
export default {
    components: {},
    computed: {
        orders () {return this.$store.state.moduleOrders},
        basicData() {return this.$store.state.moduleOrders.basicData},
    },
    created() {
        // Load all detail components
        this.$store.state.moduleOrders.details.components.forEach(component => {
            this.$options.components[component.name] = component.component.default
        })
    },
    mounted () {
        this.basicData.hasContinuedToDetails = true
        this.$store.commit('setDashboardTitle', {
            title: () => this.$translations.newOrder.title,
            subTitle: () => this.$store.getters.getOrderTypeTitle
        })
    }
}
</script>
