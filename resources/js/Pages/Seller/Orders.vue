<template>
  <SellerDashboardLayout>
    <div class="container py-4">
      <h2 class="mb-4 text-center text-md-start">📦 Seller Orders</h2>

      <!-- 🔍 Search & Filter -->
      <div class="row gy-2 gx-2 mb-3">
        <div class="col-12 col-md-4">
          <input
            v-model="search"
            @keyup.enter="handleSearch"
            class="form-control"
            placeholder="🔍 Search by buyer, product, or status"
          />
        </div>
        <div class="col-6 col-md-2">
          <button class="btn btn-primary w-100" @click="handleSearch">Search</button>
        </div>
        <div class="col-6 col-md-2">
          <select v-model="limit" class="form-select">
            <option value="5">5 per page</option>
            <option value="10">10 per page</option>
            <option value="20">20 per page</option>
          </select>
        </div>
        <div class="col-12 col-md-2">
          <select v-model="statusFilter" class="form-select">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="declined">Declined</option>
            <option value="canceled">Canceled</option>
          </select>
        </div>
      </div>

      <!-- 📋 Table (Desktop) -->
      <div class="table-responsive d-none d-md-block">
        <table class="table table-bordered text-center align-middle">
          <thead class="table-light">
            <tr>
              <th>Buyer</th>
              <th>Product</th>
              <th>Qty</th>
              <th>Status</th>
              <th>Delivery Date</th>
              <th>Delivery Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="order in filteredOrders" :key="order.id">
              <td>{{ order.user.name }}</td>
              <td>{{ order.product.name }}</td>
              <td>{{ order.quantity }}</td>
              <td>
                <span :class="{
                  'text-warning': order.status === 'pending',
                  'text-success': order.status === 'approved',
                  'text-danger': order.status === 'declined',
                  'text-secondary': order.status === 'canceled'
                }">
                  {{ order.status }}
                </span>
              </td>
              <td>
                <div v-if="order.status === 'pending'">
                  <input type="date" v-model="deliveryDates[order.id]" class="form-control form-control-sm" />
                </div>
                <span v-else>{{ order.delivery_date || 'N/A' }}</span>
              </td>
              <td>
                <div v-if="order.status === 'approved'">
                  <select
                    class="form-select form-select-sm"
                    v-model="deliveryStatuses[order.id]"
                    @change="updateDeliveryStatus(order.id)"
                  >
                    <option value="pending">📦 Pending</option>
                    <option value="delivered">✅ Delivered</option>
                    <option value="failed">❌ Failed</option>
                  </select>
                </div>
                <span v-else class="badge bg-secondary">
                  {{ order.delivery_status ?? 'N/A' }}
                </span>
              </td>
              <td>
                <div class="d-flex gap-1 justify-content-center flex-wrap">
                  <button
                    v-if="order.status === 'pending'"
                    @click="approve(order.id)"
                    class="btn btn-sm btn-success"
                  >Approve</button>
                  <button
                    v-if="order.status === 'pending'"
                    @click="decline(order.id)"
                    class="btn btn-sm btn-danger"
                  >Decline</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- 📱 Card View (Mobile) -->
      <div class="d-block d-md-none">
        <div v-for="order in filteredOrders" :key="order.id" class="card mb-3 shadow-sm">
          <div class="card-body">
            <p><strong>👤 Buyer:</strong> {{ order.user.name }}</p>
            <p><strong>🛍️ Product:</strong> {{ order.product.name }}</p>
            <p><strong>🔢 Qty:</strong> {{ order.quantity }}</p>
            <p>
              <strong>📌 Status:</strong>
              <span :class="{
                'text-warning': order.status === 'pending',
                'text-success': order.status === 'approved',
                'text-danger': order.status === 'declined',
                'text-secondary': order.status === 'canceled'
              }">
                {{ order.status }}
              </span>
            </p>
            <p>
              <strong>📅 Delivery Date:</strong>
              <span v-if="order.status !== 'pending'">{{ order.delivery_date || 'N/A' }}</span>
              <input
                v-else
                type="date"
                v-model="deliveryDates[order.id]"
                class="form-control form-control-sm"
              />
            </p>
            <p>
              <strong>🚚 Delivery Status:</strong>
              <span v-if="order.status !== 'approved'" class="badge bg-secondary">
                {{ order.delivery_status ?? 'N/A' }}
              </span>
              <select
                v-else
                class="form-select form-select-sm"
                v-model="deliveryStatuses[order.id]"
                @change="updateDeliveryStatus(order.id)"
              >
                <option value="pending">📦 Pending</option>
                <option value="delivered">✅ Delivered</option>
                <option value="failed">❌ Failed</option>
              </select>
            </p>
            <div class="d-flex flex-wrap gap-2 justify-content-center mt-3">
              <button
                v-if="order.status === 'pending'"
                @click="approve(order.id)"
                class="btn btn-sm btn-success"
              >Approve</button>
              <button
                v-if="order.status === 'pending'"
                @click="decline(order.id)"
                class="btn btn-sm btn-danger"
              >Decline</button>
            </div>
          </div>
        </div>
      </div>

      <!-- No Orders -->
      <div v-if="filteredOrders.length === 0" class="alert alert-info">No matching orders found.</div>

      <!-- 🔄 Load More -->
      <div v-if="offset < total" class="text-center mt-4">
        <button class="btn btn-outline-primary" :disabled="loadingMore" @click="loadMore">
          {{ loadingMore ? 'Loading...' : 'Load More' }}
        </button>
      </div>

      <!-- ✅ Toast -->
      <div
        v-if="toastMessage"
        class="position-fixed top-0 start-50 translate-middle-x p-3 w-100"
        style="z-index: 1055; max-width: 360px;"
      >
        <div class="toast show text-white bg-success d-flex align-items-center" role="alert">
          <div class="toast-body">{{ toastMessage }}</div>
          <button
            type="button"
            class="btn-close btn-close-white me-2 m-auto"
            @click="toastMessage = ''"
          ></button>
        </div>
      </div>
    </div>
  </SellerDashboardLayout>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import { router } from '@inertiajs/vue3'
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue'

