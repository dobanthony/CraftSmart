<template>
  <div class="mb-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">Upload Profile Photo</h2>

    <form @submit.prevent="submitAvatar" enctype="multipart/form-data" class="space-y-4">
      <div class="flex items-center gap-4">
        <img
          :src="user.avatar ? `/storage/${user.avatar}` : getDefaultAvatar"
          :class="avatarClass"
          class="w-20 h-20 rounded-full object-cover"
        />

        <input
          type="file"
          @change="handleFileChange"
          accept="image/*"
          class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:bg-indigo-600 file:text-white hover:file:bg-indigo-700"
        />
      </div>

      <button
        type="submit"
        :disabled="form.processing"
        class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
      >
        Upload
      </button>
    </form>
  </div>
</template>

<script setup>
import { usePage, useForm } from '@inertiajs/vue3'
import { computed } from 'vue'

const user = usePage().props.auth.user

const form = useForm({
  avatar: null,
})

const handleFileChange = (e) => {
  form.avatar = e.target.files[0]
}

const submitAvatar = () => {
  form.post(route('profile.avatar'), {
    preserveScroll: true,
  })
}

const getDefaultAvatar = computed(() => {
  switch (user.role) {
    case 'admin':
      return '/images/default-admin.png'
    case 'seller':
      return '/images/default-seller.png'
    case 'buyer':
      return '/images/default-buyer.png'
    default:
      return '/images/default.png'
  }
})

const avatarClass = computed(() => {
  switch (user.role) {
    case 'admin':
      return 'ring-4 ring-red-500'
    case 'seller':
      return 'ring-4 ring-green-500'
    case 'buyer':
      return 'ring-4 ring-blue-500'
    default:
      return 'ring-2 ring-gray-300'
  }
})
</script>
