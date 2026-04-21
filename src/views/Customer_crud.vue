<template>
  <div class="container mt-5 animate-fade-in">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h2 class="fw-bold text-dark mb-1">💎 Manage Customers</h2>
      </div>
      <div class="d-flex gap-2">
        <div class="search-container shadow-sm">
          <i class="bi bi-search text-muted ms-3"></i>
          <input v-model="searchQuery" type="text" class="search-input" placeholder="Search name, user or phone..." />
        </div>
        <button class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold" @click="openAddModal">
          <i class="bi bi-person-plus-fill me-2"></i> Add Customer
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
              <th>Username</th>
              <th>Contact</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="cus in filteredCustomers" :key="cus.cust_id">
              <td class="ps-4 fw-bold text-primary">#{{ cus.cust_id }}</td>
              <td><div class="fw-semibold">{{ cus.cfull_name }}</div></td>
              <td><code class="text-dark">{{ cus.c_username }}</code></td>
              <td>
                <div class="small"><i class="bi bi-telephone me-1"></i> {{ cus.phone }}</div>
                <div class="small text-muted"><i class="bi bi-envelope me-1"></i> {{ cus.email }}</div>
              </td>
              <td class="text-center">
                <div class="btn-group shadow-sm rounded-3">
                  <button class="btn btn-outline-warning btn-sm px-3" @click="openEditModal(cus)">
                    <i class="bi bi-pencil-square"></i>
                  </button>
                  <button class="btn btn-outline-danger btn-sm px-3" @click="deleteCustomer(cus.cust_id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredCustomers.length === 0 && !loading">
              <td colspan="5" class="text-center py-5 text-muted">ไม่พบข้อมูลลูกค้า</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal fade" id="customerModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pb-0">
            <h5 class="fw-bold">{{ isEditMode ? "📝 Edit Customer" : "🆕 New Customer" }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body py-4">
            <form @submit.prevent="saveCustomer">
              <div class="mb-3">
                <label class="form-label fw-bold small text-uppercase">ชื่อ-นามสกุล</label>
                <input v-model="editCustomer.cfull_name" type="text" class="form-control rounded-3" required />
              </div>
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase">Username</label>
                  <input v-model="editCustomer.c_username" type="text" class="form-control rounded-3" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase">
                    Password <span v-if="isEditMode" class="text-lowercase fw-normal">(เว้นว่างถ้าไม่เปลี่ยน)</span>
                  </label>
                  <input v-model="editCustomer.c_password" type="password" class="form-control rounded-3" :required="!isEditMode" />
                </div>
              </div>
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase">เบอร์โทรศัพท์</label>
                  <input v-model="editCustomer.phone" type="text" class="form-control rounded-3" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-uppercase">อีเมล</label>
                  <input v-model="editCustomer.email" type="email" class="form-control rounded-3" required />
                </div>
              </div>
              <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold">บันทึกข้อมูล</button>
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
      try {
        const res = await fetch(API_URL);
        const result = await res.json();
        if (result.success) customers.value = result.data;
      } catch (err) { console.error(err); }
      finally { loading.value = false; }
    };

    const filteredCustomers = computed(() => {
      if (!customers.value) return [];
      return customers.value.filter(c => 
        (c.cfull_name || "").toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (c.c_username || "").toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (c.phone || "").includes(searchQuery.value)
      );
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
    };

    const deleteCustomer = async (id) => {
      if (!confirm("ลบข้อมูลลูกค้านี้ใช่หรือไม่?")) return;
      await fetch(API_URL, {
        method: "DELETE",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ cust_id: id })
      });
      fetchCustomers();
    };

    return { customers, loading, searchQuery, filteredCustomers, editCustomer, isEditMode, openAddModal, openEditModal, saveCustomer, deleteCustomer };
  }
};
</script>

<style scoped>
.search-container { display: flex; align-items: center; background: white; border: 1px solid #e2e8f0; border-radius: 50px; min-width: 250px; height: 42px; }
.search-input { border: none; outline: none; width: 100%; padding: 0 15px; font-size: 0.95rem; background: transparent; }
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.table thead th { font-size: 0.75rem; text-transform: uppercase; color: #64748b; padding: 1rem; }
</style>