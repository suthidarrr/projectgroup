<template>
  <div class="register-container animate-fade-in">
    <div class="container py-5">
      <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
          
          <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="card-header bg-dark py-4 text-center border-0">
              <h3 class="fw-bold text-white mb-0">Staff Registration</h3>
              <p class="text-white-50 small mb-0">Create a new employee account</p>
            </div>
            
            <div class="card-body p-4 p-md-5">
              <form @submit.prevent="addData">
                
                <div class="mb-3">
                  <label class="form-label small fw-bold text-secondary">FULL NAME</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-person"></i></span>
                    <input v-model="employee.efull_name" class="form-control bg-light border-0 py-2" placeholder="Ex: Suthida Ruangsuksud" required />
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-6">
                    <label class="form-label small fw-bold text-secondary">DEPARTMENT</label>
                    <input v-model="employee.department" class="form-control bg-light border-0 py-2" placeholder="IT, HR, etc." required />
                  </div>
                  <div class="col-6">
                    <label class="form-label small fw-bold text-secondary">SALARY</label>
                    <input type="number" v-model="employee.salary" class="form-control bg-light border-0 py-2" placeholder="0.00" required />
                  </div>
                </div>

                <hr class="my-4 border-light">

                <div class="mb-3">
                  <label class="form-label small fw-bold text-secondary">SYSTEM USERNAME</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-person-badge"></i></span>
                    <input v-model="employee.e_username" class="form-control bg-light border-0 py-2" placeholder="Set username" required />
                  </div>
                </div>

                <div class="mb-4">
                  <label class="form-label small fw-bold text-secondary">SYSTEM PASSWORD</label>
                  <div class="input-group">
                    <span class="input-group-text bg-light border-0"><i class="bi bi-key"></i></span>
                    <input type="password" v-model="employee.e_password" class="form-control bg-light border-0 py-2" placeholder="••••••••" required />
                  </div>
                </div>

                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-dark py-2 rounded-pill fw-bold shadow-sm" :disabled="loading">
                    <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                    Save Employee Data
                  </button>
                  <button type="reset" class="btn btn-light py-2 rounded-pill text-secondary fw-bold">Clear Form</button>
                </div>
              </form>

              <div v-if="message" :class="['alert mt-4 py-2 text-center small border-0 shadow-sm', statusSuccess ? 'alert-success' : 'alert-info']">
                {{ message }}
              </div>

              <div class="text-center mt-4">
                <router-link to="/elogin" class="text-decoration-none text-muted small">
                  ← Back to Staff Login
                </router-link>
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
  data() {
    return {
      employee: {
        efull_name: "",  // ปรับชื่อให้ตรงกับ DB (e_)
        department: "",
        salary: "",
        active: "1",
        e_username: "", // เพิ่มใหม่
        e_password: ""  // เพิ่มใหม่
      },
      loading: false,
      message: "",
      statusSuccess: false
    };
  },
  methods: {
    async addData() {
      this.loading = true;
      this.message = "";
      try {
        const res = await fetch("http://localhost/projectgroup/php_api/add_employee.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(this.employee)
        });
        const data = await res.json();
        
        this.message = data.message;
        this.statusSuccess = data.success;

        if (data.success) {
          // ✅ เคลียร์ข้อมูลหลังบันทึกสำเร็จ
          this.employee = { efull_name: "", department: "", salary: "", active: "1", e_username: "", e_password: "" };
        }
      } catch (err) {
        this.message = "Connection Error: " + err.message;
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
  min-height: calc(100vh - 80px); /* จัดกลางโดยหักพื้นที่ Navbar */
  display: flex;
  align-items: center;
}

.form-control:focus {
  background-color: #fff !important;
  border: 1px solid #212529 !important;
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
  color: #6c757d;
}
</style>