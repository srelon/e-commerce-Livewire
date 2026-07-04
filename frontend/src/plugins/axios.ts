import axios from 'axios'
import { useToast } from 'vue-toastification'
import { useSeo } from '@/composables/useSeo'

const api = axios.create({
    baseURL: import.meta.env.VITE_API_URL ?? '/api',
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    withCredentials: true,
})

function extract_error_message(error: unknown): string {
    if (axios.isAxiosError(error)) {
        const errors = error.response?.data?.errors

        if (typeof errors === 'string') {
            return errors
        }

        if (errors && typeof errors === 'object') {
            const first_field_errors = Object.values(errors)[0]
            if (Array.isArray(first_field_errors) && first_field_errors.length) {
                return first_field_errors[0]
            }
        }

        if (!error.response) {
            return 'Network error. Please try again.'
        }
    }

    return 'Something went wrong. Please try again.'
}

api.interceptors.response.use(
    (response) => {
        const data = response.data?.data
        useSeo(data?.page?.seo ?? data?.seo)

        return response
    },
    (error) => {
        useToast().error(extract_error_message(error))

        return Promise.reject(error)
    },
)

export default api
