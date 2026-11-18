<template>
    <div class="card p-5">
        <!-- Form -->
        <div>
            <form @submit.prevent="changePassword">
                <!-- Password old -->
                <div class="mb-4">
                    <panel-input
                        :placeholder="$translations.changePassword.passwordCur "
                        v-model="passwordData.passwordCur"
                        type="password"
                    />

                    <small v-if="v$.passwordData.passwordCur.$error" class="text-danger">{{ v$.passwordData.passwordCur.$errors[0].$message}}</small>
                </div>

                <!-- Password oew -->
                <div class="mb-4">
                    <panel-input
                        :placeholder="$translations.changePassword.passwordNew "
                        v-model="passwordData.passwordNew"
                        type="password"
                    />

                    <small v-if="v$.passwordData.passwordNew.$error" class="text-danger">{{ v$.passwordData.passwordNew.$errors[0].$message }}</small>
                </div>

              <!-- Password repeat -->
              <div class="mb-4">
                <panel-input
                    :placeholder="$translations.changePassword.passwordRepeat "
                    v-model="passwordData.passwordRepeat"
                    type="password"
                />

                <small v-if="v$.passwordData.passwordRepeat.$error" class="text-danger">{{ v$.passwordData.passwordRepeat.$errors[0].$message }}</small>
              </div>

                <!-- Login button -->
                <div class="d-flex justify-content-center">
                    <button type="submit" class="btn btn-secondary panel-button d-md-inline-block d-block blue border-0 px-5">
                      {{ $translations.changePassword.changePassword}}
                    </button>
                </div>
            </form>
        </div>
    </div>
  <!-- Error message -->
  <div class="toast-container position-fixed top-0 end-50 p-3">
    <div ref="errorToast" class="toast danger" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header">
        <strong class="me-auto">{{ $translations.changePassword.dialog.error}}</strong>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" style="background: #f55a4e; color: #FFFFFF">
        {{ $translations.changePassword.dialog.messages.changePasswordError}}
      </div>
    </div>
  </div>
</template>

<script>
import { required, helpers, minLength } from '@vuelidate/validators';
import { useVuelidate } from '@vuelidate/core';
import { Toast } from 'bootstrap';
import PanelInput from '@/components/PanelInput.vue'
import Language from '@/utils/language.js'
import {reactive} from "vue";

export default {
  setup() {
    const passwordData = reactive({passwordCur: '', passwordNew: '', passwordRepeat: ''});
    const sameAs = () => (value) => value === passwordData.passwordNew;
    const reg =() => (value) => new RegExp("^[a-zA-Z0-9!@#$&()\\-.+,/\"]*$").test(value);
    return {
      v$: useVuelidate(),
      sameAs,
      reg,
      passwordData
    }
  },
  components:{PanelInput},
  validations() {
    return {passwordData: {
      passwordCur: { required: helpers.withMessage(this.$translations.changePassword.passwordCurError, required) },
      passwordNew: {  required: helpers.withMessage(this.$translations.changePassword.passwordNewError, required),
        minLength: helpers.withMessage(this.$translations.changePassword.minLength, minLength(6)),
        regex: helpers.withMessage(this.$translations.changePassword.invalidChars, this.reg(this.passwordData.passwordNew))},
      passwordRepeat: {  required: helpers.withMessage(this.$translations.changePassword.passwordRepeatError, required),
        sameAs: helpers.withMessage(this.$translations.changePassword.passwordSame, this.sameAs(this.passwordData.passwordNew))}}
    }
  },
  mounted() {
    this.$store.commit('setDashboardTitle', {
      title: () => this.$translations.changePassword.title,
      subTitle: () => ""
    })
  },
  methods: {
    changePassword() {
      this.v$.$touch();
      if (this.v$.$error) return;

      this.$store.dispatch('changePassword', {
        pwdCur: this.passwordData.passwordCur,
        pwdNew: this.passwordData.passwordNew
      }).then((response) => {
        if(response.data === 'error'){
          (new Toast(this.$refs.errorToast)).show();
        }
        else
          this.$router.push({ name: 'home' });
      }, (error) => {
        (new Toast(this.$refs.errorToast)).show();
      });
    }
  }
}
</script>
