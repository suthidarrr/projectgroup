<template>
  <div class="app-container">
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm fixed-top py-3 transition-nav">
      <div class="container">
        <router-link class="navbar-brand fw-bold text-primary fs-4 d-flex align-items-center gap-2" to="/home">
          <i class="bi bi-geo-alt-fill text-primary"></i> PAI TIEW GUN
        </router-link>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="mainNav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4">
            <li class="nav-item">
              <router-link class="nav-link px-3 fw-semibold d-flex align-items-center gap-2" to="/home">
              HOME
              </router-link>
            </li>
            
            <li class="nav-item px-2" v-if="userRole !== 'employee'">
              <router-link class="nav-link fw-semibold d-flex align-items-center gap-2" to="/attraction_list">
                TOURING
              </router-link>
            </li>

            <li class="nav-item dropdown px-2" v-else>
              <a class="nav-link dropdown-toggle fw-semibold d-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown">
                MANAGEMENT
              </a>
              <ul class="dropdown-menu border-0 shadow-lg mt-2 py-2 rounded-4">
                <li>
                  <router-link class="dropdown-item py-2 px-3 fw-medium" to="/attraction_list">
                    <i class="bi bi-eye text-primary me-2"></i> View All Tours
                  </router-link>
                </li>
                <li><hr class="dropdown-divider opacity-25"></li>
                <li>
                  <router-link class="dropdown-item py-2 px-3 fw-medium" to="/categories_crud">
                    <i class="bi bi-tags text-success me-2"></i> Manage Categories
                  </router-link>
                </li>
                <li>
                  <router-link class="dropdown-item py-2 px-3 fw-medium" to="/attraction_crud">
                    <i class="bi bi-map text-warning me-2"></i> Manage Touring
                  </router-link>
                </li>
                <li>
                  <router-link class="dropdown-item py-2 px-3 fw-medium" to="/booking_crud">
                    <i class="bi bi-calendar-check text-info me-2"></i> Manage Booking
                  </router-link>
                </li>
              </ul>
            </li>

            <li class="nav-item" v-if="userRole === 'employee'">
              <router-link class="nav-link fw-semibold px-3 d-flex align-items-center gap-2" to="/employee_crud">
              EMPLOYEE
              </router-link>
            </li>

            <li class="nav-item" v-if="userRole === 'employee'">
              <router-link class="nav-link fw-semibold px-3 d-flex align-items-center gap-2" to="/customer_crud">
               CUSTOMER
              </router-link>
            </li>

            <li class="nav-item px-2" v-if="userRole === 'customer'">
              <router-link class="nav-link fw-semibold d-flex align-items-center gap-2" to="/mybooking">
               MY BOOKING
              </router-link>
            </li>
            
            <li class="nav-item px-2">
              <router-link class="nav-link fw-semibold d-flex align-items-center gap-2" to="/contact">
              CONTACT
              </router-link>
            </li>
          </ul>
          
          <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
            <div v-if="!isLoggedIn" class="d-flex gap-2 w-100">
              <router-link to="/clogin" class="btn btn-primary px-4 rounded-pill fw-bold shadow-sm w-100">Login</router-link>
              <router-link to="/elogin" class="btn btn-outline-secondary px-3 rounded-pill fw-bold w-100">Staff</router-link>
            </div>

            <div v-else class="d-flex align-items-center gap-3">
              <div class="profile-capsule d-flex align-items-center ps-3 pe-1 py-1">
                <div class="user-info-text d-flex flex-column me-3 text-end">
                  <span class="user-name">{{ userName }}</span>
                  <span class="user-role" :class="userRole">{{ userRole }}</span>
                </div>
                <div class="avatar-wrapper shadow-sm" :class="userRole">{{ userName.charAt(0).toUpperCase() }}</div>
              </div>
              <button @click="logout" class="btn-logout-minimal shadow-sm" title="Logout">
                <i class="bi bi-box-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <main class="main-content">
      <div class="container py-4">
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
        this.userRole = ""; 
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
.app-container { min-height: 100vh; background-color: #f8fafc; }
.transition-nav { transition: all 0.3s ease; }
.main-content { padding-top: 90px; }
.nav-link { color: #64748b; transition: all 0.2s; }
.nav-link:hover, .nav-link.router-link-active { color: #0d6efd; }
.nav-link i { font-size: 1.1rem; }
.dropdown-item { transition: all 0.2s ease; color: #475569; }
.dropdown-item:hover { background-color: #f1f5f9; color: #0f172a; padding-left: 1.5rem !important; }
.profile-capsule { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 50px; }
.user-name { font-size: 0.95rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
.user-role { font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; }
.user-role.employee { color: #0284c7; }
.user-role.customer { color: #059669; }
.avatar-wrapper { width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: white; font-size: 1.1rem; }
.avatar-wrapper.employee { background: linear-gradient(135deg, #38bdf8 0%, #0284c7 100%); }
.avatar-wrapper.customer { background: linear-gradient(135deg, #34d399 0%, #059669 100%); }
.btn-logout-minimal { width: 38px; height: 38px; border: none; background: #fee2e2; color: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem; cursor: pointer; transition: all 0.2s; }
.btn-logout-minimal:hover { background: #ef4444; color: white; transform: scale(1.05); }
.fade-enter-active, .fade-leave-active { transition: opacity 0.2s ease, transform 0.2s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(5px); }
</style>