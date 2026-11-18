<template>
    <div id="bulkorder">

        <!-- PRODUCT -->
        <div :class="'card pt-3 pb-4 px-5 mb-3 ' + (isComponentEnabled ? '' : 'component-disabled')">
            <h4 class="panel-title pt-4 pb-3">{{$translations.newOrder.details.product.title}}</h4>
            <div class="row">
                <div class="col-md-6 col-12">
                    <panel-select 
                        :placeholder="$translations.newOrder.details.bulkorder.placeholders.group"
                        :selection="groupSelection" 
                        v-model="bulkorder.product.selectedGroup"
                        titleParameter="Text" 
                        idParameter="Key"
                        :notNull="true"
                        :disabled="!isComponentEnabled"
                    />
                    <panel-select 
                        :placeholder="$translations.newOrder.details.bulkorder.placeholders.lensType"
                        :selection="bulkorder.product.lensTypeSelection" 
                        v-model="bulkorder.product.selectedLensType"
                        :disabled="!isComponentEnabled"
                        :titleParameter="lensDescriptionKey"
                    />
                </div>
                <div class="col-md-6 col-12">
                    <panel-select 
                        :placeholder="$translations.newOrder.details.bulkorder.placeholders.lensDiameter"
                        :selection="bulkorder.product.lensDiameterSelection" 
                        v-model="bulkorder.product.selectedLensDiameter"
                        :disabled="!isComponentEnabled"
                        :titleParameter="lensDiameterKey"
                    />
                </div>
            </div>
        </div>

        <!-- STOCK LENS TABLE -->
        <div :class="'card pt-3 pb-4 px-5 mb-3 ' + (isComponentEnabled ? '' : 'component-disabled')">

            <!-- HEADER -->
            <div class="d-flex justify-content-md-between justify-content-center flex-md-nowrap flex-wrap mt-0 pt-4 pb-3">
                <div class="d-flex">
                    <!-- Selected lens label-->
                    <h4 class="panel-title me-3">{{lensType}}</h4>

                    <!-- Loading indicator -->
                    <span v-if="bulkorder.grid.isLoading" class="spinner-icon large opaque"/>
                </div>
                
                <!-- Clear grid button -->
                <button 
                    v-if="doesGridHaveData"
                    class="d-flex expand-button dark border-0" 
                    @click="onClearGrid" 
                    :disabled="!isComponentEnabled"
                >
                    {{$translations.newOrder.details.bulkorder.clearGrid}}
                </button>
            </div>

            <!-- GRID (X = cylinder, Y = sphere) -->
            <div class="table-responsive" v-if="doesGridHaveData" style="height: calc(100vh - 290px); min-height: 370px">
                <table class="table table-borderless table-sm" style="display: table">
                    <thead style="display: table-header-group">
                        <tr>
                            <!-- Corner label -->
                            <th scope="col" class="placeholder-text align-middle">
                                {{$translations.newOrder.details.bulkorder.sph}}<br> {{$translations.newOrder.details.bulkorder.cyl}}
                            </th>

                            <!-- X (cylinder) info fields -->
                            <th scope="col" class="placeholder-text-grid align-middle" v-for="x in xValues" :key="x">
                                {{parseGridInfoValue(x)}}
                            </th>
                        </tr>
                    </thead>
                    <tbody style="display: table-row-group">
                        <tr v-for="(row, rowIndex) in rows" :key="row"> 
                            <!-- Y (sphere) info fields -->
                            <th scope="row" class="placeholder-text-grid align-middle">
                                {{parseGridInfoValue(yValues[rowIndex])}}
                            </th>

                            <!-- Input fields -->
                            <td v-for="cell in row" :key="cell">
                                <panel-input
                                    v-if="cell"
                                    dense 
                                    placeholder="0" 
                                    v-model="cell.value" 
                                    type="number" 
                                    :step="1" 
                                    @input="onCellInput(cell)"
                                    :disabled="!isComponentEnabled"
                                    :errorMessage="cell.errorMessage"
                                />
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
import PanelInput from '@/components/PanelInput.vue'
import PanelSelect from '@/components/PanelSelect.vue'
import Bulkorder from '@/utils/bulkorder.js'

export default {
    components: { PanelInput, PanelSelect },
    computed: {
        isComponentEnabled() { return this.$store.state.moduleOrders.details.isComponentEnabled('bulkorder') },
        bulkorder() { return this.$store.state.moduleOrders.details.bulkorder },

        // Product
        lensDescriptionKey() { return Bulkorder.lensDescriptionKey },
        lensDiameterKey() { return Bulkorder.lensDiameterKey },
        groupSelection() { return Bulkorder.getGroupSelection() },
        lensType() { return Bulkorder.getLensType() },

        // Grid
        rows() { return this.bulkorder.grid.rows },
        xValues() { return this.bulkorder.grid.xValues },
        yValues() { return this.bulkorder.grid.yValues },
        doesGridHaveData() { return Bulkorder.doesGridHaveData() },
    },
    watch: {
        "bulkorder.product.selectedLensType": function() {
            Bulkorder.resetGrid()
            Bulkorder.setLensDiameterValues()
        },
        "bulkorder.product.selectedLensDiameter": function(val) {
            if (val === undefined || val === null) Bulkorder.resetGrid()
            Bulkorder.getGrid()
        }
    },
    mounted() {
        Bulkorder.getLenses()
        Bulkorder.setLensDiameterValues()
    },
    methods: {
        onCellInput(cell) {
            Bulkorder.evaluateCell(cell)
        },
        onClearGrid() {
            // If the user has entered no values into the grid, return
            if (!Bulkorder.doesGridHaveValues())
                return

            // Create a confirmation dialog window when the user wants to clear the grid, but there are values present
            let dialogParams = {
                title: this.$translations.dialog.warning,
                description: this.$translations.dialog.messages.resetAllFileds,
                falseButtonText: this.$translations.dialog.cancel,
                trueButtonText: this.$translations.dialog.continue,
                type: 'warning',
                allowClosing: true,
            }
            this.$store.dispatch('dialog', dialogParams).then(hasConfirmed => {
                if (hasConfirmed) Bulkorder.clearGridValues()
            })
        },
        parseGridInfoValue(val) {
            // Keep trailing zeroes (2 -> 2.00)
            return parseFloat(val).toFixed(2)
        },
    }
}
</script>
<style lang="scss" scoped>
    th{
        min-width: 3.5rem;
    }
    table {
      display: inline-block;
      thead{
        th {
          position: sticky;
          z-index: 1;
          top: 0;
          opacity: 1;
          background: white;
        }
        th:first-child{
          z-index: 2;
        }
      }
    }

    th:first-child, td:first-child
    {
      position: sticky;
      z-index: 1;
      left: 0;
      opacity: 1;
      background: white;
    }

</style>