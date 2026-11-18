<template>
    <div :class="'card pt-3 pb-4 px-5 mb-3 ' + (isComponentEnabled ? '' : 'component-disabled')" id="formAndGeometry" >
        <h4 class="panel-title pt-4 pb-4">{{$translations.newOrder.details.formAndGeometry.title}}</h4>

        <!-- SWITCH buttons in header -->
        <div class="d-flex justify-content-md-between justify-content-start flex-wrap mb-5 mt-2">

            <!-- "Shape from tracer or database" -->
            <div v-if="isShapeMode" class="form-switch d-flex mb-3 me-md-0 me-3">
                <input
                    id="formAndGeometry_shapeFromTracerOrDatabase"
                    v-model="formAndGeometry.options.shapeFromTracerOrDatabase"
                    class="form-check-input panel-switch position-absolute"
                    type="checkbox"
                    :disabled="formAndGeometryProperties.options.shapeFromTracerOrDatabase.disabled"
                />
                <label class="form-check-label ms-4 ps-2" for="formAndGeometry_shapeFromTracerOrDatabase">
                    {{$translations.newOrder.details.formAndGeometry.shapeFromTracerOrDatabase}}
                </label>
            </div>

            <!-- Add empty div to maintain the same position of Forced Decentration switch and keep it responsive -->
            <div v-if="isForcedDecentrationMode"/>

            <!-- "Forced Decentration" -->
            <div class="form-switch d-flex" v-if="showForcedDecentration === 'YES'">
                <input
                    id="formAndGeometry_forcedDecentration"
                    v-model="forcedDecentration"
                    @change="onForcedDecentrationChange"
                    class="form-check-input panel-switch position-absolute"
                    type="checkbox"
                    :disabled="formAndGeometryProperties.options.forcedDecentration.disabled"
                />
                <label class="form-check-label ms-4 ps-2" for="formAndGeometry_forcedDecentration">
                    {{$translations.newOrder.details.formAndGeometry.forcedDecentration.title}}
                </label>
            </div>
        </div>

        <!-- "Shape from tracer or database" - BUTTONS -->
        <div v-if="formAndGeometry.options.shapeFromTracerOrDatabase && isShapeMode" class="d-flex justify-content-md-around justify-content-start flex-md-nowrap flex-wrap mb-5">
            <button
                class="btn btn-outline-primary panel-button2 label-16 border border-1 border-radius-xl px-xl-3 px-lg-4 px-md-3 py-1 me-sm-4 me-4 m-md-0 m-3 mx-md-2 mx-0 my-2"
                @click="onTraceShape"
            >{{$translations.newOrder.details.formAndGeometry.traceShape.title}}</button>
            <button
                class="btn btn-outline-primary panel-button2 label-16 border border-1 border-radius-xl px-xl-3 px-lg-4 px-md-3 py-1 me-sm-4 me-4 m-md-0 m-3 mx-md-2 mx-0 my-2"
                @click="onLoadFromDatabase"
            ><p style="display: contents" v-html="$translations.newOrder.details.formAndGeometry.loadFromDatabase.title"/></button>
            <button
                class="btn btn-outline-primary panel-button2 label-16 border border-1 border-radius-xl px-xl-3 px-lg-4 px-md-3 py-1 me-sm-4 me-4 m-md-0 m-3 mx-md-2 mx-0 my-2"
                @click="onLoadFromDisk"
            >{{$translations.newOrder.details.formAndGeometry.loadFromDisk.title}}</button>
            <button
                class="btn btn-outline-primary panel-button2 label-16 border border-1 border-radius-xl px-xl-3 px-lg-4 px-md-3 py-1 me-sm-4 me-4 m-md-0 m-3 mx-md-2 mx-0 my-2"
                @click="onHistory"
            >{{$translations.newOrder.details.formAndGeometry.history.title}}</button>
          <button v-if="showCenterringData === 'YES'"
              class="btn btn-outline-primary panel-button2 label-16 border border-1 border-radius-xl px-xl-3 px-lg-4 px-md-3 py-1 me-sm-4 me-4 m-md-0 m-3 mx-md-2 mx-0 my-2"
              @click="onAnaylyseB2B"
          >{{$translations.newOrder.details.formAndGeometry.analyseB2B.title}}</button>
        </div>

        <!-- DIAMETER -->
        <div class="row" style="z-index:1">
            <!-- Right lens -->
            <div class="col-md-6 col-12 mb-3">
                <h4 class="fs-6 d-flex justify-content-center mb-3">{{$translations.common.rightLens}}</h4>

                <div class="d-flex justify-content-center mb-3 ">
                    <input
                        v-model="formAndGeometry.right.type"
                        type="radio"
                        class="btn-check"
                        id="radio_right_round"
                        autocomplete="off"
                        value="R"
                        :disabled="rightSwitchDisabledLogic('R')"
                        :checked="rightSwitchCheckedLogic('R')"
                    >
                    <label class="btn btn-secondary radio-border-left radio-switch flex-grow-1" for="radio_right_round">{{$translations.newOrder.details.formAndGeometry.round}}</label>

                    <input
                        v-model="formAndGeometry.right.type"
                        type="radio"
                        class="btn-check"
                        id="radio_right_oval"
                        autocomplete="off"
                        value="O"
                        :disabled="rightSwitchDisabledLogic('O')"
                        :checked="rightSwitchCheckedLogic('O')"
                    >
                    <label class="btn btn-secondary radio-border-right radio-switch flex-grow-1" for="radio_right_oval">{{$translations.newOrder.details.formAndGeometry.oval}}</label>
                </div>

                <panel-slider
                    :title="$translations.newOrder.details.formAndGeometry.lensDiameter"
                    v-model="formAndGeometry.right.lensDiameter"
                    :values="displayLensDiameterValues('right')"
                    :step="1"
                    type="number"
                    :disabled="formAndGeometryProperties.right.lensDiameter.disabled"
                    v-model:errorMessage="formAndGeometryProperties.right.lensDiameter.errorMessage"
                    :preserveOnDisable="!formAndGeometryProperties.right.lensDiameter.resetIfDisabled"
                    :snapInputToRange="product.right.isStock"
                >
                    <template #body>
                        <panel-input
                            :placeholder="$translations.newOrder.details.formAndGeometry.opticalDiameter"
                            v-model="formAndGeometry.right.opticalDiameter"
                            :disabled="formAndGeometryProperties.right.opticalDiameter.disabled"
                            :preserveOnDisable="!formAndGeometryProperties.right.opticalDiameter.resetIfDisabled"
                        />
                        <panel-input
                            v-if="formAndGeometryProperties.right.elipticalDiameter.displayed"
                            :placeholder="$translations.newOrder.details.formAndGeometry.elipticalDiameter"
                            v-model="formAndGeometry.right.elipticalDiameter"
                            :min="formAndGeometry.right.lensDiameter - formAndGeometry.right.ovalMaxDiff"
                            :max="formAndGeometry.right.lensDiameter - 1"
                            type="number"
                            :disabled="formAndGeometryProperties.right.elipticalDiameter.disabled"
                            v-model:errorMessage="formAndGeometryProperties.right.elipticalDiameter.errorMessage"
                            :preserveOnDisable="!formAndGeometryProperties.right.elipticalDiameter.resetIfDisabled"
                        />
                    </template>
                </panel-slider>

                <div v-if="isShapeMode" class="d-flex mb-4">
                    <input id="formAndGeometry_right_isOptimized"
                        v-model="formAndGeometry.right.isOptimized"
                        class="form-check-input panel-checkbox"
                        type="checkbox"
                        :disabled="formAndGeometryProperties.right.isOptimized.disabled"
                    />
                    <label class="form-check-label ms-4 px-3 position-absolute" for="formAndGeometry_right_isOptimized">
                        {{$translations.newOrder.details.formAndGeometry.optimized}}
                    </label>
                </div>
            </div>
            <!-- Left lens -->
            <div class="col-md-6 col-12 mb-3">
                <h4 class="fs-6 d-flex justify-content-center mb-3">{{$translations.common.leftLens}}</h4>

                <div class="d-flex justify-content-center mb-3">
                    <input
                        v-model="formAndGeometry.left.type"
                        type="radio"
                        class="btn-check"
                        id="radio_left_round"
                        autocomplete="off"
                        value="R"
                        :disabled="leftSwitchDisabledLogic('R')"
                        :checked="leftSwitchCheckedLogic('R')"
                    >
                    <label class="btn btn-secondary radio-border-left radio-switch flex-grow-1" for="radio_left_round">{{$translations.newOrder.details.formAndGeometry.round}}</label>

                    <input
                        v-model="formAndGeometry.left.type"
                        type="radio"
                        class="btn-check"
                        id="radio_left_oval"
                        autocomplete="off"
                        value="O"
                        :disabled="leftSwitchDisabledLogic('O')"
                        :checked="leftSwitchCheckedLogic('O')"
                    >
                    <label class="btn btn-secondary radio-border-right radio-switch flex-grow-1" for="radio_left_oval">{{$translations.newOrder.details.formAndGeometry.oval}}</label>
                </div>

                <panel-slider
                    :title="$translations.newOrder.details.formAndGeometry.lensDiameter"
                    v-model="formAndGeometry.left.lensDiameter"
                    :values="displayLensDiameterValues('left')"
                    :step="1"
                    type="number"
                    :disabled="formAndGeometryProperties.left.lensDiameter.disabled"
                    v-model:errorMessage="formAndGeometryProperties.left.lensDiameter.errorMessage"
                    :preserveOnDisable="!formAndGeometryProperties.left.lensDiameter.resetIfDisabled"
                    :snapInputToRange="product.left.isStock"
                >
                    <template #body>
                        <panel-input
                            type="number"
                            :placeholder="$translations.newOrder.details.formAndGeometry.opticalDiameter"
                            v-model="formAndGeometry.left.opticalDiameter"
                            :disabled="formAndGeometryProperties.left.opticalDiameter.disabled"
                            :preserveOnDisable="!formAndGeometryProperties.left.opticalDiameter.resetIfDisabled"
                        />
                        <panel-input
                            v-if="formAndGeometryProperties.left.elipticalDiameter.displayed"
                            :placeholder="$translations.newOrder.details.formAndGeometry.elipticalDiameter"
                            v-model="formAndGeometry.left.elipticalDiameter"
                            :min="formAndGeometry.left.lensDiameter - formAndGeometry.left.ovalMaxDiff"
                            :max="formAndGeometry.left.lensDiameter - 1"
                            type="number"
                            :step="1"
                            :disabled="formAndGeometryProperties.left.elipticalDiameter.disabled"
                            v-model:errorMessage="formAndGeometryProperties.left.elipticalDiameter.errorMessage"
                            :preserveOnDisable="!formAndGeometryProperties.left.elipticalDiameter.resetIfDisabled"
                        />
                    </template>
                </panel-slider>

                <div v-if="isShapeMode" class="d-flex mb-4">
                    <input id="formAndGeometry_left_isOptimized"
                        v-model="formAndGeometry.left.isOptimized"
                        class="form-check-input panel-checkbox"
                        type="checkbox"
                        :disabled="formAndGeometryProperties.left.isOptimized.disabled"
                    />
                    <label class="form-check-label ms-4 px-3 position-absolute" for="formAndGeometry_left_isOptimized">
                        {{$translations.newOrder.details.formAndGeometry.optimized}}
                    </label>
                </div>
            </div>
        </div>

        <!-- Forced Decentration mode -->
        <div v-if="isForcedDecentrationMode">

            <!-- Decentration Fields -->
            <div class="row">

                <!-- Right lens -->
                <div class="col-md-6 col-12 mb-md-0 mb-4">
                    <h4 v-if="isVerticalView" class="fs-6 d-flex justify-content-center mb-3">{{$translations.common.rightLens}}</h4>

                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <panel-input
                                :placeholder="$translations.newOrder.details.formAndGeometry.forcedDecentration.placeholders.decentrationX"
                                v-model="formAndGeometry.right.forcedDecentration.decentrationX"
                                :min="formAndGeometry.right.forcedDecentration.decentrationXMin"
                                :max="formAndGeometry.right.forcedDecentration.decentrationXMax"
                                type="number"
                                :step="0.01"
                                :disabled="formAndGeometryProperties.right.forcedDecentration.decentrationX.disabled"
                                v-model:errorMessage="formAndGeometryProperties.right.forcedDecentration.decentrationX.errorMessage"
                            />
                        </div>
                        <div class="col-sm-6 col-12">
                            <panel-input
                                :placeholder="$translations.newOrder.details.formAndGeometry.forcedDecentration.placeholders.decentrationY"
                                v-model="formAndGeometry.right.forcedDecentration.decentrationY"
                                :min="formAndGeometry.right.forcedDecentration.decentrationYMin"
                                :max="formAndGeometry.right.forcedDecentration.decentrationYMax"
                                type="number"
                                :step="0.01"
                                :disabled="formAndGeometryProperties.right.forcedDecentration.decentrationY.disabled"
                                v-model:errorMessage="formAndGeometryProperties.right.forcedDecentration.decentrationY.errorMessage"
                            />
                        </div>
                    </div>
                </div>

                <!-- Left lens -->
                <div class="col-md-6 col-12">
                    <h4 v-if="isVerticalView" class="fs-6 d-flex justify-content-center mb-3">{{$translations.common.leftLens}}</h4>

                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <panel-input
                                :placeholder="$translations.newOrder.details.formAndGeometry.forcedDecentration.placeholders.decentrationX"
                                v-model="formAndGeometry.left.forcedDecentration.decentrationX"
                                :min="formAndGeometry.left.forcedDecentration.decentrationXMin"
                                :max="formAndGeometry.left.forcedDecentration.decentrationXMax"
                                type="number"
                                :step="0.01"
                                :disabled="formAndGeometryProperties.left.forcedDecentration.decentrationX.disabled"
                                v-model:errorMessage="formAndGeometryProperties.left.forcedDecentration.decentrationX.errorMessage"
                            />
                        </div>
                        <div class="col-sm-6 col-12">
                            <panel-input
                                :placeholder="$translations.newOrder.details.formAndGeometry.forcedDecentration.placeholders.decentrationY"
                                v-model="formAndGeometry.left.forcedDecentration.decentrationY"
                                :min="formAndGeometry.left.forcedDecentration.decentrationYMin"
                                :max="formAndGeometry.left.forcedDecentration.decentrationYMax"
                                type="number"
                                :step="0.01"
                                :disabled="formAndGeometryProperties.left.forcedDecentration.decentrationY.disabled"
                                v-model:errorMessage="formAndGeometryProperties.left.forcedDecentration.decentrationY.errorMessage"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <hr class="mb-3 mt-3">
        </div>

        <!-- Shape mode -->
        <div v-if="isShapeMode">

            <!-- DBL -->
            <div class="row mb-md-3 mb-sm-4 mb-3" style="z-index:1">
                <div class="col-lg-4 col-md-4 col-sm-4 col-12" />
                <div class="col-lg-4 col-md-4 col-sm-4 col-12">
                    <panel-input
                        style="z-index:1"
                        :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.dbl"
                        v-model="formAndGeometry.dbl"
                        :min="formAndGeometry.dblMin"
                        :max="formAndGeometry.dblMax"
                        :step="0.1"
                        :decimals="1"
                        type="number"
                        :preserveOnDisable="!formAndGeometryProperties.dbl.resetIfDisabled"
                        :disabled="formAndGeometryProperties.dbl.disabled"
                        v-model:errorMessage="formAndGeometryProperties.dbl.errorMessage"
                    />
                </div>
            </div>

            <!-- DRAWING GRAPHICS CONTAINER -->
            <div class="d-flex justify-content-center">
                <div class="d-flex" :style="drawingContainerStyle">
                    <div 
                        v-if="hasGraphics" 
                        id="formAndGeometry_drawing_container" 
                        class="graphics-container position-relative"
                    />
                </div>
            </div>

            <!-- Form buttons -->
            <div v-if="!isDrillHolesExpanded" class="d-flex justify-content-md-between justify-content-center flex-md-nowrap flex-wrap my-3 mb-4" style="z-index:1">
                <div class="d-flex justify-content-md-between justify-content-center flex-md-nowrap flex-wrap mb-sm-4 mb-0">
                    <!-- Left side -->
                    <button
                        class="ms-3 d-flex expand-button dark border-0"
                        style="z-index:1"
                        @click="onDeleteForm"
                        :disabled="formAndGeometryProperties.isDeleteFormButtonDisabled"
                    >{{$translations.newOrder.details.formAndGeometry.deleteForm}}
                    </button>
                    <button
                        class="ms-sm-4 ms-3 d-flex expand-button border-0"
                        style="z-index:1"
                        @click="onRefreshForm"
                        :disabled="formAndGeometryProperties.isRefreshFormButtonDisabled"
                    >{{$translations.newOrder.details.formAndGeometry.refreshForm}}
                    </button>
                </div>
                <div class="d-flex justify-content-md-between justify-content-center flex-md-nowrap flex-wrap">
                    <!-- Right side -->
                    <!-- <div class="me-3 d-flex mt-sm-0 mt-4">
                        <button
                            class="d-flex expand-button border-0"
                            @click="onShowLensesScaled1_1"
                            :disabled="!isComponentEnabled || !form.shape.current.decodedData"
                        >
                            <span :class="'no-hover px-3 ' + showLensesScaledToggleIconStyle"></span>
                            Show lenses scaled 1:1
                        </button>
                    </div> -->
                </div>
            </div>

            <!-- SHAPE PARAMETERS -->
            <div v-if="!isDrillHolesExpanded" class="row" style="z-index:1">
                <!-- Right lens -->
                <div class="col-md-6 col-12 mb-md-0 mb-4">
                    <h4 v-if="isVerticalView" class="fs-6 d-flex justify-content-center mb-3">{{$translations.common.rightLens}}</h4>

                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <panel-input
                                :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.pd"
                                v-model="refraction.right.pd"
                                type="number"
                                :min="refraction.right.pdMin"
                                :max="refraction.right.pdMax"
                                :step="0.1"
                                :decimals="1"
                                :disabled="!product.right.hasLens || !isComponentEnabled"
                                v-model:errorMessage="refractionProperties.right.pd.errorMessage"
                            />
                        </div>
                        <div class="col-sm-6 col-12">
                            <panel-input
                                :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.height"
                                v-model="refraction.right.height"
                                type="number"
                                :min="refraction.right.heightMin"
                                :max="refraction.right.heightMax"
                                :step="0.1"
                                :decimals="1"
                                :disabled="!product.right.hasLens || !isComponentEnabled"
                                v-model:errorMessage="refractionProperties.right.height.errorMessage"
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <panel-input
                                :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.boxWidth"
                                v-model="formAndGeometry.right.boxWidth"
                                :step="0.1"
                                :decimals="1"
                                type="number"
                                :preserveOnDisable="!formAndGeometryProperties.right.boxWidth.resetIfDisabled"
                                :disabled="formAndGeometryProperties.right.boxWidth.disabled"
                                @updateValue="setBoxWidth"
                            />
                        </div>
                        <div class="col-sm-6 col-12">
                            <panel-input
                                :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.boxHeight"
                                v-model="formAndGeometry.right.boxHeight"
                                :step="0.1"
                                :decimals="1"
                                type="number"
                                :preserveOnDisable="!formAndGeometryProperties.right.boxHeight.resetIfDisabled"
                                :disabled="formAndGeometryProperties.right.boxHeight.disabled"
                                @updateValue="setBoxHeight"
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <panel-input
                                id="formAndGeometry_right_decentrationX"
                                :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.decentrationX"
                                v-model="formAndGeometry.right.decentrationX"
                                :step="0.01"
                                :decimals="2"
                                type="number"
                                :disabled="true"
                                preserveOnDisable
                            />
                        </div>
                        <div class="col-sm-6 col-12">
                            <panel-input
                                id="formAndGeometry_right_decentrationY"
                                :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.decentrationY"
                                v-model="formAndGeometry.right.decentrationY"
                                :step="0.01"
                                :decimals="2"
                                type="number"
                                :disabled="true"
                                preserveOnDisable
                            />
                        </div>
                    </div>

                    <panel-input
                        :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.minimumDiameter"
                        v-model="formAndGeometry.right.minimumDiameter"
                        type="number"
                        :disabled="true"
                        preserveOnDisable
                    />
                </div>
                <!-- Left lens -->
                <div class="col-md-6 col-12">
                    <h4 v-if="isVerticalView" class="fs-6 d-flex justify-content-center mb-3">{{$translations.common.leftLens}}</h4>

                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <panel-input
                                placeholder="PD"
                                v-model="refraction.left.pd"
                                type="number"
                                :min="refraction.left.pdMin"
                                :max="refraction.left.pdMax"
                                :step="0.1"
                                :decimals="1"
                                :disabled="!product.left.hasLens || !isComponentEnabled"
                                v-model:errorMessage="refractionProperties.left.pd.errorMessage"
                            />
                        </div>
                        <div class="col-sm-6 col-12">
                            <panel-input
                                :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.height"
                                v-model="refraction.left.height"
                                type="number"
                                :min="refraction.left.heightMin"
                                :max="refraction.left.heightMax"
                                :step="0.1"
                                :decimals="1"
                                :disabled="!product.left.hasLens || !isComponentEnabled"
                                v-model:errorMessage="refractionProperties.left.height.errorMessage"
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <panel-input
                                :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.boxWidth"
                                v-model="formAndGeometry.left.boxWidth"
                                :step="0.1"
                                :decimals="1"
                                type="number"
                                :preserveOnDisable="!formAndGeometryProperties.left.boxWidth.resetIfDisabled"
                                :disabled="formAndGeometryProperties.left.boxWidth.disabled"
                            />
                        </div>
                        <div class="col-sm-6 col-12">
                            <panel-input
                                :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.boxHeight"
                                v-model="formAndGeometry.left.boxHeight"
                                :step="0.1"
                                :decimals="1"
                                type="number"
                                :preserveOnDisable="!formAndGeometryProperties.left.boxHeight.resetIfDisabled"
                                :disabled="formAndGeometryProperties.left.boxHeight.disabled"
                            />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6 col-12">
                            <panel-input
                                id="formAndGeometry_left_decentrationX"
                                :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.decentrationX"
                                v-model="formAndGeometry.left.decentrationX"
                                :step="0.01"
                                :decimals="2"
                                type="number"
                                :disabled="true"
                                preserveOnDisable
                            />
                        </div>
                        <div class="col-sm-6 col-12">
                            <panel-input
                                id="formAndGeometry_left_decentrationY"
                                :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.decentrationY"
                                v-model="formAndGeometry.left.decentrationY"
                                :step="0.01"
                                :decimals="2"
                                type="number"
                                :disabled="true"
                                preserveOnDisable
                            />
                        </div>
                    </div>

                    <panel-input
                        :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.minimumDiameter"
                        v-model="formAndGeometry.left.minimumDiameter"
                        type="number"
                        :disabled="true"
                        preserveOnDisable
                    />
                </div>
                <hr class="mb-3 mt-md-2 mt-sm-2">
            </div>

            <!-- DRILL HOLES PARAMETERS-->
            <div v-if="isDrillHolesExpanded">
                <hr class="mb-3 mt-3">
                <label class="variable-label-text mb-4">{{$translations.newOrder.details.formAndGeometry.drillHoles.title}}</label>

                <div class="form-switch d-flex flex-column flex-sm-row mb-4 pb-3 justify-content-between ">
                    <input
                        id="drillHoles_isSameLeftRight"
                        v-model="isDrillHolesSameLeftAndRight"
                        @change="onDrillHolesSameLeftRightChange"
                        class="form-check-input panel-switch position-absolute"
                        type="checkbox"
                        :disabled="!isComponentEnabled"
                    />
                    <label class="form-check-label ms-4 ps-2" for="drillHoles_isSameLeftRight">{{$translations.common.sameLeftAndRight}}</label>
                </div>

                <div class="table-responsive mb-5">
                    <table class="table table-borderless table-sm">
                        <tbody>
                            <tr>
                                <!-- HEADER - Right lens drill holes -->
                                <td class="form-table-header-style form-table-header-text text-center">{{$translations.newOrder.details.formAndGeometry.drillHoles.startX}}</td>
                                <td class="form-table-header-style form-table-header-text text-center">{{$translations.newOrder.details.formAndGeometry.drillHoles.startY}}</td>
                                <td class="form-table-header-style form-table-header-text text-center">{{$translations.newOrder.details.formAndGeometry.drillHoles.endX}}</td>
                                <td class="form-table-header-style form-table-header-text text-center">{{$translations.newOrder.details.formAndGeometry.drillHoles.endY}}</td>
                                <td class="form-table-header-style form-table-header-text text-center">{{$translations.newOrder.details.formAndGeometry.drillHoles.diam}}</td>
                                <td class="form-table-header-style form-table-header-text text-center">{{$translations.newOrder.details.formAndGeometry.drillHoles.depth}}</td>
                                <td class="form-table-header-style" />

                                <!-- HEADER - Delete button -->
                                <td class="form-table-header-delete-style form-table-header-text text-center">{{$translations.newOrder.details.formAndGeometry.drillHoles.delete}}</td>

                                <!-- HEADER - Vertical line -->
                                <td rowspan="0" class="p-0 bg-dark opacity-25 ">
                                    <hr style="width: 1px;">
                                </td>
                                <td rowspan="0" />

                                <!-- HEADER - Left lens drill holes-->
                                <td class="form-table-header-style form-table-header-text text-center">{{$translations.newOrder.details.formAndGeometry.drillHoles.startX}}</td>
                                <td class="form-table-header-style form-table-header-text text-center">{{$translations.newOrder.details.formAndGeometry.drillHoles.startY}}</td>
                                <td class="form-table-header-style form-table-header-text text-center">{{$translations.newOrder.details.formAndGeometry.drillHoles.endX}}</td>
                                <td class="form-table-header-style form-table-header-text text-center">{{$translations.newOrder.details.formAndGeometry.drillHoles.endY}}</td>
                                <td class="form-table-header-style form-table-header-text text-center">{{$translations.newOrder.details.formAndGeometry.drillHoles.diam}}</td>
                                <td class="form-table-header-style form-table-header-text text-center">{{$translations.newOrder.details.formAndGeometry.drillHoles.depth}}</td>
                                <td class="form-table-header-style" />
                            </tr>
                            <tr v-for="drillHole in formAndGeometry.drillHoles" :key="drillHole" :value="drillHole">
                                <!-- Right lens drill holes -->
                                <td name="drillsXR">
                                    <panel-input dense placeholder="0" v-model="drillHole.right.startX" type="number" :step="0.01" :disabled="!isComponentEnabled"/>
                                </td>
                                <td name="drillsYR">
                                    <panel-input dense placeholder="0" v-model="drillHole.right.startY" type="number" :step="0.01" :disabled="!isComponentEnabled"/>
                                </td>
                                <td name="drilleXR">
                                    <panel-input dense placeholder="0" v-model="drillHole.right.endX" type="number" :step="0.01" :disabled="!isComponentEnabled"/>
                                </td>
                                <td name="drilleYR">
                                    <panel-input dense placeholder="0" v-model="drillHole.right.endY" type="number" :step="0.01" :disabled="!isComponentEnabled"/>
                                </td>
                                <td name="drilldiR">
                                    <panel-input dense placeholder="0" v-model="drillHole.right.diam" type="number" :step="0.01" :disabled="!isComponentEnabled"/>
                                </td>
                                <td name="drilldeR">
                                    <panel-input dense placeholder="0" v-model="drillHole.right.depth" type="number" :step="0.01" :disabled="!isComponentEnabled"/>
                                </td>
                                <td name="directionR">
                                    <panel-select
                                        dense
                                        placeholder="0"
                                        v-model="drillHole.right.direction"
                                        :selection="formAndGeometry.drillHolesDirectionSelection"
                                        :notNull="true"
                                        :disabled="!isComponentEnabled"
                                    />
                                </td>

                                <!-- Delete button -->
                                <td class="d-flex justify-content-center">
                                    <button class="expand-button dark border-0 pt-1" @click="onDeleteDrillHole(drillHole)" :disabled="!isComponentEnabled">X</button>
                                </td>

                                <!-- Left lens drill holes-->
                                <td name="drillsXL">
                                    <panel-input dense placeholder="0" v-model="drillHole.left.startX" type="number" :step="0.01" :isImmutable="formAndGeometry.isDrillHolesSameLeftAndRight" :disabled="!isComponentEnabled"/>
                                </td>
                                <td name="drillsYL">
                                    <panel-input dense placeholder="0" v-model="drillHole.left.startY" type="number" :step="0.01" :isImmutable="formAndGeometry.isDrillHolesSameLeftAndRight" :disabled="!isComponentEnabled"/>
                                </td>
                                <td name="drilleXL">
                                    <panel-input dense placeholder="0" v-model="drillHole.left.endX" type="number" :step="0.01" :isImmutable="formAndGeometry.isDrillHolesSameLeftAndRight" :disabled="!isComponentEnabled"/>
                                </td>
                                <td name="drilleYL">
                                    <panel-input dense placeholder="0" v-model="drillHole.left.endY" type="number" :step="0.01" :isImmutable="formAndGeometry.isDrillHolesSameLeftAndRight" :disabled="!isComponentEnabled"/>
                                </td>
                                <td name="drilldiL">
                                    <panel-input dense placeholder="0" v-model="drillHole.left.diam" type="number" :step="0.01" :isImmutable="formAndGeometry.isDrillHolesSameLeftAndRight" :disabled="!isComponentEnabled"/>
                                </td>
                                <td name="drilldeL">
                                    <panel-input dense placeholder="0" v-model="drillHole.left.depth" type="number" :step="0.01" :isImmutable="formAndGeometry.isDrillHolesSameLeftAndRight" :disabled="!isComponentEnabled"/>
                                </td>
                                <td name="directionL">
                                    <panel-select
                                        dense
                                        placeholder="0"
                                        v-model="drillHole.left.direction"
                                        :selection="formAndGeometry.drillHolesDirectionSelection"
                                        :notNull="true"
                                        :disabled="!isComponentEnabled"
                                        :isImmutable="formAndGeometry.isDrillHolesSameLeftAndRight"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- DRILL HOLES toggle button -->
            <div class="d-flex justify-content-md-between justify-content-center flex-md-nowrap flex-wrap mt-0">
                <!-- Left side -->
                <div class="d-flex justify-content-md-start justify-content-center ">
                    <div v-if="isDrillHolesExpanded" class="d-block">
                        <panel-input
                            :placeholder="$translations.newOrder.details.formAndGeometry.drillHoles.placeholders.midEdgeThkn"
                            v-model="formAndGeometry.minEdgeThickness"
                            step="0.1"
                            type="number"
                            :disabled="!isComponentEnabled"
                        />
                    </div>
                </div>
                <!-- Right side -->
                <div v-if="isDrillHolesExpanded" class="d-flex justify-content-md-end justify-content-center align-items-center">
                    <button
                        v-if="isDrillHolesExpanded"
                        class="me-3 d-flex expand-button dark border-0 mb-2 pb-3"
                        @click="onCancelDrillHoles"
                        :disabled="!isComponentEnabled"
                    >
                        {{$translations.newOrder.details.formAndGeometry.drillHoles.cancelDrillHoles}}
                    </button>
                    <button
                        class="me-3 d-flex expand-button border-0 mb-2 pb-3"
                        @click="onConfirmDrillHoles"
                        :disabled="!isComponentEnabled"
                    >
                        <span :class="'no-hover px-3 ' + drillHolesToggleIconStyle"></span>
                        {{$translations.newOrder.details.formAndGeometry.drillHoles.confirmDrillHoles}}
                    </button>
                </div>
                <div v-else class="me-3 d-flex mb-3">
                    <button
                        class="me-3 d-flex expand-button border-0"
                        @click="onDrillHolesExpand"
                        :disabled="formAndGeometryProperties.isDrillHolesButtonDisabled"
                    >
                        <span :class="'no-hover px-3 ' + drillHolesToggleIconStyle"></span>
                        {{$translations.newOrder.details.formAndGeometry.drillHoles.title}}
                    </button>
                </div>
            </div>

            <hr v-if="isDrillHolesExpanded" class="mb-3 mt-3">

            <!-- FORM OPTIONS -->
            <div v-if="!isDrillHolesExpanded" class="row">
                <div class="col-md-4 col-sm-8 col-12">
                    <panel-select
                        :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.frameType"
                        v-model="formAndGeometry.selectedFrameType"
                        :selection="$store.getters.getFrameTypeSelection"
                        :notNull="true"
                        titleParameter="description"
                        :preserveOnDisable="true"
                        :disabled="formAndGeometryProperties.selectedFrameType.disabled"
                    />
                </div>
                <div v-if="showPolish === 'YES'" class="col-md-4 col-sm-4 col-12">
                    <div class="d-flex mb-4 py-sm-3 py-0">
                        <input
                            id="formAndGeometry_isPolish"
                            v-model="formAndGeometry.isPolish"
                            class="form-check-input panel-checkbox"
                            type="checkbox"
                            :disabled="!isComponentEnabled"
                        />
                        <label class="form-check-label ms-4 px-3 position-absolute" for="formAndGeometry_isPolish">
                            {{$translations.newOrder.details.formAndGeometry.polish}}
                        </label>
                    </div>
                </div>
            </div>

            <div v-if="!isDrillHolesExpanded" class="row">
                <div v-if="showBevelPosition === 'YES'" class="col-md-4 col-sm-6 col-xs-12">
                    <panel-select
                        :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.bevelPosition"
                        :selection="conditionedBevelSelection()"
                        v-model="formAndGeometry.selectedBevelPosition"
                        :notNull="true"
                        titleParameter="description"
                        :preserveOnDisable="true"
                        :disabled="formAndGeometryProperties.selectedBevelPosition.disabled"
                    />
                </div>
                <div v-if="showBevelPosition === 'YES'" class="col-md-4 col-sm-6 col-xs-12 mb-2">
                    <panel-input
                        placeholder="mm"
                        v-model="formAndGeometry.bevelPositionValue"
                        type="number"
                        :step="0.1"
                        :decimals="1"
                        :preserveOnDisable="!formAndGeometryProperties.bevelPositionValue.resetIfDisabled"
                        :disabled="formAndGeometryProperties.bevelPositionValue.disabled"
                    />
                </div>
                <div v-if="showEdgePinbevel === 'YES' && showBevelPosition === 'YES'" class="col-md-4 col-12">
                    <div class="d-flex mb-4 py-md-3 pb-md-0 ">
                        <input
                            id="formAndGeometry_isEdgePinbevel"
                            v-model="formAndGeometry.isEdgePinbevel"
                            class="form-check-input panel-checkbox"
                            type="checkbox"
                            :disabled="formAndGeometryProperties.isEdgePinbevel.disabled"
                        />
                        <label class="form-check-label ms-4 px-3 position-absolute" for="formAndGeometry_isEdgePinbevel">
                            {{$translations.newOrder.details.formAndGeometry.edgePinbevel}}
                        </label>
                    </div>
                </div>
            </div>

            <div v-if="!isDrillHolesExpanded" class="row">
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <panel-input
                        :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.grvWidth"
                        v-model="formAndGeometry.grvWidthInput"
                        type="number"
                        :step="0.1"
                        :disabled="formAndGeometryProperties.grvWidthInput.disabled"
                    />
                </div>
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <panel-input
                        :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.grvDepth"
                        v-model="formAndGeometry.grvDepthInput"
                        type="number"
                        :step="0.1"
                        :disabled="formAndGeometryProperties.grvDepthInput.disabled"
                    />
                </div>
                <div class="col-4" />
            </div>
        </div>

      <div class="row mb-4">
        <div class="col-md-4 col-sm-6 col-xs-12">
          <panel-input
            :placeholder="$translations.newOrder.details.individual.placeholders.FRCV"
            v-model="formAndGeometry.frcv"
            :step="0.01"
            :decimals="2"
            type="number"
            :disabled="true"
            preserveOnDisable
          />
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12">
          <panel-input
            :placeholder="$translations.newOrder.details.individual.placeholders.FACEFORMANGLE"
            v-model="formAndGeometry.ztilt"
            :step="0.01"
            :decimals="2"
            type="number"
            :disabled="true"
            preserveOnDisable
          />
        </div>
      </div>

        <!-- THICKNESS -->
        <div class="row mb-3">
            <!-- Right side -->
            <div class="col-md-6 col-12">
                <label v-if="isVerticalView" class="variable-label-text mb-4" >{{$translations.newOrder.details.formAndGeometry.thickness}} ({{$translations.common.rightLens}})</label>
                <label v-else class="variable-label-text mb-4" >{{$translations.newOrder.details.formAndGeometry.thickness}}</label>

                <panel-radio-input
                    @change="ThickOptChange('R', formAndGeometry.right.selectedThicknessOption)"
                    radioName="right_radioInput_thickness"
                    v-model="formAndGeometry.right.selectedThicknessOption"
                    :radioInputData="formAndGeometry.right.thicknessRadioInputData"
                    :disabled="formAndGeometryProperties.right.selectedThicknessOption.disabled"
                    v-model:errorMessage="formAndGeometryProperties.right.selectedThicknessOption.errorMessage"
                />
            </div>
            <!-- Left side -->
            <div class="col-md-6 col-12">
                <label v-if="isVerticalView" class="variable-label-text mb-4" >{{$translations.newOrder.details.formAndGeometry.thickness}} ({{$translations.common.leftLens}})</label>
                <label v-else class="variable-label-text mb-4" >{{$translations.newOrder.details.formAndGeometry.thickness}}</label>

                <panel-radio-input
                    @change="ThickOptChange('L', formAndGeometry.left.selectedThicknessOption)"
                    radioName="left_radioInput_thickness"
                    v-model="formAndGeometry.left.selectedThicknessOption"
                    :radioInputData="formAndGeometry.left.thicknessRadioInputData"
                    :disabled="formAndGeometryProperties.left.selectedThicknessOption.disabled"
                    v-model:errorMessage="formAndGeometryProperties.left.selectedThicknessOption.errorMessage"
                />
            </div>
        </div>

        <!-- BASECURVE -->
        <div class="row">
            <div class="col-6">
                <panel-input
                    :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.basecurve"
                    v-model="formAndGeometry.right.basecurve"
                    type="number"
                    :min="formAndGeometry.right.basecurveMin"
                    :max="formAndGeometry.right.basecurveMax"
                    :step="0.25"
                    :preserveOnDisable="!formAndGeometryProperties.right.basecurve.resetIfDisabled"
                    :disabled="formAndGeometryProperties.right.basecurve.disabled"
                    v-model:errorMessage="formAndGeometryProperties.right.basecurve.errorMessage"
                />
            </div>
            <div class="col-6">
                <panel-input
                    :placeholder="$translations.newOrder.details.formAndGeometry.placeholders.basecurve"
                    v-model="formAndGeometry.left.basecurve"
                    type="number"
                    :min="formAndGeometry.left.basecurveMin"
                    :max="formAndGeometry.left.basecurveMax"
                    :step="0.25"
                    :preserveOnDisable="!formAndGeometryProperties.left.basecurve.resetIfDisabled"
                    :disabled="formAndGeometryProperties.left.basecurve.disabled"
                    v-model:errorMessage="formAndGeometryProperties.left.basecurve.errorMessage"
                />
            </div>
        </div>

        <!-- POPUP - Trace Shape -->
        <panel-popup :isOpen="isTraceShapePopupOpened" @onClose="onTraceShapeCancelTracing" :allowClosingWithBackground="false" :parentStyle="'width:30rem;'">

            <!-- WAITING PAGE -->
            <div v-if="traceShape.isTracing">
                <div class="d-grid">
                    <h4 class="panel-title mb-3">{{$translations.newOrder.details.formAndGeometry.traceShape.title}}</h4>
                    <h4 class="fs-6 panel-title mb-3">{{$translations.newOrder.details.formAndGeometry.traceShape.tracingInProgress}}</h4>
                </div>

                <!-- Loading indicator -->
                <div class="d-flex justify-content-center">
                    <span class="spinner-icon large opaque"/>
                </div>

                <!-- Timer -->
                <div class="d-flex justify-content-center mb-2">
                    {{tracerSecondsToTime}}
                </div>

                <!-- Tracer driver -->
                <small>{{$translations.newOrder.details.formAndGeometry.traceShape.tracer}} {{tracerName}}</small>

                <!-- Cancel button -->
                <div class="d-flex justify-content-center mt-3 mb-2 px-sm-5 px-0">
                    <button class="btn btn-secondary panel-button border-0 w-100 p-2" @click="onTraceShapeCancelTracing">{{$translations.newOrder.details.formAndGeometry.traceShape.cancelTracing}}</button>
                </div>
            </div>

            <!-- SUCCESS PAGE -->
            <div v-else>
                <h4 class="panel-title mb-4">{{$translations.newOrder.details.formAndGeometry.traceShape.tracingComplete}}</h4>

                <panel-input
                    :placeholder="$translations.newOrder.details.formAndGeometry.traceShape.reference"
                    v-model="traceShape.reference"
                />

                <div class="d-flex flex-md-nowrap flex-wrap justify-content-start my-2 mt-4">
                    <button class="btn btn-secondary panel-button border-0 w-100 p-md-2 p-2 my-md-0 mt-2" @click="onTraceShapeContinueWithoutSaving">{{$translations.dialog.continueWithoutSaving}}</button>
                    <button class="btn btn-secondary panel-button blue border-0 w-100 p-md-2 p-2 my-md-0 mt-2 ms-md-2 ms-0" @click="onTraceShapeSave">{{$translations.dialog.save}}</button>
                </div>
            </div>
        </panel-popup>

        <!-- POPUP - Load from database -->
        <panel-popup :isOpen="isLoadFromDatabasePopupOpened" @onClose="onClosePopup" :parentStyle="'max-width:65rem;'">
            <h4 class="panel-title pb-3">{{ $translations.newOrder.details.formAndGeometry.loadFromDatabase.title }}</h4>

            <!-- Loading indicator -->
            <div v-if="loadFromDatabase.isLoading" class="d-flex justify-content-center mb-3">
                <span class="spinner-icon large opaque"/>
            </div>

            <div v-else class="d-flex justify-content-start flex-wrap pt-1" style="max-height: 30rem; overflow: auto;">
                <div v-for="shape in loadFromDatabase.shapesList" :value="shape" :key="shape.id">
                    <input type="checkbox"
                        :value="shape"
                        :id="shape.id"
                        class="btn-check"
                        autocomplete="off"
                        @change="onLoadFromDatabaseShapeSelected(shape)"
                        :checked="isShapeSelected(shape)"
                    />
                    <label class="btn btn-outline-primary grid-button shape-text-style border border-radius-xl me-4 mb-4" :for="shape.id">
                        <img :src="shape.image_url" class="p-1 pb-2 disable-events">
                    </label>
                </div>
            </div>

            <div class="d-flex flex-md-nowrap flex-wrap justify-content-between mb-2 mt-0">
                <button class="btn btn-secondary panel-button border-0 w-50 p-md-2 p-2 my-md-0 mt-2" @click="onLoadFromDatabaseCancel">{{$translations.dialog.cancel}}</button>
                <button class="btn btn-secondary blue panel-button border-0 w-50 p-md-2 p-2 my-md-0 mt-2 ms-md-2 ms-0" @click="onLoadFromDatabaseConfirm" :disabled="!loadFromDatabase.selectedShape">{{$translations.dialog.confirm}}</button>
            </div>

        </panel-popup>

        <!-- POPUP - History -->
        <panel-popup :isOpen="isHistoryPopupOpened" @onClose="onClosePopup">
            <h4 class="panel-title pb-3">{{ $translations.newOrder.details.formAndGeometry.history.title }}</h4>

            <panel-input
                :placeholder="$translations.newOrder.details.formAndGeometry.history.placeholders.reference"
                v-model="history.searchReference"
                v-on:keyup.enter="getHistory"
                icon="search-icon"
            />

            <div class="d-flex justify-content-end flex-md-nowrap flex-wrap">
                <button class="btn btn-secondary panel-button border-0 px-5 me-2" @click="getHistory">{{$translations.common.search}}</button>
            </div>

            <hr />

            <panel-table
                :tableData="history.tableData"
                :isLoading="history.isLoading"
                v-model="history.selected"
                v-model:currentPage="history.currentPage"
                v-model:totalPages="history.totalPages"
                @onHeaderClick="onHistoryHeaderClick"
                :hideRowEyeAction="true"
                type="radio"
            >

                <!-- Form view -->
                <template v-slot:form />

                <!-- Action bar -->
                <template v-slot:actions="{ isSelected, row }">
                    <div>
                        <span :class="'close-icon align-middle pointer ' + (isSelected ? ' inverted-color' : '' )" @click="onHistoryRowDelete(row)"/>
                    </div>
                </template>
            </panel-table>

            <div class="d-flex flex-md-nowrap flex-wrap justify-content-between mb-2 mt-0">
                <button class="btn btn-secondary panel-button border-0 w-50 p-md-2 p-2 my-md-0 mt-2" @click="onHistoryCancel">{{ $translations.dialog.cancel }}</button>
                <button class="btn btn-secondary blue panel-button border-0 w-50 p-md-2 p-2 my-md-0 mt-2 ms-md-2 ms-0" @click="onHistoryConfirm" :disabled="!history.selected">{{$translations.dialog.confirm}}</button>
            </div>

        </panel-popup>

        <!-- POPUP - Load from disk -->
        <panel-popup :isOpen="isLoadFromDiskPopupOpened" @onClose="onClosePopup" :parentStyle="'width:30rem;'" >
            <h4 class="panel-title pb-3">{{ $translations.newOrder.details.formAndGeometry.loadFromDisk.title }}</h4>

            <label
                for="file-upload"
                :class="uploadBoxStyle"
                @dragover="onLoadFromDiskDragover"
                @dragleave="onLoadFromDiskDragleave"
                @drop="onLoadFromDiskFileChange"
                style="height:150px;"
            >
                <div v-if="loadFromDisk.uploadedFile" class="d-flex flex-column align-items-center justify-content-center h-100">
                    <span class="uploadbox-font-secondary fs-6">{{loadFromDisk.uploadedFile.name}}</span>
                </div>
                <div v-else class="d-flex flex-column align-items-center justify-content-center h-100">
                    <span class="uploadbox-font-primary">{{ $translations.newOrder.details.formAndGeometry.chooseFile }}</span>
                    <span class="uploadbox-font-secondary">{{ $translations.newOrder.details.formAndGeometry.dragAndDrop }}</span>
                </div>
            </label>
            <input id="file-upload" type="file" @change="onLoadFromDiskFileChange" />

            <panel-input
                :placeholder="$translations.newOrder.details.formAndGeometry.loadFromDisk.placeholders.reference"
                v-model="loadFromDisk.uploadedFileReference"
            />

            <div v-if="loadFromDisk.uploadedFileError" class="text-danger mb-3">
                {{loadFromDisk.uploadedFileError}}
            </div>

            <div class="d-flex flex-md-nowrap flex-wrap justify-content-start my-2">
                <!-- <button class="btn btn-secondary cancel panel-button border-0 w-100 p-md-2 p-2 mb-md-0 mb-2 me-md-2 me-0" @click="OnShapeUploadCancel">{{$translations.dialog.cancel}}</button> -->
                <button class="btn btn-secondary panel-button border-0 w-100 p-md-2 p-2 my-md-0 mt-2" @click="onLoadFromDiskClear">{{$translations.common.clear}}</button>
                <button class="btn btn-secondary panel-button blue border-0 w-100 p-md-2 p-2 my-md-0 mt-2 ms-md-2 ms-0" @click="onLoadFromDiskSave" :disabled="!loadFromDisk.uploadedFile" >{{$translations.dialog.save}}</button>
            </div>
        </panel-popup>

      <!-- POPUP - Analyse B2B -->
      <panel-popup :isOpen="isAnylyseB2BPopupOpened" @onClose="onClosePopup" :parentStyle="'width:30rem;'" >
        <h4 class="panel-title pb-3">{{ $translations.newOrder.details.formAndGeometry.loadFromDisk.title }}</h4>

        <label
            for="file-upload"
            :class="uploadBoxStyle"
            @dragover="onLoadFromDiskDragover"
            @dragleave="onLoadFromDiskDragleave"
            @drop="onLoadFromDiskFileChange"
            style="height:150px;"
        >
          <div v-if="loadFromDisk.uploadedFile" class="d-flex flex-column align-items-center justify-content-center h-100">
            <span class="uploadbox-font-secondary fs-6">{{loadFromDisk.uploadedFile.name}}</span>
          </div>
          <div v-else class="d-flex flex-column align-items-center justify-content-center h-100">
            <span class="uploadbox-font-primary">{{ $translations.newOrder.details.formAndGeometry.chooseFile }}</span>
            <span class="uploadbox-font-secondary">{{ $translations.newOrder.details.formAndGeometry.dragAndDrop }}</span>
          </div>
        </label>
        <input id="file-upload" type="file" @change="onLoadFromDiskFileChange" />

        <panel-input
            :placeholder="$translations.newOrder.details.formAndGeometry.loadFromDisk.placeholders.reference"
            v-model="loadFromDisk.uploadedFileReference"
        />

        <div v-if="loadFromDisk.uploadedFileError" class="text-danger mb-3">
          {{loadFromDisk.uploadedFileError}}
        </div>

        <div class="d-flex flex-md-nowrap flex-wrap justify-content-start my-2">
          <!-- <button class="btn btn-secondary cancel panel-button border-0 w-100 p-md-2 p-2 mb-md-0 mb-2 me-md-2 me-0" @click="OnShapeUploadCancel">{{$translations.dialog.cancel}}</button> -->
          <button class="btn btn-secondary panel-button border-0 w-100 p-md-2 p-2 my-md-0 mt-2" @click="onLoadFromDiskClear">{{$translations.common.clear}}</button>
          <button class="btn btn-secondary panel-button blue border-0 w-100 p-md-2 p-2 my-md-0 mt-2 ms-md-2 ms-0" @click="onLoadFromDiskSave" :disabled="!loadFromDisk.uploadedFile" >{{$translations.dialog.save}}</button>
        </div>
      </panel-popup>
    </div>
