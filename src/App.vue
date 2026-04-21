<template>
  <div class="app-container">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top py-3">
      <div class="container">
        <router-link class="navbar-brand fw-bold text-primary fs-4" to="/home">🌍 ATTRACTION</router-link>

        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
            <li class="nav-item">
              <router-link class="nav-link px-3 fw-semibold" to="/home">HOME</router-link>
            </li>
            
            <li class="nav-item px-2" v-if="userRole !== 'employee'">
              <router-link class="nav-link fw-semibold" to="/attraction_list">TOURING</router-link>
            </li>

            <li class="nav-item dropdown px-2" v-else>
              <a class="nav-link dropdown-toggle fw-semibold" href="#" role="button" data-bs-toggle="dropdown">
                TOURING
              </a>
              <ul class="dropdown-menu border-0 shadow-lg mt-2">
                <li>
                  <router-link class="dropdown-item py-2" to="/attraction_list">
                    <i class="bi bi-eye me-2"></i>View All Tours
                  </router-link>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                  <router-link class="dropdown-item py-2" to="/categories_crud">
                    <i class="bi bi-grid me-2"></i>Manage Categories
                  </router-link>
                </li>
                <li>
                  <router-link class="dropdown-item py-2" to="/product_crud">
                    <i class="bi bi-pencil-square me-2"></i>Manage Touring
                  </router-link>
                </li>
              </ul>
            </li>

            <li class="nav-item" v-if="userRole === 'employee'">
              <router-link class="nav-link fw-semibold px-3" to="/employee_crud">EMPLOYEE</router-link>
            </li>

            <li class="nav-item" v-if="userRole === 'employee'">
              <router-link class="nav-link fw-semibold px-3" to="/customer_crud">CUSTOMER</router-link>
            </li>

            <li class="nav-item px-2" v-if="userRole === 'customer'">
              <router-link class="nav-link fw-semibold" to="/mybooking">MY BOOKING</router-link>
            </li>
            
            <li class="nav-item px-2">
              <router-link class="nav-link fw-semibold" to="/contact">CONTACT</router-link>
            </li>
          </ul>
          
          <div class="d-flex align-items-center gap-2">
            <div v-if="!isLoggedIn" class="d-flex gap-2">
              <router-link to="/clogin" class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm">Login</router-link>
              <router-link to="/elogin" class="btn btn-outline-secondary px-3 rounded-pill fw-bold">Staff</router-link>
            </div>

            <div v-else class="d-flex align-items-center gap-3">
              <div class="profile-capsule d-flex align-items-center ps-3 pe-1 py-1">
                <div class="user-info-text d-flex flex-column me-3">
                  <span class="user-name">{{ userName }}</span>
                  <span class="user-role" :class="userRole">{{ userRole }}</span>
                </div>
                <div class="avatar-wrapper" :class="userRole">{{ userName.charAt(0).toUpperCase() }}</div>
              </div>
              <button @click="logout" class="btn-logout-minimal"><i class="bi bi-box-arrow-right"></i></button>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <div class="container py-5">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </div>
    </main>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isLoggedIn: false,
      userName: "",
      userRole: "",
      categories: []
    }
  },
  mounted() {
    this.checkLogin();
  },
  methods: {
    checkLogin() {
      const userJSON = localStorage.getItem("user");
      if (userJSON) {
        this.isLoggedIn = true;
        const user = JSON.parse(userJSON);
        this.userName = user?.name || "User";
        this.userRole = user?.role || "";
      } else {
        this.isLoggedIn = false;
        this.userName = "";
        this.userRole = ""; // Reset role เมื่อไม่ได้ล็อกอิน
      }
    },
    logout() {
      localStorage.clear();
      this.isLoggedIn = false;
      this.userRole = "";
      this.$router.push("/clogin");
    }
  },
  watch: {
    '$route'() {
      this.checkLogin();
    }
  }
}
</script>

<style scoped>
/* CSS เดิมของคุณสวยแล้วค่ะ คงไว้ได้เลย */
.app-container { min-height: 100vh; background-color: #f8f9fa; }
.main-content { padding-top: 85px; }
.profile-capsule { background: #ffffff; border: 1px solid #edf2f7; border-radius: 50px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05); }
.user-name { font-size: 0.95rem; font-weight: 700; color: #2d3748; }
.user-role { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; }
.user-role.employee { color: #3182ce; }
.user-role.customer { color: #38a169; }
.avatar-wrapper { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: white; }
.avatar-wrapper.employee { background: linear-gradient(135deg, #63b3ed 0%, #3182ce 100%); }
.avatar-wrapper.customer { background: linear-gradient(135deg, #68d391 0%, #38a169 100%); }
.btn-logout-minimal { padding: 8px 12px; border: none; background: #fff5f5; color: #e53e3e; border-radius: 50px; cursor: pointer; transition: 0.2s; }
.btn-logout-minimal:hover { background: #e53e3e; color: white; }
.fade-enter-active, .fade-leave-active { transition: opacity 0.15s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }
</style>