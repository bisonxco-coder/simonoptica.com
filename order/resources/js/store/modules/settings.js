import ApiService from '@/utils/api';
import store from '@/store';

export default {
    state () {
        return {
            getIniProperties: null,
            translations: null,
            translationsLanding: null,
            languageCode: null,
            areDashboardRoutesRegistered: false,
            areMainModulesRegistered: false,
        }
    },
    getters: {
        translations(state){
            return state.translations
        },
        translationsLanding(state){
            return state.translationsLanding
        },
        getLanguageCode(state) {
            if (state.languageCode) return state.languageCode

            let languageCode = localStorage.getItem('languageCode')
            if (languageCode) return languageCode

            return ''
        },
        getIniProperties(state){
            return state.getIniProperties
        },
    },
    mutations: {
        setLanguageCode(state, data) {
            let languageCode = data.languageCode
            if (!languageCode || languageCode === undefined || typeof languageCode !== 'string') return

            localStorage.setItem('languageCode', languageCode);
            state.languageCode = languageCode
        },
    },
    actions: {
        getIniProperties (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('settings/get-ini-properties', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getTranslations (context, data) {
            return new Promise((resolve, reject) => {
                data = ApiService.addAuthParameters(data);
                ApiService.post('language', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getLandingTranslations (context, data) {
            return new Promise((resolve, reject) => {
                ApiService.post('language/landing', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getDefaultLanguageCode (context, data) {
            return new Promise((resolve, reject) => {
                ApiService.get('language/default')
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getLanguageCodes (context, data) {
            return new Promise((resolve, reject) => {
                ApiService.get('language/codes')
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        },
        getIniPropertiesSettings (context, data) {
            return new Promise((resolve, reject) => {
                ApiService.post('settings/get-ini-properties-settings', data)
                    .then(({data}) => {
                        resolve(data);
                    })
                    .catch(({response}) => {
                        reject(response);
                    });
            });
        }
    }
}
