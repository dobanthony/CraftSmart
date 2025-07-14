<template>
  <AdminDashboardLayout>
    <div class="container py-4">
      <!-- Page Title -->
      <h2 class="mb-4 fw-bold">👤 Profile Overview</h2>

      <!-- Profile Card -->
      <div class="card shadow-sm p-4 border-0">
        <div class="d-flex flex-column align-items-start mb-4">
          <!-- Avatar -->
          <img
            :src="user.avatar ? `/storage/${user.avatar}` : defaultAvatar"
            alt="User Avatar"
            class="rounded-circle border mb-3"
            style="width: 100px; height: 100px; object-fit: cover;"
          />

          <!-- Full Name -->
          <h4 class="mb-1">
            {{ user.first_name }}
            <template v-if="user.middle_name"> {{ user.middle_name }} </template>
            {{ user.last_name }}
          </h4>

          <!-- Role -->
          <span class="badge bg-primary text-capitalize">{{ user.role }}</span>
        </div>

        <!-- Info Section -->
        <div class="border-top pt-3">
          <!-- Email -->
          <div class="mb-3">
            <small class="text-muted">📧 Email</small>
            <p class="mb-0">{{ user.email }}</p>
          </div>

          <!-- Phone -->
          <div class="mb-3">
            <small class="text-muted">📱 Phone Number</small>
            <p class="mb-0">{{ user.phone || '—' }}</p>
          </div>

          <!-- Address -->
          <div class="mb-3">
            <small class="text-muted">🏠 Address</small>
            <p class="mb-0">{{ user.address || '—' }}</p>
          </div>

          <!-- Date of Birth -->
          <div class="mb-3">
            <small class="text-muted">🎂 Date of Birth</small>
            <p class="mb-0">
              {{ user.dob ? formatDate(user.dob) : '—' }}
            </p>
          </div>

          <!-- Social Links -->
          <div class="mb-3" v-if="user.social_links?.facebook">
            <small class="text-muted">🔗 Facebook</small>
            <p class="mb-0">
              <a
                :href="user.social_links.facebook"
                class="text-decoration-none"
                target="_blank"
              >
                {{ user.social_links.facebook }}
              </a>
            </p>
          </div>

          <div class="mb-3" v-if="user.social_links?.twitter">
            <small class="text-muted">🔗 Twitter</small>
            <p class="mb-0">
              <a
                :href="user.social_links.twitter"
                class="text-decoration-none"
                target="_blank"
              >
                {{ user.social_links.twitter }}
              </a>
            </p>
          </div>

          <!-- Registration Date -->
          <div class="mb-3">
            <small class="text-muted">🗓️ Registered At</small>
            <p class="mb-0">{{ formatDate(user.created_at) }}</p>
          </div>
        </div>
      </div>

      <!-- Back Button -->
      <div class="mt-4">
        <Link href="/admin/users" class="btn btn-outline-secondary">
          ← Back to Users
        </Link>
      </div>
    </div>
  </AdminDashboardLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import { defineProps } from 'vue'
import AdminDashboardLayout from '@/Layouts/AdminDashboardLayout.vue'

const props = defineProps({
  user: Object,
})

const defaultAvatar = '/images/default-avatar.jpg'

function formatDate(date) {
  const d = new Date(date)
  return d.toLocaleString('en-PH', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: 'numeric',
    minute: '2-digit',
    hour12: true,
  })
}
</script>
