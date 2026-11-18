export default [
    {
        path: '/login',
        name: 'auth-login',
        component: () => { return import('@/views/authentication/Login.vue'); },
        meta: {
            layout: 'layout-landing',
            redirectIfLoggedIn: true
        }
    },
    {
        path: '/register',
        name: 'auth-register',
        component: () => { return import('@/views/authentication/Register.vue'); },
        meta: {
            layout: 'layout-landing',
            redirectIfLoggedIn: true
        }
    },
    {
        path: '/forgot-password',
        name: 'auth-forgot-password',
        component: () => { return import('@/views/authentication/ForgotPassword.vue'); },
        meta: {
            layout: 'layout-landing',
            redirectIfLoggedIn: true
        }
    },
    {
        path: '/forgot-password/reset-password',
        name: 'auth-reset-password-v1',
        component: () => { return import('@/views/authentication/ResetPassword.vue'); },
        meta: {
            layout: 'layout-landing',
            redirectIfLoggedIn: true
        }
    },
]
