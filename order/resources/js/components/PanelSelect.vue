<template>
    <div :class="inputGroupStyle">
        <span v-if="canRenderPlaceholder" :class="placeholderTextStyle">{{placeholder}}</span>
        <div class="d-flex flex-row position-relative">
            <select :class="selectStyle"
                style="font-weight: 500;"
                v-model="selected"
                :disabled="disabledLogic"
                @focus="onFocus"
                @click="onOpen"
            >
                <option v-for="item of selection" :value="item" :key="item">
                    <span v-if="titleParameterLogic(item)">{{ item[titleParameter] }}</span>
                    <span v-else>{{ item }}</span>
                </option>
            </select>

            <div :class="errorStyle">
                <span v-if="hasError">{{currentErrorMessage}}</span>
                <span v-else>{{errorMessage}}</span>
            </div>
        </div>
    </div>
</template>

<script>
import Helpers from '@/utils/helpers.js'
import store from '@/store'

export default {
    props: ['modelValue','placeholder', 'selection', 'titleParameter', 'idParameter', 'disabled','notNull','errorMessage','dense','preserveOnDisable','isImmutable','firstValue'],
    emits: ['update:modelValue', 'update:errorMessage', 'onOpen'],
    data () {
        return {
            selected: null,
            hasError: false,
            currentErrorMessage: '',
            errorMessages: {
                get selectValue() { return store.getters.translations.notifications.selectValue }
            },
        }
    },
    computed:{
        isDense () { return this.dense == undefined ? false : true },
        isPreserveOnDisable () {
            if (this.preserveOnDisable == undefined ||
                (this.preserveOnDisable != undefined && this.preserveOnDisable == null) ||
                (this.preserveOnDisable != undefined && this.preserveOnDisable != null && this.preserveOnDisable === false)
            )
                return false
            return true
        },
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
        selectStyle() {
            let style = 'form-select form-select-lg fs-6 border border-1 border-radius-xl pt-4 pb-1 h-25'
            let denseStyle = 'form-select border border-1 border-radius-xl py-1 px-1 dense-input-text'
            if (this.errorMessage || this.hasError){
                style += ' is-invalid border-danger'
                denseStyle += ' is-invalid border-danger'
            }
            if (this.isDense) return denseStyle
            return style
        },
        inputGroupStyle() {
            let style = 'mb-4'
            if (this.isDense) style = ''

            if (this.isErrorLong)
                style += ' mb-5'

            return style
        },
        placeholderTextStyle() {
            let style = 'panel-field-placeholder focus'
            if (this.isDense) return ''
            if (this.selected) return style
            return style + ' empty py-2'
        },
        disabledLogic() {
            if (this.disabled != undefined && this.disabled) return this.disabled
            if (!this.selection || this.selection == [] || this.selection.length == 0) return true
            if (this.isImmutable) return true
            return false
        }
    },
    watch: {
        selected(val) {
            this.$emit('update:errorMessage', '')
            this.$emit('update:modelValue', val)
        },
        selection(val){
            if (!this.notNull) this.addEmptyField()
            this.checkValue()
        },
        disabled(val){
            if (val) {
                if (!this.isPreserveOnDisable)
                    this.selected = null

                this.hasError = null
                this.currentErrorMessage = ""
                this.$emit('update:errorMessage', '')
            }
            if (!this.notNull) this.addEmptyField()
            this.checkValue()
        }
    },
    updated () {
        // Make sure that the selection updates on every change
        this.selected = this.modelValue
    },
    mounted () {
        this.selected = this.modelValue

        //In case the field is notNull
        if (!this.notNull) this.addEmptyField()
        this.checkValue()
    },
    methods: {
        onOpen(){
            this.$emit('onOpen')
        },
        onFocus(){
            this.hasError = false
            this.currentErrorMessage = ''
        },
        checkValue () {
            if (this.disabled && !this.firstValue){
                if (!this.isPreserveOnDisable) this.selected = null
                return
            }

            // No selection
            if (!this.selection){
                this.selected = null
                this.$emit('update:modelValue', this.selected)
                return
            }

            // Initial selection
            if (this.notNull){
                if (this.selection.length > 0)
                    this.selected = this.selection[0]
            }

            // Get last selection
            if (this.modelValue){
                this.selected = this.modelValue
                this.$emit('update:modelValue', this.selected)
            }

            // Deselect if selected element is not in selection list
            let found = false
            this.selection.forEach(sel => {
                if (found) return

                if (JSON.stringify(this.selected) === JSON.stringify(sel)){
                    this.foundSwitch = false
                    found = true
                    return
                }

                if (this.selected == null && sel == null){
                    found = false
                    return
                }
                else {
                    // If selection data has changed and idParameter is provided,
                    // use idParameter for trying to find out weather the selected element is in the current selection data
                    if (this.idParameter && this.idParameter !== undefined && this.idParameter !== ''){
                        let idFromSelected = Helpers.getObjProp(this.selected, this.idParameter, null)
                        let idFromSelectionItem = Helpers.getObjProp(sel, this.idParameter, null)
                        if (idFromSelected && idFromSelected !== undefined &&
                            idFromSelectionItem && idFromSelectionItem !== undefined &&
                            idFromSelected == idFromSelectionItem
                        ){
                            this.selected = sel
                            this.$emit('update:modelValue', this.selected)
                            found = true
                            return
                        }
                    }
                }
            });
            if (!found) {
                if (this.notNull){
                    if (this.selection.length > 0)
                        this.selected = this.selection[0]
                } else {
                    this.selected = null
                    this.$emit('update:modelValue', this.selected)
                }
            }
        },
        addEmptyField () {
            if (!this.selection || this.selection.length <= 0)
                return
            if (this.selection[0] === null)
                return
            this.selection.splice(0, 0, null)
        },
        titleParameterLogic(item) {
            if (item && typeof item === 'object')
                return true
            return false
        }
    },
}
</script>

