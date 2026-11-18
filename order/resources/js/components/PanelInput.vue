<template>
    <div :class="inputGroupStyle">
        <span v-if="canRenderPlaceholder" ref="placeholderRef" :class="placeholderTextStyle">{{placeholder}}</span>
        <span v-if="icon" :class="'input-icon ' + icon" />
        <template v-if="isTextArea">
            <textarea
                autocomplete="off"
                :placeholder="inputPlaceholder"
                :class="inputTextStyle"
                v-model="input"
                @focus="onInputFocus"
                @blur="onInputBlur"
                @click="onInputClick"
                v-on:keyup.enter="onInputFocus"
                :disabled="disabledLogic"
                lang="en"
                rows="3"
            />
        </template>
        <template v-else>
            <input
              autocomplete="off"
                :placeholder="inputPlaceholder"
                :class="inputTextStyle"
                v-model="input"
                name="name"
                @focus="onInputFocus"
                @blur="onInputBlur"
                @click="onInputClick"
                v-on:keyup.enter="onInputFocus"
                :type="type"
                :step="numberStep"
                :disabled="disabledLogic"
                lang="en"
                :list="uniqueListId"
                :maxlength="maxlength"
                :min="min"
                :max="max"
            >
            <datalist v-if="hasList" :id="uniqueListId">
                <option
                    v-for="option in list"
                    :value="option[listValue]"
                    :key="option"
                >{{option[listLabel]}}</option>
            </datalist>
        </template>

        <div :class="errorStyle">
            <span v-if="hasError">{{currentErrorMessage}}</span>
            <span v-else>{{errorMessage}}</span>
        </div>
    </div>
</template>

