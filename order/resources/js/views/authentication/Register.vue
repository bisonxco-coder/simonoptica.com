<template>
    <div class="row">
        <div class="col">
            <h2 class="mb-1 font-weight-bold">
                {{ $t('authentication.welcome') }}
            </h2>

            <p class="mb-4">
                {{ $t('authentication.register_your_account') }}
            </p>

            <form @submit.prevent="register">
                <div class="mb-3">
                    <label for="login-name" class="form-label">{{ $t('authentication.label_name') }}</label>
                    <input
                        class="form-control"
                        id="login-name"
                        name="login-name"
                        :placeholder="$t('authentication.placeholder_name')"
                        v-model="userName"
                    />

                    <small v-if="v$.userName.$error" class="text-danger">{{ $t('authentication.error.name') }}</small>
                </div>
                <div class="mb-3">
                    <label for="login-email" class="form-label">{{ $t('authentication.label_email') }}</label>
                    <input
                        class="form-control"
                        id="login-email"
                        name="login-email"
                        :placeholder="$t('authentication.placeholder_email')"
                        v-model="userEmail"
                    />

                    <small v-if="v$.userEmail.$error" class="text-danger">{{ $t('authentication.error.email') }}</small>
                </div>

                <div class="mb-4">
                    <label for="login-password"  class="form-label">{{ $t('authentication.label_password') }}</label>
                    <input
                        class="form-control"
                        id="login-password"
                        name="login-password"
                        type="password"
                        :placeholder="$t('authentication.placeholder_password')"
                        v-model="userPassword"
                    />

                    <small v-if="v$.userPassword.$error" class="text-danger">{{ $t('authentication.error.password') }}</small>
                </div>

                <div class="form-check mb-1">
                    <input class="form-check-input" type="checkbox" value="" id="terms" v-model="terms" />
                    <label class="form-check-label" for="terms">
                        {{ $t('authentication.label_terms') }}
                    </label><br />
                    <small v-if="v$.terms.$error" class="text-danger">{{ $t('authentication.error.terms') }}</small>
                </div>

                <button type="submit" class="btn btn-primary">{{ $t('authentication.label_register') }}</button>
            </form>
        </div>
    </div>

    <div class="toast-container position-fixed top-0 end-0 p-3">
        <div ref="errorToast" class="toast danger" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <strong class="me-auto">{{ $t('authentication.error.error') }}</strong>
                <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                {{ $t('authentication.error.submit_register_error') }}
            </div>
        </div>
    </div>
</template>

<script>
    import { email, required } from '@vuelidate/validators';
    import { useVuelidate } from '@vuelidate/core';
    import { Toast } from 'bootstrap';
    import store from '@/store/index';

    export default {
        setup() {
            return {
                v$: useVuelidate()
            }
        },
        data() {
            return {
                userName: '',
                userEmail: '',
                userPassword: '',
                terms: true
            }
        },
        validations() {
            return {
                userName: { required },
                userEmail: { required, email },
                userPassword: { required },
                terms: {
                    checked (val) {
                        console.log(val);
                        return val;
                    }
                }
            }
        },
        methods: {
            register() {
                this.v$.$touch();
                if (this.v$.$error) return;

                this.$store.dispatch('register', {
                    name: this.userName,
                    email: this.userEmail,
                    password: this.userPassword,
                    agree_terms: this.terms
                }).then((response) => {
                    this.$router.push({ name: 'home' });
                }, () => {
                    (new Toast(this.$refs.errorToast)).show();
                });
            }
        }
    }
</script>
