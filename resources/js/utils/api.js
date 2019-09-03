import { ACCESS_TOKEN } from '@/constants'
import axios from 'axios'
import store from '@/store'
import { $t } from '@/utils/i18n'
import swal from '@/utils/swal'
import router from '@/router'

axios.interceptors.request.use(request => {
  const token = store.getters['auth/' + ACCESS_TOKEN]
  request.headers.common['Authorization'] = `Basic ${btoa(process.env.MIX_BASIC_AUTHEN)}`
  if (token) {
    request.headers.common['Accept'] = 'application/json'
    request.headers.common['Authorization-Bearer'] = `Bearer ${token}`
  }

  return request
})

// Response interceptor
axios.interceptors.response.use(response => Promise.resolve(response.config.originData ? response : response.data), error => {
  const { status } = error.response

  if (status === 401) {
    if (store.getters['auth/check']) {
      swal('warning', {
        title: $t('error.token_expired.title'),
        text: $t('error.token_expired.text')
      }).then(() => {
        store.commit('auth/LOGOUT')
        router.push({ name: 'login' })
      })
    }
  } else {
    let text = $t('error.unknown');
    switch (status) {
    case 500:
      text = $t('error.server')
      break
    case 403:
      text = $t('error.forbidden')
      break
    case 404:
      text = $t('error.not_found')
      break
    case 421:
      text = $t('error.misdirect_request')
      break
    }
    swal('error', {
      title: status,
      text
    })
  }

  return Promise.reject(error)
})

const Request = {
  request({ method, options }) {
    return axios({ ...options, method })
  },

  get(options) {
    return this.request({ method: 'GET', options })
  },

  post(options) {
    return this.request({ method: 'POST', options })
  },

  put(options) {
    return this.request({ method: 'PUT', options })
  },

  patch(options) {
    return this.request({ method: 'PATCH', options })
  },

  delete(options) {
    return this.request({ method: 'DELETE', options })
  }
}

export default Request
