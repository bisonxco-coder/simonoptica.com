<template>
    <div v-if="calculation.isCalculationOpen" class="row d-flex align-items-end h-100">

        <!--  Upper right corner -->
        <div :class="headerStyle">
            <div class="d-flex me-4">
                <!-- Loading indicator -->
                <span v-if="calculation.isLoading" class="spinner-icon large opaque"/>

                <!-- Buttons -->
                <button class="expand-button border-0 me-3" :disabled="calculation.isLoading" @click="onClear()" >{{$translations.footer.calculation.clear}}</button>
                <button class="expand-button border-0" :disabled="calculation.isLoading" @click="onCalculate()" >{{$translations.footer.calculation.calculate}}</button>
            </div>
        </div>

        <!-- Refresh shape button -->
        <div v-if="hasGraphics && !hasErrors()" class="d-flex justify-content-end">
            <button class="expand-button border-0 me-4 mt-3" :disabled="calculation.isLoading" @click="onRefresh()" >{{$translations.footer.calculation.refresh}}</button>
        </div>

      <!-- Warnings -->
      <div v-if="hasWarning()">
        <div class="warning alert-warning">
          <p class="mb-3">{{calculation.left.warning}}</p>
          <p class="mb-0">{{calculation.right.warning}}</p>
        </div>
      </div>

        <!-- DRAWING GRAPHICS CONTAINER -->
        <div v-if="hasGraphics && !hasErrors()" class="position-relative graphics-container">
            <div id="calculation_drawing_container" class="position-absolute graphics-object" />
        </div>

        <!-- Errors -->
        <div v-if="hasErrors()">
            <div class="alert alert-danger">
                <p class="mb-3">{{calculation.left.error}}</p>
                <p class="mb-0">{{calculation.right.error}}</p>
            </div>
        </div>
        <!-- Data -->
        <div class="row">
            <div class="col-6 px-4 mb-md-0 mb-4" v-for="side in ['right', 'left']" :key="side">
                <!-- Title -->
                <div class="row mb-3">
                    <h4 class="fs-6 text-center" v-if="side == 'right'">{{$translations.common.rightLens}}</h4>
                    <h4 class="fs-6 text-center" v-if="side == 'left'">{{$translations.common.leftLens}}</h4>
                </div>

                <!-- Lens -->
                <div class="mb-3 section">
                    <h4 class="variable-label-text">{{$translations.footer.calculation.lens.title}}</h4>
                    <div class="row gx-5">
                        <div class="col-auto section label">
                            <label class="placeholder-text d-flex"> {{$translations.footer.calculation.lens.lensType}} </label>
                            <span>{{calculation[side].lensType}}</span>
                        </div>
                        
                        <div class="col-auto section label">
                            <label class="placeholder-text d-flex">{{$translations.footer.calculation.lens.diameter}}</label>
                            <span>{{calculation[side].diameter}}</span>
                        </div>

                        <div class="col-auto section label">
                            <label class="placeholder-text d-flex">{{$translations.footer.calculation.lens.optimized}}</label>
                            <div class="mb-2">
                                {{boolToText(calculation[side].isOptimized)}}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Central thickness -->
                <div class="mb-3 section">
                    <h4 class="variable-label-text">{{$translations.footer.calculation.centralThickness.title}}</h4>
                    <div class="row gx-5">
                        <div class="col-auto section label">
                            <label class="placeholder-text d-flex">{{$translations.footer.calculation.centralThickness.geometrical}}</label>
                            <span>{{calculation[side].geometrical}}</span>
                        </div>
                        
                        <div class="col-auto section label">
                            <label class="placeholder-text d-flex">{{$translations.footer.calculation.centralThickness.optical}}</label>
                            <span>{{calculation[side].optical}}</span>
                        </div>
                    </div>
                </div>

                <!-- Edge thickness -->
                <div class="mb-3 section">
                    <h4 class="variable-label-text">{{$translations.footer.calculation.edgeThickness.title}}</h4>
                    <div class="row gx-5">
                        <div class="col-auto section label">
                            <label class="placeholder-text d-flex">{{$translations.footer.calculation.edgeThickness.minimal}}</label>
                            <span>{{calculation[side].minimal}}</span>
                        </div>
                        
                        <div class="col-auto section label">
                            <label class="placeholder-text d-flex">{{$translations.footer.calculation.edgeThickness.maximal}}</label>
                            <span>{{calculation[side].maximal}}</span>
                        </div>

                        <div class="col-auto section label">
                            <label class="placeholder-text d-flex">{{$translations.footer.calculation.edgeThickness.top}}</label>
                            <span>{{calculation[side].top}}</span>
                        </div>

                        <div class="col-auto section label">
                            <label class="placeholder-text d-flex">{{$translations.footer.calculation.edgeThickness.down}}</label>
                            <span>{{calculation[side].down}}</span>
                        </div>

                        <div class="col-auto section label">
                            <label class="placeholder-text d-flex">{{$translations.footer.calculation.edgeThickness.nasal}}</label>
                            <span>{{calculation[side].nasal}}</span>
                        </div>

                        <div class="col-auto section label">
                            <label class="placeholder-text d-flex">{{$translations.footer.calculation.edgeThickness.temporal}}</label>
                            <span>{{calculation[side].temporal}}</span>
                        </div>
                    </div>
                </div>

                <!-- Geometry -->
                <div class="mb-3 section">
                    <h4 class="variable-label-text">{{$translations.footer.calculation.geometry.title}}</h4>
                    <div class="row gx-5">
                        <div class="col-auto section label">
                            <label class="placeholder-text d-flex">{{$translations.footer.calculation.geometry.basecurve}}</label>
                            <span>{{calculation[side].basecurve}}</span>
                        </div>
                        
                        <div class="col-auto section label">
                            <label class="placeholder-text d-flex">{{$translations.footer.calculation.geometry.weight}}</label>
                            <span>{{calculation[side].weight}}</span>
                        </div>
                    </div>
                </div>
                
            </div>
        </div>
    </div>
