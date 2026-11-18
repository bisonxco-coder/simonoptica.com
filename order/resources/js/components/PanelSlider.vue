<template>
    <panel-input
        :placeholder="title"
        v-model="sliderInput"
        @input="onInput"
        @blur="onLeave"
        :disabled="disabledLogic"
        :preserveOnDisable="preserveOnDisable"
        v-model:errorMessage="errorMessage"
        :step="sliderStep"
        :type="type"
        itemtype="slider"
        type="number"
    />

    <slot name="body"/>

    <input type="range"
           v-model="sliderValue"
           class="form-range"
           :min="sliderMin"
           :max="sliderMax"
           :step="sliderStep"
           @input="onSliderInput"
           id="component_slider_values"
           :disabled="disabledLogic"
    >
    <div class="d-flex justify-content-between">
        <label for="component_slider_values"
               class="form-label"
               v-for="(item, index) in valuesText" :value="index" :key="index"
        >
          <span v-if="index < 1" :style="'text-align:left;word-wrap: normal;display: inline-block;width:' + LabelTextWidth + 'px;' ">
            {{item}}
          </span>
          <span v-else-if="index > 0 && index < (Object.keys(valuesText).length - 1)" :style="'text-align:center;word-wrap: normal;display: inline-block;width:' + LabelTextWidth + 'px;' ">
            {{item}}
          </span>
          <span v-else :style="'text-align:right;word-wrap: normal;display: inline-block;width:' + LabelTextWidth + 'px;' ">
            {{item}}
          </span>
        </label>
        <!-- Always maintain fixed empty space to avoid position popping -->
        <!--<div v-if="valuesText === undefined" class="pb-4 mb-2" />-->
    </div>
</template>

<script>
import PanelUpDownInputVue from './PanelUpDownInput.vue'
import PanelInput from "./PanelInput.vue";
import Empty from "./Empty.vue";

export default {
    components: {PanelInput, PanelUpDownInputVue },
    props: ['modelValue', 'values', 'step', 'title', 'disabled', 'errorMessage', 'preserveOnDisable', 'snapInputToRange', 'type'],
    emits: ['update:modelValue'],
    data () {
        return {
            uniqueId: 0,
            sliderValue: null,
            sliderInput: null,
            sliderStep: 1,
            sliderWidth: 1
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
        sliderMin () {
            if (this.values.length > 0)
                return this.values[0]
            else return null
        },
        sliderMax () {
            if (this.values.length > 0)
                return this.values[this.values.length-1]
            else return null
        },
        disabledLogic () {
            if (this.disabled != undefined && this.disabled) return this.disabled
            if (this.values.length > 0) return false
            this.sliderValue = null
            return true
        },
        isSnapToRange () {
            return this.snapInputToRange === true
        },
        valuesText() {
          if(this.values.length <= 0) return
          let val = {}
          let z = 0;
          for (let i = this.sliderMin; i <= this.sliderMax; i++) {
            if (Object.values(this.values).indexOf(i.toString()) > -1) {
              val[z] = i
            }
            else {
              val[z] = ''
            }
            z++;
          }
          return val;
        },
       LabelTextWidth() {
         if(this.sliderWidth / Object.keys(this.valuesText).length < 5) {
           const el = document.getElementsByClassName('form-range')[0]
           if(el) {
            return el.offsetWidth / Object.keys(this.valuesText).length
           }
         }
         return this.sliderWidth / Object.keys(this.valuesText).length
       }
    },
    watch: {
        disabled (value) {
            const hasList = (list) => list !== undefined && list && list.length !== undefined && list.length > 0
            if (hasList(this.values) && !value)
                return this.validateValues(this.values)
        },
        sliderValue(value){
            this.sliderInput = value
            this.$emit('update:modelValue', value)
        },
        sliderInput(val){
            if (this.disabled) {
                if (!this.isPreserveOnDisable){
                    this.sliderValue = null
                    return
                }
            }

            let num = this.validateNumber(val)
            this.sliderValue = num
        },
        values(val) {
            return this.validateValues(val)
        },
        modelValue(value) {
            this.sliderValue = value
        },
        snapInputToRange(value) {
            this.sliderStep = 1
            if (this.step > 0 && this.isSnapToRange)
                this.sliderStep = this.step
        }
    },
    mounted() {
        this.uniqueId = Math.floor(Math.random() * 10000000)

        let min = this.sliderMin
        if (this.modelValue)
            min = this.modelValue
        this.sliderValue = min
        this.sliderInput = min

        this.sliderStep = 1
        if (this.step > 0 && this.isSnapToRange)
            this.sliderStep = this.step
    },
  created() {
    window.addEventListener("resize", this.eventHandlerResize);
  },
  destroyed() {
    window.removeEventListener("resize", this.eventHandlerResize);
  },
    methods: {
      eventHandlerResize(e) {
        this.sliderWidth = document.getElementsByClassName('form-range')[0].offsetWidth
      },
        onInput(event) {
            this.sliderStep = 1
            if (this.step > 0 && this.isSnapToRange)
                this.sliderStep = this.step

            let num = this.validateNumber(event.target.value)
            this.sliderInput = num
        },
        onSliderInput() {
            this.sliderStep = this.step
        },
        validateNumber(val){
          if(val === '') return ''
            let newVal = val

            if (newVal % this.sliderStep != 0)
                newVal = Math.round(newVal / this.sliderStep ) * this.sliderStep
            return newVal
        },
        onLeave() {
          let newVal = this.sliderValue
          if (newVal >= this.sliderMax)
            this.sliderValue = this.sliderMax

          if (newVal <= this.sliderMin)
            this.sliderValue=  this.sliderMin
        },
        validateValues(values) {
            if (values.length == 0) {
                this.sliderValue = null
                return
            }

            let min = Math.min(...values)
            if (this.sliderValue <= min || this.sliderValue === undefined || this.sliderValue === null){
                this.sliderValue = min
                return
            }

            let max = Math.max(...values)
            if (this.sliderValue >= max){
                this.sliderValue = max
                return
            }
        }
    }
}
</script>

<style>
.input-style{
    min-width: 80px;
}
</style>
