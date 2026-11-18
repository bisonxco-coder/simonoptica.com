<template>

    <div class="position-absolute h-100 w-100 end-0 top-0 d-flex">

        <!-- Background -->
        <div class="w-100">
            <div class="login-banner w-100 h-100"/>
        </div>

        <!-- Login window-->
        <div class="align-items-baseline card card-login shadow p-3 login-wrapper">
            <div class="w-100" style="margin-bottom: 5%;">
                <div class="d-grid align-items-center h-100 p-3">

                    <div class="row mb-4 d-flex justify-content-between">
                        <!-- Logo -->
                        <div class="col-sm-6 col-12 mb-4">
                            <span class="login-logo"/>
                        </div>

                        <!-- Languages -->
                        <div class="col-sm-6 col-12">
                            <panel-select
                                :placeholder="$translationsLanding.login.placeholders.language"
                                v-model="selectedLanguageCode"
                                :selection="languageCodeSelection"
                            />
                        </div>
                    </div>

                    <!-- Title -->
                    <div class="mb-4">
                        <h2 class="mb-1 font-weight-bold">
                            {{ $translationsLanding.login.title }}
                        </h2>
                        <p v-if="showSubTitle !== 'NO'">{{ $translationsLanding.login.subtitle }}</p>
                    </div>

                    <!-- Form -->
                    <div>
                        <form @submit.prevent="login">
                            <!-- Username -->
                            <div class="mb-3">
                                <panel-input
                                    :placeholder="$translationsLanding.login.placeholders.username"
                                    v-model="username"
                                />
                                <small v-if="v$.username.$error" class="text-danger">
									{{ $translationsLanding.login.dialog.messages.enterUsername }}
								</small>
                            </div>

                            <!-- Password -->
                            <div class="mb-4">
                                <panel-input
                                    :placeholder="$translationsLanding.login.placeholders.password"
                                    v-model="userPassword"
                                    type="password"
                                />

                                <small v-if="v$.userPassword.$error" class="text-danger">
									{{ $translationsLanding.login.dialog.messages.enterPassword }}
								</small>
                            </div>

                            <!-- Remember me -->
                            <div v-if="rememberMeEnabled" class="d-flex mb-4">
                                <input v-model="remember" class="form-check-input panel-checkbox" type="checkbox" id="remember" checked >
                                <label class="form-check-label ms-4 px-2 position-absolute" for="remember">
                                    {{ $translationsLanding.login.remeberMe}}
                                </label>
                            </div>

                            <!-- Login button -->
                            <div class="d-flex justify-content-center">
                                <button type="submit" class="btn btn-secondary panel-button d-md-inline-block d-block blue border-0 px-5">
                                    {{ $translationsLanding.login.login}}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Error message -->
    <div class="toast-container position-fixed top-0 end-50 p-3">
        <div ref="errorToast" class="toast danger" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">{{ $translationsLanding.login.dialog.error}}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ $translationsLanding.login.dialog.messages.loginError}}
            </div>
        </div>
    </div>

	<panel-popup 
		style="padding:50px;" 
		:isOpen="openPanel" 
		@onClose="onCloseLensSoapTracerPopup" 
		:parentStyle="'height: 100%; width:100%; overflow-y: auto;'"
	>
		<div id="popupLogin" v-html="data" />
	</panel-popup>

	<vue-cookie-accept-decline
		:debug="true"
		:disableDecline="false"
		@clicked-accept="cookieClickedAccept"
		@clicked-decline="cookieClickedDecline"
		:showPostponeButton="false"
		elementId="cookieEl"
		position="top"
		v-if="showCookieHint"
		ref="cookieEl"
		transitionName="slideFromTop"
		type="bar"
	>
		<!-- Optional -->
		<template #postponeContent>&times;</template>

		<!-- Optional -->
		<template #message>
			We use cookies to ensure you get the best experience on our website.
			<a href="https://cookiesandyou.com/" target="_blank">Learn More...</a>
		</template>

		<!-- Optional -->
		<template #declineContent>{{ $translationsLanding.login.decline}}</template>

		<!-- Optional -->
		<template #acceptContent>{{ $translationsLanding.login.accept}}</template>
  	</vue-cookie-accept-decline>
</template>

<script>
import { required } from '@vuelidate/validators'
import { useVuelidate } from '@vuelidate/core'
import { Toast } from 'bootstrap'
import PanelInput from '@/components/PanelInput.vue'
import PanelSelect from '@/components/PanelSelect.vue'
import PanelPopup from '@/components/PanelPopup.vue'
import Language from '@/utils/language.js'
import {VueCookieNext} from "vue-cookie-next";
import store from "../../store";

export default {
	components: {PanelInput, PanelSelect, PanelPopup},
	setup() {
		return {
			v$: useVuelidate()
		}
	},
	data() {
		return {
			username: '',
			userPassword: '',
			remember: false,
			languageCodeSelection: [],
			selectedLanguageCode: null,
			rememberMeEnabled: false,
			showCookieHint: false,
			data: null,
      showSubTitle:  process.env.MIX_SHOW_LOGIN_SUBTITLE
		}
	},
	validations() {
		return {
			username: { required },
			userPassword: { required }
		}
	},
	computed:{
		openPanel(){
			return this.data !== null;
		}
	},
	watch: {
		async selectedLanguageCode(val) {
			this.$store.state.moduleSettings.translationsLanding = await Language.loadLandingTranslations(this.selectedLanguageCode)
		},
	},
	mounted () {
		this.emitter.on('legal-popup', (data) => { // here you need to use the arrow function
			this.data = data;
		})
		const getLanguageCodes = async () => {
			this.languageCodeSelection = await Language.getLanguageCodes()
		}
		getLanguageCodes()
		if (this.$store.getters.getLanguageCode == '') Language.setDefaultLanguageCode()
		const cookiesAccepted = VueCookieNext.getCookie('vue-cookie-accept-decline-cookieEl');
		if(cookiesAccepted === 'accept') this.rememberMeEnabled = true;
		if(cookiesAccepted === null) this.showCookieHint = true;
  	},
  	methods: {
		onCloseLensSoapTracerPopup() {
			this.data = null;
		},
		async login() {
			this.v$.$touch();
			if (this.v$.$error) return;

			this.$store.dispatch('login', {
				uid: this.username,
				pwd: this.userPassword,
				rmb: this.remember
			}).then(async (response) => {
        if (this.selectedLanguageCode === null) {
          this.$store.state.moduleSettings.translationsLanding = await Language.loadLandingTranslations(response['LANG'])
        }
        this.$router.push({name: 'home'});
      }, () => {
				(new Toast(this.$refs.errorToast)).show();
			});
		},
		cookieClickedAccept(){
			VueCookieNext.setCookie('vue-cookie-accept-decline-cookieEl', 'accept')
			this.rememberMeEnabled = true;
		},
		cookieClickedDecline(){
			VueCookieNext.setCookie('vue-cookie-accept-decline-cookieEl', 'decline')
			this.rememberMeEnabled = false;
		},
  	}
}
</script>

<style lang="scss" scoped>
.login-wrapper {
    width: 30%;
    min-width: 350px;

    @media only screen and (max-width: 768px) {
        width: 100%;
        min-width: 100%;
    }
    @media only screen and (max-height: 420px) {
        height: min-content;
    }
}
</style>
