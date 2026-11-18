import store from '@/store'
import Helpers from '@/utils/helpers.js'

const self = {
    get shopConfiguration() {return store.state.moduleOrders.shopConfiguration},
    get translations() {return store.getters.translations},
}
export default {
    isEditing() { return self.shopConfiguration.currentUserState === self.shopConfiguration.userStates.editing },
    isCreating() { return self.shopConfiguration.currentUserState === self.shopConfiguration.userStates.new },
    isManipulatingForm() { return this.isEditing() || this.isCreating() },
    hasFormViewUser() { return self.shopConfiguration.formViewUser !== undefined && self.shopConfiguration.formViewUser !== null},

    /**
     * Computed user states:
     * ---------------------------
     * STATE    | Can edit | Can delete | Can save | Can close
     * New      |   false  |   false    |   true   |   false
     * Editing  |   false  |    true    |   true   |   false
     * Readonly |   true   |    true    |   false  |   true
     * 
     * 
     */
    canEdit() {
        switch (self.shopConfiguration.currentUserState) {
            case self.shopConfiguration.userStates.new: return false;
            case self.shopConfiguration.userStates.editing: return false;
            case self.shopConfiguration.userStates.readonly: return true;
        }
        return true
    },
    canDelete() {
        switch (self.shopConfiguration.currentUserState) {
            case self.shopConfiguration.userStates.new: return false;
            case self.shopConfiguration.userStates.editing: return true;
            case self.shopConfiguration.userStates.readonly: return true;
        }
        return true
    },
    canSave() {
        switch (self.shopConfiguration.currentUserState) {
            case self.shopConfiguration.userStates.new: return true;
            case self.shopConfiguration.userStates.editing: return true;
            case self.shopConfiguration.userStates.readonly: return false;
        }
        return false
    },
    canClose() {
        switch (self.shopConfiguration.currentUserState) {
            case self.shopConfiguration.userStates.new: return false;
            case self.shopConfiguration.userStates.editing: return false;
            case self.shopConfiguration.userStates.readonly: return true;
        }
        return true
    },
    // ----------------------------------------------
    
    async onFormViewOpen(row, resetFormViewUser = true) {
        self.shopConfiguration.currentUserState = self.shopConfiguration.userStates.readonly
        self.shopConfiguration.selectedListRow = row
        await this.showUser(row, resetFormViewUser)
    },
    onFormViewClose() {
        self.shopConfiguration.currentUserState = null
        self.shopConfiguration.selectedListRow = null
        self.shopConfiguration.formViewUser = null
    },
    async onNewClick() {
        // If already creating a new user or editing an existing one, warn the user about losing changes
        if (this.isManipulatingForm()) {
            await this.showLoseDataWarningDialog(() => this.newUserAction())
        } else {
            this.newUserAction()
        }
    },
    async onUserEdit(row, isFormData = false) {
        const data = isFormData ? row : row.rawData

        self.shopConfiguration.currentUserState = self.shopConfiguration.userStates.editing
        self.shopConfiguration.selectedListRow = data

        // If already in formview, the user details have already been loaded
        if (!isFormData) {
            await this.showUser(data)
        }
    },
    onUserDelete(row, isFormData = false, callback = () => {}) {
        const data = isFormData ? row : row.rawData

        return store.dispatch('dialog', {
            title: self.translations.dialog.warning,
            description: self.translations.dialog.messages.deleteUserConfirmation,
            falseButtonText: self.translations.dialog.cancel,
            trueButtonText: self.translations.dialog.delete,
            type: 'warning',
            allowClosing: true
        }).then(async value => {
            if (!value) return

            const userId = data.shopNumber
            if (userId === undefined) return 
            if (userId === '' || userId === null) return

            await this.deleteUser(userId)
            callback()
            
            return 
        })
    },
    onUserCancel(row, callback = () => {}) {
        return this.showLoseDataWarningDialog(() => {
            self.shopConfiguration.selectedListRow = row
            callback()
        })
    },
    async onUserSave(row) {
        await this.saveUser()
    },
    saveUser() {
        if (this.isCreating()){
            return this.createUser(self.shopConfiguration.formViewUser)
        }
        if (this.isEditing()){
            return this.editUser(self.shopConfiguration.formViewUser)
        }
    },
    showLoseDataWarningDialog(callback = () => {}) {
        return store.dispatch('dialog', {
            title: self.translations.dialog.warning,
            description: self.translations.dialog.messages.loseData,
            falseButtonText: self.translations.dialog.no,
            trueButtonText: self.translations.dialog.yes,
            type: 'warning',
            allowClosing: true
        }).then(value => {
            if (!value) return
            callback()
        })
    },
    newUserAction() {
        self.shopConfiguration.currentUserState = self.shopConfiguration.userStates.new
        const emptyUser = {
            rawData: this.transformUserDetails(),
        }
        self.shopConfiguration.formViewUser = emptyUser
        self.shopConfiguration.selectedListRow = null
    },
    async showUser(row, resetFormViewUser = true) {
        if (resetFormViewUser)
            self.shopConfiguration.formViewUser = this.transformUserDetails()

        const userId = row.shopNumber
        if (userId === '' || userId === null) return

        const userDetails = await this.getUserDetails(userId)
        if (userDetails !== undefined && userDetails !== null && Object.entries(userDetails).length > 0)
            self.shopConfiguration.formViewUser = userDetails
    },
    validateFormUser(user){
        const isEmpty = (val) => {
            if (val === undefined) return true
            if (val === null || val === "") return true
            return false
        }

        const response = {
            isValid: true,
            message: ""
        }

        if (user === undefined || user === '' || user === null || Object.entries(user).length === 0) {
            response.message = self.translations.notifications.missingUser
            response.isValid = false
            return response
        }

        if (isEmpty(user.shopId.value)) {
            user.shopId.errorMessage = self.translations.notifications.newValue
            response.message = self.translations.notifications.missingShopId
            response.isValid = false
            return response
        }

        if (isEmpty(user.shopPw.value)) {
            user.shopPw.errorMessage = self.translations.notifications.newValue
            response.message = self.translations.notifications.missingShopPassword
            response.isValid = false
            return response
        }

        if (isEmpty(user.shopName.value)) {
            user.shopName.errorMessage = self.translations.notifications.newValue
            response.message = self.translations.notifications.missingShopName
            response.isValid = false
            return response
        }

        return response
    },
    getUsers() {
        return store.dispatch('getShopList', {}).then(response => {
            const shopList = Helpers.getObjProp(response, 'ShopList', null)
            if (shopList === undefined || shopList === '' || Object.entries(response.ShopList).length === 0) 
                return null 

            self.shopConfiguration.userTableData.rows = shopList.map(_row => {
                const row = this.transformListRow(_row)
                return {
                    id: row.shopNumber,
                    text: row.shopNumber + ' - ' + row.shopName,
                    rawData: row,
                    values: [
                        row.shopNumber,
                        row.shopName,
                        row.shopCity
                    ]
                }
            })
            
            return shopList
        },e => {
            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
            return e
        })
    },
    getUserDetails(userId) {
        if (userId === undefined || userId === '' || userId === null) 
            return

        const params = {
            'custNumb': userId
        }
        return store.dispatch('getCustomerDetails', params).then(response => {
            const shopDetail = Helpers.getObjProp(response, 'ShopDetail', null)
            if (shopDetail === undefined || shopDetail === '' || Object.entries(shopDetail).length === 0) 
                return null 
            
            return this.transformUserDetails(shopDetail[0], userId)
        },e => {
            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
            return e
        })
    },
    createUser(user) {
        if (user === undefined || user === '' || user === null || Object.entries(user).length === 0) 
            return null

        const validationResult = this.validateFormUser(user)
        if (!validationResult.isValid) return null

        const custNumb = store.getters.userUID + "-" + user.shopId.value

        const params = {
            'custNumb': custNumb,
            'password': user.shopPw.value,
            'name': user.shopName.value,
            'street': user.shopStreet.value,
            'city': user.shopCity.value,
            'zipCode': user.shopZip.value,
            'country': user.shopCountry.value,
        }
        return store.dispatch('createCustomer', params).then(response => {
            return response
        },e => {
            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
            return e
        })
    },
    editUser(user) {
        if (user === undefined || user === '' || user === null || Object.entries(user).length === 0) 
            return null

        const validationResult = this.validateFormUser(user)
        if (!validationResult.isValid) return null

        const custNumb = store.getters.userUID + "-" + user.shopId.value

        const params = {
            'custNumb': custNumb,
            'password': user.shopPw.value,
            'name': user.shopName.value,
            'street': user.shopStreet.value,
            'city': user.shopCity.value,
            'zipCode': user.shopZip.value,
            'country': user.shopCountry.value,
        }
        return store.dispatch('changeCustomerDetails', params).then(response => {
            return response
        },e => {
            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
            return e
        })
    },
    deleteUser(userId) {
        if (userId === undefined || userId === '' || userId === null) 
            return

        const params = {
            'custNumb': userId
        }
        return store.dispatch('deleteCustomer', params).then(response => {
            return response
        },e => {
            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
            return e
        })
    },
    parseUserId(userId) {
        // Returns ending user id number, for example userId = "999999-991" will return "991"
        try {
            const value = userId.split("-")[1]
            if (value === undefined) return ""
            return value
        } catch (error) {
            console.error(error)
            return userId
        }
    },
    transformUserDetails(user = null, userId = "") {
        return {
            shopIdComplete: {
                value: userId,
                errorMessage: "",
            },
            shopId: {
                value: this.parseUserId(userId),
                errorMessage: "",
            },
            shopPw: {
                value: Helpers.getObjProp(user, 'Password', ""),
                errorMessage: "",
            },
            shopName: {
                value: Helpers.getObjProp(user, 'Name', ""),
                errorMessage: "",
            },
            shopStreet: {
                value: Helpers.getObjProp(user, 'Street', ""),
                errorMessage: "",
            },
            shopCity: {
                value: Helpers.getObjProp(user, 'City', ""),
                errorMessage: "",
            },
            shopZip: {
                value: Helpers.getObjProp(user, 'ZipCode', ""),
                errorMessage: "",
            },
            shopCountry: {
                value: Helpers.getObjProp(user, 'Country', ""),
                errorMessage: "",
            },
            original: user,
        }
    },
    transformListRow(row) {
        return {
            shopCity: Helpers.getObjProp(row, 'ShopCity', null),
            shopName: Helpers.getObjProp(row, 'ShopName', null),
            shopNumber: Helpers.getObjProp(row, 'ShopNumb', null),
            original: row,
        }
    },
}