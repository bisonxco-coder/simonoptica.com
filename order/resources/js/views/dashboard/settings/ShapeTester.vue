<template>
    <div class="card">
        <panel-input v-model="type" placeholder="Type" />
        <div class="d-flex align-self-start mb-5">
            <div 
                class="me-5"
                v-for="option in typeOptions"
                :key="option"
                :value="option"
            >
                <input 
                    class="form-check-input panel-radio me-3"
                    type="radio" 
                    name="typeOptions"
                    :id="option"
                    :value="option"
                    @change="onTypeOptionChange(option)"
                    :checked="isTypeOptionChecked(option)"
                >
                <label :for="option">{{option}}</label>
            </div>
        </div>

        <textarea v-model="form"
            rows="10" 
            class="form-control border border-1 p-3 border-radius-xl mb-3" 
            placeholder="Form"
        />
        
        <!-- Scale shape options -->
        <div v-if="isSelectedOptionScaleShape" class="row">
            <div class="col-3">
                <panel-input v-model="boxXL" placeholder="boxXL" />
            </div>
            <div class="col-3">
                <panel-input v-model="boxXR" placeholder="boxXR" />
            </div>
            <div class="col-3">
                <panel-input v-model="boxYL" placeholder="boxYL" />
            </div>
            <div class="col-3">
                <panel-input v-model="boxYR" placeholder="boxYR" />
            </div>
        </div>

        <!-- Button box -->
        <div class="d-flex justify-content-md-start justify-content-center flex-md-nowrap flex-wrap" id="result">
            <div class="col-auto pt-2 me-2">
                <input id="shapeTester_autorun"
                    v-model="autorun"
                    class="form-check-input panel-checkbox" 
                    type="checkbox"
                />
                <label class="form-check-label px-3" for="shapeTester_autorun">Autorun</label>
            </div>
            <div class="col-auto">
                <button class="btn btn-secondary panel-button border-0 px-5 my-1 me-2" @click="onRun">Run</button>
                <button class="btn btn-secondary panel-button border-0 px-5 my-1 me-2" @click="onClear">{{$translations.common.clear}}</button>
            </div>
            
            <div class="col d-flex flex-wrap align-self-center ms-4">
                <div 
                    class="me-5 mb-2"
                    v-for="option in options"
                    :key="option"
                    :value="option"
                    style="width: 170px"
                >
                    <input 
                        class="form-check-input panel-radio me-3"
                        type="radio" 
                        name="options"
                        :id="option"
                        :value="option"
                        @change="onOptionChange(option)"
                        :checked="isOptionChecked(option)"
                    >
                    <label :for="option">{{option}}</label>
                </div>
            </div>
        </div>

        <hr>

        <!-- GRAPHICS CONTAINER -->
        <div v-if="isSelectedOptionDraw" id="shapeTester_drawing_container" class="position-relative" style="height:30rem" />

        <!-- CONVERSION CONTAINER -->
        <div v-else>
            <div class="d-flex justify-content-md-start justify-content-center flex-md-nowrap flex-wrap">
                <button class="btn btn-secondary panel-button border-0 px-5 my-1 me-2" @click="onCopyResultToForm">Copy To Form</button>
            </div>

            <textarea
                v-model="result"
                rows="10" 
                class="form-control border border-1 p-3 border-radius-xl mb-3" 
                placeholder="Result"
            />
        </div>

    </div>
</template>

<script>
import PanelInput from '@/components/PanelInput.vue'
import Graphics  from '@/utils/graphics.js'
import Tracer from '@/utils/tracer.js'
import Helpers  from '@/utils/helpers.js'


