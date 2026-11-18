<template>
    <div class="mb-3">
        <div class="row ps-4" v-for="item in radioInputData" :value="item" :key="item.id">
            <div class="col-xl-1 col-2 d-flex ">
                <input 
                    v-model="selection"
                    class="form-check-input panel-radio align-self-center position-absolute mb-3"
                    type="radio" 
                    :name="radioName"
                    :value="item"
                    checked
                    :disabled="disableComponentLogic()"
                >
            </div>
            <div class="col-xl-11 col-10 ps-xl-3 ps-lg-2 ps-md-1 ps-sm-0 ps-2">
                <div v-if="isItemSelected(item)">
                    <panel-input 
                        :placeholder="item.name" 
                        v-model="item.value" 
                        :min="item.min"
                        :max="item.max"
                        :step="item.step"
                        type='number'
                        :decimals="item.decimals"
                        :disabled="disabledLogicPanelInput(item)"
                        v-model:errorMessage="errorMessage"
                    />
                </div>
                <div v-else>
                    <panel-input 
                        :placeholder="item.name" 
                        v-model="item.value" 
                        :min="item.min"
                        :max="item.max"
                        :step="item.step"
                        type='number'
                        :decimals="item.decimals"
                        :disabled="disabledLogicPanelInput(item)"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import PanelInput from '@/components/PanelInput.vue'

export default {
    components: { PanelInput },
    props: ['modelValue', 'radioInputData', 'radioName', 'disabled', 'errorMessage'],
    emits: ['update:modelValue', 'update:errorMessage'],
    data () {
        return {
            selection: null
        }
    },
    watch: {
        selection() {
            this.$emit('update:errorMessage', "")
            this.$emit('update:modelValue', this.selection)
        }
    },
    mounted () {
        this.checkValue()
    },
    methods: {
        checkValue () {
            if (this.modelValue){
                this.selection = this.modelValue
                return
            }

            if (this.radioInputData.length > 0)
                this.selection = this.radioInputData[0]
        },
        disabledLogicPanelInput (item) {
            if (this.disabled != undefined && this.disabled) return this.disabled
            if (!this.selection || !item) return false
            if (this.selection.id == item.id) return false
            return true
        },
        disableComponentLogic(){
            if (this.disabled != undefined && this.disabled) return this.disabled
        },
        isItemSelected(item) {
            if (!this.selection) return false
            if (this.selection.id == item.id) return true
            return false
        }
    }
}
</script>
<style>

</style>
