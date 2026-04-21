<template>
  <div class="register-container animate-fade-in">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          
          <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-primary py-4 text-center border-0">
              <h3 class="fw-bold text-white mb-0">Create Account</h3>
              <p class="text-white-50 small mb-0">Start your amazing journey with us</p>
            </div>
            
            <div class="card-body p-4 p-md-5">
              <form @submit.prevent="handleRegister">
                
                <div class="mb-3">
                  <label class="form-label small fw-bold text-secondary">FULL NAME</label>
                  <input v-model="customer.cfull_name" type="text" class="form-control bg-light border-0 py-2" placeholder="John Doe" required />
                </div>

                <div class="row mb-3">
                  <div class="col-6">
                    <label class="form-label small fw-bold text-secondary">PHONE</label>
                    <input v-model="customer.phone" type="text" class="form-control bg-light border-0 py-2" placeholder="090-xxx-xxxx" required />
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-bold text-secondary">EMAIL</label>
                    <input v-model="customer.email" type="email" class="form-control bg-light border-0 py-2" placeholder="john@example.com" required />
                  </div>
                </div>

                <hr class="my-4 border-light">

                <div class="mb-3">
                  <label class="form-label small fw-bold text-secondary">USERNAME</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-person"></i></span>
                    <input v-model="customer.c_username" type="text" class="form-control bg-light border-0 py-2" placeholder="Choose a username" required />
                  </div>
                </div>

                <div class="mb-4">
                  <label class="form-label small fw-bold text-secondary">PASSWORD</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-shield-lock"></i></span>
                    <input v-model="customer.c_password" type="password" class="form-control bg-light border-0 py-2" placeholder="••••••••" required />
                  </div>
                </div>

                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-primary py-2 rounded-pill fw-bold shadow-sm" :disabled="loading">
                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                    Sign Up Now
                  </button>
                </div>
              </form>

              <div v-if="message" :class="['alert mt-4 py-2 text-center small border-0 shadow-sm', statusSuccess ? 'alert-success' : 'alert-danger']">
                {{ message }}
              </div>

              <div class="text-center mt-4">
                <p class="small text-muted">
                  Already have an account? 
                  <router-link to="/clogin" class="text-primary fw-bold text-decoration-none">Login</router-link>
                </p>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: "RegisterCustomer",
  data() {
    return {
      customer: {
        cfull_name: "",
        phone: "",
        email: "",
        c_username: "",
        c_password: ""
      },
      loading: false,
      message: "",
      statusSuccess: false
    };
  },
  methods: {
    async handleRegister() {
      this.loading = true;
      this.message = "";
      try {
        const res = await fetch("http://localhost/projectgroup/php_api/add_customer.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(this.customer)
        });
        const data = await res.json();
        this.message = data.message;
        this.statusSuccess = data.success;

        if (data.success) {
          // หน่วงเวลา 2 วินาทีแล้วไปหน้า Login
          setTimeout(() => this.$router.push("/login_customer"), 2000);
        }
      } catch (err) {
        this.message = "Connection error: " + err.message;
      } finally {
        this.loading = false;
      }
    }
  }
}
</script>

<style scoped>
.register-container {
  background-color: #f8fafc;
  min-height: calc(100vh - 80px);
  display: flex;
  align-items: center;
}

.form-control:focus {
  background-color: #fff !important;
  border: 1px solid #0d6efd !important;
  box-shadow: none;
}

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

.input-group-text {
  color: #0d6efd;
}
</style>