export default {
    components: { PanelInput},
    data() {
        return {
            typeOptions: ['HOYVCA', 'TBLNAT', 'OMAVCA'],
            autorun: true,
            selectedTypeOption: 'HOYVCA',
            type: 'HOYVCA',
            form: null,
            result: null,
            boxXL: null, 
            boxXR: null, 
            boxYL: null, 
            boxYR: null,
            options: ['Draw', 'Decode Hex', 'Decode Base64', 'Encode Hex', 'Encode Base64', 'Interpolate', 'Scale Shape'],
            selectedOption: 'Draw',
            graphicsApp: null,
        }
    },
    computed: {
        orders() {return this.$store.state.moduleOrders},
        isSelectedOptionDraw() { return this.selectedOption == 'Draw'},
        isSelectedOptionDecodeFromHex() { return this.selectedOption == 'Decode Hex'},
        isSelectedOptionDecodeFromBase64() { return this.selectedOption == 'Decode Base64'},
        isSelectedOptionEncodeToHex() { return this.selectedOption == 'Encode Hex'},
        isSelectedOptionEncodeToBase64() { return this.selectedOption == 'Encode Base64'},
        isSelectedOptionInterpolate() { return this.selectedOption == 'Interpolate'},
        isSelectedOptionScaleShape() { return this.selectedOption == 'Scale Shape'},
    },
    mounted() {
        this.setDashboardTitle()
    },
    methods: {
        async onRun() {
            try {
                if (!this.form || this.form === undefined) return
                
                this.result = null
                if (this.isSelectedOptionDraw){
                    let formData = Tracer.decodeTracerFormData(this.form, false)
                    this.$nextTick(() => {
                        this.graphicsApp = Graphics.drawForm('shapeTester_drawing_container', this.graphicsApp, formData)
                    })
                }
                else if(this.isSelectedOptionDecodeFromHex){
                    this.result = Helpers.hexToString(this.form)
                }
                else if(this.isSelectedOptionDecodeFromBase64){
                    this.result = Helpers.base64ToString(this.form)
                }
                else if(this.isSelectedOptionEncodeToHex){
                    this.result = Helpers.stringToHex(this.form)
                }
                else if(this.isSelectedOptionEncodeToBase64){
                    this.result = Helpers.stringToBase64(this.form)
                }
                else if(this.isSelectedOptionInterpolate){
                    if (!this.type || this.type === undefined || this.type == '') return
                    this.result = await this.interpolate(this.type, this.form)
                }
                else if (this.isSelectedOptionScaleShape){
                    if (!this.type || this.type === undefined || this.type == '') return
                    this.result = await this.scaleShape(this.type, this.form, this.boxXL, this.boxXR, this.boxYR, this.boxYL)
                }
            } catch (error) {
                this.orders.isLoading = false
                console.error(e)
                this.$store.dispatch('notify', {
                    message: error,
                    duration: 2
                })
            }
            window.location.href = "#result"
        },
        onClear() {
            this.result = null
            Graphics.clearDrawingContainer('shapeTester_drawing_container')
        },
        onOptionChange(option) {
            this.selectedOption = option
            if (this.autorun) this.onRun()
        },
        onTypeOptionChange(option) {
            this.type = option
        },
        onCopyResultToForm() {
            this.form = this.result
        },
        setDashboardTitle() {
            this.$store.commit('setDashboardTitle', {
                title: "Shape Tester",
                subTitle: ""
            })
        },
        isOptionChecked(option) {
            return this.selectedOption == option
        },
        isTypeOptionChecked(option) {
            return this.selectedTypeOption == option
        },
        async interpolate(type, form) {
            this.orders.isLoading = false
            return await this.$store.dispatch('interpolate', {
                type: type,
                vca: form
            })
            .then(response => {
                this.orders.isLoading = false
                return response
            }, e => {
                this.orders.isLoading = false
                
                this.$store.dispatch('notify', {
                    message: e.data.message,
                })
                console.error(e)
            })
        },
        async scaleShape(type, form, boxXR, boxXL, boxYR, boxYL) {
            this.orders.isLoading = false
            return await this.$store.dispatch('scaleShape', {
                type: type,
                trcstr: form,
                boxXR: boxXR,
                boxYR: boxYR,
                boxXL: boxXL,
                boxYL: boxYL,
            })
            .then((response) => {
                this.orders.isLoading = false
                return response.ShapeList[0].VCA
            }, e => {
                this.orders.isLoading = false
                
                this.$store.dispatch('notify', {
                    message: e.data.message,
                })
                console.error(e)
            });
        },
    }
}
</script>