<template>
  <div class="login-container animate-fade-in">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
          
          <div class="card shadow-lg border-0 rounded-4">
            <div class="card-body p-5">
              
              <div class="text-center mb-4">
                <div class="login-icon mb-3 bg-primary text-white shadow-sm">👤</div>
                <h2 class="fw-bold text-primary">Welcome</h2>
                <p class="text-muted small">Sign in to book your next trip</p>
              </div>

              <form @submit.prevent="login">
                <div class="mb-3">
                  <label class="form-label small fw-bold text-secondary">USERNAME</label>
                  <input v-model="username" type="text" class="form-control bg-light border-0 py-2" placeholder="Your Username" required />
                </div>
                <div class="mb-4">
                  <label class="form-label small fw-bold text-secondary">PASSWORD</label>
                  <input v-model="password" type="password" class="form-control bg-light border-0 py-2" placeholder="••••••••" required />
                </div>
                
                <button type="submit" class="btn btn-primary w-100 py-2 rounded-pill fw-bold shadow-sm" :disabled="loading">
                  <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                  Login
                </button>
              </form>

              <div v-if="error" class="alert alert-danger mt-3 py-2 text-center small border-0 shadow-sm">{{ error }}</div>

              <div class="mt-4 pt-3 border-top text-center">
                <p class="small text-muted mb-2">Don't have an account?</p>
                <router-link to="/add_customer" class="btn btn-outline-primary btn-sm w-100 rounded-pill fw-bold">
                  Register Now
                </router-link>
                <div class="mt-3">
                  <router-link to="/home" class="text-decoration-none text-muted" style="font-size: 0.75rem;">
                    ← Back to Homepage
                  </router-link>
                </div>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  name: "LoginCustomer",
  data() { 
    return { 
      username: "", 
      password: "", 
      error: "", 
      loading: false 
    }; 
  },
  methods: {
    async login() {
      this.loading = true; 
      this.error = "";
      try {
        const res = await axios.post("http://localhost/projectgroup/php_api/clogin.php", {
          username: this.username, 
          password: this.password
        });
        
        if (res.data.success) {
          // เก็บข้อมูล User พร้อมระบุ Role เป็น customer
          localStorage.setItem("user", JSON.stringify({ ...res.data.user, role: 'customer' }));
          // ล็อกอินเสร็จส่งลูกค้ากลับหน้า Home เพื่อไปเลือกทัวร์
          this.$router.push("/home"); 
        } else { 
          this.error = res.data.message; 
        }
      } catch (err) { 
        this.error = "Server Connection Error"; 
      } finally { 
        this.loading = false; 
      }
    }
  }
}
</script>

<style scoped>
.login-container {
  /* ✅ ล็อคให้อยู่กลางหน้าจอพอดี โดยหักลบความสูง Navbar ออก (ประมาณ 80px) */
  min-height: calc(100vh - 80px);
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f8fafc;
}

/* ✅ สไตล์ไอคอนให้เป็นวงกลมสมบูรณ์แบบ */
.login-icon {
  font-size: 2.2rem;
  width: 70px;
  height: 70px;
  line-height: 70px;
  margin: 0 auto;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.form-control:focus {
  background-color: #fff !important;
  border: 1px solid #0d6efd !important;
  box-shadow: none;
}

/* ✅ แอนิเมชัน Fade-in นุ่มนวล */
.animate-fade-in {
  animation: fadeIn 0.8s ease-in-out;
}

@keyframes fadeIn {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}

.card {
  border: 1px solid #edf2f7 !important;
}
</style>