<template>
  <div class="container mt-5 animate-fade-in">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h2 class="fw-bold text-dark mb-1">👥 Manage Employee</h2>
      </div>
      <div class="d-flex gap-2">
        <div class="search-container shadow-sm">
          <i class="bi bi-search text-muted ms-3"></i>
          <input v-model="searchQuery" type="text" class="search-input" placeholder="Search name or username..." />
        </div>
        <button class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold" @click="openAddModal">
          <i class="bi bi-person-plus-fill me-2"></i> Add Employee
        </button>
      </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="ps-4">ID</th>
              <th>Full Name</th>
              <th>Department</th>
              <th>Salary</th>
              <th class="text-center">Status</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="emp in filteredEmployees" :key="emp.emp_id">
              <td class="ps-4 fw-bold text-primary">#{{ emp.emp_id }}</td>
              <td>
                <div class="fw-semibold">{{ emp.efull_name }}</div>
                <small class="text-muted">Username: {{ emp.e_username }}</small>
              </td>
              <td>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill px-3">{{ emp.department }}</span>
              </td>
              <td class="fw-bold">{{ parseFloat(emp.salary).toLocaleString() }} ฿</td>
              <td class="text-center">
                <span v-if="emp.active == 1" class="badge bg-success-subtle text-success rounded-pill px-3">ปกติ</span>
                <span v-else class="badge bg-danger-subtle text-danger rounded-pill px-3">ลาออก</span>
              </td>
              <td class="text-center">
                <div class="btn-group shadow-sm rounded-3">
                  <button class="btn btn-outline-warning btn-sm px-3" @click="openEditModal(emp)">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button class="btn btn-outline-danger btn-sm px-3" @click="deleteEmployee(emp.emp_id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal fade" id="employeeModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pb-0">
            <h5 class="fw-bold">{{ isEditMode ? "📝 Edit Employee" : "🆕 Add Employee" }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body py-4">
            <form @submit.prevent="saveEmployee">
              <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase">ชื่อ-นามสกุล</label>
                <input v-model="editEmployee.efull_name" type="text" class="form-control rounded-3" required />
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase">Username</label>
                  <input v-model="editEmployee.e_username" type="text" class="form-control rounded-3" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase">
                    Password <span v-if="isEditMode" class="text-lowercase fw-normal">(เว้นว่างถ้าไม่เปลี่ยน)</span>
                  </label>
                  <input v-model="editEmployee.e_password" type="password" class="form-control rounded-3" :required="!isEditMode" />
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase">แผนก</label>
                  <input v-model="editEmployee.department" type="text" class="form-control rounded-3" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase">เงินเดือน</label>
                  <input v-model="editEmployee.salary" type="number" class="form-control rounded-3" required />
                </div>
              </div>

              <div class="mb-3" v-if="isEditMode">
                <label class="form-label fw-bold small text-uppercase">สถานะ</label>
                <select v-model="editEmployee.active" class="form-select rounded-3">
                  <option value="1">ปกติ</option>
                  <option value="0">ลาออก</option>
                </select>
              </div>

              <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                  {{ isEditMode ? "บันทึกการแก้ไข" : "ยืนยันเพิ่มพนักงาน" }}
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
    
    // ✅ เพิ่ม e_username และ e_password ใน Object
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

    const filteredEmployees = computed(() => {
  if (!employees.value) return [];
  
  return employees.value.filter(e => {
    // ดึงค่ามาทำเป็นตัวพิมพ์เล็กทั้งหมดเพื่อการค้นหาที่แม่นยำ
    const name = (e.efull_name || "").toLowerCase();
    const user = (e.e_username || "").toLowerCase();
    const dept = (e.department || "").toLowerCase(); // ✅ เพิ่มส่วนนี้
    const search = searchQuery.value.toLowerCase();

    // ค้นหาจาก ชื่อ OR Username OR แผนก
    return name.includes(search) || 
           user.includes(search) || 
           dept.includes(search); // ✅ เพิ่มเงื่อนไขนี้
  });
});

    onMounted(() => {
      fetchEmployees();
      modalInstance = new window.bootstrap.Modal(document.getElementById("employeeModal"));
    });

    const openAddModal = () => {
      isEditMode.value = false;
      // ✅ ล้างค่า Username/Password เมื่อกด Add
      editEmployee.value = { efull_name: "", department: "", salary: "", active: "1", e_username: "", e_password: "" };
      modalInstance.show();
    };

    const openEditModal = (emp) => {
      isEditMode.value = true;
      // ✅ ดึงข้อมูลเดิมมาโชว์ (รหัสผ่านเว้นว่างไว้)
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

    return { employees, loading, searchQuery, filteredEmployees, editEmployee, isEditMode, openAddModal, openEditModal, saveEmployee, deleteEmployee };
  }
};
</script>

<style scoped>
/* CSS เหมือนเดิมเพื่อความคุมโทน */
.search-container { display: flex; align-items: center; background: white; border: 1px solid #e2e8f0; border-radius: 50px; min-width: 250px; height: 42px; }
.search-input { border: none; outline: none; width: 100%; padding: 0 15px; font-size: 0.95rem; background: transparent; }
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.table thead th { font-size: 0.75rem; text-transform: uppercase; color: #64748b; padding: 1rem; }
</style>