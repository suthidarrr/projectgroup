<template>
  <div class="container mt-4 animate-fade-in">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h4 class="fw-bold text-dark mb-1">Manage Customers</h4>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">จัดการและตรวจสอบข้อมูลลูกค้าในระบบ</p>
      </div>
      <div class="d-flex gap-2 flex-wrap flex-md-nowrap">
        <div class="search-container shadow-sm">
          <i class="bi bi-search text-muted ms-3"></i>
          <input 
            v-model="searchQuery" 
            type="text" 
            class="search-input" 
            placeholder="ค้นหาด้วย ID, ชื่อ, เบอร์โทร..." 
          />
        </div>
        <button class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold d-flex align-items-center" @click="openAddModal">
          <i class="bi bi-person-plus-fill me-2"></i> Add Customer
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
              <th style="width: 25%;">Username</th>
              <th style="width: 25%;">Contact</th>
              <th class="text-center" style="width: 10%;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cus in filteredCustomers" :key="cus.cust_id">
              <td class="ps-4 fw-bold text-primary font-monospace small">#{{ cus.cust_id }}</td>
              <td>
                <div class="fw-semibold text-dark" style="font-size: 0.95rem;">{{ cus.cfull_name }}</div>
              </td>
              <td>
                <code class="text-secondary bg-light px-2 py-1 rounded" style="font-size: 0.85rem;">{{ cus.c_username }}</code>
              </td>
              <td>
                <div class="small mb-1" style="font-size: 0.85rem;">
                  <i class="bi bi-telephone text-muted me-1"></i> {{ cus.phone }}
                </div>
                <div class="small text-muted" style="font-size: 0.85rem;">
                  <i class="bi bi-envelope me-1"></i> {{ cus.email }}
                </div>
              </td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-2">
                  <button class="btn btn-light btn-sm px-3 rounded-pill text-primary fw-medium border shadow-sm btn-action" @click="openEditModal(cus)">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button class="btn btn-light btn-sm px-3 rounded-pill text-danger fw-medium border shadow-sm btn-action" @click="deleteCustomer(cus.cust_id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredCustomers.length === 0 && !loading">
              <td colspan="5" class="text-center py-5 text-muted">
                <i class="bi bi-search fs-3 d-block mb-2 opacity-50"></i>
                ไม่พบข้อมูลลูกค้าที่คุณค้นหา
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pb-0 mt-2 mx-2">
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
              <i v-if="isEditMode" class="bi bi-pencil-square text-warning me-2 fs-4"></i>
              <i v-else class="bi bi-person-plus-fill text-primary me-2 fs-4"></i>
              {{ isEditMode ? "Edit Customer" : "New Customer" }}
            </h5>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <form @submit.prevent="saveCustomer">
              <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase text-muted">ชื่อ-นามสกุล</label>
                <input v-model="editCustomer.cfull_name" type="text" class="form-control rounded-3" required />
              </div>
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase text-muted">Username</label>
                  <input v-model="editCustomer.c_username" type="text" class="form-control rounded-3" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase text-muted">
                    Password <span v-if="isEditMode" class="text-lowercase fw-normal">(เว้นว่างถ้าไม่เปลี่ยน)</span>
                  </label>
                  <input v-model="editCustomer.c_password" type="password" class="form-control rounded-3" :required="!isEditMode" />
                </div>
              </div>
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase text-muted">เบอร์โทรศัพท์</label>
                  <input v-model="editCustomer.phone" type="text" class="form-control rounded-3" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase text-muted">อีเมล</label>
                  <input v-model="editCustomer.email" type="email" class="form-control rounded-3" required />
                </div>
              </div>
              <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                  <i class="bi bi-save me-2"></i> บันทึกข้อมูล
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
    const customers = ref([]);
    const loading = ref(true);
    const searchQuery = ref("");
    const isEditMode = ref(false);
    const editCustomer = ref({ cust_id: "", cfull_name: "", phone: "", email: "", c_username: "", c_password: "" });
    
    let modalInstance = null;
    const API_URL = "http://localhost/projectgroup/php_api/customer_crud.php";

    const fetchCustomers = async () => {
      loading.value = true;
      try {
        const res = await fetch(API_URL);
        const result = await res.json();
        if (result.success) customers.value = result.data;
      } catch (err) { console.error(err); }
      finally { loading.value = false; }
    };

    // ✅ ปรับระบบค้นหาให้ครอบคลุม ID, Name, Username, Phone, Email
    const filteredCustomers = computed(() => {
      if (!searchQuery.value) return customers.value;
      
      const search = searchQuery.value.toLowerCase().trim();

      return customers.value.filter(c => {
        const idMatch = c.cust_id ? String(c.cust_id).includes(search) : false;
        const nameMatch = c.cfull_name ? c.cfull_name.toLowerCase().includes(search) : false;
        const userMatch = c.c_username ? c.c_username.toLowerCase().includes(search) : false;
        const phoneMatch = c.phone ? String(c.phone).includes(search) : false;
        const emailMatch = c.email ? c.email.toLowerCase().includes(search) : false;

        return idMatch || nameMatch || userMatch || phoneMatch || emailMatch;
      });
    });

    onMounted(() => {
      fetchCustomers();
      modalInstance = new window.bootstrap.Modal(document.getElementById("customerModal"));
    });

    const openAddModal = () => {
      isEditMode.value = false;
      editCustomer.value = { cfull_name: "", phone: "", email: "", c_username: "", c_password: "" };
      modalInstance.show();
    };

    const openEditModal = (cus) => {
      isEditMode.value = true;
      editCustomer.value = { ...cus, c_password: "" };
      modalInstance.show();
    };

    const saveCustomer = async () => {
      const method = isEditMode.value ? "PUT" : "POST";
      try {
        const res = await fetch(API_URL, {
          method,
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(editCustomer.value)
        });
        const result = await res.json();
        if (result.success) {
          fetchCustomers();
          modalInstance.hide();
        } else { alert(result.message); }
      } catch (err) { alert("Error saving data"); }
    };

    const deleteCustomer = async (id) => {
      if (!confirm("คุณแน่ใจหรือไม่ว่าต้องการลบข้อมูลลูกค้านี้?")) return;
      try {
        await fetch(API_URL, {
          method: "DELETE",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ cust_id: id })
        });
        fetchCustomers();
      } catch (err) { alert("Error deleting data"); }
    };

    return { 
      customers, loading, searchQuery, filteredCustomers, 
      editCustomer, isEditMode, openAddModal, openEditModal, 
      saveCustomer, deleteCustomer 
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

/* ปุ่ม Action ในตาราง */
.btn-action {
  transition: all 0.2s ease;
  background-color: #ffffff;
}
.btn-action:hover {
  transform: translateY(-2px);
  background-color: #f8fafc;
}

/* Modal Inputs */
.form-control:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

/* ตาราง */
.table thead th { font-weight: 700; text-transform: uppercase; color: #64748b; padding: 1rem; border-bottom: 2px solid #e2e8f0; }

.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>