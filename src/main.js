import { createApp } from 'vue'
import App from './App.vue'

// npm install --save @fortawesome/fontawesome-free
import '@fortawesome/fontawesome-free/css/all.css'
import '@fortawesome/fontawesome-free/js/all.js'


// npm install -S vue-sweetalert2 (( not sweetalert2 , 
import VueSweetalert2 from 'vue-sweetalert2';
import 'sweetalert2/dist/sweetalert2.min.css';


// 這個要用vue ui's dependent 安裝 axios , 不要用npm
// 子 .vue 檔內 也要加下面這串
import axios from 'axios'

import * as bootstrap from 'bootstrap'

import "bootstrap/dist/css/bootstrap.min.css"
import "bootstrap"


// import router from './router'
window.$ = window.jQuery = require('jquery');



import { createRouter, createWebHistory, createWebHashHistory } from 'vue-router'

const routes = [
    {
        path: '/manage',
        name: 'manage',
        component: () => import('./components/manage.vue')
    }

]

const router = createRouter({

    history: createWebHashHistory(),


    routes: routes,
    // routes
})


const app = createApp(App).use(router) // 這個router 不能放在下面的use()
app.use(VueSweetalert2, axios, bootstrap)


app.mount('#app');

// createApp(App).mount('#app')


