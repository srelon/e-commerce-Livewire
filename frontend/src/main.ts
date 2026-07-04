import { createApp } from 'vue'
import { createPinia } from 'pinia'
import Toast, { POSITION } from 'vue-toastification'
import router from './routes/router'
import App from './App.vue'
import './assets/scss/main.scss'
import 'vue-toastification/dist/index.css'

const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(Toast, {
    position: POSITION.TOP_RIGHT,
    timeout: 4000,
})
app.mount('#app')
