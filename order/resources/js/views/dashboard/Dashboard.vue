<template>
    <div>
        <router-view></router-view>
    </div>
</template>

<script>
    import { email, required } from '@vuelidate/validators';
    import { useVuelidate } from '@vuelidate/core';

    export default {
        setup() {
            return {
                v$: useVuelidate()
            }
        },
        data() {
            return {}
        },
        validations() {
            return {}
        }        
    }
</script>


