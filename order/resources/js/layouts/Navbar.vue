<template>
    <!-- Loading indicator -->
    <div v-if="$store.state.moduleOrders.isLoading" class="sticky-top" id="overlay-loading" style="z-index:5000000; height:0;">
       <span>{{$translations.notifications.loading}}</span>
    </div>
    <nav class="app-layout navbar navbar-style navbar-expand-lg navbar-light shadow-md sticky-top mb-lg-4 mb-3 ">
        <div class="container-fluid">

            <!-- LOGO -->
            <router-link to="/" class="navbar-brand">
                <span class="d-inline-block align-text-top header-logo"/>
                <slot name="logo" />
            </router-link>

            <!-- RESPONSIVE NAVBAR -->
            <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent"
                aria-expanded="false"
                aria-label="Toggle navigation"
            >
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <!-- NEW ORDER -->
                    <router-link to="/newOrder" class="nav-item pe-5">
                        <a class="nav-link a-fat-font active" aria-current="page" href="#">
                            {{$translations.navbar.newOrder}}
                        </a>
                    </router-link>

                    <!-- ORDER BASKET -->
                    <router-link to="/orderBasket" class="nav-item pe-5">
                        <a class="nav-link a-fat-font" href="#">
                            {{$translations.navbar.orderbasket}}
                        </a>
                    </router-link>

                    <!-- OVERVIEW -->
                    <router-link to="/overview" class="nav-item pe-5">
                        <a class="nav-link a-fat-font" href="#">
                            {{$translations.navbar.overview}}
                        </a>
                    </router-link>

                    <!-- USER ICON DROPDOWN -->
                    <li class="nav-item">
                        <div class="dropdown">
                            <a href="#" role="button" id="userDropdownMenu" data-bs-toggle="dropdown" aria-expanded="false">
                                <span class="user-icon-dot" />
                            </a>

                            <ul class="dropdown-menu dropdown-menu-lg-end shadow mt-4 border-1 p-3" aria-labelledby="userDropdownMenu">
                                <!-- Shape tester -->
                                <li v-if="orders.inTesting">
                                    <router-link to="/shapeTester" @click="to('/shapeTester')" class="dropdown-item drop py-2 px-3">
                                        Shape tester
                                    </router-link>
                                </li>

                                <!-- User configuration -->
                                <li v-if="showUserConfiguration === 'YES'">
                                    <a class="dropdown-item drop py-2 px-3" href="#">
                                        {{$translations.navbar.dropdown.userSettings}}
                                    </a>
                                </li>

                                <!-- Tracer configuration -->
                                <li>
                                    <router-link to="/tracerConfiguration" @click="to('/tracerConfiguration')" class="dropdown-item drop py-2 px-3">
                                        {{$translations.navbar.dropdown.tracerSettings}}
                                    </router-link>
                                </li>

                                <!-- Shop configuration -->
                                <li v-if="showShopConfiguration === 'YES' && isManager">
                                    <router-link to="/shopConfiguration" @click="to('/shopConfiguration')" class="dropdown-item drop py-2 px-3">
                                        {{$translations.navbar.dropdown.shopConfiguration}}
                                    </router-link>
                                </li>

                                <!-- Contact -->
                                <li>
                                    <router-link to="/contact" @click="to('/contact')" class="dropdown-item drop py-2 px-3">
                                        {{$translations.navbar.dropdown.contact}}
                                    </router-link>
                                </li>

                                <!-- Change Password -->
                                <li v-if="showChangePassword === 'YES'">
                                    <router-link to="/changePassword" @click="to('/changePassword')" class="dropdown-item drop py-2 px-3">
                                        {{$translations.navbar.dropdown.changePassword}}
                                    </router-link>
                                </li>

                                <!-- Logout -->
                                <li>
                                    <a class="d-flex dropdown-item drop py-2 px-3" href="#" @click="logout">
                                        <div class="logout-icon small" />
                                        {{$translations.navbar.dropdown.logout}}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
</template>

<script>
    import store from '@/store/index';
    import Initializator from '@/utils/initializator.js'
    import {VueCookieNext} from "vue-cookie-next";

    export default {
      data(){
        return{
          showUserConfiguration: store.getters.getIniProperties['SETTINGS_SHOW_USER_CONFIG'],
          showShopConfiguration: store.getters.getIniProperties['SETTINGS_SHOW_SHOP_CONFIG'],
          showChangePassword: store.getters.getIniProperties['SETTINGS_SHOW_CHANGE_PASSWORD']
        }
      },
      computed: {
            isManager () {return this.$store.getters.isManager},
            orders () {return this.$store.state.moduleOrders},
        },
        methods: {
            to(route){
                this.$router.push(route)
            },
            logout() {
                // Remove event listener so that you don't get a warning for moving to a different page
                window.removeEventListener('beforeunload', Initializator.leaveListener);

                // Set to true so that you don't get a warning for leaving the order
                store.state.moduleOrders.dialog.wasOrderSavedFromFooter = true
                this.$store.dispatch('logout', {
                    email: this.userEmail,
                    password: this.userPassword,
                    token: VueCookieNext.getCookie('lensorderweb_token'),
                }).then((response) => {
                    this.$router.go('login');
                }, e => {
                    store.dispatch('notify', {
                        message: e.data.message,
                    })
                    console.error(e)
                });
            }
        }
    }
</script>

<style scoped>
.dropdown-item:hover {
    color: rgb(71, 11, 11);
}
</style>
