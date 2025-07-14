<template>
  <div>
    <!-- Sidebar -->
    <nav id="sidebar" class="p-3 text-white" :class="{ show: sidebarOpen }">
      <form class="mb-3">
        <input type="text" class="form-control form-control-sm" placeholder="Search..." />
      </form>

      <ul class="nav flex-column mb-3">
        <li class="nav-item">
          <Link
            :href="route('seller.analytics')"
            class="nav-link"
            :class="{ active: isActive('seller.analytics') }"
          >
            Dashboard
          </Link>
        </li>


        <!-- <li class="nav-item">
          <Link href="/dashboard" class="nav-link" :class="{ active: page.url === '/dashboard' }">Dashboard</Link>
        </li> -->
        <!-- <li class="nav-item"><Link href="/seller/orders" class="nav-link">Orders</Link></li> -->
         <li class="nav-item position-relative">
  <Link href="/seller/orders" class="nav-link d-flex align-items-center">
    Orders
    <span
      v-if="pendingOrdersCount > 0"
      class="badge bg-danger ms-2"
      style="font-size: 0.7rem;"
    >
      {{ pendingOrdersCount }}
    </span>
  </Link>
</li>

        <li class="nav-item"><Link :href="route('seller.products.index')" class="nav-link">Products</Link></li>
        <li class="nav-item"><Link :href="route('seller.shop')" class="nav-link">My Shop</Link></li>


        <Link href="/seller/notifications" class="nav-link d-flex align-items-center">
  🔔 Notifications
  <span
    v-if="store.unreadCount > 0"
    class="badge bg-danger ms-1"
    style="font-size: 0.7rem;"
  >
    {{ store.unreadCount }}
  </span>
</Link>



      <!-- 📨 Inbox Link -->
      <!-- <Link
        href="/seller/inbox"
        class="nav-link text-white"
      >
        📩 Inbox
      </Link> -->


      <li class="nav-item position-relative">
  <Link
    href="/seller/inbox"
    class="nav-link d-flex align-items-center text-white"
    @click="inboxClicked = true"
  >
    📩 Inbox
    <span
      v-if="!inboxClicked && unreadMessagesCount > 0"
      class="badge bg-danger ms-2"
      style="font-size: 0.7rem;"
    >
      {{ unreadMessagesCount }}
    </span>
  </Link>
</li>






      
      </ul>
      <hr class="bg-secondary" />
    </nav>

    <!-- Desktop Sidebar Toggle -->
    <button
      id="hideSidebarBtn"
      v-show="!isMobile && sidebarOpen"
      @click="closeSidebar"
      title="Hide Sidebar"
    >
      &#8592;
    </button>
    <button
      id="showSidebarBtn"
      v-show="!isMobile && !sidebarOpen"
      @click="openSidebar"
      title="Show Sidebar"
    >
      &#8594;
    </button>

    <!-- Topbar -->
    <nav id="topbar" class="d-flex justify-content-between align-items-center px-3 shadow-sm">
      <div class="d-flex align-items-center gap-3">
        <button id="toggleArrow" class="btn p-1" @click="openSidebar">&#9776;</button>
        <h4 class="fw-bold text-success">CraftSmart</h4>
      </div>

      <!-- Profile Avatar Dropdown -->
      <div class="position-relative">
        <img
          :src="user?.avatar ? `/storage/${user.avatar}` : getDefaultAvatar"

          class="avatar cursor-pointer"
          @click="toggleProfileModal"
        />

        <!-- Custom Profile Modal -->
        <div v-show="profileModalOpen" class="profile-modal shadow rounded bg-white">
          <div class="d-flex align-items-center gap-3 p-3 border-bottom">
            <img
              :src="user?.avatar ? `/storage/${user.avatar}` : getDefaultAvatar"
              class="avatar-lg"
            />
            <div>
              <div class="fw-bold">Hi, {{ $page.props.auth?.user?.name ?? 'N/A' }}</div>
              <div class="text-muted small">{{ $page.props.auth?.user?.email ?? 'N/A' }}</div>
              <div class="text-primary small bg-light px-2 py-1 rounded d-inline">{{ $page.props.auth?.user?.role ?? 'N/A' }}</div>
              
            </div>
          </div>
          <div>
            <Link class="dropdown-item" href="/profile">My Profile</Link>
            <Link class="dropdown-item" href="/logout" method="post" as="button">Log Out</Link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Overlay for mobile sidebar -->
    <div id="overlay" :class="{ show: isMobile && sidebarOpen }" @click="closeSidebar"></div>

    <!-- Main Content -->
    <main id="content" :class="{ shifted: sidebarOpen && !isMobile }">
      <slot />
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch  } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'
import { useNotificationStore } from '@/stores/notification'
import { computed } from 'vue'

