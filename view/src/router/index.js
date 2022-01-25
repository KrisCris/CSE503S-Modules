import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Auth from '../views/Auth.vue'
import Redirect from '../views/Redirect.vue'
import Links from '../views/Links.vue'
import Exception from '../views/Exception.vue'

const routes = [
  {
    path: '/:urlKey',
    name: 'Redirect',
    component: Redirect,
    props: true
  },
  {
    path: '/app/',
    name: 'Home',
    component: Home
  },
  {
    path: '/app/auth',
    name: 'Auth',
    component: Auth
  },
  {
    path: '/app/links',
    name: 'Links',
    component: Links
  },
  // redirect
  {
    path: '/',
    redirect: '/app/'
  },
  // ERROR
  {
    path: '/:catchAll(.*)',
    name: 'Exception',
    component: Exception
  }
]

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes
})

export default router
