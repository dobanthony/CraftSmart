<template>
  <AdminDashboardLayout>
    <div class="container">
      <h4 class="mb-4"><i class="bi bi-send"></i> Seller Applications</h4>

      <div v-if="applications.length === 0" class="alert alert-info">
        No applications pending.
      </div>

      <div
        v-for="user in applications"
        :key="user.id"
        class="card mb-3 shadow-sm"
      >
        <div class="card-body">
          <h5 class="card-title">{{ user.name }} ({{ user.email }})</h5>
          <p class="card-text">
            <strong>Reason:</strong> {{ user.application_reason }}
          </p>

          <div class="d-flex gap-2">
            <!-- ✅ Approve Form with CSRF -->
            <form
              :action="`/admin/seller-applications/${user.id}/approve`"
              method="POST"
            >
              <input type="hidden" name="_token" :value="csrf" />
              <button class="btn btn-success btn-sm">Approve</button>
            </form>

            <!-- ✅ Decline Form with CSRF -->
            <form
              :action="`/admin/seller-applications/${user.id}/decline`"
              method="POST"
            >
              <input type="hidden" name="_token" :value="csrf" />
              <button class="btn btn-danger btn-sm">Decline</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AdminDashboardLayout>
</template>

<script setup>
//CSRF token from meta tag (required for form POSTs)
const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content')

defineProps(['applications'])
</script>
