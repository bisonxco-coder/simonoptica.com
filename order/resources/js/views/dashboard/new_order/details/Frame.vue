<template>
    <div :class="'card pt-3 pb-4 px-5 mb-3 ' + (isComponentEnabled ? '' : 'component-disabled')" id="frame" ref="frame">
        <h4 class="panel-title pt-4 pb-4">{{$translations.newOrder.details.frame.title}}</h4>

        <div class="d-flex justify-content-between">
            <div class="w-100">
                <panel-input
                    :placeholder="$translations.newOrder.details.frame.placeholders.frameCode"
                    v-model="frame.search.frameCode"
                    icon="search-icon"
                    v-on:keyup.enter="onSearch"
                    :disabled="!isComponentEnabled"
                />
            </div>
            <div class="d-flex justify-content-center ms-3 pb-3">
                <span :class="filterButtonStyle" @click="onFilterClick" />
            </div>
        </div>

        <!-- Search options -->
        <div class="row" v-if="showFilters">
            <div class="col-md-4 col-sm-6 col-12">
                <panel-select
                    :placeholder="$translations.newOrder.details.frame.brand"
                    v-model="frame.search.selectedBrand"
                    :selection="frame.search.brandSelection"
                    titleParameter="FSUPPLIER"
                    :disabled="!isComponentEnabled"
                />
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <panel-input
                    :placeholder="$translations.newOrder.details.frame.model"
                    v-model="frame.search.model"
                    :disabled="!isComponentEnabled"
                />
            </div>
            <div class="col-md col-sm-6 col-12">
                <panel-input
                    :placeholder="$translations.newOrder.details.frame.eyeSize"
                    v-model="frame.search.eyeSize"
                    :disabled="!isComponentEnabled"
                />
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <panel-input
                    :placeholder="$translations.newOrder.details.frame.dbl"
                    v-model="frame.search.dbl"
                    :disabled="!isComponentEnabled"
                />
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <panel-select
                    :placeholder="$translations.newOrder.details.frame.frameType"
                    v-model="frame.search.selectedFrameType"
                    :selection="$store.getters.getFrameTypeSelection"
                    titleParameter="description"
                    :disabled="!isComponentEnabled"
                />
            </div>
            <div class="col-md-4 col-sm-6 col-12">
                <panel-input
                    :placeholder="$translations.newOrder.details.frame.color"
                    v-model="frame.search.color"
                    :disabled="!isComponentEnabled"
                />
            </div>
        </div>

        <!-- Button box -->
        <div class="d-flex justify-content-md-end justify-content-center flex-md-nowrap flex-wrap">
            <button
                class="btn btn-secondary panel-button border-0 px-5 my-1 me-2"
                @click="onClear"
                :disabled="!isComponentEnabled"
            >
                {{$translations.common.clear}}
            </button>
            <button
                class="btn btn-secondary panel-button border-0 px-5 my-1 mx-md-3 mx-sm-0"
                @click="onSearch"
                :disabled="!isComponentEnabled"
            >
                {{$translations.common.search}}
            </button>
        </div>

        <hr />

        <!-- Selected shape/frame -->
        <div v-if="frame.selectedFrame" class="d-flex justify-content-sm-between justify-content-center flex-sm-nowrap flex-wrap mb-4">
            <span class="selected-frame-text">
                {{$translations.newOrder.details.frame.selectedFrame}} {{selectedFrameInfo}}
            </span>

            <!-- Deselect shape/frame button -->
            <button
                v-if="$store.getters.isFrameAvailable"
                class="expand-button border-0"
                @click="onFrameSelect(frame.selectedFrame)"
                :disabled="!isComponentEnabled"
            >
                <span>{{$translations.newOrder.details.frame.deselectFrame}}</span>
            </button>
        </div>

        <panel-table
            :tableData="frame.frameTableData"
            v-model="frame.selectedFrame"
            :isLoading="isLoading"
            @onContentRowOpen="onContentRowOpen"
            :isFrame="true"
            type="radio"
        >

            <!-- Form view -->
            <template v-slot:form />

            <!-- Content view -->
            <template v-slot:content="{ row, isSelected }">
                <div class="d-flex mx-0 p-2">
                    <div class="position-relative w-50" style="height: 150px">
                        <span v-if="row.isLoading" class="spinner-icon large opaque position-absolute start-100 h-100"/>
                        <div id="frame_drawing_container" class="position-relative start-50 h-100"/>
                    </div>

                    <div class="align-self-end ms-auto pe-5 mb-4">
                        <button
                            class="expand-button border-0"
                            @click="onFrameSelect(row)"
                            :disabled="!isComponentEnabled"
                        >
                            <span v-if="$store.getters.isFrameAvailable && !isSelected">{{$translations.newOrder.details.frame.selectFrame}}</span>
                            <span v-if="$store.getters.isFrameAvailable && isSelected">{{$translations.newOrder.details.frame.deselectFrame}}</span>
                            <span v-if="$store.getters.isShapeFromFrameAvailable && !isSelected">{{$translations.newOrder.details.frame.loadShape}}</span>
                            <span v-if="$store.getters.isShapeFromFrameAvailable && isSelected">{{$translations.newOrder.details.frame.deselectFrame}}</span>
                        </button>
                    </div>
                </div>
            </template>

        </panel-table>

        <!-- Image popup -->
        <panel-popup :isOpen="frame.showImage" @onClose="onCloseImage">
            <img :class="imageStyle" :src="frame.imageUrl">
        </panel-popup>
    </div>
