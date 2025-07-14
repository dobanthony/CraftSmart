<template>
  <SellerDashboardLayout>
    <div class="container">

      <div v-if="Object.keys(groupedMessages).length">
        <div
          v-for="(conversation, userId) in groupedMessages"
          :key="userId"
          class="border rounded mb-5 p-4 bg-white shadow-sm"
        >
          <h5 class="mb-4">Chat with {{ conversation.user_name }}</h5>

          <!-- 💬 Chat Box -->
          <div class="chat-box mb-3 p-3 bg-light rounded overflow-auto">
            <div
              v-for="(message, index) in conversation.messages"
              :key="message.id"
              class="d-flex mb-3"
              :class="message.sender.id === shop.user_id ? 'justify-content-end' : 'justify-content-start'"
            >
              <div
                class="d-flex flex-column"
                :class="message.sender.id === shop.user_id ? 'align-items-end' : 'align-items-start'"
              >
                <img
                  :src="message.sender.id === shop.user_id
                    ? `/storage/${shop.shop_logo}`
                    : (message.sender.avatar ? `/storage/${message.sender.avatar}` : '/images/default-user.png')"
                  class="rounded-circle me-2"
                  style="width: 40px; height: 40px; object-fit: cover;"
                />

                <div
                  class="p-2 rounded"
                  :class="message.sender.id === shop.user_id ? 'bg-primary text-white' : 'bg-white border'"
                  style="max-width: 70%; min-width: 80px;"
                >
                  <div class="fw-bold small mb-1">
                    {{ message.sender.id === shop.user_id ? 'You' : message.sender.name }}
                  </div>
                  <div>{{ message.message }}</div>
                </div>

                <div class="text-muted small mt-1">
                  {{ formatTime(message.created_at) }}
                  <span v-if="message.sender.id === shop.user_id">
                    • {{ message.is_read ? 'Delivered' : 'Sent' }}
                  </span>
                </div>

                <div
                  v-if="message.sender.id === shop.user_id && isLastOwnMessage(conversation.messages, index) && message.is_read"
                  class="text-success small mt-1"
                >
                  ✔ Seen {{ formatSeenTime(message.updated_at || message.created_at) }}
                </div>
              </div>
            </div>
          </div>

          <!-- ✉️ Reply Form -->
          <form @submit.prevent="sendReply(conversation)">
            <div class="d-flex mt-2">
              <input
                v-model="conversation.reply"
                type="text"
                class="form-control me-2"
                placeholder="Type your reply..."
              />
              <button class="btn btn-primary">Send</button>
            </div>
          </form>
        </div>
      </div>

      <div v-else>
        <p class="text-muted">No messages from customers yet.</p>
      </div>
    </div>
  </SellerDashboardLayout>
</template>

<script setup>
import SellerDashboardLayout from '@/Layouts/SellerDashboardLayout.vue'
import { reactive, onMounted, onBeforeUnmount } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { defineProps } from 'vue'

const { shop, messages } = defineProps({
  shop: Object,
  messages: Array
})

const groupedMessages = reactive({})
let interval = null

// ✅ Group messages and preserve reply inputs
function groupAllMessages() {
  const prevReplies = {}

  for (const userId in groupedMessages) {
    prevReplies[userId] = groupedMessages[userId].reply
  }

  Object.keys(groupedMessages).forEach((key) => delete groupedMessages[key])

  messages.forEach((msg) => {
    const userId = msg.sender.id !== shop.user_id ? msg.sender.id : msg.receiver.id

    if (!groupedMessages[userId]) {
      groupedMessages[userId] = {
        user_id: userId,
        user_name: msg.sender.id !== shop.user_id ? msg.sender.name : msg.receiver.name,
        messages: [],
        reply: prevReplies[userId] || '',
      }
    }

    groupedMessages[userId].messages.push(msg)
  })
}
groupAllMessages()

// ✅ Send a reply and reload messages
function sendReply(conversation) {
  if (!conversation.reply.trim()) return

  router.post('/messages/send', {
    shop_id: shop.id,
    message: conversation.reply,
    receiver_id: conversation.user_id,
  }, {
    onSuccess: () => {
      conversation.reply = ''
      router.reload({
        only: ['messages'],
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => groupAllMessages()
      })
    }
  })
}

// ✅ Utility: Format time
function formatTime(datetime) {
  const date = new Date(datetime)
  return date.toLocaleString('en-PH', {
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
    month: 'short',
    day: 'numeric'
  })
}

// ✅ Utility: Seen time
function formatSeenTime(datetime) {
  const date = new Date(datetime)
  return date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', hour12: true })
}

// ✅ Check if message is the last own sent message
function isLastOwnMessage(messages, index) {
  const ownMessages = messages.filter(m => m.sender.id === shop.user_id)
  return ownMessages.length && messages[index].id === ownMessages.at(-1).id
}

// 🔁 Auto-refresh every 3s
onMounted(() => {
  interval = setInterval(() => {
    router.reload({
      only: ['messages'],
      preserveScroll: true,
      preserveState: true,
      onSuccess: () => groupAllMessages()
    })
  }, 3000)
})

onBeforeUnmount(() => {
  clearInterval(interval)
})
</script>

<style scoped>
.chat-box {
  max-height: 400px;
}
</style>