</template>

<script>
import PanelSlider from '@/components/PanelSlider.vue'
import PanelRadioInput from '@/components/PanelRadioInput.vue'
import PanelInput from '@/components/PanelInput.vue'
import PanelUpDownInput from '@/components/PanelUpDownInput.vue'
import PanelSelect from '@/components/PanelSelect.vue'
import PanelPopup from '@/components/PanelPopup.vue'
import PanelTable from '@/components/PanelTable.vue'
import Graphics  from '@/utils/graphics.js'
import Helpers from '@/utils/helpers.js'
import Tracer from '@/utils/tracer.js'
import UploadedShape from '@/utils/uploadedShape.js'
import UploadedAnalyseB2b from '@/utils/analyseB2B.js'
import Initializator from '@/utils/initializator.js'
import store from '@/store'
import refraction from "./Refraction.vue";

export default {
    components: {
        PanelSlider, PanelRadioInput, PanelInput, PanelSelect, PanelPopup, PanelTable, PanelUpDownInput
    },
    data () {
        return {
            showEdgePinbevel: this.$store.getters.getIniProperties['SETTINGS_SHOW_EDGE_PINBEVEL'],
            showBevelPosition: this.$store.getters.getIniProperties['SETTINGS_SHOW_BEVEL_POSITION'],
            showPolish: this.$store.getters.getIniProperties['SETTINGS_SHOW_POLISH'],
            showCenterringData: this.$store.getters.getIniProperties['SETTINGS_SHOW_CENTERING_DATA'],
            isDrillHolesSameLeftAndRight: true,
            forcedDecentration: false,
            clientWidth: 0,
            clientHeight: 0,
            oldBoxHeight: 0,
            oldBoxWidth: 0,
            resizeTimer: null,
            currentlyOpenedPopup: null,
            radioButtonThicknessR: 0,
            radioButtonThicknessL: 0,
            traceShape: {
                tracerConfiguration: null,
                isTracing: false,
                timer: 0,
                form: null,
                type: null,
                reference: null
            },
            loadFromDatabase: {
                shapesList: [],
                selectedShape: null,
                isLoading: false
            },
            loadFromDisk: {
                uploadedFile: null,
                uploadedFileReference: null,
                uploadedFileError: null,
                isUploadedFileHovering: false,
            },
            history: {
                searchReference: '',
                tableData: {
                    headers: [
                        {label: 'ID', sortParam: 'id', enabled: true},
                        {get label() {return store.getters.translations.newOrder.details.formAndGeometry.history.reference}, sortParam: 'reference', enabled: true},
                        {get label() {return store.getters.translations.newOrder.details.formAndGeometry.history.type}, sortParam: 'type', enabled: true},
                        {get label() {return store.getters.translations.newOrder.details.formAndGeometry.history.createdDate}, sortParam: 'created_at', enabled: true},
                    ],
                    rows: [],
                    style: {
                        selection: "height: 3.65rem; max-width: 89%",
                        td: "max-width: 30rem;"
                    }
                },
                isLoading: false,
                selected: null,
                selectedPerPage: 5,
                currentPage: null,
                totalPages: null,
                orderby: 'created_at',
                order: 'desc'
            },
            setOptimized: process.env.MIX_SETOPTIMIZED,
            graphicsApp: null,
            graphicsScreenResizeHook: () => {},
            selectedFrameTypeOld: null,
          showForcedDecentration: store.getters.getIniProperties['SETTINGS_SHOW_FORCED_DECENTRATION'],
        }
    },
    computed: {
        isComponentEnabled() {return this.$store.state.moduleOrders.details.isComponentEnabled('formAndGeometry')},
        tracer () {return this.$store.state.moduleTracer},
        settings () {return this.$store.state.moduleSettings},
        orders() {return this.$store.state.moduleOrders},
        erp () {return this.$store.state.moduleOrders.erp},
        initializator () {return this.$store.state.moduleOrders.initializator},
        formAndGeometry() {return this.$store.state.moduleOrders.details.formAndGeometry},
        formAndGeometryProperties() {return this.$store.state.moduleEvaluator.details.formAndGeometry},
        refraction() {return this.$store.state.moduleOrders.details.refraction},
        refractionProperties() {return this.$store.state.moduleEvaluator.details.refraction},
        individuals() {return this.$store.state.moduleOrders.details.individual},
        product() {return this.$store.state.moduleOrders.details.product},
        frame () {return this.$store.state.moduleOrders.details.frame},
        form () {return this.$store.state.moduleOrders.details.form},
        isVerticalView() {return this.clientWidth < 780},
        isDrillHolesExpanded() {return this.formAndGeometry.options.drillHoles},
        isTraceShapePopupOpened() {return this.currentlyOpenedPopup == 'traceShape'},
        isLoadFromDatabasePopupOpened() {return this.currentlyOpenedPopup == 'loadFromDatabase'},
        isLoadFromDiskPopupOpened() {return this.currentlyOpenedPopup == 'loadFromDisk'},
        isHistoryPopupOpened() {return this.currentlyOpenedPopup == 'history'},
      isAnylyseB2BPopupOpened() {return this.currentlyOpenedPopup == 'analyseB2B'},
        isForcedDecentrationMode() {return this.formAndGeometry.options.forcedDecentration === true},
        isShapeMode() {return this.formAndGeometry.options.forcedDecentration !== true},
        tracerName() {return Helpers.getObjProp(this.traceShape.tracerConfiguration, 'tracer')},
        tracerSecondsToTime() {
            let date = new Date(0)
            date.setSeconds(this.traceShape.timer / 1000)
            return date.toISOString().substring(14, 19)
        },
        showLensesScaledToggleIconStyle () {
            if (this.formAndGeometry.options.showLensesScaled1_1) return 'minimize-icon'
            return 'add-icon'
        },
        drillHolesToggleIconStyle () {
            if (this.formAndGeometry.options.drillHoles) return 'minimize-icon'
            return 'add-icon'
        },
        hasGraphics() {
            return (
                this.form.shape.current.decodedData !== undefined &&
                this.form.shape.current.decodedData !== null &&
                this.form.shape.current.decodedData !== "" &&
                Object.entries(this.form.shape.current.decodedData).length > 0
            )
        },
        drawingContainerStyle () {
            if (!this.hasGraphics) return `height: 0px`
            let height = this.clientWidth * 0.35
            return `height: ${height}px`
        },
        uploadBoxStyle() {
            let style = "uploadbox-border pointer mb-4"
            if (this.loadFromDisk.isUploadedFileHovering)
                style += " dragover"
            return style
        },
        isOptimizedDefault(){
            if(this.$store.state.moduleOrders.details.product.right.isStock
                || this.$store.state.moduleEvaluator.details.formAndGeometry.right.isOptimized.disabled
                || this.$store.state.moduleOrders.details.product.right.selectedLensType === null)
                return false
            return true
        }
    },
    watch: {
        isOptimizedDefault(val){
            if(Initializator.setOptimized() === 'YES'){
                this.formAndGeometry.right.isOptimized = val;
            }
        },
        'erp.right.getLensGeo': function (val) {
            this.renderGraphics()
        },
        'erp.left.getLensGeo': function (val) {
            this.renderGraphics()
        },
        'formAndGeometry.right.isOptimized': function(val) {
            this.formAndGeometry.left.isOptimized = val
        },
        'formAndGeometry.right.boxWidth': function(val) {
            this.renderGraphics()
        },
        'formAndGeometry.right.boxHeight': function(val) {
            this.renderGraphics()
        },
        'formAndGeometry.left.boxWidth': function(val) {
            this.renderGraphics()
        },
        'formAndGeometry.left.boxHeight': function(val) {
            this.renderGraphics()
        },
        'form.shape.current.decodedData': function(val) {
            this.renderGraphics()
        },
        'formAndGeometry.dbl': function(val) {
            this.renderGraphics()
        },
        'formAndGeometry.drillHoles': {
            handler(val) {
                this.renderGraphics()
                this.setSameLeftAndRightDrillHoles()
            },
            deep: true
        },
        'formAndGeometry.isDrillHolesSameLeftAndRight': function(val) {
            this.setSameLeftAndRightDrillHoles()
        },
        'formAndGeometry.options.forcedDecentration': function(val) {
            this.forcedDecentration = val
            this.setForcedDecentration()
        },
        'formAndGeometry.selectedFrameType': function(val) {
            this.setDefaultGrv()
            this.setDefaultThk()
        },
        'history.currentPage': function(val) {
            if (!val) return
            this.history.currentPage = val
            this.getHistory()
        }
    },
    mounted () {
        this.isDrillHolesSameLeftAndRight = this.formAndGeometry.isDrillHolesSameLeftAndRight
        this.forcedDecentration = this.formAndGeometry.options.forcedDecentration

        this.renderGraphics()
        this.onScreenResize()
        window.addEventListener('resize', this.onScreenResize)
    },
    unmounted () {
        window.removeEventListener('resize', this.onScreenResize)
    },
    methods: {
      setBoxWidth() {
        const val = (Math.round(this.formAndGeometry.right.boxWidth * 100) / 100).toFixed(1)
        if(this.oldBoxWidth !== val){
          this.formAndGeometry.right.boxWidth = val
          this.formAndGeometry.left.boxWidth = val
          this.oldBoxWidth = val
        }

      },
      setBoxHeight() {
        const val = (Math.round(this.formAndGeometry.right.boxHeight * 100) / 100).toFixed(1)
        if(this.oldBoxHeight !== val){
          this.formAndGeometry.right.boxHeight = val
          this.formAndGeometry.left.boxHeight = val
          this.oldBoxHeight = val
        }
      },
        onScreenResize() {
            this.clientWidth = window.innerWidth
            this.clientHeight = window.innerHeight

            // Used in graphics.js to handle onScreenResize
            this.graphicsScreenResizeHook()
        },
        async onTraceShape() {
            this.tracer.cancelTracingSignal = false

            // Load last tracer configuration
            this.traceShape.tracerConfiguration = await Tracer.getTracerConfiguration()
            if (this.tracer.cancelTracingSignal) return

            // If there is no configuration or the tracer is null, raise error
            if (!this.traceShape.tracerConfiguration ||
                this.traceShape.tracerConfiguration === undefined ||
                !Tracer.isTracerNameValid(Helpers.getObjProp(this.traceShape.tracerConfiguration, 'tracer'))
            ){
                this.$store.dispatch('notify', {
                    message: this.$translations.notifications.tracerNotConfigured,
                    duration: 3,
                })
                return
            }

            // Set isTracing flag
            this.traceShape.isTracing = true

            // Open popup
            this.currentlyOpenedPopup = 'traceShape'

            // Timeout timer
            this.traceShape.timer = this.tracer.lensSoapTracerTimeout
            const interval = setInterval(() => {
                this.traceShape.timer -= 1000

                if (this.traceShape.timer <= 0)
                    window.clearInterval(interval)
            }, 1000);

            // Get shape data from LensSoapTracer API
            const traceShapeResult = await Tracer.traceShape(this.traceShape.tracerConfiguration)

            // Prevent multiple tracings from overlapping (suppose one was canceled)
            if (this.tracer.cancelTracingSignal) {
                window.clearInterval(interval)
                this.onClosePopup()
                return
            }

            // Close popup on unseccessfull tracing
            if (!traceShapeResult) {
                window.clearInterval(interval)
                this.onClosePopup()
                this.$store.dispatch('notify', {
                    message: this.$translations.notifications.tracingUnseccessful,
                    duration: 3,
                })
                return
            }
            if (traceShapeResult.errorMessage){
                window.clearInterval(interval)
                this.onClosePopup()
                this.$store.dispatch('notify', {
                    message: traceShapeResult.errorMessage,
                })
                return
            }

            // Prevent multiple tracings from overlapping (suppose one was canceled)
            if (this.tracer.cancelTracingSignal) {
                window.clearInterval(interval)
                this.onClosePopup()
                return
            }

            // Load shape
            this.traceShape.form = traceShapeResult.vca
            this.traceShape.type = 'TRCVCA'
            this.loadShape(this.traceShape.form, this.traceShape.type)

            // Set reference for saving
            this.traceShape.reference = traceShapeResult.reference

            // Transition to success page
            this.traceShape.isTracing = false
            window.clearInterval(interval)
            this.$store.dispatch('notify', {
                message: traceShapeResult.successMessage,
                type: 'success'
            })
        },
        onTraceShapeCancelTracing() {
            if (!this.traceShape.isTracing) {
                this.onTraceShapeContinueWithoutSaving()
                return
            }

            let dialogParams = {
                title: this.$translations.dialog.warning,
                description: this.$translations.dialog.messages.stopTracing,
                falseButtonText: this.$translations.dialog.cancel,
                trueButtonText: this.$translations.dialog.yes,
                type: 'warning',
                allowClosing: true
            }
            this.$store.dispatch('dialog', dialogParams).then(hasConfirmed => {
                if (hasConfirmed) {
                    this.onClosePopup()

                    // Abort the sent request to the LensSOAPTracer API
                    if (this.tracer.lensSoapTracerSourceToken || this.tracer.lensSoapTracerSourceToken !== undefined)
                        this.tracer.lensSoapTracerSourceToken.cancel()

                    this.tracer.cancelTracingSignal = true
                }
            })
        },
        onTraceShapeContinueWithoutSaving() {
            this.onClosePopup()
        },
        onTraceShapeSave() {
            let params = {
                vca: this.traceShape.form,
                reference: this.traceShape.reference
            }

            // Save shape file contents to database
            UploadedShape.saveFromTracer(params,
            async response => {

                // Clear the data for a new upload and close popup
                this.onClosePopup()

                // Notify user
                this.$store.dispatch('notify', {
                    message: this.$translations.notifications.shapeSaved,
                    duration: 3,
                    type: 'success'
                })
            })
        },
        onLoadFromDatabase() {
            this.currentlyOpenedPopup = 'loadFromDatabase'
            this.getStandardShapes()
        },
        onLoadFromDatabaseShapeSelected(shape) {
            if (this.loadFromDatabase.selectedShape && this.loadFromDatabase.selectedShape !== undefined &&
                this.loadFromDatabase.selectedShape.id !== undefined && shape.id !== undefined &&
                this.loadFromDatabase.selectedShape.id == shape.id){
                this.loadFromDatabase.selectedShape = null
                return
            }
            this.loadFromDatabase.selectedShape = shape
        },
        async onLoadFromDatabaseConfirm() {
            if (!this.loadFromDatabase.selectedShape || this.loadFromDatabase.selectedShape === undefined ||
                this.loadFromDatabase.selectedShape.id == undefined) return

            // Deselect the frame
            this.frame.selectedFrame = null

            // Get shape form data
            let form = await UploadedShape.getStandardShapeForm(this.loadFromDatabase.selectedShape.id)

            // Load the shape
            this.loadShape(form, 'TBLNAT')

            // Clear the data for a new upload and close popup
            this.onClosePopup()
        },
        onLoadFromDatabaseCancel() {
            this.onClosePopup()
        },
        onLoadFromDisk() {
            this.currentlyOpenedPopup = 'loadFromDisk'

            this.loadFromDisk.uploadedFileReference = UploadedShape.generateReference('Shape')
        },
        onLoadFromDiskDragover(e) {
            e.preventDefault()
            this.loadFromDisk.isUploadedFileHovering = true
        },
        onLoadFromDiskDragleave(e) {
            e.preventDefault()
            this.loadFromDisk.isUploadedFileHovering = false
        },
        onLoadFromDiskFileChange(e){
            e.preventDefault()
            let files = e.target.files || e.dataTransfer.files
            if (!files.length || files.length > 1) return
            let file = files[0]

            this.loadFromDisk.uploadedFile = file
            this.loadFromDisk.isUploadedFileHovering = true
            this.loadFromDisk.uploadedFileError = null
        },
       async onLoadFromDiskSave() {
            this.loadFromDisk.uploadedFileError = null
            let params = {
                file: this.loadFromDisk.uploadedFile,
                reference: this.loadFromDisk.uploadedFileReference,
            }

            if(this.isAnylyseB2BPopupOpened) {
              if(this.refraction['right'].range) {
                if(this.refraction['right'].range['INDIVIDUALTYPE']) {
                  params['indtype'] = this.refraction['right'].range['INDIVIDUALTYPE']
                }
              }
              else {
                if(this.refraction['left'].range) {
                  if(this.refraction['left'].range['INDIVIDUALTYPE']) {
                    params['indtype'] = this.refraction['left'].range['INDIVIDUALTYPE']
                  }
                }
              }
              await UploadedAnalyseB2b.saveAnalyseB2B(params, response => {
                if(response.message.JobData) {
                  const sides = response.message.JobData
                  for (const [key, side] of Object.entries(sides)) {
                    for (const [keySide, sectionSide] of Object.entries(side)) {
                      for (const [keySection, valSection] of Object.entries(sectionSide)) {
                        if(keySide === 'Refraction') {
                          this.refraction[key.toLowerCase()][keySection.toLowerCase()] = valSection
                        }
                        if(keySide === 'Form') {
                          if(keySection === 'DBL')
                            this.formAndGeometry[keySection.toLowerCase()] = valSection
                        }
                        if(keySide === 'Individual') {
                          if(this.individuals.definedFields && this.individuals.definedFields.length > 0) {
                            const el = this.individuals.definedFields.find(x => x.name === keySection.toUpperCase())
                            if(el) {
                              el.value = valSection
                            }
                          }
                          if(this.individuals[key.toLowerCase()].definedFields && this.individuals[key.toLowerCase()].definedFields.length > 0) {
                            const el = this.individuals[key.toLowerCase()].definedFields.find(x => x.name === keySection.toUpperCase())
                            if(el) {
                              el.value = valSection
                            }
                          }
                        }
                      }
                    }
                  }
                }
                // Display error text if there was a problem in file content validation
                // Clear the data for a new upload and close popup
                this.onLoadFromDiskClear()
                this.onClosePopup()

                // Notify user
                this.$store.dispatch('notify', {
                  message: this.$translations.notifications.shapeSaved,
                  duration: 3,
                  type: 'success'
                })
              })
            }
            else {
              // Save shape file contents to database
              UploadedShape.saveFromDisk(params,
                  response => {

                    // Display error text if there was a problem in file content validation
                    if (response.error) {
                      this.loadFromDisk.uploadedFileError = response.error
                      return
                    }

                    // Deselect the frame
                    this.frame.selectedFrame = null

                    // Load the shape
                    this.loadShape(response.form, 'OMAVCA')

                    // Clear the data for a new upload and close popup
                    this.onLoadFromDiskClear()
                    this.onClosePopup()

                    // Notify user
                    this.$store.dispatch('notify', {
                      message: this.$translations.notifications.shapeSaved,
                      duration: 3,
                      type: 'success'
                    })
                  })
            }
        },
        onLoadFromDiskClear() {
            this.loadFromDisk.uploadedFile = null
            this.loadFromDisk.isUploadedFileHovering = false
            this.loadFromDisk.uploadedFileError = null
        },
        onHistory() {
            this.currentlyOpenedPopup = 'history'
            this.getHistory()
        },
        onHistoryHeaderClick(event) {
            this.history.orderby = event.header.sortParam
            this.history.order = event.isSorting ? 'asc' : 'desc'
            this.getHistory()
        },
        onHistoryRowDelete(row) {
            let dialogParams = {
                title: this.$translations.dialog.warning,
                description: this.$translations.dialog.messages.deleteShape,
                falseButtonText: this.$translations.dialog.cancel,
                trueButtonText: this.$translations.dialog.yes,
                type: 'warning',
                allowClosing: true
            }
            this.$store.dispatch('dialog', dialogParams).then(async hasConfirmed => {
                if (!hasConfirmed) return

                await UploadedShape.delete(row.rawData)
                this.getHistory()
            })
        },
        onHistoryConfirm() {
            if (!this.history.selected) return

            // Deselect the frame
            this.frame.selectedFrame = null

            // Load the shape
            this.loadShape(this.history.selected.rawData.form, 'OMAVCA')

            // Clear the data for a new upload and close popup
            this.onClosePopup()
        },
        onHistoryCancel() {
            this.onClosePopup()
        },
        onClosePopup() {
            this.currentlyOpenedPopup = null

            // Reset trace shape popup data
            this.traceShape.tracerConfiguration = null
            this.traceShape.isTracing = false
            this.traceShape.timer = 0
            this.traceShape.reference = null
            this.tracer.cancelTracingSignal = false

            // Reset load-from-disk popup data
            this.loadFromDisk.uploadedFile = null
            this.loadFromDisk.uploadedFileReference = null
            this.loadFromDisk.uploadedFileError = null
            this.loadFromDisk.isUploadedFileHovering = null

            // Reset load-from-database popup data
            this.loadFromDatabase.selectedShape = null
            this.loadFromDatabase.isLoading = false

            // Reset history popup data
            this.history.isLoading = false
            this.history.selected = null
            this.history.currentPage = null
            this.history.totalPages = null
            this.history.selectedPerPage = 5
            this.history.orderby = 'id'
            this.history.order = 'asc'
            this.history.tableData.rows = []
        },
        onDeleteForm() {
            let dialogParams = {
                title: this.$translations.dialog.warning,
                description: this.$translations.dialog.messages.resetAllForm,
                falseButtonText: this.$translations.dialog.cancel,
            trueButtonText: this.$translations.dialog.continue,
                type: 'warning',
                allowClosing: true
            }
            this.$store.dispatch('dialog', dialogParams).then(hasConfirmed => {
                if (!hasConfirmed) return

                // Clear frame and interpolation data
                this.erp.getFrameByID = null
                this.erp.interpolate = null

                // Deselect frame
                // this.frame.selectedFrame = null
                // this.frame.hasFrameBeenSelected = false

                // Reset form and geometry data
                this.form.shape.type = null
                this.form.shape.current.data = null
                this.form.shape.current.decodedData = null
                this.form.shape.original.data = null
                this.form.shape.original.decodedData = null
                this.formAndGeometry.dbl = null
                this.formAndGeometry.panto = null
                this.formAndGeometry.frcv = null
                this.formAndGeometry.ztilt = null
                let sides = ['left','right'].forEach(side => {
                    this.erp[side].getLensGeo = null
                    this.formAndGeometry[side].isOptimized = false
                    this.formAndGeometry[side].boxWidth = null
                    this.formAndGeometry[side].boxHeight = null
                    this.formAndGeometry[side].decentrationX = null
                    this.formAndGeometry[side].decentrationY = null
                    this.formAndGeometry[side].basecurve = null
                    this.formAndGeometry[side].minimumDiameter = null
                })

                // Reset drill holes
                this.formAndGeometry.drillHoles.map(x => this.deleteDrillHole(x))

                // Clear graphics
                Graphics.clearDrawingContainer('formAndGeometry_drawing_container')
              this.formAndGeometry.right.selectedThicknessOption.value = ''
              this.formAndGeometry.left.selectedThicknessOption.value = ''
            })
        },
        onRefreshForm() {
            Initializator.getLensGeo('left')
            Initializator.getLensGeo('right')
            this.renderGraphics()
        },
        onShowLensesScaled1_1() {
            this.formAndGeometry.options.showLensesScaled1_1 = !this.formAndGeometry.options.showLensesScaled1_1
        },
        onConfirmDrillHoles() {
            this.formAndGeometry.options.drillHoles = false
        },
        onDrillHolesExpand() {
            this.formAndGeometry.options.drillHoles = true

            // Make a deep copy of the drill data for editing
            this.formAndGeometry.drillHolesEditingData = JSON.parse(JSON.stringify(this.formAndGeometry.drillHoles))
        },
        onCancelDrillHoles() {
            let dialogParams = {
                title: this.$translations.dialog.warning,
                description: this.$translations.dialog.messages.insertedDrillHolesDelete,
                falseButtonText: this.$translations.dialog.cancel,
                trueButtonText: this.$translations.dialog.continue,
                type: 'warning',
                allowClosing: true
            }
            this.$store.dispatch('dialog', dialogParams).then(hasConfirmed => {
                if (!hasConfirmed) return

                // Revert drill holes data
                if (this.formAndGeometry.drillHolesEditingData)
                    this.formAndGeometry.drillHoles = this.formAndGeometry.drillHolesEditingData

                this.formAndGeometry.options.drillHoles = false
            })
        },
        onDeleteDrillHole(drillHole){
            let dialogParams = {
                title: this.$translations.dialog.warning,
                description: this.$translations.dialog.messages.deleteRowData,
                falseButtonText: this.$translations.dialog.cancel,
                trueButtonText: this.$translations.dialog.continue,
                type: 'warning',
                allowClosing: true
            }
            this.$store.dispatch('dialog', dialogParams).then(hasConfirmed => {
                if (!hasConfirmed) return

                // Delete all data in the selected row
                this.deleteDrillHole(drillHole)
            })
        },
        onDrillHolesSameLeftRightChange() {
            this.formAndGeometry.isDrillHolesSameLeftAndRight = this.isDrillHolesSameLeftAndRight
        },
        onForcedDecentrationChange() {
            let oldVal = this.formAndGeometry.options.forcedDecentration
            let newVal = !this.formAndGeometry.options.forcedDecentration

            let description = this.$translations.dialog.messages.loseForcedDecentrationData
            if (newVal)
                description = this.$translations.dialog.messages.loseShapeData

            let dialogParams = {
                title: this.$translations.dialog.warning,
                description: description,
                falseButtonText: this.$translations.dialog.cancel,
                trueButtonText: this.$translations.dialog.continue,
                type: 'warning',
                allowClosing: true
            }
            this.$store.dispatch('dialog', dialogParams).then(hasConfirmed => {
                if (!hasConfirmed) {
                    this.formAndGeometry.options.forcedDecentration = oldVal
                    this.forcedDecentration = oldVal
                    return
                }
                this.formAndGeometry.options.forcedDecentration = newVal
                this.forcedDecentration = newVal
            })
        },
        setSameLeftAndRightDrillHoles() {
            if (!this.formAndGeometry.isDrillHolesSameLeftAndRight) return

            this.formAndGeometry.drillHoles.forEach(drillHole => {
                let result = Helpers.copyDataFromRightToLeftSide(drillHole)
                if (result !== true) {
                    console.error(result)
                    return
                }
            })
        },
        setForcedDecentration() {
            // Setup Forced Decentration mode
            if (this.isForcedDecentrationMode) {

                // Reset Form values
                if (this.orders.emptyOrderCopy.form === undefined || this.orders.emptyOrderCopy.form === null)
                    return

                this.$store.state.moduleOrders.details.form = JSON.parse(JSON.stringify(this.orders.emptyOrderCopy.form))

                // Reset Frame values
                if (this.orders.emptyOrderCopy.frame === undefined || this.orders.emptyOrderCopy.frame === null)
                    return

                const newFrame = JSON.parse(JSON.stringify(this.orders.emptyOrderCopy.frame))
                newFrame.frameTableData = this.$store.state.moduleOrders.details.frame.frameTableData
                this.$store.state.moduleOrders.details.frame = newFrame

                // Disable Frame component
                const frameComponent = this.orders.details.components.find(x => x.name === 'frame')
                this.$nextTick(() => {
                    frameComponent.enabled = false
                })

                // Reset Form and Geometry -> shape values
                if (this.orders.emptyOrderCopy.formAndGeometry === undefined || this.orders.emptyOrderCopy.formAndGeometry === null)
                    return

                const newFormAndGeometry = JSON.parse(JSON.stringify(this.orders.emptyOrderCopy.formAndGeometry))
                this.formAndGeometry.dbl = newFormAndGeometry.dbl
                this.formAndGeometry.dblMin = newFormAndGeometry.dblMin
                this.formAndGeometry.dblMax = newFormAndGeometry.dblMax
                this.formAndGeometry.selectedFrameType = newFormAndGeometry.selectedFrameType
                this.formAndGeometry.selectedBevelPosition = newFormAndGeometry.selectedBevelPosition
                this.formAndGeometry.bevelPositionValue = newFormAndGeometry.bevelPositionValue
                this.formAndGeometry.grvWidthInput = newFormAndGeometry.grvWidthInput
                this.formAndGeometry.grvDepthInput = newFormAndGeometry.grvDepthInput
                this.formAndGeometry.minEdgeThickness = newFormAndGeometry.minEdgeThickness
                this.formAndGeometry.isPolish = newFormAndGeometry.isPolish
                this.formAndGeometry.isEdgePinbevel = newFormAndGeometry.isEdgePinbevel
                this.formAndGeometry.drillHolesButton = newFormAndGeometry.drillHolesButton
                this.formAndGeometry.fPinbevel = newFormAndGeometry.fPinbevel
                this.formAndGeometry.pinbevel = newFormAndGeometry.pinbevel
                this.formAndGeometry.isDrillHolesSameLeftAndRight = newFormAndGeometry.isDrillHolesSameLeftAndRight
                this.formAndGeometry.drillHoles = newFormAndGeometry.drillHoles
                this.formAndGeometry.drillHolesEditingData = newFormAndGeometry.drillHolesEditingData
                this.formAndGeometry.panto = newFormAndGeometry.panto
                this.formAndGeometry.frcv = newFormAndGeometry.frcv
                this.formAndGeometry.ztilt = newFormAndGeometry.ztilt
                let sides = ['left','right'].forEach(side => {
                    this.formAndGeometry[side].startX = newFormAndGeometry[side].isOptimized
                    this.formAndGeometry[side].boxWidth = newFormAndGeometry[side].boxWidth
                    this.formAndGeometry[side].boxHeight = newFormAndGeometry[side].boxHeight
                    this.formAndGeometry[side].decentrationX = newFormAndGeometry[side].decentrationX
                    this.formAndGeometry[side].decentrationY = newFormAndGeometry[side].decentrationY
                    this.formAndGeometry[side].minimumDiameter = newFormAndGeometry[side].minimumDiameter
                })

                // Reset PD and Height values in Refraction
                if (this.orders.emptyOrderCopy.refraction === undefined || this.orders.emptyOrderCopy.refraction === null)
                    return

                const newRefraction = JSON.parse(JSON.stringify(this.orders.emptyOrderCopy.refraction))
                this.refraction.left.height = newRefraction.left.height
                this.refraction.left.pd = newRefraction.left.pd
                this.refraction.right.height = newRefraction.right.height
                this.refraction.right.pd = newRefraction.right.pd

                return
            }

            // Set back to Shape mode
            if (this.isShapeMode) {

                // Enable frame component
                const frameComponent = this.orders.details.components.find(x => x.name === 'frame')
                frameComponent.enabled = true

                // Reset Form and Geometry -> forced decentration values
                if (this.orders.emptyOrderCopy.formAndGeometry === undefined || this.orders.emptyOrderCopy.formAndGeometry === null)
                    return

                const newFormAndGeometry = JSON.parse(JSON.stringify(this.orders.emptyOrderCopy.formAndGeometry))
                let sides = ['left','right'].forEach(side => {
                    this.formAndGeometry[side].forcedDecentration.decentrationX = newFormAndGeometry[side].forcedDecentration.decentrationX
                    this.formAndGeometry[side].forcedDecentration.decentrationXMin = newFormAndGeometry[side].forcedDecentration.decentrationXMin
                    this.formAndGeometry[side].forcedDecentration.decentrationXMax = newFormAndGeometry[side].forcedDecentration.decentrationXMax
                    this.formAndGeometry[side].forcedDecentration.decentrationY = newFormAndGeometry[side].forcedDecentration.decentrationY
                    this.formAndGeometry[side].forcedDecentration.decentrationYMin = newFormAndGeometry[side].forcedDecentration.decentrationYMin
                    this.formAndGeometry[side].forcedDecentration.decentrationYMax = newFormAndGeometry[side].forcedDecentration.decentrationYMax
                })

                return
            }
        },
        deleteDrillHole(drillHole){
            let sides = ['left','right'].forEach(side => {
                drillHole[side].startX = null
                drillHole[side].startY = null
                drillHole[side].endX = null
                drillHole[side].endY = null
                drillHole[side].diam = null
                drillHole[side].depth = null
                drillHole[side].direction = 'T'
            })
        },
        renderGraphics(){
            if (!this.form.shape.current.decodedData && this.clientHeight <= 0 && this.clientHeight <= 0) return

            this.$nextTick(() => {
                this.graphicsApp = Graphics.drawFormAndGeometryLenses('formAndGeometry_drawing_container', this.graphicsApp, this)
            })
          this.setDefaultThk()
        },
        leftSwitchDisabledLogic(val) {
            if (!this.isComponentEnabled || this.formAndGeometry.left.isOptimized) return true
            if (this.formAndGeometry.left.typeAllowedCode == 'A') return false
            if (this.formAndGeometry.left.typeAllowedCode == val) return false
            return true
        },
        rightSwitchDisabledLogic(val) {
            if (!this.isComponentEnabled || this.formAndGeometry.right.isOptimized) return true
            if (this.formAndGeometry.right.typeAllowedCode == 'A') return false
            if (this.formAndGeometry.right.typeAllowedCode == val) return false
            return true
        },
        leftSwitchCheckedLogic(val) {
            const allowedType = this.formAndGeometry.left.typeAllowedCode
            const selectedType = this.formAndGeometry.left.type
            const isType = (type) => (allowedType === 'A' && val === type || allowedType === val) && selectedType === type

            // Round (R) or oval (O) type
            if (isType('R') || isType('O')){
                return true
            }

            return false
        },
        rightSwitchCheckedLogic(val) {
            const allowedType = this.formAndGeometry.right.typeAllowedCode
            const selectedType = this.formAndGeometry.right.type
            const isType = (type) => (allowedType === 'A' && val === type || allowedType === val) && selectedType === type

            // Round (R) or oval (O) type
            if (isType('R') || isType('O')){
                return true
            }

            return false
        },
        setDefaultGrv() {
            if (!this.settings.getIniProperties) return

            let env = this.settings.getIniProperties
            let type = Helpers.getObjProp(this.formAndGeometry.selectedFrameType, 'type', false)
            let setToDefault = false
            if (type){
                switch (type) {
                    case '01': setToDefault = false; break;
                    case '02': setToDefault = false; break;
                    case '03': setToDefault = true; break;
                    case '04': setToDefault = true; break;
                    case '05': setToDefault = false; break;
                    case '06': setToDefault = false; break;
                }
            }

            // Default grv depth and width
            if (setToDefault){
                this.formAndGeometry.grvWidthInput = parseFloat(env.LENS_ORDER_DEFAULTS_GWIDTH)
                this.formAndGeometry.grvDepthInput = parseFloat(env.LENS_ORDER_DEFAULTS_GDEPTH)
            }
        },
      ThickOptChange(side, val) {
        if(val.id === 0 && side === 'R') {
          this.formAndGeometry.right.thicknessRadioInputData[1].value = ''
        }
        else if(val.id === 0 && side === 'L') {
          this.formAndGeometry.left.thicknessRadioInputData[1].value = ''
        }
        if(val.id === 1 && side === 'R') {
          this.formAndGeometry.right.thicknessRadioInputData[0].value = ''
        }
        else if(val.id === 1 && side === 'L') {
          this.formAndGeometry.left.thicknessRadioInputData[0].value = ''
        }
        if(side === 'L') {
          if(this.radioButtonThicknessL !== val.id) {
            this.radioButtonThicknessL = val.id
            this.setDefaultThk(side)
          }
        }
        if(side === 'R') {
          if(this.radioButtonThicknessR !== val.id) {
            this.radioButtonThicknessR = val.id
            this.setDefaultThk(side)
          }
        }
      },
      setDefaultThk(side) {
        if(this.form.shape.current.data === null) return;
        const thickness = this.$store.getters.getDefaultMinThk;
        if(thickness === undefined) return;
        if(!this.formAndGeometry.selectedFrameType) {
          return;
        }

        if(this.selectedFrameTypeOld === this.formAndGeometry.selectedFrameType) {
          return;
        }
        this.selectedFrameTypeOld = this.formAndGeometry.selectedFrameType

        if(this.formAndGeometry.selectedFrameType.control === '3;3'){
            if(thickness['MinEdgNyl'] !== '') {
              if(this.formAndGeometry.right.selectedThicknessOption.id === 0 && side !== 'L'){
                  this.formAndGeometry.right.selectedThicknessOption.value = thickness['MinEdgNyl']
              }
              if(this.formAndGeometry.left.selectedThicknessOption.id === 0 && side !== 'R'){
                this.formAndGeometry.left.selectedThicknessOption.value = thickness['MinEdgNyl']
              }
              this.formAndGeometry.minEdgeThickness = ''
            }
        }
        else if(this.formAndGeometry.selectedFrameType.control === '3;33'){
            if(thickness['MinEdgNyl'] !== '') {
              if(this.formAndGeometry.right.selectedThicknessOption.id === 0 && side !== 'L'){
                this.formAndGeometry.right.selectedThicknessOption.value = thickness['MinEdgNyl']
              }
              if(this.formAndGeometry.left.selectedThicknessOption.id === 0 && side !== 'R'){
                this.formAndGeometry.left.selectedThicknessOption.value = thickness['MinEdgNyl']
              }
            }
            if(thickness['MinDrhThk'] !== '') {
              this.formAndGeometry.minEdgeThickness = thickness['MinDrhThk']
            }
        }
        else if(this.formAndGeometry.selectedFrameType.control === '3;2'){
            if(thickness['MinDrhThk'] !== '') {
              if(this.formAndGeometry.right.selectedThicknessOption.id === 0 && side !== 'L'){
                this.formAndGeometry.right.selectedThicknessOption.value = thickness['MinDrhThk']
              }
              if(this.formAndGeometry.left.selectedThicknessOption.id === 0 && side !== 'R'){
                this.formAndGeometry.left.selectedThicknessOption.value = thickness['MinDrhThk']
              }
              this.formAndGeometry.minEdgeThickness = thickness['MinDrhThk']
            }
        }
        else if(this.formAndGeometry.selectedFrameType.control === '2;1'){ //Metall
            this.formAndGeometry.minEdgeThickness = ''
            if(thickness['MinEdgMet'] !== '') {
              if(this.formAndGeometry.right.selectedThicknessOption.id === 0 && side !== 'L'){
                this.formAndGeometry.right.selectedThicknessOption.value = thickness['MinEdgMet']
              }
              if(this.formAndGeometry.left.selectedThicknessOption.id === 0 && side !== 'R'){
                this.formAndGeometry.left.selectedThicknessOption.value = thickness['MinEdgMet']
              }
            }
        }
        else if(this.formAndGeometry.selectedFrameType.control === '1;1'){ //Plastic
          this.formAndGeometry.minEdgeThickness = ''
          if(thickness['MinEdgCel'] !== '') {
            if(this.formAndGeometry.right.selectedThicknessOption.id === 0 && side !== 'L'){
              this.formAndGeometry.right.selectedThicknessOption.value = thickness['MinEdgCel']
            }
            if(this.formAndGeometry.left.selectedThicknessOption.id === 0 && side !== 'R'){
              this.formAndGeometry.left.selectedThicknessOption.value = thickness['MinEdgCel']
            }
          }
        }
        else if(this.formAndGeometry.selectedFrameType.control === '3;40') { //Flat
            this.formAndGeometry.minEdgeThickness = ''
            if (thickness['MinEdgFlat'] !== '') {
              if(this.formAndGeometry.right.selectedThicknessOption.id === 0 && side !== 'L'){
                this.formAndGeometry.right.selectedThicknessOption.value = thickness['MinEdgFlat']
              }
              if(this.formAndGeometry.left.selectedThicknessOption.id === 0 && side !== 'R'){
                this.formAndGeometry.left.selectedThicknessOption.value = thickness['MinEdgFlat']
              }
            }
          }
        else {
          this.formAndGeometry.minEdgeThickness = ''
        }
      },
        conditionedBevelSelection() {
            /**
             * Returns a filtered bevel position selection based on conditions defined in project_info->Overview_geometry_field.xlsx.
             * Depending on which frame type is selected, bevel position selection values can be different.
            */

            // Get bevel position selection
            const bevelPositionSelection = this.formAndGeometry.bevelPositionSelection
            if (!bevelPositionSelection || bevelPositionSelection === undefined || Object.entries(bevelPositionSelection).length == 0)
                return bevelPositionSelection

            // Frame type selection is required.
            const frameTypeSelection = this.$store.getters.getFrameTypeSelection
            if (!frameTypeSelection || frameTypeSelection === undefined || frameTypeSelection == [])
                return bevelPositionSelection

            // Get frame type
            const frameType = Helpers.getObjProp(this.formAndGeometry.selectedFrameType, 'type', false)
            if (!frameType) return bevelPositionSelection

            // Get bevel position references
            const _automatic = this.$store.getters.getBevelPositionAutomatic
            const _manual = this.$store.getters.getBevelPositionManual
            const _followFront = this.$store.getters.getBevelPositionFollowFront
            const _followBack = this.$store.getters.getBevelPositionFollowBack
            const _3_7 = this.$store.getters.getBevelPosition3_7
            const _4_6 = this.$store.getters.getBevelPosition4_6
            const _5_5 = this.$store.getters.getBevelPosition5_5
            const _6_4 = this.$store.getters.getBevelPosition6_4
            const _7_3 = this.$store.getters.getBevelPosition7_3
            const _frameCurve = this.$store.getters.getBevelPositionFrameCurve

            // Define bevel position selection values for each frame type individually.
            const bevelPositionGroups = {
                metal:                    [_automatic, _manual, _followFront, _followBack, _3_7, _4_6, _5_5, _6_4, _7_3, _frameCurve],
                cell:                     [_automatic, _manual, _followFront, _followBack, _3_7, _4_6, _5_5, _6_4, _7_3, _frameCurve],
                rimlessGroove:            [_automatic, _manual, _followFront, _followBack, _3_7, _4_6, _5_5, _6_4, _7_3, _frameCurve],
                rimlessGrooveAndDrilled:  [_automatic, _manual, _followFront, _followBack, _3_7, _4_6, _5_5, _6_4, _7_3, _frameCurve],
                rimlessDrilled:           [_automatic],
                rimlessFlatEdge:          [_automatic],
            }

            // Get bevel position group based on selected frame type.
            let newSelection = bevelPositionSelection
            switch (frameType) {
                case this.$store.getters.getFrameTypeMetal.type: newSelection = bevelPositionGroups.metal; break;
                case this.$store.getters.getFrameTypeCell.type: newSelection = bevelPositionGroups.cell; break;
                case this.$store.getters.getFrameTypeRimlessGrooved.type: newSelection = bevelPositionGroups.rimlessGroove; break;
                case this.$store.getters.getFrameTypeRimlessGroovedDrilled.type: newSelection = bevelPositionGroups.rimlessGrooveAndDrilled; break;
                case this.$store.getters.getFrameTypeRimlessDrilled.type: newSelection = bevelPositionGroups.rimlessDrilled; break;
                case this.$store.getters.getFrameTypeRimlessFlatEdge.type: newSelection = bevelPositionGroups.rimlessFlatEdge; break;
            }

            // Return selected bevel position group. If some group values are missing, return all values.
            const areAllGroupValuesPresent = (vals) => vals.every(val => val && val !== undefined)
            return areAllGroupValuesPresent(newSelection) ? newSelection : bevelPositionSelection
        },
        displayLensDiameterValues(side){
            if (!side) return

            // Display only the optimized diameter on the scale
            let values = this.formAndGeometry[side].lensDiameterValues
            // if (this.formAndGeometry[side].lensDiameterOptimizedValue.length > 0)
            //     values = this.formAndGeometry[side].lensDiameterOptimizedValue

            return values
        },
        isShapeSelected(shape) {
            if (!this.loadFromDatabase.selectedShape || this.loadFromDatabase.selectedShape === undefined ||
                this.loadFromDatabase.selectedShape.id === undefined || shape.id === undefined)
                return false
            if (this.loadFromDatabase.selectedShape.id == shape.id)
                return true
            return false
        },
        async loadShape(form, type) {
            // Load up a new shape using a form (in hex).
            // Decode FORM from hex to ascii and extract set variables
            // this.form.shape.type = type
            // this.form.shape.current.data = form
            // this.form.shape.current.decodedData = Tracer.decodeTracerFormData(form)
            // this.form.shape.original.data = this.form.shape.current.data = form
            // this.form.shape.original.decodedData = this.form.shape.current.decodedData

            // Set DBL
            let formProperties = Tracer.decodeTracerFormData(form)
            this.formAndGeometry.dbl = Helpers.getObjProp(formProperties.properties, 'DBL', null)

            // Reset height and width
            this.formAndGeometry.right.boxHeight = null
            this.formAndGeometry.right.boxWidth = null
            this.formAndGeometry.left.boxHeight = null
            this.formAndGeometry.left.boxWidth = null

            this.formAndGeometry.panto = null
            this.formAndGeometry.frcv = null
            this.formAndGeometry.ztilt = null

            // Reset shape data
            this.form.shape.type = null
            this.form.shape.current.data = null
            this.form.shape.current.decodedData = null
            this.form.shape.original.data = null
            this.form.shape.original.decodedData = null

            Initializator.setFrameByIdFormData(formProperties)
            await Initializator.interpolate(type, form)
            Initializator.getLensGeo('left')
            Initializator.getLensGeo('right')
        },
        getHistory() {
            let params = {
                reference: this.history.searchReference,
                perPage: this.history.selectedPerPage,
                page: this.history.currentPage,
                orderby: this.history.orderby,
                order: this.history.order,
                dateFrom: null,
                dateTo: null,
            }

            this.history.isLoading = true
            UploadedShape.get(params, response => {
                this.history.isLoading = false

                let data = response.data
                if (data == undefined) data = []

                // Pagination - load from database
                if (this.isHistoryPopupOpened){
                    let rowsCount = response == undefined ? 0 : response.total
                    this.history.totalPages = rowsCount
                    if (rowsCount > 0)
                        this.history.totalPages = Math.ceil(rowsCount / this.history.selectedPerPage)

                    if (rowsCount <= 0 || this.history.totalPages <= 0) {
                        this.history.totalPages = 1
                        this.history.currentPage = 1
                    }
                    this.history.tableData.headers.map(x => x.enabled = true)
                }

                this.history.tableData.rows = data.map(row => {
                    return {
                        id: row.id,
                        rawData: row,
                        values: [
                            row.id,
                            row.reference,
                            row.type,
                            new Date(row.created_at).toLocaleString('de-DE'),
                        ],
                        custom: {

                        }
                    }
                })
            }, error => {
                this.history.isLoading = false
            })
        },
        async getStandardShapes() {
            if (this.loadFromDatabase.shapesList && this.loadFromDatabase.shapesList.length > 0) return

            this.loadFromDatabase.isLoading = true
            this.loadFromDatabase.shapesList = await UploadedShape.getStandardShapesImages()
            this.loadFromDatabase.isLoading = false
        },

      onAnaylyseB2B() {
        this.currentlyOpenedPopup = 'analyseB2B'
      }
    }
}
</script>
<style lang="scss" scoped>
.radio-border-left {
    border-radius: 8px 0px 0px 8px !important;
}
.radio-border-right {
    border-radius: 0px 8px 8px 0px !important;
}
.panel-button2{
    height: 80px;
    width: 16%;
    min-width: 8em;
}
.form-table-header-style{
    min-width: 70px;
}
.form-table-header-delete-style{
    min-width: 40px;
    max-width: 70px;
}
.graphics-container{
    height: 80vh;
    width: 85vw;
    align-self: center;
    @media only screen and (max-width: 990px) { width: 95vw; }
}
</style>

