<template>
  <SellerDashboardLayout>
    <div class="container">
      <h2 class="mb-4 text-center text-md-start"><i class="bi bi-box-seam me-2"></i> Orders Orders</h2>

      <!-- 🔍 Search & Filter -->
      <div class="row gy-2 gx-2 mb-3">
        <div class="col-12 col-md-4">
          <input
            v-model="search"
            @keyup.enter="handleSearch"
            class="form-control"
            placeholder="Search by buyer, product, or status"
          />
        </div>
        <div class="col-6 col-md-2">
          <button class="btn btn-success w-100" @click="handleSearch">Search</button>
        </div>
        <div class="col-12 col-md-3">
          <select v-model="statusFilter" class="form-select" @change="handleSearch">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="declined">Declined</option>
            <option value="canceled">Canceled</option>
          </select>
        </div>
      </div>

      <!-- 📋 Table View -->
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
            <tr v-for="order in orders.data" :key="order.id">
              <td>{{ order.user.first_name }}</td>
              <td>{{ order.product.name }}</td>
              <td>{{ order.quantity }}</td>
              <td>
                <span :class="{
                  'text-warning': order.status === 'pending',
                  'text-success': order.status === 'approved',
                  'text-danger': order.status === 'declined',
                  'text-secondary': order.status === 'canceled'
                }">{{ order.status }}</span>
              </td>
              <td>
                <div v-if="order.status === 'pending'">
                  <input type="date" v-model="deliveryDates[order.id]" class="form-control form-control-sm" />
                </div>
                <span v-else>{{ order.delivery_date ?? 'N/A' }}</span>
              </td>
              <td>
                <div v-if="order.status === 'approved'">
                  <select
                    class="form-select form-select-sm"
                    v-model="deliveryStatuses[order.id]"
                    @change="updateDeliveryStatus(order.id)"
                  >
                    <option value="pending">Pending</option>
                    <option value="delivered">Delivered</option>
                    <option value="failed">Failed</option>
                  </select>
                </div>
                <span v-else class="badge bg-secondary">
                  {{ order.delivery_status ?? 'N/A' }}
                </span>
              </td>
              <td>
                <div class="d-flex gap-1 justify-content-center flex-wrap">
                  <button v-if="order.status === 'pending'" @click="approve(order.id)" class="btn btn-sm btn-success">
                    Approve
                  </button>
                  <button v-if="order.status === 'pending'" @click="decline(order.id)" class="btn btn-sm btn-danger">
                    Decline
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- 📱 Mobile Card View -->
      <div class="d-block d-md-none">
        <div v-for="order in orders.data" :key="order.id" class="card mb-3 shadow-sm">
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
              }">{{ order.status }}</span>
            </p>
            <p>
              <strong>📅 Delivery Date:</strong>
              <span v-if="order.status !== 'pending'">{{ order.delivery_date ?? 'N/A' }}</span>
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
              <button v-if="order.status === 'pending'" @click="approve(order.id)" class="btn btn-sm btn-success">
                Approve
              </button>
              <button v-if="order.status === 'pending'" @click="decline(order.id)" class="btn btn-sm btn-danger">
                Decline
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ❗ Empty State -->
      <div v-if="orders.data.length === 0" class="alert alert-info">No matching orders found.</div>

      <!-- 🔢 Pagination -->
      <nav v-if="orders.links.length > 3" class="mt-4">
        <ul class="pagination justify-content-center flex-wrap gap-1">
          <li
            v-for="(link, index) in orders.links"
            :key="index"
            class="page-item"
            :class="{ active: link.active, disabled: !link.url }"
          >
            <Link
              class="page-link"
              :href="link.url || ''"
              v-html="link.label"
              preserve-scroll
              preserve-state
              style="min-width: 40px; text-align: center;"
            />
          </li>
        </ul>
      </nav>

      <!-- ✅ Toast with Bootstrap Icons -->
      <div
        v-if="toastMessage"
        class="position-fixed top-0 start-50 translate-middle-x p-3 w-100"
        style="z-index: 1055; max-width: 360px;"
      >
        <div class="toast show text-white bg-success d-flex align-items-center" role="alert">
          <div class="toast-body">
            <span v-html="toastMessage"></span>
          </div>
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
import { ref, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue'

const props = defineProps({
  orders: Object,
  filters: Object,
})

const search = ref(props.filters.search || '')
const statusFilter = ref(props.filters.status || '')
const deliveryDates = ref({})
const deliveryStatuses = ref({})
const toastMessage = ref('')

// ✅ Auto-populate delivery statuses
watch(
  () => props.orders,
  (newOrders) => {
    deliveryStatuses.value = {}
    newOrders.data.forEach(order => {
      deliveryStatuses.value[order.id] = order.delivery_status || 'pending'
    })
  },
  { immediate: true }
)

const handleSearch = () => {
  router.get('/seller/orders', {
    search: search.value,
    status: statusFilter.value,
  }, {
    preserveScroll: true,
    preserveState: true,
  })
}

// ✅ Approve Order
const approve = (id) => {
  if (!deliveryDates.value[id]) {
    toastMessage.value = '<i class="bi bi-exclamation-circle-fill me-2"></i>Please select a delivery date.'
    return
  }

  router.post(`/seller/orders/${id}/approve`, {
    delivery_date: deliveryDates.value[id],
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toastMessage.value = '<i class="bi bi-check-circle-fill me-2"></i>Order approved.'
    }
  })
}

// ✅ Decline Order
const decline = (id) => {
  router.post(`/seller/orders/${id}/decline`, {}, {
    preserveScroll: true,
    onSuccess: () => {
      toastMessage.value = '<i class="bi bi-x-circle-fill me-2"></i>Order declined.'
    }
  })
}

// ✅ Update Delivery Status
const updateDeliveryStatus = (id) => {
  const status = deliveryStatuses.value[id]
  const iconMap = {
    pending: 'bi-box-seam',
    delivered: 'bi-truck',
    failed: 'bi-exclamation-triangle-fill',
  }

  router.post(`/seller/orders/${id}/delivery-status`, {
    delivery_status: status,
  }, {
    preserveScroll: true,
    onSuccess: () => {
      toastMessage.value = `<i class="bi ${iconMap[status]} me-2"></i>Delivery status updated to "${status}".`
    }
  })
}
</script>

<style scoped>
input.form-control:focus {
  border-color: #28a745; /* green */
  box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.5); /* green with 50% opacity */
}
.pagination .page-link {
  color: #28a745; /* green */
  background-color: rgb(255, 255, 255);
  border-color: #28a745; /* green */
}
.pagination .page-link:hover {
  color: white;
  background-color: #28a745; /* green */
  border-color: #ffffff;
}
.pagination .page-link:focus {
  border-color: black;
  box-shadow: 0 0 0 0.25rem rgba(0, 0, 0, 0.5); /* black with 50% opacity */
}
</style>
