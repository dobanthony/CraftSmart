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
          v-for="product in products.data"
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
                  Sold by: <strong>{{ product.shop?.user?.name ?? 'Unknown' }}</strong>
                </p>
              </div>
            </div>
          </Link>
        </div>
      </div>

      <!-- 🔢 Traditional Pagination -->
      <nav v-if="products.links.length > 3" class="d-flex justify-content-center mt-4">
        <ul class="pagination">
          <li
            v-for="(link, index) in products.links"
            :key="index"
            :class="['page-item', { active: link.active, disabled: !link.url }]"
          >
            <Link
              class="page-link"
              :href="link.url || ''"
              v-html="link.label"
              preserve-scroll
              preserve-state
            />
          </li>
        </ul>
      </nav>

      <!-- ℹ️ No Products Message -->
      <div v-if="products.data.length === 0" class="alert alert-info mt-3">
        No products found.
      </div>
    </div>
  </DashboardLayout>
</template>

<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'

const props = defineProps({
  products: Object, // pagination object (data, links, meta)
  search: {
    type: String,
    default: ''
  }
})

const search = ref(props.search)

const searchProducts = () => {
  router.get(
    '/view',
    { search: search.value },
    {
      preserveScroll: true,
      preserveState: true,
    }
  )
}
</script>
