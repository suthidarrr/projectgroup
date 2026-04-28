<template>
  <div class="container mt-4 animate-fade-in">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h4 class="fw-bold text-dark mb-1">Manage Employee</h4>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">จัดการและตรวจสอบข้อมูลพนักงานในระบบ</p>
      </div>
      <div class="d-flex gap-2 flex-wrap flex-md-nowrap">
        <div class="search-container shadow-sm">
          <i class="bi bi-search text-muted ms-3"></i>
          <input 
            v-model="searchQuery" 
            type="text" 
            class="search-input" 
            placeholder="ค้นหาด้วย ID, ชื่อ, หรือแผนก..." 
          />
        </div>
        <button class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold d-flex align-items-center" @click="openAddModal">
          <i class="bi bi-person-plus-fill me-2"></i> Add Employee
        </button>
      </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr style="font-size: 0.85rem;">
              <th class="ps-4" style="width: 10%;">ID</th>
              <th style="width: 30%;">Full Name</th>
              <th style="width: 20%;">Department</th>
              <th style="width: 15%;">Salary</th>
              <th class="text-center" style="width: 10%;">Status</th>
              <th class="text-center" style="width: 15%;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="emp in filteredEmployees" :key="emp.emp_id">
              <td class="ps-4 fw-bold text-primary font-monospace small">#{{ emp.emp_id }}</td>
              <td>
                <div class="fw-semibold text-dark" style="font-size: 0.95rem;">{{ emp.efull_name }}</div>
                <small class="text-muted" style="font-size: 0.75rem;">Username: {{ emp.e_username }}</small>
              </td>
              <td>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3 py-1 fw-medium" style="font-size: 0.75rem;">
                  {{ emp.department }}
                </span>
              </td>
              <td class="fw-bold text-success" style="font-size: 0.95rem;">
                {{ parseFloat(emp.salary).toLocaleString() }} ฿
              </td>
              <td class="text-center">
                <span v-if="emp.active == 1" class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-medium" style="font-size: 0.75rem;">
                  ปกติ
                </span>
                <span v-else class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 fw-medium" style="font-size: 0.75rem;">
                  ลาออก
                </span>
              </td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-2">
                  <button class="btn btn-light btn-sm px-3 rounded-pill text-primary fw-medium border shadow-sm btn-action" @click="openEditModal(emp)">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button class="btn btn-light btn-sm px-3 rounded-pill text-danger fw-medium border shadow-sm btn-action" @click="deleteEmployee(emp.emp_id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredEmployees.length === 0 && !loading">
              <td colspan="6" class="text-center py-5 text-muted">
                <i class="bi bi-search fs-3 d-block mb-2 opacity-50"></i>
                ไม่พบข้อมูลพนักงานที่คุณค้นหา
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    
    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pb-0 mt-2 mx-2">
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
              <i v-if="isEditMode" class="bi bi-pencil-square text-warning me-2 fs-4"></i>
              <i v-else class="bi bi-person-plus-fill text-primary me-2 fs-4"></i>
              {{ isEditMode ? "Edit Employee" : "Add Employee" }}
            </h5>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <form @submit.prevent="saveEmployee">
              <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase text-muted">ชื่อ-นามสกุล</label>
                <input v-model="editEmployee.efull_name" type="text" class="form-control rounded-3" required />
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase text-muted">Username</label>
                  <input v-model="editEmployee.e_username" type="text" class="form-control rounded-3" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase text-muted">
                    Password <span v-if="isEditMode" class="text-lowercase fw-normal">(เว้นว่างถ้าไม่เปลี่ยน)</span>
                  </label>
                  <input v-model="editEmployee.e_password" type="password" class="form-control rounded-3" :required="!isEditMode" />
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase text-muted">แผนก</label>
                  <input v-model="editEmployee.department" type="text" class="form-control rounded-3" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase text-muted">เงินเดือน</label>
                  <input v-model="editEmployee.salary" type="number" class="form-control rounded-3" required />
                </div>
              </div>

              <div class="mb-3" v-if="isEditMode">
                <label class="form-label fw-bold small text-uppercase text-muted">สถานะ</label>
                <select v-model="editEmployee.active" class="form-select rounded-3">
                  <option value="1">ปกติ</option>
                  <option value="0">ลาออก</option>
                </select>
              </div>

              <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                  <i class="bi bi-save me-2"></i> {{ isEditMode ? "บันทึกการแก้ไข" : "ยืนยันเพิ่มพนักงาน" }}
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from "vue";

