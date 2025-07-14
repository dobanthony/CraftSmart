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

        <div class="col-6 col-md-3 col-lg-2">
          <select v-model="limit" class="form-select">
            <option value="5">5 per page</option>
            <option value="10">10 per page</option>
            <option value="20">20 per page</option>
          </select>
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

      <!-- 🔄 Load More -->
      <div v-if="offset < total" class="text-center mt-4">
        <button class="btn btn-outline-primary" :disabled="loadingMore" @click="loadMore">
          {{ loadingMore ? 'Loading...' : 'Load More' }}
        </button>
      </div>
    </div>
  </DashboardLayout>

  <!-- ✅ Success Toast -->
  <div
    class="toast-container position-fixed top-0 start-50 translate-middle-x p-3"
    style="z-index: 1055"
  >
    <div
      id="orderSuccessToast"
      class="toast align-items-center text-bg-success border-0"
      role="alert"
      aria-live="assertive"
      aria-atomic="true"
    >
      <div class="d-flex">
        <div class="toast-body">✅ Order placed! Waiting for seller approval.</div>
        <button
          type="button"
          class="btn-close btn-close-white me-2 m-auto"
          data-bs-dismiss="toast"
          aria-label="Close"
        ></button>
      </div>
    </div>
  </div>

</template>

<script setup>
import { ref, computed, watch, onMounted, onBeforeUnmount } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import DashboardLayout from '@/Layouts/DashboardLayout.vue'

// Page Props from Inertia
const props = defineProps({
  orders: Array,
  total: Number,
  limit: Number,
  offset: Number,
  search: String
})

// State and Viewport Tracking
const isMobileView = ref(window.innerWidth <= 768)
function updateView() {
  isMobileView.value = window.innerWidth <= 768
}
onMounted(() => window.addEventListener('resize', updateView))
onBeforeUnmount(() => window.removeEventListener('resize', updateView))

// Flash Toast
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

// Filters and Pagination
const search = ref(props.search ?? '')
const limit = ref(Number(props.limit) || 5)
const offset = ref(Number(props.offset) || 0)
const total = ref(Number(props.total) || 0)
const allOrders = ref([...props.orders])
const loadingMore = ref(false)
const statusFilter = ref('')

// Watch Props Changes
watch(() => props.limit, val => limit.value = Number(val || 5))
watch(() => props.offset, val => offset.value = Number(val || 0))
watch(() => props.total, val => total.value = Number(val || 0))
watch(() => props.orders, val => {
  if (offset.value === 0) {
    allOrders.value = [...val]
  } else {
    allOrders.value.push(...val)
  }
})

// Filtered Data and Methods
const filteredOrders = computed(() => {
  if (!statusFilter.value) return allOrders.value
  return allOrders.value.filter(order => order.status === statusFilter.value)
})

const hasPendingOrder = computed(() =>
  allOrders.value.some(order => order.status === 'pending')
)

function handleSearch(reset = true) {
  const currentOffset = reset ? 0 : offset.value
  const params = {
    search: search.value,
    limit: limit.value,
    offset: currentOffset
  }

  router.get('/my-orders', params, {
    preserveState: true,
    preserveScroll: true,
    only: ['orders', 'total', 'offset', 'limit'],
    onStart: () => loadingMore.value = true,
    onFinish: () => loadingMore.value = false,
    onSuccess: () => {
      offset.value = reset ? limit.value : offset.value + limit.value
    }
  })
}

function loadMore() {
  handleSearch(false)
}

watch(limit, () => {
  offset.value = 0
  handleSearch(true)
})
</script>
