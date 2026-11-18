import store from '@/store'
import Helpers from '@/utils/helpers.js'

const self = {
    get orders() {return store.state.moduleOrders},
}
export default { 
    loadTranslations(_langCode = null){
        return this.getTranslations(_langCode, 'getTranslations')
    },
    loadLandingTranslations(_langCode = null){
        return this.getTranslations(_langCode, 'getLandingTranslations')
    },
    getTranslations(_langCode = null, dispatchAction) {
        let params = {}
        if (_langCode && _langCode !== undefined && _langCode != ''){
            store.commit('setLanguageCode', {languageCode: _langCode})
            params.langCode = _langCode
        }

        return store.dispatch(dispatchAction, params).then(response => {
            return response
        },e => {
            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
            return e
        })
    },
    getDefaultLanguageCode() {
        return store.dispatch('getDefaultLanguageCode').then(response => {
            return response
        },e => {
            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
            return e
        })
    },
    getLanguageCodes() {
        return store.dispatch('getLanguageCodes').then(response => {
            return response
        },e => {
            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
            return e
        })
    },
    setDefaultLanguageCode() {
        return store.dispatch('getDefaultLanguageCode').then(response => {
            store.commit('setLanguageCode', {languageCode: response})
        },e => {
            console.error(e)
            store.dispatch('notify', {
                message: e.data.message,
            })
            return e
        })
    },
    parseRecTypeJsonTranslations(recTypeArray, recTypeArrayName, textParam = 'Text'){
        try {
            const recTypeTranslations = store.getters.translations.recType[recTypeArrayName]
            return recTypeArray.map(x => {
                let params = {...x}

                // Get the text property
                const textProperty = Helpers.getObjProp(x, textParam, null)
                
                // Assign translation to text property
                if (textProperty) {
                    const translatedTextProperty = Helpers.getObjProp(recTypeTranslations, textProperty, textProperty)
                    params[textParam] = translatedTextProperty
                }
                
                return params
            })
        } catch (error) {
            console.error(error)
            return recTypeArray
        }
    },
    parseEnvTranslations(envArray, envArrayName, envKeyParam = 'control', envTextParam = 'description') {
        try {
            const envTranslations = store.getters.translations.env[envArrayName]
            return envArray.map(x => {
                if (!x || x === undefined) return x

                let params = {...x}

                // Get the text property
                const textProperty = Helpers.getObjProp(x, envTextParam, null)
                
                // Assign translation to text property
                if (textProperty) {
                    const translationKey = Helpers.getObjProp(x, envKeyParam, null)
                    if (!translationKey) return params

                    const translatedTextProperty = Helpers.getObjProp(envTranslations, translationKey, textProperty)
                    params[envTextParam] = translatedTextProperty
                }
                
                return params
            })
        } catch (error) {
            console.error(error)
            return recTypeArray
        }
    }
}


