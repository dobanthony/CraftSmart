<template>
  <AdminDashboardLayout>
    <div class="container py-4">
      <h2 class="mb-4 text-primary">📊 Admin Dashboard</h2>

      <!-- 🔢 Top Metric Cards -->
      <div class="row g-4 mb-4">
        <div
          class="col-12 col-sm-6 col-lg-4"
          v-for="card in cards"
          :key="card.label"
        >
          <div class="card shadow border-start border-4 h-100" :class="card.border">
            <div class="card-body d-flex align-items-center">
              <div class="display-5 me-3">{{ card.icon }}</div>
              <div>
                <h6 class="text-muted mb-1">{{ card.label }}</h6>
                <h4 class="fw-bold mb-0 text-dark">{{ card.count }}</h4>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 📅 Chart Filter -->
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">📊 Chart Filters</h5>
        <select v-model="selectedFilter" class="form-select w-auto" @change="applyFilter">
          <option value="month">This Month</option>
          <option value="week">This Week</option>
          <option value="year">This Year</option>
        </select>
      </div>

      <!-- 📈 Charts -->
      <div class="row g-4">
        <!-- 💰 Sales Chart -->
        <div class="col-12">
          <div class="card shadow">
            <div class="card-body">
              <h5 class="text-success">💰 Sales</h5>
              <div class="chart-container">
                <Line :data="salesChartData" :options="chartOptions" />
              </div>
            </div>
          </div>
        </div>

        <!-- 🛒 Orders Chart -->
        <div class="col-12">
          <div class="card shadow">
            <div class="card-body">
              <h5 class="text-primary">🛒 Orders</h5>
              <div class="chart-container">
                <Bar :data="orderChartData" :options="chartOptions" />
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 🔥 Bottom Metric Cards -->
      <div class="row g-4 mt-4">
        <div
          class="col-md-4"
          v-for="metric in bottomMetrics"
          :key="metric.label"
        >
          <div class="card shadow border-start border-4 h-100" :class="metric.border">
            <div class="card-body">
              <h6 class="text-muted">{{ metric.label }}</h6>
              <h5 class="fw-bold mb-0" :class="metric.text">
                {{ metric.value || 'N/A' }}
              </h5>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AdminDashboardLayout>
</template>

<script setup>
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue'
import { defineProps, computed, ref } from 'vue'
import { router } from '@inertiajs/vue3'
import {
  Chart as ChartJS,
  Title,
  Tooltip,
  Legend,
  BarElement,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Filler
} from 'chart.js'
import { Bar, Line } from 'vue-chartjs'

ChartJS.register(
  Title,
  Tooltip,
  Legend,
  BarElement,
  LineElement,
  PointElement,
  CategoryScale,
  LinearScale,
  Filler
)

const props = defineProps({
  metrics: {
    type: Object,
    default: () => ({})
  },
  monthlyStats: {
    type: Array,
    default: () => []
  },
  topMetrics: {
    type: Object,
    default: () => ({})
  }
})

const selectedFilter = ref('month')
function applyFilter() {
  router.get(route('admin.dashboard'), { filter: selectedFilter.value }, { preserveScroll: true })
}

// 💳 Top Metric Cards
const cards = computed(() => [
  { label: 'Total Orders', count: props.metrics.totalOrders, icon: '🛒', border: 'border-primary' },
  { label: 'Total Users', count: props.metrics.totalUsers, icon: '👤', border: 'border-info' },
  { label: 'Total Sellers', count: props.metrics.totalSellers, icon: '🧑‍🎨', border: 'border-success' },
  { label: 'Total Announcements', count: props.metrics.totalAnnouncements, icon: '📢', border: 'border-secondary' },
  { label: 'Total Products Listed', count: props.metrics.totalProducts, icon: '📦', border: 'border-warning' },
  { label: 'Pending Seller Applications', count: props.metrics.pendingSellerApplications, icon: '🧾', border: 'border-danger' },
])

// 🔥 Bottom Metric Cards
const bottomMetrics = computed(() => [
  {
    label: '🏪 Top Shop',
    value: props.topMetrics.topShop,
    border: 'border-primary',
    text: 'text-primary',
  },
  {
    label: '🧍 Top Customer',
    value: props.topMetrics.topCustomer,
    border: 'border-success',
    text: 'text-success',
  },
  {
    label: '🛍️ Top Selling Product',
    value: props.topMetrics.topProduct,
    border: 'border-danger',
    text: 'text-danger',
  },
])

// Chart Labels
const labels = (props.monthlyStats || []).map(stat => stat.month)

// Chart Data
const orderChartData = {
  labels,
  datasets: [
    {
      label: 'Orders',
      backgroundColor: '#0d6efd',
      data: (props.monthlyStats || []).map(stat => stat.orders),
    },
  ],
}

const salesChartData = {
  labels,
  datasets: [
    {
      label: 'Sales (₱)',
      borderColor: '#198754',
      backgroundColor: 'rgba(25, 135, 84, 0.2)',
      data: (props.monthlyStats || []).map(stat => stat.sales),
      tension: 0.4,
      fill: true,
    },
  ],
}

// Chart Options
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: true },
    title: { display: false },
    tooltip: {
      callbacks: {
        label: function (context) {
          const value = context.raw
          return context.dataset.label + ': ₱' + value.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          })
        }
      }
    }
  },
  scales: {
    y: {
      ticks: {
        callback: function (value) {
          return '₱' + value.toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
          })
        }
      }
    }
  }
}
</script>

<style scoped>
.chart-container {
  position: relative;
  width: 100%;
  height: 300px;
}
</style>
