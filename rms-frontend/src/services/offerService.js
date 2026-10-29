import api from '@/services/api'

const BASE = '/offers'

const offerService = {
  async list() {
    const response = await api.get(BASE)
    return response?.data ?? {}
  },

  async create(payload) {
    const response = await api.post(BASE, payload)
    return response?.data ?? {}
  },

  async update(id, payload) {
    const response = await api.put(`${BASE}/${id}`, payload)
    return response?.data ?? {}
  },

  async remove(id) {
    const response = await api.delete(`${BASE}/${id}`)
    return response?.data ?? {}
  },

  async report(filters = {}) {
    const response = await api.get(`${BASE}/report`, { params: filters })
    return response?.data ?? {}
  },

  getErrorMessage(error, fallback = 'Could not complete the offer request.') {
    return String(
      error?.response?.data?.message ||
      Object.values(error?.response?.data?.errors || {}).flat().find(Boolean) ||
      error?.message ||
      fallback,
    )
  },
}

export default offerService
