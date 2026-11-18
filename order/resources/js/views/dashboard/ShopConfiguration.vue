<template>
    <!-- Action bar -->
    <div class="card p-4 mb-3">
        <div class="d-flex justify-content-sm-start justify-content-center flex-md-nowrap flex-wrap">
            <button class="btn btn-secondary panel-button border-0 px-5 my-1 me-2" @click="onNewClick">{{$translations.shopConfiguration.actionBar.new}}</button>
            <button class="btn btn-secondary panel-button border-0 px-5 my-1 me-2" @click="onRefreshClick">{{$translations.shopConfiguration.actionBar.refresh}}</button>
        </div>
    </div>

    <!-- User List -->
    <div class="card p-4 mb-5">
        <panel-table 
            ref="userTableRef"
            :tableData="shopConfiguration.userTableData" 
            :isLoading="isLoading"
            :allowClosing="canClose"
            v-model:currentPage="currentPage"
            v-model:totalPages="totalPages"
            @onFormViewOpen="onFormViewOpen"
            @onFormViewClose="onFormViewClose"
        >

            <!-- Form view Toolbar -->
            <template v-slot:formToolbar="{ formData }">

                <!-- Mobile view -->
                <div v-if="isVerticalView" class="btn-group dropup">
                    <div data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="more-icon small pointer px-4 me-3"/>
                    </div>
                    <ul class="dropdown-menu dropdown-menu-lg-end shadow border-1 p-3 my-1 me-2">
                        <li v-if="canEdit">
                            <a class="dropdown-item drop pointer py-2 px-3" @click="onUserEdit(formData, true)">
                                <span class="edit-icon small align-middle" />
                                {{$translations.shopConfiguration.table.actions.edit}}
                            </a>
                        </li>
                        <li v-if="canDelete">
                            <a class="dropdown-item drop pointer py-2 px-3" @click="onUserDelete(formData, true)">
                                <span class="delete-icon small align-middle" />
                                {{$translations.shopConfiguration.table.actions.delete}}
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Desktop view -->
                <div v-else>
                    <button v-if="canEdit" class="btn edit-icon px-4 me-3" :title="$translations.shopConfiguration.table.actions.edit" @click="onUserEdit(formData, true)"></button>
                    <button v-if="canDelete" class="btn delete-icon px-4 me-3" :title="$translations.shopConfiguration.table.actions.delete" @click="onUserDelete(formData, true)"></button>
                </div>
            </template>

            <!-- Action bar -->
            <template v-slot:actions="{ isSelected, row }">
                <div class="btn-group dropup">
                    <div data-bs-toggle="dropdown" aria-expanded="false">
                        <span :class="'more-icon small pointer ' + (isSelected ? ' inverted-color' : '' )" />
                    </div>
                    <ul class="dropdown-menu dropdown-menu-lg-end shadow border-1 p-3 my-1 me-2">
                        <li>
                            <a class="dropdown-item pointer drop py-2 px-3" @click="onUserEdit(row)">
                                <span class="edit-icon small align-middle" />
                                {{$translations.shopConfiguration.table.actions.edit}}
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item pointer drop py-2 px-3" @click="onUserDelete(row)">
                                <span class="delete-icon small align-middle" />
                                {{$translations.shopConfiguration.table.actions.delete}}
                            </a>
                        </li>
                    </ul>
                </div>
            </template>

            <!-- Form view footer buttons -->
            <template v-slot:formFooter="{ formData }">
                <button v-if="canSave" class="expand-button dark border-0 mx-3" @click="onUserCancel(formData)">{{$translations.shopConfiguration.table.actions.cancel}}</button>
                <button v-if="canSave" class="expand-button dark border-0" @click="onUserSave(formData)">{{$translations.shopConfiguration.table.actions.save}}</button>
            </template>

            <!-- Form view -->
            <template v-slot:form="{  }">
                <div class="row" v-if="hasFormViewUser">
                    <div class="d-flex">
                        <div class="col-auto py-3 me-3">
                            <span>{{userUID}}</span>
                            <span class="ms-3">-</span>
                        </div>
                        <div class="col">
                            <panel-input 
                                v-model="shopConfiguration.formViewUser.shopId.value" 
                                :placeholder="$translations.shopConfiguration.table.formView.shopNumber"
                                preserveOnDisable
                                :disabled="!isManipulatingForm || isEditing"
                                v-model:errorMessage="shopConfiguration.formViewUser.shopId.errorMessage"
                                :maxlength="3"
                            />
                        </div>
                    </div>
                    <panel-input 
                        v-model="shopConfiguration.formViewUser.shopPw.value" 
                        :placeholder="$translations.shopConfiguration.table.formView.shopPassword"
                        type="password"
                        preserveOnDisable
                        :disabled="!isManipulatingForm"
                        v-model:errorMessage="shopConfiguration.formViewUser.shopPw.errorMessage"
                    />
                    <panel-input 
                        v-model="shopConfiguration.formViewUser.shopName.value" 
                        :placeholder="$translations.shopConfiguration.table.formView.shopName"
                        preserveOnDisable
                        :disabled="!isManipulatingForm"
                        v-model:errorMessage="shopConfiguration.formViewUser.shopName.errorMessage"
                    />
                    <panel-input 
                        v-model="shopConfiguration.formViewUser.shopStreet.value" 
                        :placeholder="$translations.shopConfiguration.table.formView.street"
                        preserveOnDisable
                        :disabled="!isManipulatingForm"
                    />
                    <panel-input 
                        v-model="shopConfiguration.formViewUser.shopCity.value" 
                        :placeholder="$translations.shopConfiguration.table.formView.city"
                        preserveOnDisable
                        :disabled="!isManipulatingForm"
                    />
                    <panel-input 
                        v-model="shopConfiguration.formViewUser.shopZip.value" 
                        :placeholder="$translations.shopConfiguration.table.formView.zipCode"
                        preserveOnDisable
                        :disabled="!isManipulatingForm"
                    />
                    <panel-input 
                        v-model="shopConfiguration.formViewUser.shopCountry.value" 
                        :placeholder="$translations.shopConfiguration.table.formView.country"
                        preserveOnDisable
                        :disabled="!isManipulatingForm"
                    />
                </div>
            </template>
        </panel-table>
    </div>
