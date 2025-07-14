<template>
  <DashboardLayout>
    <div class="container py-4">
      <h2 class="mb-4">🛍️ All Products</h2>

      <!-- 🔍 Search Form -->
      <form @submit.prevent="searchProducts" class="row g-2 mb-4">
        <div class="col-md-10">
          <input
            type="text"
            class="form-control"
            placeholder="Search products..."
            v-model="search"
          />
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-primary w-100">Search</button>
        </div>
      </form>

      <!-- 📦 Product Cards -->
      <div class="row">
        <div
          class="col-6 col-md-4 col-lg-3 mb-4"
          v-for="product in products"
          :key="product.id"
        >
          <Link
            :href="`/product/${product.id}`"
            class="text-decoration-none text-dark"
          >
            <div class="card h-100 shadow-sm border border-light">
              <img
                :src="product.image ? `/storage/${product.image}` : 'https://via.placeholder.com/500x300?text=No+Image'"
                class="card-img-top"
                alt="Product Image"
                style="height: 200px; object-fit: cover;"
              />
              <div class="card-body">
                <h5 class="card-title text-primary">{{ product.name }}</h5>
                <p class="card-text small text-muted">
                  {{ product.description }}
                </p>
                <p class="fw-bold text-success">
                  ₱{{ product.price ?? '0.00' }}
                </p>
                <p class="text-muted small">
                  Sold by:
                  <strong>{{ product.shop?.user?.name ?? 'Unknown' }}</strong>
                </p>
              </div>
            </div>
          </Link>
        </div>
      </div>

      <!-- 📥 Load More -->
      <div class="text-center mt-4" v-if="hasMore">
        <button @click="loadMore" class="btn btn-outline-secondary">
          Load More
        </button>
      </div>

      <!-- ℹ️ No Products Message -->
      <div v-if="products.length === 0" class="alert alert-info mt-3">
        No products found.
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({
  products: {
    type: Array,
    default: () => []
  },
  total: {
    type: Number,
    default: 0
  },
  limit: {
    type: Number,
    default: 5
  },
  offset: {
    type: Number,
    default: 0
  },
  search: {
    type: String,
    default: ''
  }
})

const search = ref(props.search)
const limit = ref(props.limit)
const offset = ref(props.offset)
const products = ref([...props.products])
const total = ref(props.total)

const searchProducts = () => {
  router.get(
    '/view',
    {
      search: search.value,
      limit: limit.value,
      offset: 0
    },
    {
      preserveScroll: true,
      preserveState: true
    }
  )
}

const loadMore = () => {
  router.get(
    '/view',
    {
      search: search.value,
      limit: limit.value,
      offset: offset.value + limit.value
    },
    {
      preserveScroll: true,
      preserveState: true,
      only: ['products'],
      onSuccess: (page) => {
        if (Array.isArray(page.props.products)) {
          products.value.push(...page.props.products)
          offset.value += limit.value
        }
      }
    }
  )
}

const hasMore = computed(() => products.value.length < total.value)
</script>
