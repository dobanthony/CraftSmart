<template>
  <DashboardLayout>
    <div class="container py-5">
      <h2 class="mb-4">🧾 My Orders</h2>

      <!-- 🔍 Search, Limit, and Filter -->
      <div class="row mb-4 g-2">
        <div class="col-12 col-md-6 col-lg-4">
          <input
            v-model="search"
            @keyup.enter="handleSearch"
            class="form-control"
            placeholder="Search by product, seller, or status"
          />
        </div>

        <div class="col-6 col-md-3 col-lg-2">
          <button class="btn btn-primary w-100" @click="handleSearch">Search</button>
        </div>
      </div>

      <!-- 🔔 Pending Note -->
      <div v-if="hasPendingOrder" class="alert alert-warning d-flex align-items-center gap-2">
        <i class="bi bi-info-circle-fill"></i>
        <div>
          <strong>Note:</strong> Your order is pending seller approval. Stock will only be deducted once approved.
        </div>
      </div>

      <!-- ✅ Desktop Table View -->
      <div v-if="!isMobileView" class="table-responsive">
        <table class="table table-hover align-middle text-center">
          <thead class="table-light">
            <tr class="text-nowrap">
              <th>🖼️</th>
              <th>Product</th>
              <th>Price</th>
              <th>Qty</th>
              <th>
                <div class="d-flex flex-column align-items-center gap-1">
                  <span>Status</span>
                  <select
                    v-model="statusFilter"
                    class="form-select form-select-sm"
                    style="width: 130px"
                  >
                    <option value="">All</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="declined">Declined</option>
                    <option value="canceled">Canceled</option>
                  </select>
                </div>
              </th>
              <th>Seller</th>
              <th>Date</th>
              <th>Receipt</th>
              <th>Delivery Date</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in filteredOrders" :key="order.id">
              <td>
                <img
                  :src="order.product?.image ? `/storage/${order.product.image}` : 'https://via.placeholder.com/50'"
                  width="50"
                  class="rounded"
                  alt="Product image"
                />
              </td>
              <td class="text-wrap">{{ order.product?.name }}</td>
              <td>₱{{ parseFloat(order.product?.price).toFixed(2) }}</td>
              <td>{{ order.quantity }}</td>
              <td>
                <span
                  class="badge"
                  :class="{
                    'bg-warning text-dark': order.status === 'pending',
                    'bg-success': order.status === 'approved',
                    'bg-danger': order.status === 'declined',
                    'bg-secondary': order.status === 'canceled'
                  }"
                >
                  {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                </span>
              </td>
              <td>{{ order.product?.shop?.user?.name ?? 'Unknown' }}</td>
              <td class="text-nowrap">{{ new Date(order.created_at).toLocaleString() }}</td>
              <td>
                <button
                  v-if="!order.received_order && order.status !== 'canceled'"
                  @click="$inertia.visit(`/receipt/${order.id}`)"
                  class="btn btn-sm btn-outline-primary"
                >
                  View
                </button>
                <span v-else class="text-muted small">Not available</span>
              </td>
              <td class="text-nowrap">{{ order.delivery_date ?? 'N/A' }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ✅ Mobile Card View -->
      <div v-else class="row g-3">
        <div v-for="order in filteredOrders" :key="order.id" class="col-12">
          <div class="card shadow-sm border">
            <div class="card-body">
              <div class="d-flex align-items-center gap-3 mb-3">
                <img
                  :src="order.product?.image ? `/storage/${order.product.image}` : 'https://via.placeholder.com/80'"
                  class="rounded"
                  style="width: 80px; height: 80px; object-fit: cover;"
                />
                <div>
                  <h5 class="mb-0">{{ order.product?.name }}</h5>
                  <small class="text-muted">₱{{ parseFloat(order.product?.price).toFixed(2) }}</small>
                </div>
              </div>

              <p class="mb-1"><strong>Quantity:</strong> {{ order.quantity }}</p>
              <p class="mb-1">
                <strong>Status:</strong>
                <span
                  class="badge"
                  :class="{
                    'bg-warning text-dark': order.status === 'pending',
                    'bg-success': order.status === 'approved',
                    'bg-danger': order.status === 'declined',
                    'bg-secondary': order.status === 'canceled'
                  }"
                >
                  {{ order.status.charAt(0).toUpperCase() + order.status.slice(1) }}
                </span>
              </p>
              <p class="mb-1"><strong>Seller:</strong> {{ order.product?.shop?.user?.name ?? 'Unknown' }}</p>
              <p class="mb-1"><strong>Date:</strong> {{ new Date(order.created_at).toLocaleString() }}</p>
              <p class="mb-1"><strong>Delivery Date:</strong> {{ order.delivery_date ?? 'N/A' }}</p>

              <div class="mt-3">
                <button
                  v-if="!order.received_order && order.status !== 'canceled'"
                  @click="$inertia.visit(`/receipt/${order.id}`)"
                  class="btn btn-sm btn-outline-primary w-100"
                >
                  View Receipt
                </button>
                <span v-else class="text-muted small">Receipt not available</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ℹ️ No Orders -->
      <div v-if="filteredOrders.length === 0" class="alert alert-info mt-3">
        You have no orders matching your search and filter.
      </div>

      <!-- 🔢 Pagination -->
      <nav v-if="orders.links.length > 3" class="d-flex justify-content-center mt-4">
        <ul class="pagination">
          <li
            v-for="(link, index) in orders.links"
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
    </div>
  </DashboardLayout>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

// Props
const props = defineProps({
  orders: Object,
  search: String,
})

// State
const isMobileView = ref(window.innerWidth <= 768)
const search = ref(props.search ?? '')
const limit = ref(5)
const statusFilter = ref('')

// Resize detection
const updateView = () => {
  isMobileView.value = window.innerWidth <= 768
}
onMounted(() => window.addEventListener('resize', updateView))
onBeforeUnmount(() => window.removeEventListener('resize', updateView))

// Toast
const page = usePage()
onMounted(() => {
  const message = page.props.flash?.success
  if (message) {
    const toastEl = document.getElementById('orderSuccessToast')
    const toastBody = toastEl.querySelector('.toast-body')
    toastBody.innerText = message
    const toast = new bootstrap.Toast(toastEl)
    toast.show()
  }
})

// Methods
const handleSearch = () => {
  router.get('/my-orders', {
    search: search.value,
    limit: limit.value,
  }, {
    preserveScroll: true,
    preserveState: true,
  })
}

// Filters
const filteredOrders = computed(() => {
  if (!statusFilter.value) return props.orders.data
  return props.orders.data.filter(order => order.status === statusFilter.value)
})

const hasPendingOrder = computed(() =>
  props.orders.data.some(order => order.status === 'pending')
)
</script>
