import ErrorRoutes from '@userfrosting/sprinkle-core/routes'
import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
    history: createWebHistory(),
    routes: [
        {
            path: '',
            redirect: { name: 'home' },
            component: () => import('../layouts/LayoutDashboard.vue'),
            children: [
                {
                    path: '/',
                    name: 'home',
                    component: () => import('../views/HomeView.vue')
                },
                {
                    path: '/about',
                    name: 'about',
                    meta: {
                        title: 'ABOUT'
                    },
                    component: () => import('../views/AboutView.vue')
                },
                // Include sprinkles routes
                ...ErrorRoutes
            ]
        }
    ]
})

export default router
