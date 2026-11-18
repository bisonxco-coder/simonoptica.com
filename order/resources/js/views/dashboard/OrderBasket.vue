<template>

    <!-- Search bar -->
    <div class="card pt-3 pb-4 px-5 mb-3">
        <h4 class="panel-title pt-4 pb-3">{{$translations.orderbasket.search.title}}</h4>
        <div class="row">
            <div class="col-xl-6 col-lg-12 col-12">
                <panel-input
                    :placeholder="$translations.orderbasket.search.placeholders.reference"
                    v-model="reference"
                    v-on:keyup.enter="onSearch"
                    icon="search-icon"
                />
            </div>
            <div class="col-xl-2 col-lg-6 col-md-6 col-12">
                <panel-input
                    :placeholder="$translations.orderbasket.search.placeholders.dateFrom"
                    v-model="dateFrom"
                    icon="calendar-icon"
                    type="date"
                />
            </div>
            <div class="col-xl-2 col-lg-6 col-md-6 col-12">
                <panel-input
                    :placeholder="$translations.orderbasket.search.placeholders.dateTo"
                    v-model="dateTo"
                    icon="calendar-icon"
                    type="date"
                />
            </div>
            <!-- <div class="col-xl-2 col-lg-6 col-md-6 col-12" v-if="isManager">
                <panel-select
                    :placeholder="$translations.orderbasket.search.placeholders.shop"
                    :selection="shopSelection"
                    v-model="selectedShop"
                    :notNull="true"
                />
            </div> -->
            <div class="col-xl-2 col-lg-6 col-md-6 col-12">
                <panel-select
                    :placeholder="$translations.orderbasket.search.placeholders.perPage"
                    :selection="perPageSelection"
                    v-model="selectedPerPage"
                    :notNull="true"
                />
            </div>
        </div>

        <!-- Button box -->
        <div class="d-flex justify-content-md-end justify-content-center flex-md-nowrap flex-wrap">
            <button class="btn btn-secondary panel-button border-0 px-5 my-1 me-2" @click="onClear">{{$translations.common.clear}}</button>
            <button class="btn btn-secondary panel-button border-0 px-5 my-1 me-2" @click="onSearch">{{$translations.common.search}}</button>
        </div>
    </div>

    <!-- Orders -->
    <div class="card pt-3 pb-4 px-5 mb-5">
        <panel-table
            :tableData="orderBasket.orderBasketTableData"
            :isLoading="isLoading"
            type="checkbox"
            v-model="orderBasket.selectedOrderBaskets"
            v-model:currentPage="currentPage"
            v-model:totalPages="totalPages"
            @onHeaderClick="onHeaderClick"
            @onFormViewOpen="onFormViewOpen"
            :showId="true"
        >
            <!-- Action bar -->
            <template v-slot:actions="{ isSelected, row }">
                <div class="btn-group dropup">
                    <div data-bs-toggle="dropdown" aria-expanded="false">
                        <span :class="'more-icon small pointer ' + (isSelected ? ' inverted-color' : '' )"/>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-lg-end shadow border-1 p-3 my-1 me-2">
                        <li>
                            <a class="dropdown-item drop pointer py-2 px-3" @click="onOrderEdit(row)" :disabled="editOrderCondition(row)">
                                <span class="edit-icon small align-middle" />
                                {{$translations.orderbasket.table.actions.edit}}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item drop pointer py-2 px-3" @click="onOrderCopy(row)">
                                <span class="copy-icon small align-middle" />
                                {{$translations.orderbasket.table.actions.copy}}
                            </a>
                        </li>
                        <li v-if="row.rawData.Rectype !== 'W'">
                            <a class="dropdown-item drop pointer py-2 px-3" @click="onOrderPrint(row)" :disabled="printOrderCondition(row)">
                                <span class="print-icon small align-middle" />
                                {{$translations.orderbasket.table.actions.print}}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item drop pointer py-2 px-3" @click="onOrderDelete(row)" :disabled="deleteOrderCondition(row)">
                                <span class="delete-icon small align-middle" />
                                {{$translations.orderbasket.table.actions.delete}}
                            </a>
                        </li>
                    </ul>
                </div>
            </template>

            <!-- Form view Toolbar -->
            <template v-slot:formToolbar="{ formData }">

                <!-- Mobile view -->
                <div v-if="isVerticalView" class="btn-group dropup">
                    <div data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="more-icon small pointer px-4 me-3"/>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-lg-end shadow border-1 p-3 my-1 me-2">
                        <li>
                            <a class="dropdown-item drop pointer py-2 px-3" @click="onOrderEdit(formData, true)" :disabled="editOrderCondition(formData, true)">
                                <span class="edit-icon small align-middle" />
                                {{$translations.orderbasket.table.actions.edit}}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item drop pointer py-2 px-3" @click="onOrderCopy(formData, true)">
                                <span class="copy-icon small align-middle" />
                                {{$translations.orderbasket.table.actions.copy}}
                            </a>
                        </li>
                        <li v-if="formData.Rectype !== 'W'">
                            <a class="dropdown-item drop pointer py-2 px-3" @click="onOrderPrint(formData, true)" :disabled="printOrderCondition(formData, true)">
                                <span class="print-icon small align-middle" />
                                {{$translations.orderbasket.table.actions.print}}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item drop pointer py-2 px-3" @click="onOrderDelete(formData, true)" :disabled="deleteOrderCondition(formData, true)">
                                <span class="delete-icon small align-middle" />
                                {{$translations.orderbasket.table.actions.delete}}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Desktop view -->
                <div v-else>
                    <button class="btn edit-icon px-4 me-3" :title="$translations.orderbasket.table.actions.edit" @click="onOrderEdit(formData, true)" :disabled="editOrderCondition(formData, true)"></button>
                    <button class="btn copy-icon px-4 me-3" :title="$translations.orderbasket.table.actions.copy" @click="onOrderCopy(formData, true)"></button>
                    <button v-if="formData.Rectype !== 'W'" class="btn print-icon px-4 me-3" :title="$translations.orderbasket.table.actions.print" @click="onOrderPrint(formData, true)" :disabled="printOrderCondition(formData, true)"></button>
                    <button class="btn delete-icon px-4 me-3" :title="$translations.orderbasket.table.actions.delete" @click="onOrderDelete(formData, true)" :disabled="deleteOrderCondition(formData, true)"></button>
                </div>
            </template>

            <!-- Form view -->
            <template v-slot:form="{ formData }">

                <!-- PROPERTIES -->
                <div class="row justify-content-between">
                    <div class="col-auto p-3 mb-2 pt-4">
                        <label class="placeholder-text d-flex">{{$translations.orderbasket.table.header.id}}</label>
                        <label class="w-100">{{formData.OrdNumb}}</label>
                    </div>
                    <div class="col-auto p-3 mb-2 pt-4">
                        <label class="placeholder-text d-flex">{{$translations.orderbasket.table.header.status}}</label>
                        <label class="w-100">{{parseRecStatLabel(formData.RecStat)}}</label>
                    </div>
                    <div class="col-auto p-3 mb-2 pt-4">
                        <label class="placeholder-text d-flex">{{$translations.orderbasket.table.header.type}}</label>
                        <label class="w-100">{{parseRectype(formData.Rectype)}}</label>
                    </div>
                    <div class="col-auto p-3 mb-2 pt-4">
                        <label class="placeholder-text d-flex">{{$translations.orderbasket.table.header.reference}}</label>
                        <label class="w-100">{{formData.Reference}}</label>
                    </div>
                    <div class="col-auto p-3 mb-2 pt-4" v-if="!isBulkorder(formData)">
                        <label class="placeholder-text d-flex">{{$translations.orderbasket.table.header.frame}}</label>
                        <label class="w-100">{{formData.FReference}}</label>
                    </div>
                    <div class="col-auto p-3 mb-2 pt-4">
                        <label class="placeholder-text d-flex">{{$translations.orderbasket.table.header.enteredOn}}</label>
                        <label class="w-100">{{Helpers.parseUTCtoLocal(formData.EntryDate + " " + formData.EntryTime)}}</label>
                    </div>
                </div>
                <div class="row justify-content-start">
                    <div class="col-auto p-3 mb-2 pt-4">
                        <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.remark}}</label>
                        <label v-if="parseRemarkField(formData.Remark).additionalRemarkDescr !== ''" class="w-100">{{parseRemarkField(formData.Remark).additionalRemarkDescr}}</label>
                        <label class="w-100">{{parseRemarkField(formData.Remark).remark}}</label>
                    </div>
                </div>

                <hr class="mb-3">

                <!-- Bulk Order -->
                <div v-if="isBulkorder(formData)">
                    <panel-table
                        dense
                        colorEverySecondRow
                        :tableData="bulkorder.tableData"
                        :isLoading="false"
                        v-model:currentPage="bulkorder.currentPage"
                        v-model:totalPages="bulkorder.totalPages"
                        @onHeaderClick="onBulkorderTableHeaderClick"
                        :showId="true"
                    >
                    </panel-table>
                </div>

                <!-- Normal Order -->
                <div v-else>

                    <!-- DBL -->
                    <div class="mb-4">
                        <div class="position-relative w-100">
                            <label class="placeholder-text text-center w-100">{{$translations.orderbasket.table.formView.dbl}}</label>
                            <label class="text-center w-100">{{getOrderShapeFieldForSide(formData.order_shape, 'L','DBL')}}</label>
                        </div>
                    </div>

                    <!-- SHAPE PREVIEW -->
                    <div v-if="formData.shapePreviewData" class="p-2 mb-5">
                        <div class="w-100" id="orderbasket_drawing_container" style="height: 350px" />
                    </div>

                    <!-- DETAILS -->
                    <div class="row">
                    <!-- LENS PROPERTIES - right and left lens -->
                        <div :class="lensPropertiesStyle(orderLensProperty)"
                            v-for="orderLensProperty in getSortedOrderLensProperties(formData.order_lens_properties)"
                            :key="orderLensProperty"
                            :value="orderLensProperty"
                        >
                            <h4 class="fs-6 d-flex justify-content-center mb-3">
                                {{getOrderLensPropertySideHeader(orderLensProperty)}}
                            </h4>

                            <div class="row justify-content-start">
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.type}}</label>
                                    <label class="w-100">
                                        {{getOrderComponentFieldForSide(formData.order_component, orderLensProperty.Side, orderLensProperty.CTO)}}
                                    </label>
                                </div>
                            </div>
                            <div class="row justify-content-start">
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.sphere}}</label>
                                    <label class="w-100">{{orderLensProperty.Sphere}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.cylinder}}</label>
                                    <label class="w-100">{{orderLensProperty.Cylinder}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.axis}}</label>
                                    <label class="w-100">{{orderLensProperty.Axis}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{(getAddDeg(orderLensProperty.Side) > 1) ? $translations.orderbasket.table.formView.degression : $translations.orderbasket.table.formView.addition}}</label>
                                    <label class="w-100">{{orderLensProperty.Addition}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.prism1}}</label>
                                    <label class="w-100">{{orderLensProperty.Prism1}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.base1}}</label>
                                    <label class="w-100">{{orderLensProperty.PrBase1}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.prism2}}</label>
                                    <label class="w-100">{{orderLensProperty.Prism2}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.base2}}</label>
                                    <label class="w-100">{{orderLensProperty.PrBase2}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.pd}}</label>
                                    <label class="w-100">{{orderLensProperty.PD}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.height}}</label>
                                    <label class="w-100">{{orderLensProperty.Height}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.decentrationX}}</label>
                                    <label class="w-100">{{orderLensProperty.DecentX}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.decentrationY}}</label>
                                    <label class="w-100">{{orderLensProperty.DecentY}}</label>
                                </div>
                            </div>
                            <div class="row justify-content-start">
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.centTh}}</label>
                                    <label class="w-100">{{orderLensProperty.Centthick}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.edgeTh}}</label>
                                    <label class="w-100">{{orderLensProperty.Edgethick}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.orderbasket.table.formView.basec}}</label>
                                    <label class="w-100">{{orderLensProperty.BaseCurve}}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- INDIVIDAUL -->
                    <div v-if="hasIndividualValues">
                        <hr class="mb-3">

                        <h4 class="fs-6 d-flex justify-content-center mb-3">{{$translations.orderbasket.table.formView.individual}}</h4>

                        <!-- INDIVIDAUL - PROPERTIES-->
                        <div class="row justify-content-start mb-4">
                            <div
                                class="col-auto p-3 mb-2 pt-4"
                                v-for="definedField in individualFields.definedFields"
                                :key="definedField.label"
                                :value="definedField"
                            >
                                <label class="placeholder-text d-flex">{{definedField.label}}</label>
                                <label class="w-100">{{definedField.value}}</label>
                            </div>
                        </div>

                        <div class="row">
                            <!-- INDIVIDUAL - RIGHT LENS -->
                            <div class="col-md col-sm-12 mb-md-0 mb-5 border-sm-right">
                                <h4 class="fs-6 d-flex justify-content-center mb-3">{{$translations.common.rightLens}}</h4>

                                <div class="row justify-content-start ">
                                    <div
                                        class="col-auto p-3 mb-2 pt-4"
                                        v-for="definedField in individualFields.right.definedFields"
                                        :key="definedField.label"
                                        :value="definedField"
                                    >
                                        <label class="placeholder-text d-flex">{{definedField.label}}</label>
                                        <label class="w-100">{{definedField.value}}</label>
                                    </div>
                                </div>
                            </div>

                            <!-- INDIVIDUAL - LEFT LENS -->
                            <div class="col-md col-sm-12 mb-md-0 mb-5">
                                <h4 class="fs-6 d-flex justify-content-center mb-3">{{$translations.common.leftLens}}</h4>

                                <div class="row justify-content-start ">
                                    <div
                                        class="col-auto p-3 mb-2 pt-4"
                                        v-for="definedField in individualFields.left.definedFields"
                                        :key="definedField.label"
                                        :value="definedField"
                                    >
                                        <label class="placeholder-text d-flex">{{definedField.label}}</label>
                                        <label class="w-100">{{definedField.value}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </template>

        </panel-table>
    </div>
</template>

<script>
import PanelTable from '@/components/PanelTable.vue'
import PanelInput from '@/components/PanelInput.vue'
import PanelSelect from '@/components/PanelSelect.vue'
import Order from '@/utils/order.js'
import Helpers from '@/utils/helpers.js'
import Graphics from '@/utils/graphics'
import Tracer from '@/utils/tracer.js'
import Individual from '@/utils/individual.js'
import Initializator from '@/utils/initializator.js'

export default {
    components: { PanelInput, PanelTable, PanelSelect },
    data() {
        return {
            Helpers,
            formViewOrder: null,
            isLoading: false,
            clientWidth: 0,
            lastParams: {},
            getOrderbasketOrdersTimer: null,
            individualFields: {
                definedFields: [],
                right: {
                    type: null,
                    definedFields: [],
                },
                left: {
                    type: null,
                    definedFields: [],
                }
            },
            bulkorder: {
                currentPage: 0,
                totalPages: 0,
                tableData: {
                    headers: [],
                    rows: []
                }
            },

            previewShapeScale: 20,

            // Form variables
            reference: '',
            dateFrom: '',
            dateTo: '',
            shopSelection: [],
            selectedShop: null,
            perPageSelection: [1,2,3,5,10,25,50,75,100],
            selectedPerPage: 10,
            totalRows: 0,
            totalPages: 1,
            currentPage: 1,
            graphicsApp: null,
        }
    },
    computed: {
        orderBasket () {return this.$store.state.moduleOrders.orderBasket},
        orders () {return this.$store.state.moduleOrders},
        isVerticalView() {return this.clientWidth < 500},
        hasIndividualValues () {
            if (Individual.doDefinedFieldsHaveValues(this.individualFields.definedFields)) return true
            if (Individual.doDefinedFieldsHaveValues(this.individualFields.left.definedFields)) return true
            if (Individual.doDefinedFieldsHaveValues(this.individualFields.right.definedFields)) return true
            return false
        }
    },
    watch: {
        selectedPerPage() {
            this.getOrderbasketOrders(this.lastParams)
            this.orderBasket.selectedOrderBaskets = []
        },
        currentPage() {
            this.getOrderbasketOrders(this.lastParams)
            this.orderBasket.selectedOrderBaskets = []
        }
    },
    mounted () {
        // Set dashboard title
        this.$store.commit('setDashboardTitle', {
            title: () => this.$translations.orderbasket.title,
            subTitle: ''
        })

        this.onClear();
        this.onScreenResize()

        window.addEventListener('resize', this.onScreenResize)
        document.addEventListener('orderSentEvent', this.onOrderSent)
    },
    unmounted () {
        window.removeEventListener('resize', this.onScreenResize)
        document.removeEventListener('orderSentEvent', this.onOrderSent)
    },
    methods: {
        getAddDeg(side) {
          const addDeg = this.formViewOrder['order_extension'].find(x => x.RecType === 'ADDDEG_'+side)
          if(addDeg)
            return addDeg['Flag1']
          else return 0
        },
        onScreenResize() {
            this.clientWidth = window.innerWidth
        },
        onFormViewOpen(row) {
            this.formViewOrder = row

            if (this.isBulkorder(row)){
                this.bulkorder.tableData = this.getBulkorderTableData(row)
                return
            }

            this.createPreviewLens(row)
            this.createIndividualProperties(row)
        },
        onOrderSent() {
            this.getOrderbasketOrders()
        },
        onOrderEdit(row, isFormData = false) {
            Order.edit(
                isFormData ? row : row.rawData,
                response => {
                    // Notify user if there was an error editing the order
                    if (response.hasMessages()){
                        this.$store.dispatch('notify', {
                            message: response.getMessage(),
                            type: "warning",
                        })
                    }

                    // Go to details route
                    this.$router.push('/details')
                }
            )
        },
        onOrderCopy(row, isFormData = false) {
            Order.edit(
                isFormData ? row : row.rawData,
                response => {
                    // Notify user if there was an error editing the order
                    if (response.hasMessages()){
                        this.$store.dispatch('notify', {
                            message: response.getMessage(),
                            type: "warning",
                        })
                    }

                    // Reset editing flag, because later saving the order must create a new one
                    this.orders.isEditing = false
                    this.orders.editingOrder = null

                    // Go to home route
                    this.$router.push('newOrder')
                }
            )
        },
        onOrderDelete(row, isFormData = false) {
            // Warn the user before deleting any order
            this.$store.dispatch('dialog', {
                title: this.$translations.dialog.warning,
                description: this.$translations.dialog.messages.deleteOrderConfirmation,
                falseButtonText: this.$translations.dialog.cancel,
                trueButtonText: this.$translations.dialog.delete,
                type: 'warning',
                allowClosing: true
            }).then(value => {
                if (!value) return
                this.isLoading = true
                let data = isFormData ? row : row.rawData
                this.$store.dispatch('deleteOrder', data).then((response) => {
                    this.isLoading = false
                    this.getOrderbasketOrders(this.lastParams)
                    this.orderBasket.selectedOrderBaskets = []
                }, e => {
                    this.isLoading = false
                    this.orderBasket.selectedOrderBaskets = []

                    this.$store.dispatch('notify', {
                        message: e.data.message,
                        duration: 5
                    })
                    console.error(e.data)
                });
            })
        },
        onOrderPrint(row, isFormData = false) {
            let data = isFormData ? row : row.rawData
            Order.print(data)
        },
        onHeaderClick(event) {
            this.getOrderbasketOrders({
                ...this.lastParams,
                orderby: event.header.sortParam,
                order: event.isSorting ? 'asc' : 'desc',
            })
        },
        onSearch() {
            this.getOrderbasketOrders({
                reference: this.reference,
                dateFrom: this.dateFrom,
                dateTo: this.dateTo,
            })
        },
        onClear() {
            // Set variables to defaults and reload orders
            this.reference = ''
            this.dateFrom = ''
            this.dateTo = ''
            this.selectedShop = null
            this.currentPage = 1
            this.totalPages = 1

            this.getOrderbasketOrders()
        },
        onBulkorderTableHeaderClick(event) {
            const sortParam = event.header.sortParam
            const isAscending = event.isSorting

            this.bulkorder.tableData = this.getBulkorderTableData(this.formViewOrder, sortParam, isAscending)
        },
        async createPreviewLens(row) {
            // Take right lens by default
            let shape = row.order_shape_extension.find(x => x.Side == 'R')
            if (!shape || shape === undefined) {

                // If there is no right lens, take left one
                shape = row.order_shape_extension.find(x => x.Side == 'L')
                if (!shape || shape === undefined) {
                    row.shapePreviewData = null
                    return
                }
            }

            let formData = Helpers.getObjProp(shape, 'FormPreview', null)
            if (!formData || formData == '0') {
                row.shapePreviewData = null
                return
            }

            const interpolatedForm = await Initializator.interpolateBasic("VCA", formData)
            row.shapePreviewData = Tracer.decodeTracerFormData(interpolatedForm, false)
            this.$nextTick(() => {
                this.graphicsApp = Graphics.drawFramePreviewLens('orderbasket_drawing_container', this.graphicsApp, row.shapePreviewData, this.previewShapeScale)
            })
        },
        async createIndividualProperties(row){
            this.individualFields.definedFields = []
            this.individualFields.right.definedFields = []
            this.individualFields.left.definedFields = []

            if (!row.order_lens_properties || row.order_lens_properties === undefined || row.order_lens_properties.length == 0)
                return

            // Right Individual fields
            const rightOrderLensProperty = row.order_lens_properties.find(x => x.Side == 'R')
          const leftOrderComponent = row.order_component.find(x => x.Side == 'L')
          const rightOrderComponent = row.order_component.find(x => x.Side == 'R')
          let compCodeR = ''
          let compCodeL = ''
          if(rightOrderComponent) compCodeR = rightOrderComponent.Compcode
          if(leftOrderComponent) compCodeL = leftOrderComponent.Compcode
            if (rightOrderLensProperty){
                let indType = Helpers.getObjProp(rightOrderLensProperty, 'IndType')
                let ind = Helpers.getObjProp(rightOrderLensProperty, 'Individual')
                let individualFields = await Individual.translateIndividualString('right', indType, ind, compCodeR, compCodeL)
                this.individualFields.right.definedFields = individualFields.right.definedFields
                this.individualFields.definedFields = individualFields.definedFields
            }

            // Left Individual fields
            const leftOrderLensProperty = row.order_lens_properties.find(x => x.Side == 'L')
            if (leftOrderLensProperty){
                let indType = Helpers.getObjProp(leftOrderLensProperty, 'IndType')
                let ind = Helpers.getObjProp(leftOrderLensProperty, 'Individual')

                let individualFields = await Individual.translateIndividualString('left', indType, ind, compCodeR, compCodeL)
                this.individualFields.left.definedFields = individualFields.left.definedFields
                this.individualFields.definedFields = individualFields.definedFields
            }
        },
        getBulkorderTableData(order, sortBy = '', ascending = true) {
            const isBulkComponent = (x) => x.Side === 'B' && x.ItemNumb !== null && x.ItemNumb !== '' && x.Quantity !== ''
            const sortString = (a,b) => ascending ? a.toUpperCase() - b.toUpperCase() : b.toUpperCase() - a.toUpperCase()
            const sortNumber = (a,b) => ascending ? parseFloat(a) - parseFloat(b) : parseFloat(b) - parseFloat(a)
            const store = this.$store
            return {
                headers: [
                    {get label() {return store.getters.translations.orderbasket.table.bulkorder.description}, sortParam: 'descr'},
                    {get label() {return store.getters.translations.orderbasket.table.bulkorder.diameter}, sortParam: 'dia'},
                    {get label() {return store.getters.translations.orderbasket.table.bulkorder.sphere}, sortParam: 'sph'},
                    {get label() {return store.getters.translations.orderbasket.table.bulkorder.cylinder}, sortParam: 'cyl'},
                    {get label() {return store.getters.translations.orderbasket.table.bulkorder.addition}, sortParam: 'add'},
                    {get label() {return store.getters.translations.orderbasket.table.bulkorder.quantity}, sortParam: 'qty'},
                ],
                rows: order.order_component
                    .filter(x => isBulkComponent(x))
                    .sort((a, b) => {
                        switch (sortBy) {
                            case 'descr': return sortString(a.CompDescr, b.CompDescr); break;
                            case 'dia': return sortNumber(a.Diam1, b.Diam1); break;
                            case 'sph': return sortNumber(a.Sphere, b.Sphere); break;
                            case 'cyl': return sortNumber(a.Cylinder, b.Cylinder); break;
                            case 'add': return sortNumber(a.Addition, b.Addition); break;
                            case 'qty': return sortNumber(a.Quantity, b.Quantity); break;
                        }
                        return 0
                    })
                    .map(row => {
                        return {
                            id: row.ItemNumb,
                            rawData: row,
                            values: [
                                row.CompDescr,
                                row.Diam1,
                                row.Sphere,
                                row.Cylinder,
                                row.Addition,
                                row.Quantity,
                            ],
                        }
                    })
            }
        },
        getSortedOrderLensProperties(orderLensProperties) {
            if (!orderLensProperties ||
                orderLensProperties.length == 0 ||
                orderLensProperties == undefined ||
                Object.entries(orderLensProperties) == 0
            ) return orderLensProperties

            // Has only single lens properties
            if (orderLensProperties.length == 1) return orderLensProperties

            // Has both left and right lens properties. Make sure that right lens is first in order
            let sortedOrderLensProperties = orderLensProperties
            if (sortedOrderLensProperties[0].Side || sortedOrderLensProperties[0].Side !== undefined){
                if (sortedOrderLensProperties[0].Side === 'L') {
                    sortedOrderLensProperties = sortedOrderLensProperties.reverse()
                }
            }

            return sortedOrderLensProperties
        },
        getOrderLensPropertySideHeader(orderLensProperty) {
            if (!orderLensProperty.Side || orderLensProperty.Side === undefined) return ""
            switch (orderLensProperty.Side) {
                case 'R': return this.$translations.common.rightLens;
                case 'L': return this.$translations.common.leftLens;
            }
            return ""
        },
        getOrderComponentFieldForSide(ordercomponent, side, cto) {
            if (!ordercomponent || ordercomponent === undefined || !side) return null

            let component = ordercomponent.find(x => x.Comptype == '01' && x.Side == side)
            if (!component) return null

            let prodDesc = Helpers.getObjProp(component, 'CompDescr', null)
            prodDesc += ' | ' + Helpers.getObjProp(component, 'Diam1', null)
            if (parseInt(Helpers.getObjProp(component, 'Diam2', null)) > 0 && parseInt(Helpers.getObjProp(component, 'Diam2', null)) !== parseInt(Helpers.getObjProp(component, 'Diam1', null))) {
               prodDesc += '/' + Helpers.getObjProp(component, 'Diam2', null)
            }
            if (parseInt(Helpers.getObjProp(component, 'Diam3', null)) > 0) {
               prodDesc += '-' + Helpers.getObjProp(component, 'Diam3', null)
            }

            if (cto == '1') {
               prodDesc += ' | ' + this.$translations.footer.calculation.lens.optimized
            }

            for (let i = 2  ;  i < 21  ;  ++i) {
               component = ordercomponent.find(x => x.Position == i.toString().padStart(3, '0') && x.Side == side)
               if (!component) {
                  continue
               }

               prodDesc += ' | ' + Helpers.getObjProp(component, 'CompDescr', null)
            }

            for (let i = 0  ;  i < 100  ;  ++i) {
               component = ordercomponent.find(x => x.Position == i.toString() && x.Side == side)
               if (!component) {
                  continue
               }

               prodDesc += ' | ' + Helpers.getObjProp(component, 'CompDescr', null)
            }

            return prodDesc
        },
        getOrderShapeFieldForSide(ordershape, side, field) {
            if (!ordershape || ordershape === undefined || !side || !field) return null

            let shape = ordershape.find(x => x.Side == side)
            if (!shape) return null

            return Helpers.getObjProp(shape, field, null)
        },
        editOrderCondition(row, isFormData = false) {
            let stat = isFormData ? row.RecStat : row.rawData.RecStat
            if (stat == 'WIMP') return true
            return

        },
        printOrderCondition(row, isFormData = false) {
            const order = isFormData ? row : row.rawData
            return
            // return !Order.isOrderBulkorder(order)
        },
        deleteOrderCondition(row, isFormData = false) {
            let stat = isFormData ? row.RecStat : row.rawData.RecStat
            if (stat == 'WIMP') return true
            return
        },
        getOrderbasketOrders(params) {
            // Combine dynamic varibles with static ones, that always need to be present
            this.lastParams = {
                ...params,
                count: this.selectedPerPage,
                page: this.currentPage,
            }

            this.isLoading = true
            return this.getOrderbasketOrdersTimer = Helpers.throttleDelay(this.getOrderbasketOrdersTimer, () => {
                return this.$store.dispatch('getOrderbasketOrders', this.lastParams).then((response) => {
                    this.isLoading = false

                    // Pagination
                    let orderCount = response == undefined ? 0 : response.total
                    this.totalPages = orderCount
                    if (orderCount > 0)
                        this.totalPages = Math.ceil(orderCount / this.selectedPerPage)

                    if (orderCount <= 0 || this.totalPages <= 0) {
                        this.totalPages = 1
                        this.currentPage = 1
                    }

                    // Fill table data
                    let data = response.data
                    if (data === undefined || Object.entries(data).length === 0)
                        data = []

                    this.orderBasket.orderBasketTableData.rows = data.map(row => {

                        // Parse translations
                        let statusTooltip = this.parseRecStatTooltip(row.RecStat)
                        let statusLabel = this.parseRecStatLabel(row.RecStat)
                        let statusType = this.parseRectype(row.Rectype)

                        return {
                            id: row.OrdNumb,
                            rawData: row,
                            values: [
                                row.OrdNumb,
                                {value: statusLabel, tooltip: statusTooltip},
                                statusType,
                                row.Reference,
                                row.FReference,
                                Helpers.parseUTCtoLocal(row.EntryDate + " " + row.EntryTime),
                            ],
                            custom: {

                                // Add custom row coloring based on order status
                                selectElementStyle: function(row) {
                                    let stat = row.rawData.RecStat
                                    if (stat == 'REQU') return 'request'
                                    if (stat == 'WIMP') return 'waiting'
                                    return ""
                                },

                                // Prevent selecting order rows, based on order status
                                isRowSelectable: function(row) {
                                    let stat = row.rawData.RecStat
                                    if (stat == 'REQU' || stat == 'WIMP' || stat == 'DRFT') return false
                                    return true
                                }
                            }
                        }
                    })
                }, e => {
                    this.isLoading = false
                    this.orderBasket.selectedOrderBaskets = []

                    this.$store.dispatch('notify', {
                        message: e.data.message,
                    })
                    console.error(e.data)
                });
            }, 200)
        },
        lensPropertiesStyle(orderLensProperty) {
            let side = Helpers.getObjProp(orderLensProperty, 'Side', null)
            let style = "col-md col-sm-12 mb-md-0 mb-5"
            if (side && side == 'R')
                style += " border-sm-right"
            return style
        },
        parseRemarkField(remarkString) {
            const remarkData = Order.getRemarkData(remarkString)
            return remarkData
        },
        parseRectype(rectype) {
            let statusType = ''
            switch (rectype) {
                case 'F': statusType = this.$translations.orderbasket.table.statusTypes.f
                break;
                case 'N': statusType = this.$translations.orderbasket.table.statusTypes.n
                break;
                case 'E': statusType = this.$translations.orderbasket.table.statusTypes.e
                break;
                case 'G': statusType = this.$translations.orderbasket.table.statusTypes.g
                break;
                case 'S': statusType = this.$translations.orderbasket.table.statusTypes.s
                break;
                case 'W': statusType = this.$translations.orderbasket.table.statusTypes.bulkorder
                break;
            }
            return statusType
        },
        parseRecStatLabel(recstat) {
            let statusLabel = ''
            switch (recstat) {
                case 'REQU':
                    statusLabel = this.$translations.orderbasket.table.statusLabels.requ
                break;
                case 'WIMP':
                    statusLabel = this.$translations.orderbasket.table.statusLabels.wimp
                break;
                case 'UOTP':
                    statusLabel = this.$translations.orderbasket.table.statusLabels.uotp
                break;
                case 'PREP':
                    statusLabel = this.$translations.orderbasket.table.statusLabels.prep
                break;
                case 'SEND':
                    statusLabel = this.$translations.orderbasket.table.statusLabels.send
                break;
                case 'DRFT':
                    statusLabel = this.$translations.orderbasket.table.statusLabels.drft
                break;
            }
            return statusLabel
        },
        parseRecStatTooltip(recstat) {
            let statusTooltip = ''
            switch (recstat) {
                case 'REQU':
                    statusTooltip = this.$translations.orderbasket.table.statusTooltips.requ
                break;
                case 'WIMP':
                    statusTooltip = this.$translations.orderbasket.table.statusTooltips.wimp
                break;
                case 'UOTP':
                    statusTooltip = this.$translations.orderbasket.table.statusTooltips.uotp
                break;
                case 'PREP':
                    statusTooltip = this.$translations.orderbasket.table.statusTooltips.prep
                break;
                case 'SEND':
                    statusTooltip = this.$translations.orderbasket.table.statusTooltips.send
                break;
                case 'DRFT':
                    statusTooltip = this.$translations.orderbasket.table.statusTooltips.drft
                break;
            }
            return statusTooltip
        },
        isBulkorder(order) {
            return Order.isOrderBulkorder(order)
        }
    }
}
</script>
<style scoped>
.btn-group{
    position: absolute;
    display: contents;
}
</style>