const props = defineProps({
  orders: Array,
  total: Number,
  limit: Number,
  offset: Number,
  search: String,
})

// Refs
const search = ref(props.search ?? '')
const limit = ref(Number(props.limit) || 10)
const offset = ref(Number(props.offset) || 0)
const total = ref(Number(props.total) || 0)
const allOrders = ref([...props.orders])
const loadingMore = ref(false)
const statusFilter = ref('')
const toastMessage = ref('')
const deliveryDates = ref({})
const deliveryStatuses = ref({})

// Sync updates
watch(() => props.limit, val => limit.value = Number(val || 5))
watch(() => props.offset, val => offset.value = Number(val || 0))
watch(() => props.total, val => total.value = Number(val || 0))
watch(() => props.orders, val => {
  if (offset.value === 0) {
    allOrders.value = [...val]
  } else {
    allOrders.value.push(...val)
  }
  val.forEach(order => {
    deliveryStatuses.value[order.id] = order.delivery_status || 'pending'
  })
})

// Search handler
function handleSearch(reset = true) {
  const currentOffset = reset ? 0 : offset.value
  router.get('/seller/orders', {
    search: search.value,
    limit: limit.value,
    offset: currentOffset,
  }, {
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

// Load more
function loadMore() {
  handleSearch(false)
}

// Filtered
const filteredOrders = computed(() => {
  return allOrders.value.filter(order => {
    const term = search.value.toLowerCase()
    const matchSearch =
      order.user.name.toLowerCase().includes(term) ||
      order.product.name.toLowerCase().includes(term) ||
      order.status.toLowerCase().includes(term)
    const matchStatus = !statusFilter.value || order.status === statusFilter.value
    return matchSearch && matchStatus
  })
})

// Watch limit
watch(limit, () => {
  offset.value = 0
  handleSearch(true)
})

// Actions
const approve = (id) => {
  if (!deliveryDates.value[id]) {
    toastMessage.value = '❗ Please select a delivery date.'
    return
  }
  router.post(`/seller/orders/${id}/approve`, {
    delivery_date: deliveryDates.value[id]
  }, {
    onSuccess: () => {
      toastMessage.value = '✅ Order approved.'
    }
  })
}

const decline = (id) => {
  router.post(`/seller/orders/${id}/decline`, {}, {
    onSuccess: () => {
      toastMessage.value = '❌ Order declined.'
    }
  })
}

const updateDeliveryStatus = (id) => {
  router.post(`/seller/orders/${id}/delivery-status`, {
    delivery_status: deliveryStatuses.value[id]
  }, {
    onSuccess: () => {
      toastMessage.value = `🚚 Delivery status updated to "${deliveryStatuses.value[id]}".`
    }
  })
}
</script>
