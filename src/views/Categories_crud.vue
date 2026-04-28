<template>
  <div class="container mt-4 animate-fade-in">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h4 class="fw-bold text-dark mb-1">Manage Categories</h4>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">จัดการและตรวจสอบหมวดหมู่ของทัวร์ทั้งหมด</p>
      </div>
      
      <div class="d-flex gap-2 flex-wrap flex-md-nowrap">
        <div class="search-container shadow-sm">
          <i class="bi bi-search text-muted ms-3"></i>
          <input 
            type="text" 
            v-model="searchQuery" 
            class="search-input" 
            placeholder="ค้นหาด้วยรหัส (ID) หรือชื่อ..."
          />
        </div>

        <button class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold d-flex align-items-center" @click="openAddModal">
          <i class="bi bi-plus-lg me-2"></i> Add Category
        </button>
      </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr style="font-size: 0.9rem;">
              <th class="ps-4" style="width: 10%;">No.</th>
              <th style="width: 15%;">ID</th>
              <th style="width: 50%;">Category Name</th>
              <th class="text-center" style="width: 25%;">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(cat, index) in filteredCategories" :key="cat.category_id">
              <td class="ps-4 text-muted small">{{ index + 1 }}</td>
              <td class="text-muted small font-monospace">ID: {{ cat.category_id }}</td>
              <td>
                <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ cat.category_name }}</div>
              </td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-2">
                  <button class="btn btn-light btn-sm px-3 rounded-pill text-primary fw-medium border shadow-sm btn-action" @click="openEditModal(cat)">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                  </button>
                  <button class="btn btn-light btn-sm px-3 rounded-pill text-danger fw-medium border shadow-sm btn-action" @click="deleteCategory(cat.category_id)">
                    <i class="bi bi-trash me-1"></i> Delete
                  </button>
                </div>
              </td>
            </tr>
            
            <tr v-if="filteredCategories.length === 0 && !loading">
              <td colspan="4" class="text-center py-5 text-muted">
                <i class="bi bi-search fs-3 d-block mb-2 opacity-50"></i>
                ไม่พบหมวดหมู่ที่คุณค้นหา
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <div class="modal fade" id="categoryModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pb-0 mt-2 mx-2">
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
              <i v-if="isEditMode" class="bi bi-pencil-square text-warning me-2 fs-4"></i>
              <i v-else class="bi bi-plus-circle-fill text-primary me-2 fs-4"></i>
              {{ isEditMode ? "Edit Category" : "Add Category" }}
            </h5>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <form @submit.prevent="saveCategory">
              <div class="mb-4">
                <label class="form-label fw-bold small text-muted">CATEGORY NAME</label>
                <input 
                  v-model="currentCat.category_name" 
                  type="text" 
                  class="form-control form-control-lg rounded-3 fs-6" 
                  placeholder="ป้อนชื่อหมวดหมู่ที่นี่..."
                  required
                >
              </div>
              <div class="d-grid mt-2">
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
    const categories = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const currentCat = ref({ category_name: "" });
    const isEditMode = ref(false);
    const searchQuery = ref(""); // 🆕 ตัวแปรสำหรับค้นหา
    let modalInstance = null;

    const API_URL = "http://localhost/projectgroup/php_api/categories.php";

    const fetchCategories = async () => {
      try {
        const response = await fetch(API_URL);
        const result = await response.json();
        if (result.success) categories.value = result.data;
        else error.value = result.message;
      } catch (err) {
        error.value = "Failed to fetch: " + err.message;
      } finally {
        loading.value = false;
      }
    };

    // 🆕 ระบบค้นหา (กรองจาก ID หรือ ชื่อหมวดหมู่)
    const filteredCategories = computed(() => {
      if (!searchQuery.value) return categories.value;
      
      const query = searchQuery.value.toLowerCase().trim();
      
      return categories.value.filter(cat => {
        const idMatch = cat.category_id ? String(cat.category_id).includes(query) : false;
        const nameMatch = cat.category_name ? cat.category_name.toLowerCase().includes(query) : false;
        return idMatch || nameMatch;
      });
    });

    onMounted(() => {
      fetchCategories();
      modalInstance = new window.bootstrap.Modal(document.getElementById("categoryModal"));
    });

    const openAddModal = () => {
      isEditMode.value = false;
      currentCat.value = { category_name: "" };
      modalInstance.show();
    };

    const openEditModal = (cat) => {
      isEditMode.value = true;
      currentCat.value = { ...cat };
      modalInstance.show();
    };

    const saveCategory = async () => {
      const method = isEditMode.value ? "PUT" : "POST";
      try {
        const response = await fetch(API_URL, {
          method,
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(currentCat.value)
        });
        const result = await response.json();
        if (result.success) {
          fetchCategories();
          modalInstance.hide();
        } else {
          alert(result.message);
        }
      } catch (err) {
        alert("Error: " + err.message);
      }
    };

    const deleteCategory = async (id) => {
      if (!confirm("คุณแน่ใจหรือไม่ว่าต้องการลบหมวดหมู่นี้?")) return;
      try {
        const response = await fetch(API_URL, {
          method: "DELETE",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ category_id: id })
        });
        const result = await response.json();
        if (result.success) fetchCategories();
      } catch (err) {
        alert(err.message);
      }
    };

    return { 
      categories, 
      loading, 
      error, 
      currentCat, 
      isEditMode, 
      searchQuery, // 🆕 รีเทิร์นตัวแปรค้นหา
      filteredCategories, // 🆕 รีเทิร์นข้อมูลที่ผ่านการกรองแล้วไปแสดงในตาราง
      openAddModal, 
      openEditModal, 
      saveCategory, 
      deleteCategory 
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
  border: none; outline: none; background: transparent; 
  width: 100%; padding: 0 15px; font-size: 0.85rem; color: #334155;
}

/* ปุ่มในตาราง */
.btn-action {
  transition: all 0.2s ease;
  background-color: #ffffff;
}
.btn-action:hover {
  transform: translateY(-2px);
  background-color: #f8fafc;
}

/* Modal Input */
.form-control:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>