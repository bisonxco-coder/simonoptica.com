import store from '@/store';
import { createStore } from 'vuex';
import moduleUser from './modules/user';
import moduleOrders from './modules/orders';
import moduleInformation from './modules/information';
import moduleEvaluator from './modules/ordersEvaluator';
import moduleSettings from './modules/settings';
import moduleUploadedShape from './modules/uploadedShape';
import moduleTracer from './modules/tracer';
import moduleAnalyseB2b from './modules/analyseB2b'

export default createStore({
    modules: {
        moduleSettings, moduleUser
    }
})

// These modules get registered only after authentication
export function registerMainModules() {
    if (store.state.moduleSettings.areMainModulesRegistered) return
    
    store.registerModule('moduleOrders', moduleOrders)
    store.registerModule('moduleInformation', moduleInformation)
    store.registerModule('moduleEvaluator', moduleEvaluator)
    store.registerModule('moduleUploadedShape', moduleUploadedShape)
    store.registerModule('moduleTracer', moduleTracer)
    store.registerModule('moduleAnalyseB2b', moduleAnalyseB2b)

    store.state.moduleSettings.areMainModulesRegistered = true
}