</template>

<script>
import PanelInput from '@/components/PanelInput.vue'
import PanelSelect from '@/components/PanelSelect.vue'
import PanelTable from '@/components/PanelTable.vue'
import ShopConfiguration from '@/utils/shopConfiguration'

export default {
    components: { PanelTable, PanelInput, PanelSelect },
    data() {
        return {
            isLoading: false,
            clientWidth: 0,
            currentPage: 0,
            totalPages: 0,
        }
    },
    computed: {
        shopConfiguration() {return this.$store.state.moduleOrders.shopConfiguration},
        isVerticalView() {return this.clientWidth < 500},
        userUID() {return this.$store.getters.userUID},
        isEditing() {return ShopConfiguration.isEditing()},
        isCreating() {return ShopConfiguration.isCreating()},
        isManipulatingForm() {return ShopConfiguration.isManipulatingForm()},
        hasFormViewUser() {return ShopConfiguration.hasFormViewUser()},
        canEdit() {return ShopConfiguration.canEdit()},
        canDelete() {return ShopConfiguration.canDelete()},
        canSave() {return ShopConfiguration.canSave()},
        canClose() {return ShopConfiguration.canClose()}
    },
    mounted() {
        this.$store.commit('setDashboardTitle', {
            title: () => this.$translations.shopConfiguration.title,
            subTitle: () => ""
        })

        this.getUsers()
        this.onScreenResize()
        window.addEventListener('resize', this.onScreenResize)
    },
    unmounted () {
        window.removeEventListener('resize', this.onScreenResize)
    },
    watch: {
        "$route": () => {
            ShopConfiguration.onFormViewClose()
        }
    },
    methods: {
        onScreenResize() {
            this.clientWidth = window.innerWidth
        },
        async onFormViewOpen(row) {
            this.isLoading = true
            await ShopConfiguration.onFormViewOpen(row)
            this.isLoading = false
        },
        onFormViewClose(row) {
            ShopConfiguration.onFormViewClose(row)
        },
        async onNewClick() {
            await ShopConfiguration.onNewClick()
            this.openFormView(this.shopConfiguration.formViewUser)
            this.shopConfiguration.formViewUser = this.shopConfiguration.formViewUser.rawData
        },
        onRefreshClick() {
            if (ShopConfiguration.isManipulatingForm())
                ShopConfiguration.showLoseDataWarningDialog(() => this.getUsers())
            else this.getUsers()
        },
        onUserCancel(row) {
            ShopConfiguration.onUserCancel(row, () => this.closeFormView(row))
        },
        async onUserEdit(row, isFormData = false) {
            this.isLoading = true
            await ShopConfiguration.onUserEdit(row, isFormData)
            this.isLoading = false

            // Prevent closing the formview if the edit button was clicked from inside the formview
            if (!isFormData) this.openFormView(row)
        },
        async onUserDelete(row, isFormData = false) {
            this.isLoading = true
            const user = await ShopConfiguration.onUserDelete(row, isFormData, async () => {

                this.isLoading = true
                await ShopConfiguration.getUsers()
                this.isLoading = false
    
                this.closeFormView(user)
            })
            this.isLoading = false
        },
        async onUserSave(row) {
            this.isLoading = true
            const validationResult = ShopConfiguration.validateFormUser(this.shopConfiguration.formViewUser)
            if (!validationResult.isValid) {
                // Notify on error 
                this.$store.dispatch('notify', {
                    message: validationResult.message,
                    duration: 5
                })
                this.isLoading = false
                return
            }

            await ShopConfiguration.onUserSave(row)
            this.closeFormView()
            await ShopConfiguration.getUsers()
            this.isLoading = false
        },
        async getUsers() {
            this.isLoading = true
            await ShopConfiguration.getUsers()
            this.isLoading = false
        },
        openFormView(row) {
            this.$refs.userTableRef.onFormShow(row, false)
        },
        closeFormView(row = null) {
            this.$refs.userTableRef.onFormClose(row)
        },
    }
}
</script>
<style lang="scss" scoped>
.btn-group{
    position: absolute;
    display: contents;
}
</style>