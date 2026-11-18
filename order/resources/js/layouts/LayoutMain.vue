<template>
    <dialogbox></dialogbox>
    <navbar></navbar>
    <div class="app-layout row">

        <!-- Notification messages -->
        <div :class="dashboardEmptyColStyle" />
        <notificationbar :class="dashboardColStyle"></notificationbar>

        <!-- Load data -->
        <initializator></initializator>

        <!-- Dashboard title and subtitle -->
        <div :class="dashboardEmptyColStyle" />
        <div :class="dashboardColStyle" id="dashboard-title">
            <h4 class="dashboard-title" v-html="$store.state.moduleOrders.dashboardTitle.title"></h4>
            <span class="dashboard-sub-title" v-html="$store.state.moduleOrders.dashboardTitle.subTitle"></span>
        </div>

        <!-- Sidebar -->
        <div v-if="sidebar" :class="sidebarColStyle">
            <sidebar></sidebar>
        </div>

        <!-- Dashboard -->
        <div :class="dashboardColStyle">
            <slot></slot>
        </div>

        <!-- Testing -->
        <test v-if="$store.state.moduleOrders.inTesting"/>

    </div>
    <Footer></Footer>
</template>

<script>
    import Sidebar from '@/layouts/Sidebar.vue';
    import Navbar from '@/layouts/Navbar.vue';
    import Footer from '@/layouts/Footer.vue';
    import Notificationbar from '@/layouts/Notificationbar.vue';
    import Dialogbox from '@/layouts/Dialogbox.vue';
    import Initializator from '@/views/dashboard/Initializator.vue';

    import Test from '@/layouts/Test.vue';

    export default {
        components: { Sidebar, Navbar, Footer, Notificationbar, Dialogbox, Initializator, Test },
        computed: {
            basicData () { return this.$store.state.moduleOrders.basicData},
            sidebar() {
                return this.$route.meta.sidebar;
            },
            dashboardEmptyColStyle(){
                let style = 'col-12'
                if (this.sidebar)
                    style = 'col-lg-3 col-mb-3 col-sm-12 col-xs-12'
                return style
            },
            dashboardColStyle(){
                let style = 'col-12 mb-2'
                if (this.sidebar)
                    style = 'col-lg-9 col-mb-9 col-sm-12 col-xs-12 mb-2'
                return style
            },
            sidebarColStyle(){
                let style = 'col-lg-3 col-mb-3 col-sm-12 col-xs-12'
                return style
            },
        },
        watch: {
            basicData: {
                handler(){
                    if (this.$store.state.moduleOrders.inTesting) return
                    window.addEventListener('beforeunload', Initializator.leaveListener);
                },
                deep: true
            },
        },
    }
</script>
