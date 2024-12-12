import { createRouter, createWebHistory } from 'vue-router'
import { useStore } from 'vuex'

// Auth Components
import LoginForm from '../components/auth/LoginForm.vue'
import RegisterForm from '../components/auth/RegisterForm.vue'
import ForgotPassword from '../components/auth/ForgotPassword.vue'
import ResetPassword from '../components/auth/ResetPassword.vue'
import VerifyEmail from '../components/auth/VerifyEmail.vue'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: LoginForm,
    meta: { guest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: RegisterForm,
    meta: { guest: true }
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: ForgotPassword,
    meta: { guest: true }
  },
  {
    path: '/reset-password/:token',
    name: 'reset-password',
    component: ResetPassword,
    meta: { guest: true }
  },
  {
    path: '/verify-email/:token?',
    name: 'verify-email',
    component: VerifyEmail,
    meta: { auth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const store = useStore()
  const isAuthenticated = store.getters['auth/isAuthenticated']
  const requiresAuth = to.matched.some(record => record.meta.auth)
  const isGuest = to.matched.some(record => record.meta.guest)

  if (requiresAuth && !isAuthenticated) {
    next('/login')
  } else if (isGuest && isAuthenticated) {
    next('/dashboard')
  } else {
    next()
  }
})

export default router