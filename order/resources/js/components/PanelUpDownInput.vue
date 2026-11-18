<template>
    <div class="row d-flex align-items-center input-group mb-3" style="width: 100%;">
        <div class="col-sm-8 col p-3">
            <label class="variable-label-text" :for="uniqueId">{{placeholder}}</label>
        </div>
        <div class="col-sm-4 col ps-sm-0 d-flex justify-content-end input-style">
            <input 
                :id="uniqueId" 
                v-model="inputValue"
                :class="inputTextStyle"
                type="number"
                :step="inputStep"
                :disabled="disabledLogic"
            />
            <div :class="errorStyle">
                <span>{{errorMessage}}</span>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['modelValue', 'step', 'placeholder', 'disabled', 'preserveOnDisable', 'errorMessage'],
    emits: ['update:modelValue', 'update:errorMessage'],
    data () {
        return {
            randomId: 0,
            inputValue: null,
            inputStep: 1,
        }
    },
    computed: {
        isPreserveOnDisable () {
            if (this.preserveOnDisable === undefined ||
                (this.preserveOnDisable != undefined && this.preserveOnDisable == null) ||
                (this.preserveOnDisable != undefined && this.preserveOnDisable != null && this.preserveOnDisable === false)
            )
                return false
            return true
        },
        disabledLogic () {
            let value = false
            if (this.disabled != undefined && this.disabled)
                value = this.disabled
            return value
        },
        uniqueId () {
            return 'panel_input_horizontal_' + this.randomId
        },
        isErrorLong() {
            if (this.errorMessage && this.errorMessage.length >= 30) return true
            return false
        },
        errorStyle() {
            let style = 'invalid-tooltip '
            if (!this.isErrorLong) style += ' py-0'
            return style
        },
        inputTextStyle() {
            let style = 'form-control border border-1 p-3 border-radius-xl '
            
            if (this.errorMessage){
                style += ' is-invalid border-danger'
            }

            return style
        },
    },
    watch: {
        inputValue(value){
            this.inputValue = value
            this.$emit('update:modelValue', value)
            this.$emit('update:errorMessage', '')
        },
        modelValue(value) {
            this.inputValue = value
        },
        disabled(val) {
            if (val){
                if (!this.isPreserveOnDisable)
                    this.inputValue = null

                this.$emit('update:errorMessage', '')
            }
        },
        step(val) {
            this.inputStep = this.step
        }
    },
    updated() {
        if (this.disabled) {
            if (!this.isPreserveOnDisable){
                this.inputValue = null
            }
        }
    },
    mounted() {
        this.randomId = Math.floor(Math.random() * 10000000)
        if (this.modelValue) 
            this.inputValue = this.modelValue
        if (this.step)
            this.inputStep = this.step
    },
    methods: {
    }
}
</script>

<style scoped lang="scss">
.input-style{
    @media only screen and (max-width: 440px) { 
        min-width: 100%;
    }
}
</style>