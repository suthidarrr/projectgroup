<template>
  <div class="container mt-4 animate-fade-in">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h4 class="fw-bold text-dark mb-1">Manage Touring</h4>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">จัดการและตรวจสอบรายการทัวร์ทั้งหมด</p>
      </div>
      
      <div class="d-flex gap-2">
        <div class="search-container shadow-sm">
          <i class="bi bi-search text-muted ms-3"></i>
          <input 
            v-model="searchQuery" 
            type="text" 
            class="search-input" 
            placeholder="ค้นหาด้วยรหัส (ID), ชื่อทัวร์..." 
          />
        </div>
        
        <button class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold d-flex align-items-center" @click="openAddModal">
          <i class="bi bi-plus-lg me-2"></i> Add Tour
        </button>
      </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr style="font-size: 0.85rem;">
              <th class="ps-4">ID</th> <th>Image</th>
              <th>Destination</th>
              <th>Category</th>
              <th>Price</th>
              <th class="text-center">Seats</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="tour in filteredProducts" :key="tour.att_id">
              <td class="ps-4 text-muted small font-monospace">{{ tour.att_id }}</td>
              <td>
                <img v-if="tour.image" :src="'http://localhost/projectgroup/php_api/uploads/' + tour.image" class="rounded-3 border" width="60" height="45" style="object-fit: cover;" />
                <div v-else class="no-img">No Img</div>
              </td>
              <td>
                <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ tour.att_name }}</div>
                <div class="text-muted text-truncate mt-1" style="max-width: 150px; font-size: 0.8rem;">{{ tour.description }}</div>
              </td>
              <td>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill fw-medium px-3 py-2" style="font-size: 0.75rem;">
                  {{ tour.category_name }}
                </span>
              </td>
              <td class="fw-bold text-success" style="font-size: 0.95rem;">{{ parseFloat(tour.price).toLocaleString() }} ฿</td>
              <td class="text-center">
                <span class="badge bg-light text-dark border px-2 py-1">{{ tour.seat }}</span>
              </td>
              <td class="text-center">
                <div class="d-flex justify-content-center gap-2">
                  <button class="btn btn-light btn-sm px-3 rounded-pill text-primary fw-medium border shadow-sm btn-action" @click="openEditModal(tour)">
                    <i class="bi bi-pencil-square me-1"></i> Edit
                  </button>
                  <button class="btn btn-light btn-sm px-3 rounded-pill text-danger fw-medium border shadow-sm btn-action" @click="deleteProduct(tour.att_id)">
                    <i class="bi bi-trash me-1"></i> Delete
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredProducts.length === 0 && !loading">
              <td colspan="7" class="text-center py-5 text-muted">
                <i class="bi bi-search fs-3 d-block mb-2 opacity-50"></i>
                ไม่พบข้อมูลที่คุณค้นหา
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status"></div>
    </div>

    <div class="modal fade" id="tourModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pb-0 mt-2 mx-2">
            <h5 class="fw-bold mb-0 text-dark d-flex align-items-center">
              <i v-if="isEditMode" class="bi bi-pencil-square text-warning me-2 fs-4"></i>
              <i v-else class="bi bi-plus-circle-fill text-primary me-2 fs-4"></i>
              {{ isEditMode ? "Edit Tour" : "New Tour" }}
            </h5>
            <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <form @submit.prevent="saveProduct">
              <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label fw-bold small text-muted">ชื่อสถานที่ท่องเที่ยว</label>
                  <input v-model="editForm.att_name" type="text" class="form-control rounded-3" required />
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-bold small text-muted">ประเภททัวร์</label>
                  <select v-model="editForm.category_id" class="form-select rounded-3" required>
                    <option value="" disabled>-- เลือกหมวดหมู่ --</option>
                    <option v-for="cat in categories" :key="cat.category_id" :value="cat.category_id">
                      {{ cat.category_name }}
                    </option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label fw-bold small text-muted">รายละเอียด</label>
                  <textarea v-model="editForm.description" class="form-control rounded-3" rows="3"></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-muted">ราคา (THB)</label>
                  <input v-model="editForm.price" type="number" class="form-control rounded-3" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small text-muted">จำนวนที่นั่ง</label> 
                  <input v-model="editForm.seat" type="number" class="form-control rounded-3" required /> 
                </div>
                <div class="col-12">
                  <label class="form-label fw-bold small text-muted">รูปภาพ</label>
                  <input type="file" @change="handleFileUpload" class="form-control rounded-3" :required="!isEditMode" />
                  <div v-if="isEditMode && editForm.image" class="mt-2">
                    <img :src="'http://localhost/projectgroup/php_api/uploads/' + editForm.image" class="rounded border shadow-sm" width="80" />
                  </div>
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
    const products = ref([]);
    const categories = ref([]);
    const loading = ref(true);
    const searchQuery = ref("");
    const isEditMode = ref(false);

    const editForm = ref({ 
      att_id: null, 
      att_name: "", 
      category_id: "", 
      description: "", 
      price: "", 
      seat: "", 
      image: "" 
    });

    const newImageFile = ref(null);
    let modalInstance = null;

    const API_URL = "http://localhost/projectgroup/php_api/attraction_crud.php";

    const fetchData = async () => {
      loading.value = true;
      try {
        const [resProd, resCat] = await Promise.all([
          fetch(API_URL),
          fetch(API_URL + "?type=categories")
        ]);
        const dProd = await resProd.json();
        const dCat = await resCat.json();
        if (dProd.success) products.value = dProd.data;
        if (dCat.success) categories.value = dCat.data;
      } catch (err) { console.error(err); }
      loading.value = false;
    };

    // ✅ ปรับปรุงการกรองข้อมูล ให้ค้นหา ID ได้ ป้องกันค่า Null
    const filteredProducts = computed(() => {
      if (!searchQuery.value) return products.value;

      const query = searchQuery.value.toLowerCase().trim();

      return products.value.filter(p => {
        const idMatch = p.att_id ? String(p.att_id).includes(query) : false;
        const nameMatch = p.att_name ? p.att_name.toLowerCase().includes(query) : false;
        const catMatch = p.category_name ? p.category_name.toLowerCase().includes(query) : false;
        
        return idMatch || nameMatch || catMatch;
      });
    });

    onMounted(() => {
      fetchData();
      modalInstance = new window.bootstrap.Modal(document.getElementById("tourModal"));
    });

    const openAddModal = () => {
      isEditMode.value = false;
      editForm.value = { att_id: null, att_name: "", category_id: "", description: "", price: "", seat: "", image: "" };
      newImageFile.value = null;
      modalInstance.show();
    };

    const openEditModal = (tour) => {
      isEditMode.value = true;
      editForm.value = { ...tour };
      newImageFile.value = null;
      modalInstance.show();
    };

    const handleFileUpload = (e) => { newImageFile.value = e.target.files[0]; };

    const saveProduct = async () => {
      const formData = new FormData();
      formData.append("action", isEditMode.value ? "update" : "add");
      if (isEditMode.value) formData.append("att_id", editForm.value.att_id);
      
      formData.append("att_name", editForm.value.att_name);
      formData.append("category_id", editForm.value.category_id);
      formData.append("description", editForm.value.description);
      formData.append("price", editForm.value.price);
      formData.append("seat", editForm.value.seat); 
      
      if (newImageFile.value) formData.append("image", newImageFile.value);

      try {
        const res = await fetch(API_URL, { method: "POST", body: formData });
        const result = await res.json();
        if (result.success) {
          fetchData();
          modalInstance.hide();
        } else {
           alert(result.message);
        }
      } catch (err) { alert("Error connecting to server"); }
    };

    const deleteProduct = async (id) => {
      if (!confirm("คุณแน่ใจหรือไม่ว่าต้องการลบสถานที่นี้?")) return;
      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("att_id", id);
      try {
        await fetch(API_URL, { method: "POST", body: formData });
        fetchData();
      } catch (err) { alert("Error deleting product"); }
    };

    return { 
      products, categories, loading, searchQuery, 
      filteredProducts, editForm, isEditMode, 
      openAddModal, openEditModal, handleFileUpload, 
      saveProduct, deleteProduct 
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
  min-width: 280px; height: 40px;
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

/* ปุ่มและรูปในตาราง */
.no-img { 
  width: 60px; height: 45px; background: #f8fafc; 
  display: flex; align-items: center; justify-content: center; 
  font-size: 0.6rem; color: #94a3b8; border-radius: 6px; border: 1px dashed #cbd5e1;
}
.btn-action {
  transition: all 0.2s ease;
  background-color: #ffffff;
}
.btn-action:hover {
  transform: translateY(-2px);
  background-color: #f8fafc;
}

/* Modal Input Focus */
.form-control:focus, .form-select:focus {
  border-color: #0d6efd;
  box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.1);
}

.table thead th { font-weight: 700; text-transform: uppercase; color: #64748b; padding: 1rem; border-bottom: 2px solid #e2e8f0; }

.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>