<script>
import store from '@/store'
export default {
    props: ['modelValue','itemtype','placeholder','type','icon','min','max','maxlength','step','disabled','decimals','decimalSnap','errorMessage','dense','preserveOnDisable','isImmutable','list','listLabel','listValue', 'excludeZeroDisabling', 'name'],
    emits: ['update:modelValue', 'update:errorMessage', 'updateValue'],
    data () {
        return {
            uniqueId: 0,
            input: null,
            isFocused: null,
            isDisabled: false,
            numberStep: 1,
            hasError: false,
            currentErrorMessage: '',
            errorMessages: {
                get outOfRange() { return store.getters.translations.notifications.outOfDelivery },
                get enterValue() { return store.getters.translations.notifications.newValue }
            }
        }
    },
    computed:{
        isDense () { return this.dense == undefined ? false : true },
        isTextArea() { return this.type === 'textarea' },
        isPreserveOnDisable () {
            if (this.preserveOnDisable == undefined ||
                (this.preserveOnDisable != undefined && this.preserveOnDisable == null) ||
                (this.preserveOnDisable != undefined && this.preserveOnDisable != null && this.preserveOnDisable === false)
            )
                return false
            return true
        },
        hasList() { return this.list !== undefined && this.list && this.list.length !== undefined && this.list.length > 0 },
        uniqueListId() { return 'list_' + this.uniqueId },
        canRenderPlaceholder () {
            if (this.placeholder && !this.isDense) return true
            return false
        },
        isErrorLong() {
            if (this.hasError && this.currentErrorMessage && this.currentErrorMessage.length >= 30) return true
            if (this.errorMessage && this.errorMessage.length >= 30) return true
            return false
        },
        errorStyle() {
            let style = 'invalid-tooltip '
            if (!this.isErrorLong) style += ' py-0'
            return style
        },
        inputPlaceholder() {
            if (!this.isDense) return
            return this.placeholder
        },
        inputGroupStyle() {
            let style = 'input-group mb-3'
            if (this.isDense) style = 'input-group'

            if (this.isErrorLong)
                style += ' mb-5'

            return style
        },
        placeholderTextStyle() {
            if (this.isDense) return ''

            let style = 'panel-field-placeholder focus'

            if (this.icon)
                style += ' placeholder-icon'
            if (this.input || this.input === 0 || this.isFocused)
                return style
            return style + ' empty py-2'
        },
        inputTextStyle() {

            let style = 'form-control border border-1 border-radius-xl pt-4 '
            let denseStyle = 'form-control border border-1 border-radius-xl px-1 py-1 dense-input-text dense-height'

            if (this.type !== 'textarea')
                style += ' h-25'
            if (this.type == 'date')
                style += ' pb-1'
            else style += ' pb-2'
            if (this.icon)
                style += ' ps-5'
            if (this.errorMessage || this.hasError){
                style += ' is-invalid border-danger'
                denseStyle += ' is-invalid border-danger dense-error-style'
            }
            style += ' p-3 '

            if (this.isDense) return denseStyle
            return style
        },
        disabledLogic() {
            let value = this.isDisabled
            if (this.disabled != undefined && this.disabled)
                value = this.disabled
            if (this.isImmutable)
                value = true
            return value
        }
    },
    watch: {
        input(val) {
            this.$emit('update:errorMessage', '')
            this.$emit('update:modelValue', val)
        },
        min(val) {
            this.checkMinMax()
        },
        max(val) {
            this.checkMinMax()
        },
        disabled(val) {
            if (val){
                if (!this.isPreserveOnDisable)
                    this.input = null

                this.currentErrorMessage = ""
                this.hasError = false
                this.$emit('update:errorMessage', '')
            }
        },
        modelValue(val) {
            // Make sure that the input updates on every change
            this.input = val
        }
    },
    updated () {
        if (this.type === 'date'){
            this.onInputFocus()
        }
    },
    mounted () {
        // if (this.isDisabled){
        //     if (!this.isPreserveOnDisable)
        //         this.input = null
        // }
        this.uniqueId = Math.floor(Math.random() * 10000000)

        if (this.isDense) {
            this.$el.style.setProperty("--dense-input-margin-right", '-0.2rem')
            this.$el.style.setProperty("--dense-input-margin-bottom", '0')
            this.$el.style.setProperty("--dense-input-padding-top", '0')
        }

        this.input = this.modelValue

        if (this.type === 'date')
            this.onInputFocus()

        this.checkMinMax();
    },
    methods: {
        onInputClick(event) {
            if (this.type === 'date'){
                event.target.showPicker()
            }
        },
        onInputFocus() {
            this.isFocused = true
            this.checkMinMax();
            this.validateMinMaxForErrors();
        },
        onInputBlur() {
            this.isFocused = false
            this.$emit('update:errorMessage', '')
            this.checkMinMax();
            this.validateMinMaxForErrors();

            if(this.itemtype)
              this.$parent.onLeave();
          this.$emit('updateValue', 20)
        },
        toDecimals(val){
            // To simplify decimal input, divide input value by Math.pow(10,decimals)
            // when the lenght of input exceeds (decimalSnap), usually 3.
            // Example: 1255 becomes 12.55, 123 becomes 1.23
            let decimals = this.decimals ? this.decimals : 2
            let split = val.toString().split(".")

            // Split the - in front of negatives
            if (val < 0)
                split[0] = split[0].split("-")[1]

            let numberLen = split[0].length
            let decimalLen = 0
            if (split.length > 1)
                decimalLen = split[1].length
            if (decimalLen == 0 && (this.decimalSnap || this.decimalSnap > 0)){
                if (numberLen >= this.decimalSnap)
                    return val / Math.pow(10,decimals)
            }
            return val
        },
        validateMinMaxForErrors() {
          if (this.type !== 'number') return
          let val = this.input
          if(this.name === 'cyl'){
            if (parseFloat(val) < 0) {
              val = val * -1
            }
          }
            // Skip if value is empty
            if (val == '' || val == null){
                this.hasError = false
                return
            }

            val = this.toDecimals(val)

            // Check for min
            if (this.min || this.min == 0){
                if (parseFloat(val) < parseFloat(this.min)){
                    this.hasError = true
                    this.currentErrorMessage = this.errorMessages.outOfRange
                    return
                }
            }

            // Check for max
            if (this.max || this.max == 0){
                if (parseFloat(val) > parseFloat(this.max)){
                    this.hasError = true
                    this.currentErrorMessage = this.errorMessages.outOfRange
                    return
                }
            }
            this.hasError = false
        },
        checkMinMax() {
            if (this.type === 'number'){
                if (this.step)
                    this.numberStep = this.step

                // Disable if both min and max are 0
                if (this.min == 0 && this.max == 0 && this.excludeZeroDisabling !== true){
                    this.isDisabled = true
                    this.input = null
                    return
                }

                let val = this.input
                if (!val) val = 0

                val = this.toDecimals(val)

                // Round decimals to nearby step
                if (val % this.numberStep != 0)
                    val = Math.round(val / this.numberStep ) * this.numberStep;

                // Always display decimals
                let decimals = this.decimals ? this.decimals : 2
                if (val % 0.25 == 0 || val == 0)
                    decimals = 2
                if (this.numberStep.toString().split(".").length > 1 || this.numberStep.toString().split(",").length > 1)
                    val = parseFloat(val).toFixed(decimals);

                this.isDisabled = false
                if (this.input == '' || this.input == null) return
                this.input = val
            }

            if (this.disabled){
                if (!this.isPreserveOnDisable) this.input = null
            }
        }
    }
}
</script>
<style lang="scss" scoped>
.placeholder-icon{
    margin-left: 48px;
}

// Move the arrow numbers a little to the right to make space for the error icon
input[type=number]::-webkit-inner-spin-button{
    margin-right: var(--dense-input-margin-right, -0.2rem);
    margin-bottom: var(--dense-input-margin-bottom, 0.9rem);
    padding-top: var(--dense-input-padding-top, 1.3rem);
}

.dense-error-style{
    background-size: 0;
}

input{
    height: 56px !important;
}

.dense-height{
    height: auto !important;
}

</style>
