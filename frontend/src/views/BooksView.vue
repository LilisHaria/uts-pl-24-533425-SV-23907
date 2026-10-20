<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const books = ref([])
const loading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')
const editingId = ref(null)

const form = ref({
  title: '',
  author: '',
  category: '',
  publication_year: '',
})

async function fetchBooks() {
  loading.value = true
  errorMessage.value = ''

  try {
    const response = await api.get('/books')
    books.value = response.data.data ?? response.data
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal mengambil data buku.'

    if (error.response?.status === 401) {
      localStorage.removeItem('token')
      router.push('/login')
    }
  } finally {
    loading.value = false
  }
}

function resetForm() {
  editingId.value = null
  form.value = {
    title: '',
    author: '',
    category: '',
    publication_year: '',
  }
}

function editBook(book) {
  editingId.value = book.id
  form.value = {
    title: book.title,
    author: book.author,
    category: book.category,
    publication_year: book.publication_year,
  }
  successMessage.value = ''
  errorMessage.value = ''
}

async function saveBook() {
  errorMessage.value = ''
  successMessage.value = ''

  try {
    const payload = {
      ...form.value,
      publication_year: Number(form.value.publication_year),
    }

    if (editingId.value !== null) {
      await api.put(`/books/${editingId.value}`, payload)
      successMessage.value = 'Buku berhasil diperbarui!'
    } else {
      await api.post('/books', payload)
      successMessage.value = 'Buku berhasil ditambahkan!'
    }

    resetForm()
    await fetchBooks()
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message ||
      JSON.stringify(error.response?.data?.errors || {}) ||
      'Gagal menyimpan buku.'
  }
}

async function deleteBook(id) {
  if (!confirm('Yakin ingin menghapus buku ini?')) return

  errorMessage.value = ''
  successMessage.value = ''

  try {
    await api.delete(`/books/${id}`)
    successMessage.value = 'Buku berhasil dihapus!'
    await fetchBooks()
  } catch (error) {
    errorMessage.value =
      error.response?.data?.message || 'Gagal menghapus buku.'
  }
}

async function logout() {
  try {
    await api.post('/logout')
  } catch {
    // Token lokal tetap dihapus jika request gagal.
  }

  localStorage.removeItem('token')
  router.push('/login')
}

onMounted(fetchBooks)
</script>

<template>
  <main class="container">
    <header>
      <h1>Manajemen Buku</h1>
      <button type="button" @click="logout">Logout</button>
    </header>

    <section class="panel">
      <h2>{{ editingId ? 'Edit Buku' : 'Tambah Buku' }}</h2>

      <form @submit.prevent="saveBook">
        <label>Judul buku</label>
        <input v-model="form.title" required />

        <label>Penulis</label>
        <input v-model="form.author" required />

        <label>Kategori</label>
        <input v-model="form.category" required />

        <label>Tahun terbit</label>
        <input
          v-model="form.publication_year"
          type="number"
          min="1000"
          max="2100"
          required
        />

        <div class="actions">
          <button type="submit">
            {{ editingId ? 'Simpan Perubahan' : 'Tambah Buku' }}
          </button>
          <button
            v-if="editingId"
            type="button"
            class="secondary"
            @click="resetForm"
          >
            Batal
          </button>
        </div>
      </form>
    </section>

    <p v-if="successMessage" class="success">{{ successMessage }}</p>
    <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

    <section class="panel">
      <h2>Daftar Buku</h2>
      <p v-if="loading">Memuat data...</p>

      <div v-else class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>Judul</th>
              <th>Penulis</th>
              <th>Kategori</th>
              <th>Tahun</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr v-for="book in books" :key="book.id">
              <td>{{ book.title }}</td>
              <td>{{ book.author }}</td>
              <td>{{ book.category }}</td>
              <td>{{ book.publication_year }}</td>
              <td class="actions">
                <button type="button" @click="editBook(book)">Edit</button>
                <button
                  type="button"
                  class="danger"
                  @click="deleteBook(book.id)"
                >
                  Hapus
                </button>
              </td>
            </tr>

            <tr v-if="books.length === 0">
              <td colspan="5">Belum ada data buku.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </main>
</template>

<style scoped>
.container {
  max-width: 1000px;
  margin: 30px auto;
  padding: 16px;
  color: #222;
}

header,
.actions {
  display: flex;
  align-items: center;
  gap: 8px;
}

header {
  justify-content: space-between;
}

.panel {
  margin: 20px 0;
  padding: 20px;
  border: 1px solid #ddd;
  border-radius: 10px;
}

form {
  display: grid;
  gap: 10px;
}

input {
  padding: 10px;
  border: 1px solid #bbb;
  border-radius: 5px;
}

button {
  padding: 9px 13px;
  cursor: pointer;
}

.secondary {
  background: #eee;
}

.danger {
  background: #b42318;
  color: white;
}

.success {
  color: #16803c;
}

.error {
  color: #c62828;
}

.table-wrap {
  overflow-x: auto;
}

table {
  width: 100%;
  border-collapse: collapse;
}

th,
td {
  padding: 10px;
  border: 1px solid #ddd;
  text-align: left;
}
</style>
