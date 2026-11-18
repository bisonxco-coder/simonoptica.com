<template>

    <!-- Search bar -->
    <div class="card pt-3 pb-4 px-5 mb-3">
        <h4 class="panel-title pt-4 pb-3">{{$translations.overview.search.title}}</h4>
        <div class="row">
            <div class="col-xl-6 col-lg-12 col-12">
                <panel-input
                    :placeholder="$translations.overview.search.placeholders.reference"
                    v-model="reference"
                    v-on:keyup.enter="onSearch"
                    icon="search-icon"
                />
            </div>
            <div class="col-xl-2 col-lg-6 col-md-6  col-12">
                <panel-input
                    :placeholder="$translations.overview.search.placeholders.dateFrom"
                    v-model="dateFrom"
                    icon="calendar-icon"
                    type="date"
                />
            </div>
            <div class="col-xl-2 col-lg-6 col-md-6  col-12">
                <panel-input
                    :placeholder="$translations.overview.search.placeholders.dateTo"
                    v-model="dateTo"
                    icon="calendar-icon"
                    type="date"
                />
            </div>
            <div class="col-xl-2 col-lg-6 col-md-6 col-12">
                <panel-select
                    :placeholder="$translations.overview.search.placeholders.perPage"
                    :selection="perPageSelection"
                    v-model="selectedPerPage"
                    :notNull="true"
                />
            </div>
            <div class="col-xl-2 col-lg-6 col-md-6 col-12">
                <panel-select
                    :placeholder="$translations.overview.search.placeholders.daysBack"
                    :selection="daysBackSelection"
                    v-model="selectedDaysBack"
                    :notNull="true"
                />
            </div>
          <div class="col-xl-4 col-lg-6 col-md-6 col-12" v-if="showShopConfiguration === 'YES' && isManager">
            <panel-select
                :placeholder="$translations.overview.search.placeholders.shop"
                :selection="shopConfiguration.userTableData.rows"
                titleParameter="text"
                idParameter="id"
                v-model="selectedShop"
            />
          </div>
            <div class="col-xl-2 col-lg-6 col-md-6 col-12 py-3 ">
                <input
                    id="overview_isArchived"
                    v-model="isArchived"
                    class="form-check-input panel-checkbox"
                    type="checkbox"
                />
                <label class="form-check-label ms-3  position-absolute" for="overview_isArchived">{{$translations.overview.search.placeholders.archived}}</label>
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
            :tableData="overview.overviewTableData"
            :isLoading="isLoading"
            v-model:currentPage="currentPage"
            v-model:totalPages="totalPages"
            @onHeaderClick="onHeaderClick"
            @onFormViewOpen="onFormViewOpen"
            :showId="(showIdInOverviewTable === 'YES')"
        >

            <!-- Action bar -->
            <template v-slot:actions="{ isSelected, row }">
                <div class="btn-group dropup">
                    <div data-bs-toggle="dropdown" aria-expanded="false">
                        <span :class="'more-icon small pointer ' + (isSelected ? ' inverted-color' : '' )" />
                    </div>
                    <ul class="dropdown-menu dropdown-menu-lg-end shadow border-1 p-3 my-1 me-2">
                        <li>
                            <a class="dropdown-item pointer drop py-2 px-3" @click="onOrderCopy(row)">
                                <span class="copy-icon small align-middle" />
                                {{$translations.overview.table.actions.copy}}
                            </a>
                        </li>
                        <li v-if="row.rawData.Rectype !== 'W'">
                            <a class="dropdown-item pointer drop py-2 px-3" @click="onOrderPrint(row)" :disabled="printOrderCondition(row)">
                                <span class="print-icon small align-middle" />
                                {{$translations.overview.table.actions.print}}
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
                            <a class="dropdown-item drop pointer py-2 px-3" @click="onOrderCopy(formData, true)">
                                <span class="copy-icon small align-middle" />
                                {{$translations.overview.table.actions.copy}}
                            </a>
                        </li>
                        <li v-if="formData.Rectype !== 'W'">
                            <a class="dropdown-item drop pointer py-2 px-3" @click="onOrderPrint(formData, true)" :disabled="printOrderCondition(formData, true)">
                                <span class="print-icon small align-middle" />
                                {{$translations.overview.table.actions.print}}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Desktop view -->
                <div v-else>
                    <button class="btn copy-icon px-4 me-3" :title="$translations.overview.table.actions.copy" @click="onOrderCopy(formData, true)"></button>
                    <button v-if="formData.Rectype !== 'W'" class="btn print-icon px-4 me-3" :title="$translations.overview.table.actions.print" @click="onOrderPrint(formData, true)"  :disabled="printOrderCondition(formData, true)"></button>
                </div>
            </template>

            <!-- Form view -->
            <template v-slot:form="{ formData }">

                <!-- PROPERTIES -->
                <div class="row justify-content-between">
                    <div class="col-auto p-3 mb-2 pt-4">
                        <label class="placeholder-text d-flex">{{$translations.overview.table.header.id}}</label>
                        <label class="w-100">{{formData.OrdNumb}}</label>
                    </div>
                    <div class="col-auto p-3 mb-2 pt-4">
                        <label class="placeholder-text d-flex">{{$translations.overview.table.header.type}}</label>
                        <label class="w-100">{{parseRectype(formData.RecType)}}</label>
                    </div>
                    <div class="col-auto p-3 mb-2 pt-4">
                        <label class="placeholder-text d-flex">{{$translations.overview.table.header.reference}}</label>
                        <label class="w-100">{{formData.Reference}}</label>
                    </div>
                    <div class="col-auto p-3 mb-2 pt-4">
                        <label class="placeholder-text d-flex">{{$translations.overview.table.header.enteredOn}}</label>
                        <label class="w-100">{{formData.EntryDate}} {{formData.EntryTime}}</label>
                    </div>
                    <div class="col-auto p-3 mb-2 pt-4">
                        <label class="placeholder-text d-flex">{{$translations.overview.table.header.statusManufacturer}}</label>
                        <label class="w-100">{{formData.Status}}</label>
                    </div>
                    <div class="col-auto p-3 mb-2 pt-4">
                        <label class="placeholder-text d-flex">{{$translations.overview.table.header.jobNo}}</label>
                        <label class="w-100">{{formData.OrdNumbH}}</label>
                    </div>
                </div>
                <div class="row justify-content-start">
                    <div class="col-auto p-3 mb-2 pt-4">
                        <label class="placeholder-text d-flex">{{$translations.overview.table.formView.remark}}</label>
                        <label v-if="parseRemarkField(formData.Remark).additionalRemarkDescr !== ''" class="w-100">{{parseRemarkField(formData.Remark).additionalRemarkDescr}}</label>
                        <label class="w-100">{{parseRemarkField(formData.Remark).remark}}</label>
                    </div>
                    <div class="col-auto p-3 mb-2 pt-4" v-if="!isBulkorder(formData)">
                        <label class="placeholder-text d-flex">{{$translations.overview.table.formView.frame}}</label>
                        <label class="w-100">{{formData.FReference}}</label>
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

                    <!-- SHAPE PREVIEW -->
                    <div v-if="formData.shapePreviewData" class="p-2 mb-4">
                        <div id="overview_drawing_container" class="w-100" style="height: 350px" />
                    </div>

                    <!-- DETAILS -->
                    <div class="row">
                        <!-- RIGHT LENS -->
                        <div class="col-md col-sm-12 mb-md-0 mb-5 border-sm-right">
                            <h4 class="fs-6 d-flex justify-content-center mb-3">{{$translations.common.rightLens}}</h4>

                            <div class="row justify-content-start ">
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.type}}</label>
                                    <label class="w-100">{{formData.LenstypeR}}</label>
                                </div>
                            </div>
                            <div class="row justify-content-start ">
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.sphere}}</label>
                                    <label class="w-100">{{formData.SphR}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.cylinder}}</label>
                                    <label class="w-100">{{formData.CylR}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.axis}}</label>
                                    <label class="w-100">{{formData.AxisR}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{(Number(formData.AddDegR) > 1) ? $translations.overview.table.formView.degression : $translations.overview.table.formView.addition}}</label>
                                    <label class="w-100">{{formData.AddR}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.prism1}}</label>
                                    <label class="w-100">{{formData.Prism1R}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.base1}}</label>
                                    <label class="w-100">{{formData.Base1R}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.prism2}}</label>
                                    <label class="w-100">{{formData.Prism2R}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.base2}}</label>
                                    <label class="w-100">{{formData.Base2R}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.pd}}</label>
                                    <label class="w-100">{{formData.PdR}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.height}}</label>
                                    <label class="w-100">{{formData.HeightR}}</label>
                                </div>
                            </div>
                            <div class="row justify-content-start ">
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.centTh}}</label>
                                    <label class="w-100">{{formData.CentR}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.edgeTh}}</label>
                                    <label class="w-100">{{formData.EdgeR}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.basec}}</label>
                                    <label class="w-100">{{formData.BasecR}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.drhThk}}</label>
                                    <label class="w-100">{{formData.DrhThkR}}</label>
                                </div>
                            </div>
                        </div>

                        <!-- LEFT LENS -->
                        <div class="col-md col-sm-12 mb-md-0 mb-5">
                            <h4 class="fs-6 d-flex justify-content-center mb-3">{{$translations.common.leftLens}}</h4>

                            <div class="row justify-content-start ">
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.type}}</label>
                                    <label class="w-100">{{formData.LenstypeL}}</label>
                                </div>
                            </div>
                            <div class="row justify-content-start ">
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.sphere}}</label>
                                    <label class="w-100">{{formData.SphL}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.cylinder}}</label>
                                    <label class="w-100">{{formData.CylL}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.axis}}</label>
                                    <label class="w-100">{{formData.AxisL}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{(formData.AddDegL > 1) ? $translations.overview.table.formView.degression : $translations.overview.table.formView.addition}}</label>
                                    <label class="w-100">{{formData.AddL}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.prism1}}</label>
                                    <label class="w-100">{{formData.Prism1L}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.base1}}</label>
                                    <label class="w-100">{{formData.Base1L}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.prism2}}</label>
                                    <label class="w-100">{{formData.Prism2L}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.base2}}</label>
                                    <label class="w-100">{{formData.Base2L}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.pd}}</label>
                                    <label class="w-100">{{formData.PdL}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.height}}</label>
                                    <label class="w-100">{{formData.HeightL}}</label>
                                </div>
                            </div>
                            <div class="row justify-content-start ">
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.centTh}}</label>
                                    <label class="w-100">{{formData.CentL}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.edgeTh}}</label>
                                    <label class="w-100">{{formData.EdgeL}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.basec}}</label>
                                    <label class="w-100">{{formData.BasecL}}</label>
                                </div>
                                <div class="col-auto p-3 mb-2 pt-4">
                                    <label class="placeholder-text d-flex">{{$translations.overview.table.formView.drhThk}}</label>
                                    <label class="w-100">{{formData.DrhThkL}}</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- INDIVIDAUL -->
                    <div v-if="hasIndividualValues">
                        <hr class="mb-3">

                        <h4 class="fs-6 d-flex justify-content-center mb-3">{{$translations.overview.table.formView.individual}}</h4>

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
import Helpers from '@/utils/helpers.js';
import OrderOverview from '@/utils/orderOverview.js'
import Tracer from '@/utils/tracer.js'
import Graphics from '@/utils/graphics.js'
import Individual from '@/utils/individual.js'
import Order from '@/utils/order.js'
import ShopConfiguration from '@/utils/shopConfiguration'
import store from "../../store";