export default {
  setup() {
    const employees = ref([]);
    const loading = ref(true);
    const searchQuery = ref("");
    const isEditMode = ref(false);
    
    const editEmployee = ref({ 
      efull_name: "", 
      department: "", 
      salary: "", 
      active: "1", 
      e_username: "", 
      e_password: "" 
    });
    
    let modalInstance = null;
    const API_URL = "http://localhost/projectgroup/php_api/employee_crud.php";

    const fetchEmployees = async () => {
      loading.value = true;
      try {
        const response = await fetch(API_URL);
        const result = await response.json();
        if (result.success) employees.value = result.data;
      } catch (err) { console.error(err); }
      finally { loading.value = false; }
    };

    // ✅ ปรับระบบค้นหาให้หาจาก ID เพิ่ม และดัก Error จาก Null
    const filteredEmployees = computed(() => {
      if (!searchQuery.value) return employees.value;
      
      const search = searchQuery.value.toLowerCase().trim();

      return employees.value.filter(e => {
        const idMatch = e.emp_id ? String(e.emp_id).includes(search) : false;
        const nameMatch = e.efull_name ? e.efull_name.toLowerCase().includes(search) : false;
        const userMatch = e.e_username ? e.e_username.toLowerCase().includes(search) : false;
        const deptMatch = e.department ? e.department.toLowerCase().includes(search) : false;

        return idMatch || nameMatch || userMatch || deptMatch;
      });
    });

    onMounted(() => {
      fetchEmployees();
      modalInstance = new window.bootstrap.Modal(document.getElementById("employeeModal"));
    });

    const openAddModal = () => {
      isEditMode.value = false;
      editEmployee.value = { efull_name: "", department: "", salary: "", active: "1", e_username: "", e_password: "" };
      modalInstance.show();
    };

    const openEditModal = (emp) => {
      isEditMode.value = true;
      editEmployee.value = { ...emp, e_password: "" }; 
      modalInstance.show();
    };

    const saveEmployee = async () => {
      const method = isEditMode.value ? "PUT" : "POST";
      try {
        const response = await fetch(API_URL, {
          method,
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(editEmployee.value)
        });
        const result = await response.json();
        if (result.success) {
          fetchEmployees();
          modalInstance.hide();
        } else { alert(result.message); }
      } catch (err) { alert("Error saving data"); }
    };

    const deleteEmployee = async (id) => {
      if (!confirm("ลบข้อมูลพนักงานคนนี้ใช่หรือไม่?")) return;
      try {
        await fetch(API_URL, {
          method: "DELETE",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ emp_id: id })
        });
        fetchEmployees();
      } catch (err) { alert("Error deleting data"); }
    };

    return { 
      employees, loading, searchQuery, filteredEmployees, 
      editEmployee, isEditMode, openAddModal, openEditModal, 
      saveEmployee, deleteEmployee 
    };
  }
};
</script>

<style scoped>
/* Typography */
h4 { letter-spacing: -0.5px; }

/* ช่องค้นหา */
.search-container { 
  display: flex; align-items: center; background: white; 
  border: 1px solid #e2e8f0; border-radius: 50px; 
  min-width: 250px; height: 40px; 
  transition: all 0.3s ease;
}
.search-container:focus-within {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.1) !important;
}
.search-input { 
  border: none; outline: none; width: 100%; padding: 0 15px; 
  font-size: 0.85rem; color: #334155; background: transparent; 
}

/* ปุ่ม Action ตาราง */
.btn-action {
  transition: all 0.2s ease;
  background-color: #ffffff;
}
.btn-action:hover {
  transform: translateY(-2px);
  background-color: #f8fafc;
}

/* ฟอร์มใน Modal */
.form-control:focus, .form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

/* ตาราง */
.table thead th { font-weight: 700; text-transform: uppercase; color: #64748b; padding: 1rem; border-bottom: 2px solid #e2e8f0; }

.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>