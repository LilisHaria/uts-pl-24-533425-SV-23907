<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const email = ref('')
const password = ref('')
const errorMessage = ref('')
const loading = ref(false)

async function login() {
  errorMessage.value = ''
  loading.value = true

  try {
    const response = await api.post('/login', {
      email: email.value,
      password: password.value,
    })

    const token =
      response.data.token ??
      response.data.access_token

    if (!token) {
      throw new Error('Token tidak ditemukan pada respons API.')
    }

    localStorage.setItem('token', token)
    router.push('/books')
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      error.message ||
      'Login gagal. Periksa koneksi backend.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="login-page">
    <form class="login-form" @submit.prevent="login">
      <h1>Login</h1>

      <label for="email">Email</label>
      <input
        id="email"
        v-model="email"
        type="email"
        required
        autocomplete="email"
      />

      <label for="password">Password</label>
      <input
        id="password"
        v-model="password"
        type="password"
        required
        autocomplete="current-password"
      />

      <p v-if="errorMessage" class="error">
        {{ errorMessage }}
      </p>

      <button type="submit" :disabled="loading">
        {{ loading ? 'Memproses...' : 'Login' }}
      </button>
    </form>
  </main>
</template>

<style scoped>
.login-page {
  min-height: 80vh;
  display: grid;
  place-items: center;
}

.login-form {
  width: min(360px, 90%);
  display: grid;
  gap: 12px;
}

input,
button {
  padding: 10px;
}

.error {
  color: #c62828;
}
</style>
