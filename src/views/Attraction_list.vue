<template>
  <div class="container mt-5 animate-fade-in">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h4 class="fw-bold text-dark mb-1">Attraction List</h4>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">ค้นหาทัวร์ตามหมวดหมู่ หรือชื่อสถานที่ที่คุณต้องการ</p>
      </div>

      <div class="d-flex gap-2 flex-wrap flex-md-nowrap">
        <select v-model="selectedCategory" class="category-select shadow-sm px-3">
          <option :value="0">ทุกหมวดหมู่</option>
          <option v-for="cat in categories" :key="cat.category_id" :value="cat.category_id">
            {{ cat.category_name }}
          </option>
        </select>

        <div class="search-container shadow-sm">
          <i class="bi bi-search text-muted ms-3"></i>
          <input 
            type="text" 
            v-model="searchQuery" 
            class="search-input" 
            placeholder="ค้นหาด้วยรหัส (ID) หรือชื่อสถานที่..."
          />
        </div>
      </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr style="font-size: 0.9rem;">
              <th class="ps-4">ID</th> <th>รูปภาพ</th>
              <th>ชื่อสถานที่</th>
              <th>หมวดหมู่</th> 
              <th>ราคา</th>
              <th class="text-center">สถานะ</th>
            </tr>
          </thead>
          <tbody>
            <tr 
              v-for="data in filteredData" 
              :key="data.att_id" 
              @click="goToDetail(data.att_id)"
              class="clickable-row"
            >
              <td class="ps-4 text-muted small font-monospace">{{ data.att_id }}</td>
              <td>
                <div class="image-wrapper shadow-sm">
                  <img :src="'http://localhost/projectgroup/php_api/uploads/' + data.image" class="rounded-3" :alt="data.att_name">
                </div>
              </td>
              <td>
                <div class="fw-bold text-dark" style="font-size: 0.95rem;">{{ data.att_name }}</div>
              </td>
              <td>
                <span class="badge bg-secondary-subtle text-secondary rounded-pill fw-medium px-3 py-2" style="font-size: 0.75rem;">
                  {{ data.category_name || 'ทั่วไป' }}
                </span>
              </td>
              <td>
                <span class="fw-bold text-success" style="font-size: 0.95rem;">{{ parseFloat(data.price).toLocaleString() }} ฿</span>
              </td>
              <td class="text-center">
                <span 
                  class="badge rounded-pill px-3 py-2 fw-medium" 
                  style="font-size: 0.75rem;"
                  :class="data.Seat > 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'"
                >
                  <i :class="data.Seat > 0 ? 'bi bi-person-check me-1' : ''"></i>
                  {{ data.Seat > 0 ? 'ว่าง ' + data.Seat + ' ที่' : 'เต็มแล้ว' }}
                </span>
              </td>
            </tr>
            <tr v-if="filteredData.length === 0 && !loading">
              <td colspan="6" class="text-center py-5 text-muted">
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
  </div>
</template>

<script>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";

export default {
  name: "AttractionList",
  setup() {
    const Alldata = ref([]);
    const categories = ref([]); 
    const selectedCategory = ref(0); 
    const searchQuery = ref("");
    const loading = ref(true);
    const error = ref(null);
    const router = useRouter();

    const fetchData = async () => {
      loading.value = true;
      try {
        const response = await fetch("http://localhost/projectgroup/php_api/attraction_crud.php");
        const result = await response.json();
        Alldata.value = result.success ? result.data : result;
      } catch (err) {
        error.value = err.message;
      } finally {
        loading.value = false;
      }
    };

    const fetchCategories = async () => {
      try {
        // แนะนำให้แก้ไขไฟล์ categories.php ให้ส่งข้อมูลมาด้วยนะคะ (ถ้ามี)
        const response = await fetch("http://localhost/projectgroup/php_api/attraction_crud.php?type=categories");
        const result = await response.json();
        if (result.success) {
          categories.value = result.data;
        }
      } catch (err) {
        console.error("โหลดหมวดหมู่ไม่สำเร็จ");
      }
    };

    const goToDetail = (id) => {
      router.push({ name: 'attraction_detail', query: { id: id } });
    };

    // ✅ ปรับปรุงการกรองข้อมูล ให้ค้นหา ID ได้ ป้องกันค่า Null
    const filteredData = computed(() => {
      return Alldata.value.filter(item => {
        // 1. ค้นหาจากรหัส (ID) หรือ ชื่อสถานที่
        const query = searchQuery.value.toLowerCase().trim();
        const idMatch = item.att_id ? String(item.att_id).includes(query) : false;
        const nameMatch = item.att_name ? item.att_name.toLowerCase().includes(query) : false;
        const matchesSearch = idMatch || nameMatch;

        // 2. กรองด้วยหมวดหมู่ (0 = ทั้งหมด)
        const matchesCategory = selectedCategory.value === 0 || item.category_id == selectedCategory.value;
        
        return matchesSearch && matchesCategory;
      });
    });

    onMounted(() => {
      fetchData();
      fetchCategories();
    });

    return { 
      Alldata, categories, selectedCategory, searchQuery, 
      loading, error, filteredData, goToDetail 
    };
  }
};
</script>

<style scoped>
/* สไตล์ Dropdown หมวดหมู่ */
.category-select {
  border: 1px solid #e2e8f0;
  border-radius: 50px;
  background-color: white;
  font-size: 0.85rem;
  outline: none;
  min-width: 150px;
  height: 42px;
  cursor: pointer;
  color: #475569;
}

.category-select:focus {
  border-color: #0d6efd;
}

/* สไตล์กล่องค้นหา */
.search-container {
  display: flex;
  align-items: center;
  background-color: white;
  border: 1px solid #e2e8f0;
  border-radius: 50px;
  min-width: 250px;
  height: 42px;
}
.search-input { border: none; outline: none; width: 100%; padding: 0 15px; font-size: 0.85rem; background: transparent; }

/* สไตล์ตาราง */
.clickable-row { cursor: pointer; transition: all 0.2s ease; }
.clickable-row:hover { background-color: #f1f5f9 !important; }
.image-wrapper { width: 80px; height: 55px; overflow: hidden; border-radius: 8px; }
.image-wrapper img { width: 100%; height: 100%; object-fit: cover; }

.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>