<script setup>
import DashboardLayout from '@/Layouts/DashboardLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { defineProps } from 'vue'

const props = defineProps({
  announcements: {
    type: Array,
    default: () => [],
  }
})

// Format date to "June 30, 2025, 5:30 PM"
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

// Truncate long content
function truncateContent(content, maxLength = 120) {
  if (content.length <= maxLength) return content
  return content.slice(0, maxLength) + '...'
}
</script>

<template>
  <Head title="Dashboard" />

  <DashboardLayout>
    <div class="container py-5">
      <h1 class="fw-bold mb-4">User Dashboard</h1>

      <!-- ✅ Card: Update Profile -->
      <div class="card shadow-sm border-0 mb-5">
        <div class="card-header bg-primary text-white">
          Update Your Profile Information
        </div>
        <div class="card-body">
          <p class="mb-3">
            Keep your account information up-to-date. You can edit your name, email, and password here.
          </p>
          <Link href="/profile" class="btn btn-outline-primary">
            Go to Profile Settings
          </Link>
        </div>
      </div>

      <!-- 📢 Latest Announcements -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">📢 Latest Announcements</h2>
      </div>

      <!-- No Announcements -->
      <div v-if="announcements.length === 0" class="alert alert-secondary text-center shadow-sm">
        No announcements have been posted yet.
      </div>

      <!-- ✅ Responsive Announcement Grid: 2 on mobile, 4 on desktop -->
      <div class="row row-cols-2 row-cols-lg-4 g-4">
        <div
          v-for="announcement in announcements"
          :key="announcement.id"
          class="col"
        >
          <div class="card h-100 border-0 shadow-sm announcement-card">
            <div class="card-body d-flex flex-column justify-content-between">
              <div>
                <h5 class="card-title text-primary fw-semibold">
                  {{ announcement.title }}
                </h5>
                <p class="card-text text-muted">
                  {{ truncateContent(announcement.content) }}
                </p>
              </div>
              <div class="mt-3">
                <span class="badge bg-light text-secondary">
                  📅 {{ formatDate(announcement.created_at) }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </DashboardLayout>
</template>

<style scoped>
.announcement-card:hover {
  transform: translateY(-3px);
  transition: transform 0.2s ease, box-shadow 0.2s ease;
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05);
}

.card-title {
  font-size: 1.1rem;
}

.card-text {
  font-size: 0.95rem;
  line-height: 1.4;
}
</style>
