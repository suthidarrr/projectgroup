<template>
  <div class="container mt-5 animate-fade-in">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h2 class="fw-bold text-dark mb-1">🛠️ Manage Touring</h2>
      </div>
      
      <div class="d-flex gap-2">
        <div class="search-container shadow-sm">
          <i class="bi bi-search text-muted ms-3"></i>
          <input v-model="searchQuery" type="text" class="search-input" placeholder="Search touring..." />
        </div>
        
        <button class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold" @click="openAddModal">
          <i class="bi bi-plus-circle me-2"></i> Add Tour
        </button>
      </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="ps-4">No.</th>
              <th>Image</th>
              <th>Destination</th>
              <th>Category</th>
              <th>Price</th>
              <th class="text-center">Seats</th> <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(tour, index) in filteredProducts" :key="tour.att_id">
              <td class="ps-4 text-muted">{{ index + 1 }}</td>
              <td>
                <img v-if="tour.image" :src="'http://localhost/projectgroup/php_api/uploads/' + tour.image" class="rounded-3 border" width="60" height="45" style="object-fit: cover;" />
                <div v-else class="no-img">No Img</div>
              </td>
              <td>
                <div class="fw-bold text-dark">{{ tour.att_name }}</div>
                <div class="text-muted small text-truncate" style="max-width: 150px;">{{ tour.description }}</div>
              </td>
              <td>
                <span class="badge bg-info-subtle text-info rounded-pill px-3">{{ tour.category_name }}</span>
              </td>
              <td class="fw-bold text-success">{{ parseFloat(tour.price).toLocaleString() }} ฿</td>
              <td class="text-center">{{ tour.seat }}</td> <td class="text-center">
                <div class="btn-group shadow-sm rounded-3">
                  <button class="btn btn-outline-warning btn-sm px-3" @click="openEditModal(tour)">
                    <i class="bi bi-pencil"></i>
                  </button>
                  <button class="btn btn-outline-danger btn-sm px-3" @click="deleteProduct(tour.att_id)">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <tr v-if="filteredProducts.length === 0 && !loading">
              <td colspan="7" class="text-center py-5 text-muted">ไม่พบข้อมูลที่ต้องการ</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal fade" id="tourModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pb-0">
            <h5 class="fw-bold">{{ isEditMode ? "📝 Edit Tour" : "🆕 New Tour" }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body py-4">
            <form @submit.prevent="saveProduct">
              <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label fw-bold small">ชื่อสถานที่ท่องเที่ยว</label>
                  <input v-model="editForm.att_name" type="text" class="form-control rounded-3" required />
                </div>
                <div class="col-md-4">
                  <label class="form-label fw-bold small">ประเภททัวร์</label>
                  <select v-model="editForm.category_id" class="form-select rounded-3" required>
                    <option value="" disabled>-- เลือกหมวดหมู่ --</option>
                    <option v-for="cat in categories" :key="cat.category_id" :value="cat.category_id">
                      {{ cat.category_name }}
                    </option>
                  </select>
                </div>
                <div class="col-12">
                  <label class="form-label fw-bold small">รายละเอียด</label>
                  <textarea v-model="editForm.description" class="form-control rounded-3" rows="3"></textarea>
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small">ราคา (THB)</label>
                  <input v-model="editForm.price" type="number" class="form-control rounded-3" required />
                </div>
                <div class="col-md-6">
                  <label class="form-label fw-bold small">จำนวนที่นั่ง</label> <input v-model="editForm.seat" type="number" class="form-control rounded-3" required /> </div>
                <div class="col-12">
                  <label class="form-label fw-bold small">รูปภาพ</label>
                  <input type="file" @change="handleFileUpload" class="form-control rounded-3" :required="!isEditMode" />
                  <div v-if="isEditMode && editForm.image" class="mt-2">
                    <img :src="'http://localhost/projectgroup/php_api/uploads/' + editForm.image" class="rounded border" width="80" />
                  </div>
                </div>
              </div>
              <div class="d-grid mt-4">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                  บันทึกข้อมูล
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

    // ✅ เปลี่ยนชื่อตัวแปรจาก stock เป็น seat ให้ตรงตามที่คุณต้องการ
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

    const filteredProducts = computed(() => {
      return products.value.filter(p => 
        (p.att_name || "").toLowerCase().includes(searchQuery.value.toLowerCase()) ||
        (p.category_name || "").toLowerCase().includes(searchQuery.value.toLowerCase())
      );
    });

    onMounted(() => {
      fetchData();
      modalInstance = new window.bootstrap.Modal(document.getElementById("tourModal"));
    });

    const openAddModal = () => {
      isEditMode.value = false;
      // ✅ ล้างค่าฟอร์มโดยใช้ชื่อตัวแปรใหม่ (seat)
      editForm.value = { att_id: null, att_name: "", category_id: "", description: "", price: "", seat: "", image: "" };
      newImageFile.value = null;
      modalInstance.show();
    };

    const openEditModal = (tour) => {
      isEditMode.value = true;
      // ✅ ก๊อปปี้ค่ารวมถึงตัวแปร seat มาใส่ในฟอร์ม
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
      formData.append("seat", editForm.value.seat); // ✅ เปลี่ยนการส่งข้อมูลจาก stock เป็น seat
      
      if (newImageFile.value) formData.append("image", newImageFile.value);

      try {
        const res = await fetch(API_URL, { method: "POST", body: formData });
        const result = await res.json();
        if (result.success) {
          alert(result.message);
          fetchData();
          modalInstance.hide();
        }
      } catch (err) { alert("Error connecting to server"); }
    };

    const deleteProduct = async (id) => {
      if (!confirm("ลบสถานที่นี้?")) return;
      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("att_id", id);
      try {
        await fetch(API_URL, { method: "POST", body: formData });
        fetchData();
      } catch (err) { alert("Error deleting product"); }
    };

    return { products, categories, loading, searchQuery, filteredProducts, editForm, isEditMode, openAddModal, openEditModal, handleFileUpload, saveProduct, deleteProduct };
  }
};
</script>

<style scoped>
/* CSS คงเดิมทั้งหมดตาม UI ที่คุณต้องการ */
.search-container { display: flex; align-items: center; background: white; border: 1px solid #e2e8f0; border-radius: 50px; min-width: 250px; height: 42px; }
.search-input { border: none; outline: none; width: 100%; padding: 0 15px; font-size: 0.95rem; background: transparent; }
.no-img { width: 60px; height: 45px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; font-size: 0.5rem; color: #94a3b8; border-radius: 4px; }
.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.table thead th { font-size: 0.75rem; text-transform: uppercase; color: #64748b; padding: 1rem; }
</style>