import { createRouter, createWebHistory } from 'vue-router'
import type { RouteRecordRaw } from 'vue-router'

import Layout from '@/components/layout/Layout.vue'
import HomePage from '@/views/Pages/Static/HomePage.vue'
import AboutPage from '@/views/Pages/Static/AboutPage.vue'
import ContactPage from '@/views/Pages/Static/ContactPage.vue'
import ProductList from '@/views/Pages/Products/ProductList.vue'
import ProductPage from '@/views/Pages/Products/ProductPage.vue'
import CartPage from '@/views/Pages/Cart/CartPage.vue'
import AuthorList from '@/views/Pages/Authors/AuthorList.vue'
import NewsList from '@/views/Pages/News/NewsList.vue'
import NewsPage from '@/views/Pages/News/NewsPage.vue'
import NotificationsPage from '@/views/Pages/Notifications/NotificationsPage.vue'

import { useShopStore } from '@/stores/shop'
import { useAuthStore } from '@/stores/auth'
import middlewarePipeline from './middlewarePipeline'

const routes: RouteRecordRaw[] = [
    {
        path: '/',
        component: Layout,
        children: [
            {
                name: 'home',
                path: '',
                component: HomePage,
            },
            {
                name: 'about',
                path: 'about-us',
                component: AboutPage,
            },
            {
                name: 'contact',
                path: 'contact-us',
                component: ContactPage,
            },
            {
                name: 'products',
                path: 'products',
                component: ProductList,
            },
            {
                name: 'product',
                path: 'product/:slug',
                component: ProductPage,
                props: true,
            },
            {
                name: 'authors',
                path: 'authors',
                component: AuthorList,
            },
            {
                name: 'cart',
                path: 'cart',
                component: CartPage,
                beforeEnter: () => {
                    const store = useShopStore()
                    if (store.cart_items.length === 0) return { name: 'home' }
                },
            },
            {
                name: 'notifications',
                path: 'notifications',
                component: NotificationsPage,
                beforeEnter: async () => {
                    await useAuthStore().ensure_ready()

                    if (!useAuthStore().is_authenticated) return { name: 'home' }
                },
            },
            {
                name: 'reset-password',
                path: 'reset-password',
                component: { render: () => null },
                beforeEnter: (to) => {
                    useAuthStore().set_reset_pending({
                        token: String(to.query.token ?? ''),
                        email: String(to.query.email ?? ''),
                    })

                    return { name: 'home' }
                },
            },
            {
                path: 'news',
                children: [
                    {
                        name: 'news',
                        path: '',
                        component: NewsList,
                    },
                    {
                        name: 'post',
                        path: ':slug',
                        component: NewsPage,
                        props: true,
                    },
                ],
            },
        ],
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'not_found',
        redirect: '/',
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior(to, from) {
        if (to.path !== from.path) return { top: 0 }
    },
})

router.beforeEach((to, from, next) => {
    if (!to.meta?.middleware) {
        return next()
    }

    const middleware = to.meta.middleware as Function[]
    const context = { to, from, next }

    return middleware[0]({
        ...context,
        next: middlewarePipeline(context, middleware, 1),
    })
})

export default router