</template>

<script>
import Calculation from '@/utils/calculation';
import Order from '@/utils/order';
import Graphics from '@/utils/graphics';

export default {
    emits: ['onClose'],
    data(){
        return {
            rerenderCount: 3,
            graphicsApp: null,
        }
    },
    computed: {
        calculation() { return this.$store.state.moduleOrders.calculation},
        erp() { return this.$store.state.moduleOrders.erp},
        hasGraphics() {
            return this.erp.getCalculation && this.erp.getCalculation !== undefined && this.calculation.isCalculationOpen
        },
        headerStyle() {
            let style = "d-flex justify-content-end"
            if (this.hasGraphics && !this.hasErrors()) return style
            
            style += " mb-5"
            return style
        },
    },
    watch: {
        "calculation.isCalculationOpen": function(val) {
            if (!val || !this.hasGraphics || this.hasErrors()) return
            this.$nextTick(() => {
                this.renderGraphics(this.erp.getCalculation)
            })
        },
    },
    methods: {
        async onCalculate() {
            if (!Order.isOrderEvaluated()) {
                this.$emit('onClose')
                return
            }

            // Run the calculation
            await Calculation.getCalculation()

            // Render form + assign Edge thickness fields
            this.renderGraphics(this.erp.getCalculation)
        },
        onClear() {
            Calculation.clearCalculationData()
            Graphics.clearDrawingContainer('calculation_drawing_container')
        },
        onRefresh() {
            this.renderGraphics(this.erp.getCalculation)
        },
        renderGraphics(calculationData) {
            if (!this.hasGraphics || this.hasErrors()) return

            this.graphicsApp = Graphics.drawCalculationLenses('calculation_drawing_container', this.graphicsApp, calculationData)
        },
        hasErrors () {
            return (
                (this.calculation.left.error && this.calculation.left.error !== undefined) ||
                (this.calculation.right.error && this.calculation.right.error !== undefined) 
            )
        },
      hasWarning () {
        return (
            (this.calculation.left.warning && this.calculation.left.warning !== undefined) ||
            (this.calculation.right.warning && this.calculation.right.warning !== undefined)
        )
      },
        boolToText(val) {
            return val == true ? this.$translations.dialog.yes : this.$translations.dialog.no
        }
    }
}
</script>

<style scoped lang="scss">
$width: 700px;
$height: 400px;
.graphics-object{
    width: $width;
    height: $height;
    left: calc(50% - $width / 2);
    top: calc(50% - $height / 2);
    z-index: 100;
    @media only screen and (max-width: 800px) { left: calc(60% - 360px); }
    @media only screen and (max-width: 700px) { left: calc(85% - 360px); }
    @media only screen and (max-width: 500px) { left: calc(95% - 360px); }
    @media only screen and (max-width: 450px) { left: calc(100% - 360px); }
    @media only screen and (max-width: 400px) { left: calc(120% - 360px); }
}
.graphics-container{
    height: $height; 
    overflow: auto; 
    overflow-y: clip; 
}
.section {
    &.label {
        min-height: 50px;
    }
    min-height: 70px;
}
.drawing-section-style {
    left: 50%;
    @media only screen and (max-width: 800px) { left: 60% }
    @media only screen and (max-width: 700px) { left: 85% }
    @media only screen and (max-width: 500px) { left: 95% }
    @media only screen and (max-width: 450px) { left: 100% }
    @media only screen and (max-width: 400px) { left: 120% }
}
</style>