</template>

<script>
import PanelInput from '@/components/PanelInput.vue'
import PanelTable from '@/components/PanelTable.vue'
import PanelSelect from '@/components/PanelSelect.vue'
import PanelPopup from '@/components/PanelPopup.vue'
import Frame from '@/utils/frame.js'
import Graphics from '@/utils/graphics'

export default {
    components: { PanelInput, PanelTable, PanelSelect , PanelPopup},
    data () {
        return {
            isLoading: false,
            showFilters: false,
            graphicsApp: null,
        }
    },
    computed: {
        isComponentEnabled() {return this.$store.state.moduleOrders.details.isComponentEnabled('frame')},
        orders () {return this.$store.state.moduleOrders},
        frame () {
            if(this.$store.state.moduleOrders.details.frame.showImage){
                window.removeEventListener('keyup', this.onKeyUp);
                window.addEventListener('keyup', this.onKeyUp);
            }
            return this.$store.state.moduleOrders.details.frame
        },
        selectedFrameInfo(){
            if (!this.frame.selectedFrame) return ""
            return this.frame.selectedFrame.values.reduce((total, x) => {
                return total += ", " + x
            })
        },
        imageStyle () {
            let style = "img-fluid image-transition"
            if (this.frame.imageUrl) style += " w-100 h-100"
            else style += " w-25 h-25"
            return style
        },
        filterButtonStyle () {
            let style = "filter-icon blue-hover pointer align-self-center"
            if (this.showFilters) style += ' active'
            return style
        },
    },
    unmounted() {
        window.removeEventListener('keyup', this.onKeyUp);
    },
    methods: {
        onFilterClick() {
            this.showFilters = !this.showFilters
        },
        async onContentRowOpen(row) {
            row.isLoading = true
            row.framePreviewData = await Frame.getFramePreviewData(row)
            row.isLoading = false
            this.$nextTick(() => {
                this.graphicsApp = Graphics.drawFramePreviewLens('frame_drawing_container', this.graphicsApp, row.framePreviewData)
            })
        },
        onKeyUp(val){
            if(val.keyCode === 27){
                window.removeEventListener('keyup', this.onKeyUp);
                this.onCloseImage()
            }
        },
        onOpenImage(row) {
            Frame.onOpenImage(row)
        },
        onCloseImage() {
            Frame.onCloseImage()
        },
        onFrameSelect(row) {
            Frame.onFrameSelect(row)
        },
        async onSearch () {
            this.isLoading = true
            await Frame.onSearch(
                response => this.isLoading = false,
                error => this.isLoading = false,
            )
            this.isLoading = false
        },
        onClear () {
            this.frame.frameTableData.rows = []
            this.isLoading = false
        }
    }
}
</script>
<style lang="scss" scoped >
.image-transition {
    transition: 0.1s;
}
</style>
