import { createRouter, createWebHistory } from 'vue-router';
import store from '@/store';
import authRoutes from './routes/auth';
import dashboardRoutes from './routes/dashboard';
import Order from '@/utils/order.js'
import Initializator from '@/utils/initializator.js'

const routes = [
    ...authRoutes,
    ...dashboardRoutes,
    {
        path: process.env.MIX_SHOP_CALL_PATH,
        name: 'shopCallPath',
        component: () => {
            setTimeout(() =>{
                return window.location.href = process.env.MIX_SHOP_URL
            }, 500)
        },
        meta: {
            layout: 'layout-main'
        }
    },
    {
        path: '/not-found',
        name: 'not-found',
        component: () => { return import('@/views/Error404.vue') },
        meta: {
            layout: 'layout-landing'
        }
    },
    {
        path: '/redirect',
        name: 'redirect',
        component: () => { return import('@/views/Redirect.vue') },
        meta: {
            layout: 'layout-main',
            redirectIfNotLoggedIn: true,
        }
    },
    {
        path: '/:pathMatch(.*)*',
        redirect: 'not-found'
    }
];

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes
});

router.beforeEach((to, _, next) => {
    var isLoggedIn = store.getters.isAuthenticated;

    if (to.meta.redirectIfNotLoggedIn && !isLoggedIn) {
        return next({ name: 'auth-login' });
    }

    if (to.meta.redirectIfLoggedIn && isLoggedIn) {
        return next({ name: 'home' });
    }

    if (store.getters.inTesting) return next()

    // If order was saved from the footer buttons and is about to leave to the orderbasket page,
    // prevent displaying dialog box.
    if (store.getters.getWasOrderSavedFromFooter){
        store.commit('setWasOrderSavedFromFooter', false)
        return next()
    }

    if (!_.meta.isPartOfOrder || (to.meta.isPartOfOrder && _.meta.isPartOfOrder)) {
        // Allow access to details page only after filling out the required fields in basic data
        if (to.matched[to.matched.length-1].name == 'details' && !store.getters.canContinueToDetails){
            return next({ name: 'newOrder' })
        }

        return next()
    }

    // Show popup only if changes were made to the order
    if (!Initializator.hasOrderBeenEdited()) return next()

    // Show a confirmation dialog if user is leaving the order.
    const dialogParams = {
        title: store.getters.translations.dialog.warning,
        description: store.getters.translations.dialog.messages.leaveCurrentOrder,
        falseButtonText: store.getters.translations.dialog.continueWithOrder,
        trueButtonText: store.getters.translations.dialog.continue,
        type: 'warning',
        allowClosing: true,

    }
    // If draft requirements are ok, add button to the dialog box to save as draft.
    if (store.getters.areDraftFieldsEvaluated){
        dialogParams.description = store.getters.translations.dialog.messages.leaveCurrentOrderOrSave,
        dialogParams.extraButtons = [
            {
                text: store.getters.translations.dialog.saveDraft,
                style: 'blue',
                click: () => {
                    document.dispatchEvent(new Event('saveDraftEvent'))
                    return next()
                }
            },
        ]
    }
    store.dispatch('dialog', dialogParams).then(value => {
        // Continue - reset order and leave page
        if (value) {
            Order.reset()
            store.state.moduleOrders.isEditing = false
            store.state.moduleOrders.editingOrder = null
            return next()
        }

        // Cancel - do nothing/stay on the current page
        return
    })
});

export default router;
