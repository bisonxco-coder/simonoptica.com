<template>
  <div :class="'card pt-3 pb-4 px-5 mb-3 ' + (isComponentEnabled ? '' : 'component-disabled')" id="product-category">
    <h4 class="panel-title pt-4 pb-4">{{$translations.newOrder.details.productCategory.title}}</h4>
    <div class="row gx-4">

      <!-- Material -->
      <div class="col-md-6 col-12">
        <panel-select
          :placeholder="$translations.newOrder.details.productCategory.placeholders.material"
          v-model="productCategory.selectedMaterial"
          :selection="productCategory.materialSelection"
          titleParameter="Text"
          idParameter="Key"
          :notNull="true"
          :firstValue="!isMaterialEnabled"
          :disabled="!isMaterialEnabled"
        />
      </div>

      <!-- Special -->
      <div class="col-md-6 col-12">
        <panel-select
          :placeholder="$translations.newOrder.details.productCategory.placeholders.special"
          :selection="productCategory.specialSelection"
          v-model="productCategory.selectedSpecial"
          titleParameter="Text"
          idParameter="Key"
          :notNull="true"
          :disabled="!isComponentEnabled"
        />
      </div>

      <!-- Group -->
      <div class="col-md-6 col-12">
        <panel-select
          :placeholder="$translations.newOrder.details.productCategory.placeholders.group"
          :selection="productCategory.groupSelection"
          v-model="productCategory.selectedGroup"
          titleParameter="Text"
          idParameter="Key"
          :notNull="true"
          :disabled="!isComponentEnabled"
        />
      </div>

      <!-- LensFilter -->
      <div v-if="showLensFilter === 'YES'" class="col-md-6 col-12">
        <panel-select
          :placeholder="$translations.newOrder.details.productCategory.placeholders.lensFilter"
          :selection="productCategory.lensFilterSelection"
          v-model="productCategory.selectedLensFilter"
          titleParameter="Text"
          :disabled="!isComponentEnabled"
        />
      </div>

      <!-- Index -->
      <div class="col-md-3 col-sm-6 col-12">
        <panel-select
          :placeholder="$translations.newOrder.details.productCategory.placeholders.index"
          :selection="productCategory.indexSelection"
          v-model="productCategory.selectedIndex"
          titleParameter="Text"
          :notNull="productCategory.sameIndexBothSides === 'YES'"
          :disabled="!isComponentEnabled"
        />
      </div>

      <!-- Corridor -->
      <div v-if="showShopCorridor === 'YES'" class="col-md-3 col-sm-6 col-12">
        <panel-select
          :placeholder="$translations.newOrder.details.productCategory.placeholders.corridor"
          :selection="productCategory.corridorSelection"
          v-model="productCategory.selectedCorridor"
          :disabled="productCategory.isCorridorDisabled || !isComponentEnabled"
          titleParameter="Text"
        />
      </div>
    </div>

    <!-- Search -->
    <div class="me-5 d-flex mt-sm-0 mt-4">
      <button class="d-flex expand-button border-0" @click="onFullTextSearchToggle" :disabled="!isComponentEnabled">
        <span :class="'no-hover px-3 ' + toggleIconStyle"></span>
        {{$translations.newOrder.details.productCategory.placeholders.fullTextSearch}}
      </button>
    </div>
    <div v-if="fulltextSearch">
      <!-- Search products -->
      <panel-input
        class="mb-3"
        :placeholder="$translations.newOrder.details.product.placeholders.searchProducts"
        v-model="product.searchInput"
        v-on:keyup.enter="onProductSearch"
        icon="search-icon"
        :disabled="!isComponentEnabled"
      />
      <div class="d-flex mb-5 justify-content-between flex-wrap">
        <div class="d-flex align-items-center" v-if="searchWithFilter ==='YES'">
          <input
            id="product_searchWithFilter"
            v-model="product.searchWithFilter"
            class="form-check-input panel-checkbox"
            type="checkbox"
            :disabled="!isComponentEnabled"
          />
          <label class="form-check-label ms-4 px-3 position-absolute" for="product_searchWithFilter">
            {{$translations.newOrder.details.product.inSelection}}
          </label>
        </div>
        <div class="mx-5 my-4"/>
        <div class="d-flex align-items-center">
          <button
            class="btn btn-secondary panel-button border-0 px-5"
            @click="onProductSearch"
            :disabled="!isComponentEnabled"
          >
            {{$translations.common.search}}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import PanelSelect from '@/components/PanelSelect.vue'
import PanelInput from '@/components/PanelInput.vue'

export default {
  components: {
    PanelSelect, PanelInput
  },
  data(){
    return{
      showLensFilter: this.$store.getters.getIniProperties['SETTINGS_SHOW_LENS_FILTER'],
      showShopCorridor: this.$store.getters.getIniProperties['SETTINGS_SHOW_CORRIDOR'],
      materialEnabled: this.$store.getters.getIniProperties['SETTINGS_MATERIAL_ENABLED'],
      fulltextSearch: false,
      searchWithFilter: this.$store.getters.getIniProperties['SETTINGS_SEARCH_WITH_FILTER']
    }
  },
  computed: {
    product() {return this.$store.state.moduleOrders.details.product},
    isMaterialEnabled() {
      if(this.materialEnabled === 'NO'){
        this.$store.state.moduleOrders.details.productCategory.selectedMaterial = this.$store.state.moduleOrders.details.productCategory.materialSelection[0]
        return false;
      }
      return this.$store.state.moduleOrders.details.isComponentEnabled('product-category')
    },
    isComponentEnabled() {
      return this.$store.state.moduleOrders.details.isComponentEnabled('product-category')},
    productCategory() {return this.$store.state.moduleOrders.details.productCategory},
    toggleIconStyle () {
      if (this.fulltextSearch) return 'minimize-icon'
      return 'add-icon'
    }
  },
  methods:{
    onProductSearch() {
      document.dispatchEvent(new Event('getLensTypesOnly'))
    },
    onFullTextSearchToggle(){
      this.fulltextSearch = !this.fulltextSearch
    }
  }
}
</script>