export default {
    components: { PanelInput, PanelTable, PanelSelect },
    data() {
        return {
          showShopConfiguration: store.getters.getIniProperties['SETTINGS_SHOW_SHOP_CONFIG'],
          showStatusDate: store.getters.getIniProperties['SETTINGS_SHOW_OVERVIEW_STATUSDATE'],
          showIdInOverviewTable: this.$store.getters.getIniProperties['SETTINGS_SHOW_ID_IN_OVERVIEW_TABLE'],
            itemList: null,
            isLoading: false,
            clientWidth: 0,
            lastParams: {},
            getOrdersTimer: null,
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
            isArchived: false,
            reference: '',
            dateFrom: '',
            dateTo: '',
            shopSelection: [],
            selectedShop: null,
            perPageSelection: [1,2,3,5,10,25,50,75,100],
            daysBackSelection: [5,7,30,60,150,365,730],
            selectedPerPage: 10,
            selectedDaysBack: 30,
            totalRows: 0,
            totalPages: 1,
            currentPage: 1,
            graphicsApp: null,
        }
    },
    computed: {
        isManager () {return this.$store.getters.isManager},
        orders () {return this.$store.state.moduleOrders},
        overview () {return this.$store.state.moduleOrders.overview},
        isVerticalView() {return this.clientWidth < 500},
        hasIndividualValues () {
            if (Individual.doDefinedFieldsHaveValues(this.individualFields.definedFields)) return true
            if (Individual.doDefinedFieldsHaveValues(this.individualFields.left.definedFields)) return true
            if (Individual.doDefinedFieldsHaveValues(this.individualFields.right.definedFields)) return true
            return false
        },
      shopConfiguration() {return this.$store.state.moduleOrders.shopConfiguration},
    },
    mounted () {
        // Set dashboard title
        this.$store.commit('setDashboardTitle', {
            title: () => this.$translations.overview.title,
            subTitle: ''
        })

        this.onClear()

        this.onScreenResize()

        window.addEventListener('resize', this.onScreenResize)

        // Add shop info to header if manager
      if(this.showShopConfiguration === 'YES' && this.isManager &&
        this.overview.overviewTableData.headers.filter(e => e.label === this.$store.getters.translations.overview.table.header.shop).length === 0){
        this.getUsers()
        const t = this
        const headerValue = {
          get label() {return t.$store.getters.translations.overview.table.header.shop}
        }
        this.overview.overviewTableData.headers.splice(1, 0, headerValue)
      }
      // Show Status Date
      if(this.showStatusDate === 'YES') {
        const t = this
        const exists = this.overview.overviewTableData.headers.findIndex(x => x.label === t.$store.getters.translations.overview.table.header.statusDate)
        if(exists !== -1) return;
        const headerValue = {
          get label() {return t.$store.getters.translations.overview.table.header.statusDate}
        }
        const idx = this.overview.overviewTableData.headers.findIndex(x => x.sortParam === 'STATUS')
        this.overview.overviewTableData.headers.splice(idx+1, 0, headerValue)
      }
    },
    unmounted () {
        window.removeEventListener('resize', this.onScreenResize)
    },
    watch: {
        selectedPerPage() {
            this.getOrders(this.lastParams)
        },
        selectedDaysBack() {
            this.getOrders(this.lastParams)
        },
        currentPage() {
            this.getOrders(this.lastParams)
        }
    },
    methods: {
      async getUsers() {
        this.isLoading = true
        await ShopConfiguration.getUsers()
        this.isLoading = false
      },
        onScreenResize() {
            this.clientWidth = window.innerWidth
        },
        async onFormViewOpen(row) {
            if (this.isBulkorder(row)){
                // Get translated item list
                const itemList = Helpers.getObjProp(row, 'ItemListR', null)
                if (itemList === undefined || itemList === null || itemList === '') return

                const translatedItemList = await OrderOverview.getTranslatedItemList(itemList)
                if (translatedItemList === undefined || translatedItemList === null) return

                this.itemList = translatedItemList
                this.bulkorder.tableData = this.getBulkorderTableData(translatedItemList)
                return
            }

            this.createPreviewLens(row)
            this.createIndividualProperties(row)
        },
        async onOrderCopy(row, isFormData = false) {
            const response = await OrderOverview.copy(isFormData ? row : row.rawData)

            // Notify user if there was an error copying the order
            if (response.hasMessages()){
                this.$store.dispatch('notify', {
                    message: response.getMessage(),
                    type: "warning",
                })
            }

            // Go to home route
            this.$router.push('newOrder')
        },
        onHeaderClick(event) {
            this.getOrders({
                ...this.lastParams,
                sortalg: event.header.sortParam,
                sort: + !event.isSorting,
            })
        },
        onSearch() {
          let shop = 0;
          if(this.selectedShop !== null)
            shop = this.selectedShop.id
          this.getOrders({
            search: this.reference,
            date_from: Helpers.fromYMDtoDMY(this.dateFrom),
            date_to: Helpers.fromYMDtoDMY(this.dateTo),
            archive: + this.isArchived,
            filter_shop: shop
          })
        },
        onClear() {
            // Set variables to defaults and reload orders
            this.reference = ''
            this.dateFrom = ''
            this.dateTo = ''
            this.selectedShop = null
            this.isArchived = false
            this.currentPage = 1
            this.totalPages = 1

            this.getOrders()
        },
        onOrderPrint(row, isFormData = false) {
            let data = isFormData ? row : row.rawData
            OrderOverview.print(data)
        },
        onBulkorderTableHeaderClick(event) {
            const sortParam = event.header.sortParam
            const isAscending = event.isSorting

            this.bulkorder.tableData = this.getBulkorderTableData(this.itemList, sortParam, isAscending)
        },
        printOrderCondition(row, isFormData = false) {
            const order = isFormData ? row : row.rawData
            return
            // return !OrderOverview.isOrderBulkorder(order)
        },
        createPreviewLens(row) {
            // Take right lens by default
            let formData = Helpers.getObjProp(row, 'FormR', null)
            if (!formData || formData == '0') {

                // If there is no right lens, take left one
                formData = Helpers.getObjProp(row, 'FormL', null)
                if (!formData || formData == '0') {
                    row.shapePreviewData = null
                    return
                }
            }

            let fromBase64 = Helpers.base64ToString(formData)
            row.shapePreviewData = Tracer.decodeTracerFormData(fromBase64, false)
            this.$nextTick(() => {
                this.graphicsApp = Graphics.drawFramePreviewLens('overview_drawing_container', this.graphicsApp, row.shapePreviewData, this.previewShapeScale)
            })
        },
        getBulkorderTableData(itemList, sortBy = '', ascending = true) {
            const sortString = (a,b) => ascending ? a.toUpperCase() - b.toUpperCase() : b.toUpperCase() - a.toUpperCase()
            const sortNumber = (a,b) => ascending ? parseFloat(a) - parseFloat(b) : parseFloat(b) - parseFloat(a)
            const store = this.$store
            return {
                headers: [
                    {get label() {return store.getters.translations.overview.table.bulkorder.description}, sortParam: 'descr'},
                    {get label() {return store.getters.translations.overview.table.bulkorder.diameter}, sortParam: 'dia'},
                    {get label() {return store.getters.translations.overview.table.bulkorder.sphere}, sortParam: 'sph'},
                    {get label() {return store.getters.translations.overview.table.bulkorder.cylinder}, sortParam: 'cyl'},
                    {get label() {return store.getters.translations.overview.table.bulkorder.addition}, sortParam: 'add'},
                    {get label() {return store.getters.translations.overview.table.bulkorder.quantity}, sortParam: 'qty'},
                ],
                rows: itemList
                    .sort((a, b) => {
                        switch (sortBy) {
                            case 'descr': return sortString(a.description, b.description); break;
                            case 'dia': return sortNumber(a.diameter, b.diameter); break;
                            case 'sph': return sortNumber(a.sphere, b.sphere); break;
                            case 'cyl': return sortNumber(a.cylinder, b.cylinder); break;
                            case 'add': return sortNumber(a.addition, b.addition); break;
                            case 'qty': return sortNumber(a.quantity, b.quantity); break;
                        }
                        return 0
                    })
                    .map(row => {
                        return {
                            id: row.key,
                            rawData: row,
                            values: [
                                row.description,
                                row.diameter,
                                row.sphere,
                                row.cylinder,
                                row.addition,
                                row.quantity,
                            ],
                        }
                    })
            }
        },
        async createIndividualProperties(row){
            this.individualFields.definedFields = []
            this.individualFields.right.definedFields = []
            this.individualFields.left.definedFields = []

            let indTypeR = Helpers.getObjProp(row, 'IndTypeR')
            let indTypeL = Helpers.getObjProp(row, 'IndTypeL')
            let indR = Helpers.getObjProp(row, 'IndR')
            let indL = Helpers.getObjProp(row, 'IndL')

            let rightIndividualFields = await Individual.translateIndividualString('right', indTypeR, indR, row.LenscodeR, row.LenscodeL)
            let leftIndividualFields = await Individual.translateIndividualString('left', indTypeL, indL)
            this.individualFields.right.definedFields = rightIndividualFields.right.definedFields
            this.individualFields.left.definedFields = leftIndividualFields.left.definedFields
            this.individualFields.right.type = indTypeR
            this.individualFields.left.type = indTypeL

            // Individual fields for both left and right can be retrieved from either left or right string
            this.individualFields.definedFields = rightIndividualFields.definedFields
        },
        getOrders(params = {}) {
            // Combine dynamic varibles with static ones, that always need to be present
            this.lastParams = {
                ...params,
                count: this.selectedPerPage,
                days: this.selectedDaysBack,
                page: this.currentPage,
            }

            this.isLoading = true
            return this.getOrdersTimer = Helpers.throttleDelay(this.getOrdersTimer, () => {
                return this.$store.dispatch('getOrders', this.lastParams)
                .then((response) => {
                    this.isLoading = false

                    // Pagination
                    let orderCount = response.OrderCount == undefined ? 0 : response.OrderCount[0].Count
                    this.totalPages = orderCount
                    if (orderCount > 0)
                        this.totalPages = Math.ceil(orderCount / this.selectedPerPage)

                    if (orderCount <= 0 || this.totalPages <= 0) {
                        this.totalPages = 1
                        this.currentPage = 1
                    }

                    // Fill table data
                    let orderTracking = response.OrderTracking
                    if (orderTracking === undefined || Object.entries(orderTracking).length === 0)
                        orderTracking = []

                    this.overview.overviewTableData.rows = orderTracking.map(row => {
                        let statusType = this.parseRectype(row.RecType)

                        const newRow = {
                            id: row.OrdNumbH,
                            rawData: row,
                            values: [
                                row.OrdNumb,
                                statusType,
                                row.Reference,
                                `${row.LenstypeL} <br> ${row.LenstypeR}`,
                                row.EntryDate + " " + row.EntryTime,
                                row.Status,
                                row.OrdNumbH,

                            ],
                        }

                        // Add shop info to header if manager
                        if (this.isManager)
                            newRow.values.splice(1, 0, row.CustNumb)

                      if (this.showStatusDate === 'YES'){
                        const idx = this.overview.overviewTableData.headers.findIndex(x => x.sortParam === 'STATUS')
                        newRow.values.splice(idx+1, 0, row.StatDate)
                      }

                        return newRow
                    })
                }, e => {
                    this.isLoading = false
                    this.overview.overviewTableData = []

                    this.$store.dispatch('notify', {
                        message: e.data.message,
                    })
                    console.error(e)
                });
            }, 400)
        },
        parseRemarkField(remarkString) {
            const remarkData = Order.getRemarkData(remarkString)
            return remarkData
        },
        parseRectype(rectype) {
            let statusType = ''
            switch (rectype) {
                case 'F': statusType = this.$translations.overview.table.statusTypes.f
                break;
                case 'N': statusType = this.$translations.overview.table.statusTypes.n
                break;
                case 'E': statusType = this.$translations.overview.table.statusTypes.e
                break;
                case 'G': statusType = this.$translations.overview.table.statusTypes.g
                break;
                case 'S': statusType = this.$translations.overview.table.statusTypes.s
                break;
                case 'W': statusType = this.$translations.overview.table.statusTypes.bulkorder
                break;
            }
            return statusType
        },
        isBulkorder(order) {
            return OrderOverview.isOrderBulkorder(order)
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
