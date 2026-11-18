export default [
    {
        path: '/',
        name: 'home',
        component: () => { return import('@/views/dashboard/Dashboard.vue'); },
        meta: {
            layout: 'layout-main',
            redirectIfNotLoggedIn: true,
            sidebar: true,
            isPartOfOrder: true
        },
        redirect: '/redirect',
        children: [
            {
                path: 'newOrder',
                name: 'newOrder',
                component: () => { return import('@/views/dashboard/new_order/BasicData.vue'); },
                meta: {
                    layout: 'layout-main',
                    redirectIfNotLoggedIn: true,
                    sidebar: true,
                    isPartOfOrder: true
                },
            },
            {
                path: 'details',
                name: 'details',
                component: () => { return import('@/views/dashboard/new_order/Details.vue'); },
                meta: {
                    layout: 'layout-main',
                    redirectIfNotLoggedIn: true,
                    sidebar: true,
                    isPartOfOrder: true
                },
            }
        ]
    },
    {
        path: '/orderBasket',
        name: 'orderBasket',
        component: () => { return import('@/views/dashboard/OrderBasket.vue'); },
        meta: {
            layout: 'layout-main',
            redirectIfNotLoggedIn: true,
            sidebar: false,
            isPartOfOrder: false
        }
    },
    {
        path: '/overview',
        name: 'overview',
        component: () => { return import('@/views/dashboard/Overview.vue'); },
        meta: {
            layout: 'layout-main',
            redirectIfNotLoggedIn: true,
            sidebar: false,
            isPartOfOrder: false
        }
    },
    {
        path: '/shapeTester',
        name: 'shapeTester',
        component: () => { return import('@/views/dashboard/settings/ShapeTester.vue'); },
        meta: {
            layout: 'layout-main',
            redirectIfNotLoggedIn: true,
            sidebar: false,
            isPartOfOrder: false
        }
    },
    {
        path: '/tracerConfiguration',
        name: 'tracerConfiguration',
        component: () => { return import('@/views/dashboard/TracerConfiguration.vue'); },
        meta: {
            layout: 'layout-main',
            redirectIfNotLoggedIn: true,
            sidebar: true,
            isPartOfOrder: false
        }
    },
    {
        path: '/contact',
        name: 'contact',
        component: () => { return import('@/views/dashboard/Contact.vue'); },
        meta: {
            layout: 'layout-main',
            redirectIfNotLoggedIn: true,
            sidebar: false,
            isPartOfOrder: false
        }
    },
    {
        path: '/changePassword',
        name: 'changePassword',
        component: () => { return import('@/views/dashboard/ChangePassword.vue'); },
        meta: {
            layout: 'layout-main',
            redirectIfNotLoggedIn: true,
            sidebar: false,
            isPartOfOrder: false
        }
    },
    {
        path: '/imprint',
        name: 'imprint',
        component: () => { return import('@/views/dashboard/Imprint.vue'); },
        meta: {
            layout: 'layout-main',
            redirectIfNotLoggedIn: true,
            sidebar: false,
            isPartOfOrder: false
        }
    },
    {
        path: '/privacy',
        name: 'privacy',
        component: () => { return import('@/views/dashboard/Privacy.vue'); },
        meta: {
            layout: 'layout-main',
            redirectIfNotLoggedIn: true,
            sidebar: false,
            isPartOfOrder: false
        }
    },
    {
        path: '/shopConfiguration',
        name: 'shopConfiguration',
        component: () => { return import('@/views/dashboard/ShopConfiguration.vue'); },
        meta: {
            layout: 'layout-main',
            redirectIfNotLoggedIn: true,
            sidebar: false,
            isPartOfOrder: false
        }
    }
]