// ✅ Access Inertia props
const page = usePage()

// Inbox click state
const inboxClicked = ref(false)

// Get unread count from Inertia shared props
const unreadMessagesCount = computed(() => {
  return page.props.unreadMessagesCount ?? 0
})


// Automatically hide badge when already on inbox page
onMounted(() => {
  if (page.url.startsWith('/seller/inbox')) {
    inboxClicked.value = true
  }
})

const pendingOrdersCount = computed(() => page.props.pendingOrdersCount ?? 0)



// ✅ Use Pinia notification store
const store = useNotificationStore()

// ✅ Set notifications on mount
onMounted(() => {
  if (page.props.notifications) {
    store.setNotifications(page.props.notifications)
  }
})

// ✅ Watch for changes when Inertia updates props (like after form submissions)
watch(
  () => page.props.notifications,
  (newNotifications) => {
    if (newNotifications) {
      store.setNotifications(newNotifications)
    }
  }
)

function isActive(routeName) {
  return page.component.includes(routeName)
}


const getDefaultAvatar = '/images/default-avatar.jpg'

// const page = usePage()
const user = page.props.auth?.user

const sidebarOpen = ref(false)
const isMobile = ref(false)
const profileModalOpen = ref(false)


const openSidebar = () => {
  sidebarOpen.value = true
}
const closeSidebar = () => {
  sidebarOpen.value = false
}

const toggleProfileModal = () => {
  profileModalOpen.value = !profileModalOpen.value
}

const handleResize = () => {
  isMobile.value = window.innerWidth < 768
  sidebarOpen.value = !isMobile.value
}

const closeProfileModal = (e) => {
  if (!e.target.closest('.profile-modal') && !e.target.closest('.avatar')) {
    profileModalOpen.value = false
  }
}

onMounted(() => {
  handleResize()
  window.addEventListener('resize', handleResize)
  document.addEventListener('click', closeProfileModal)
})

onUnmounted(() => {
  window.removeEventListener('resize', handleResize)
  document.removeEventListener('click', closeProfileModal)
})

function logout() {
  router.post('/logout')
}
</script>

<style scoped>
body {
  overflow-x: hidden;
}

#sidebar {
  width: 250px;
  height: 100vh;
  background-color: #343a40;
  position: fixed;
  top: 0;
  left: -250px;
  z-index: 1060;
  transition: left 0.3s ease;
}

#sidebar.show {
  left: 0;
}

#sidebar .nav-link {
  color: #ffffff;
}

#sidebar .nav-link.active {
  background-color: #495057;
}

#topbar {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 60px;
  background-color: #ffffff;
  border-bottom: 1px solid #dee2e6;
  z-index: 1050;
}

.logo {
  font-size: 1.25rem;
  font-weight: 600;
}

.avatar {
  border-radius: 50%;
  width: 40px;
  height: 40px;
  object-fit: cover;
  cursor: pointer;
}

.avatar-lg {
  border-radius: 50%;
  width: 48px;
  height: 48px;
  object-fit: cover;
}

.profile-modal {
  position: absolute;
  top: 55px;
  right: 0;
  width: 260px;
  z-index: 9999;
  border: 1px solid #dee2e6;
}

.profile-modal .dropdown-item {
  padding: 10px 16px;
  cursor: pointer;
  transition: background 0.2s;
}

.profile-modal .dropdown-item:hover {
  background-color: #f8f9fa;
}

#overlay {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.5);
  z-index: 1040;
}

#overlay.show {
  display: block;
}

#toggleArrow {
  border: none;
  background: transparent;
  font-size: 1.5rem;
}

#hideSidebarBtn,
#showSidebarBtn {
  display: none;
  position: fixed;
  top: 10px;
  z-index: 1065;
  background-color: #343a40;
  color: white;
  border: none;
  padding: 4px 10px;
  font-size: 1.25rem;
}

#hideSidebarBtn {
  left: 250px;
  border-radius: 0 5px 5px 0;
}

#showSidebarBtn {
  left: -20px;
  border-radius: 0 5px 5px 0;
  box-shadow: 2px 0 5px rgba(0, 0, 0, 0.3);
  opacity: 0.9;
}

#content {
  padding: 1.5rem;
  margin-top: 70px;
  transition: margin-left 0.3s ease;
}

#content.shifted {
  margin-left: 250px;
}

@media (min-width: 768px) {
  #hideSidebarBtn,
  #showSidebarBtn {
    display: block;
  }

  #toggleArrow {
    display: none !important;
  }
}
</style>