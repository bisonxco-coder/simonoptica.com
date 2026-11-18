<template>

    <!-- Loading indicator -->
    <div v-if="!canLoad" class="position-fixed w-100 h-100 d-flex justify-content-center align-items-center" >
        <span class="spinner-icon large opaque position-absolute start-50"/>
    </div>

    <!-- Layouts -->
    <component :is="layout" v-if="canLoad" >
        <router-view></router-view>
    </component>

    <footer-legal/>
    
    <!-- Error message -->
    <div v-if="hasErrorMessage">{{errorMessage}}</div>
</template>
<script>
import Language from '@/utils/language';
import {registerMainModules} from '@/store/index';
import {VueCookieNext} from "vue-cookie-next";
import FooterLegal from "./layouts/FooterLegal.vue";
import Initializator from '@/utils/initializator.js'

export default {
  components: {FooterLegal},
  data() {
    return {
      canLoad: false,
      errorMessage: '',
      defaultLayout: 'layout-full',
    }
  },
  computed: {
    isLoggedIn() { return this.$store.getters.isAuthenticated },
    layout() { return this.$route.meta.layout || this.defaultLayout },
    hasErrorMessage() { return this.errorMessage != ''}
  },
  watch: {
    "$route.meta.layout": async function () {
      this.canLoad = false

      if (this.layout == 'layout-main'){
        await this.initializeLayoutMain()
      }

      if (this.layout == 'layout-landing'){
        await this.initializeLayoutLanding()
      }
    }
  },
  methods: {
    async initializeLayoutMain() {
      if (!this.isLoggedIn) return

      // Register main store modules
      registerMainModules()

      // Get main translations
      const translations = await Language.loadTranslations(this.$store.getters.getLanguageCode)

      // If response was an error message
      if (translations.data !== undefined && translations.data.message !== undefined){
        this.errorMessage = translations.data.message
        return
      }

      // Assign main translations
      this.$store.state.moduleSettings.translations = translations

      // Load up iniproperties from backend
      await Initializator.getIniProperties()

      // If response was an error message
      const iniPropperties = this.$store.getters.getIniProperties
      if (iniPropperties !== undefined && iniPropperties !== null && iniPropperties.message !== undefined){
        this.errorMessage = iniPropperties.message
        return
      }

      this.canLoad = true
    },
    async initializeLayoutLanding() {
      // Clear main translations
      this.$store.state.moduleSettings.translations = null

      // Get landing translations
      const translations = await Language.loadLandingTranslations(this.$store.getters.getLanguageCode)

      // If response was an error message
      if (translations.data !== undefined && translations.data.message !== undefined){
        this.errorMessage = translations.data.message
        return
      }

      // Assign landing translations
      this.$store.state.moduleSettings.translationsLanding = translations

      if(VueCookieNext.getCookie('lensorderweb_token')){
        await this.tokenLogin();
        return;
      }

      let queryString = window.location.search;
      let urlParams = new URLSearchParams(queryString);
      if( urlParams.has(process.env.MIX_JWT_AUTH) ){
        const accessSsoJwt = urlParams.get(process.env.MIX_JWT_AUTH);
        await this.tokenSsoJwt(accessSsoJwt)
        return
      }

      this.canLoad = true
    },
    async tokenLogin(){
      this.$store.dispatch('login', {
        token: VueCookieNext.getCookie('lensorderweb_token')
      }).then((response) => {
        this.$router.push({ name: 'home' });
      }, () => {
        VueCookieNext.removeCookie('lensorderweb_token')
        this.canLoad = true
      });
    },
    async tokenSsoJwt(jwt){
      this.$store.dispatch('login', {
        jwt: jwt
      }).then((response) => {
        this.$router.push({ name: 'home' });
      }, () => {
        this.canLoad = true
      });
    }
  }
}
</script>
