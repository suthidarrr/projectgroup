<template>
  <div class="container mt-4 animate-fade-in">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
      <div>
        <h4 class="fw-bold text-dark mb-1">Explore Destinations</h4>
        <p class="text-muted mb-0" style="font-size: 0.85rem;">ค้นพบสถานที่ท่องเที่ยวที่น่าสนใจสำหรับทริปของคุณ</p>
      </div>

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

    <div class="row g-4 mb-5">
      <div 
        v-for="data in filteredData" 
        :key="data.att_id" 
        class="col-12 col-md-6 col-lg-4 col-xl-3"
      >
        <router-link 
          :to="{ name: 'attraction_detail', query: { id: data.att_id } }" 
          class="text-decoration-none"
        >
          <div class="attraction-card shadow-sm h-100">
            <div class="image-wrapper">
              <img
                :src="'http://localhost/projectgroup/php_api/uploads/' + data.image"
                class="card-img-top"
                :alt="data.att_name"
              >
              <div class="seat-badge shadow-sm">
                <span v-if="data.Seat > 0" class="text-success fw-bold">
                  <i class="bi bi-people-fill me-1"></i>ว่าง {{ data.Seat }} ที่
                </span>
                <span v-else class="text-danger fw-bold">เต็มแล้ว</span>
              </div>

              <div class="price-badge shadow-sm">
                {{ parseFloat(data.price).toLocaleString() }} <span class="fw-normal" style="font-size: 0.75rem;">฿</span>
              </div>
            </div>

            <div class="card-content p-3 text-start">
              <h6 class="fw-bold text-dark mb-2 title-truncate">{{ data.att_name }}</h6>
              <div class="text-primary small fw-semibold link-hover">
                ดูรายละเอียด <i class="bi bi-arrow-right-short"></i>
              </div>
            </div>
          </div>
        </router-link>
      </div>
    </div>

    <div v-if="filteredData.length === 0 && !loading" class="text-center my-5 py-5 text-muted">
      <i class="bi bi-compass fs-1 d-block mb-3 opacity-50"></i>
      <h5>ไม่พบสถานที่ท่องเที่ยวที่คุณค้นหา</h5>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted" style="font-size: 0.85rem;">กำลังโหลดข้อมูลสถานที่ท่องเที่ยว...</p>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, computed } from "vue";

export default {
  name: "AttractionList",
  setup() {
    const Alldata = ref([]);
    const loading = ref(true);
    const searchQuery = ref("");

    const fetchData = async () => {
      try {
        const response = await fetch("http://localhost/projectgroup/php_api/attraction_crud.php");
        const result = await response.json();
        
        if (result.success) {
          Alldata.value = result.data;
        } else {
          Alldata.value = Array.isArray(result) ? result : [];
        }
      } catch (err) {
        console.error("Fetch Error:", err);
      } finally {
        loading.value = false;
      }
    };

    // ✅ ค้นหาด้วย ID หรือ ชื่อ
    const filteredData = computed(() => {
      if (!searchQuery.value) return Alldata.value;

      const searchTerm = searchQuery.value.toLowerCase().trim();

      return Alldata.value.filter((item) => {
        const idMatch = item.att_id ? String(item.att_id).includes(searchTerm) : false;
        const nameMatch = item.att_name ? item.att_name.toLowerCase().includes(searchTerm) : false;
        return idMatch || nameMatch;
      });
    });

    onMounted(fetchData);

    return { filteredData, searchQuery, loading };
  }
};
</script>

<style scoped>
/* การ์ด */
.attraction-card {
  background: white;
  border-radius: 16px;
  overflow: hidden;
  border: 1px solid #f1f5f9;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}
.attraction-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.08) !important;
}

/* รูปภาพ */
.image-wrapper { position: relative; height: 180px; }
.card-img-top { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
.attraction-card:hover .card-img-top { transform: scale(1.05); }

/* 🚩 ป้ายที่นั่ง (ขวาบน) - ลดขนาดตัวอักษรและ padding */
.seat-badge {
  position: absolute; top: 12px; right: 12px; 
  background: rgba(255, 255, 255, 0.95);
  padding: 4px 10px; border-radius: 50px; 
  font-size: 0.75rem; /* ขนาดเล็กลง */
}

/* 🚩 ป้ายราคา (ขวาล่าง) - ลดขนาดตัวอักษรและ padding */
.price-badge {
  position: absolute; bottom: 12px; right: 12px; 
  background: rgba(255, 255, 255, 0.95); 
  color: #2d3748; padding: 4px 12px; border-radius: 50px; 
  font-weight: 800; font-size: 0.85rem; /* ขนาดเล็กลง */
}

/* ชื่อสถานที่ตัดคำถ้ายาวไป */
.title-truncate {
  font-size: 1rem; /* ล็อกขนาดชื่อสถานที่ให้ไม่ใหญ่เกินไป */
  display: -webkit-box; 
  -webkit-line-clamp: 1; 
  -webkit-box-orient: vertical; 
  overflow: hidden;
}

/* ช่องค้นหา */
.search-container {
  display: flex; align-items: center; background: white; 
  border: 1px solid #e2e8f0; border-radius: 50px; 
  min-width: 280px; height: 42px; /* ลดความสูงช่องค้นหาลงนิดนึง */
}
.search-input { 
  border: none; outline: none; background: transparent; 
  width: 100%; padding: 0 15px; font-size: 0.85rem; 
}

/* ลูกเล่น Hover */
.link-hover { transition: transform 0.2s; }
.attraction-card:hover .link-hover { transform: translateX(3px); }

.animate-fade-in { animation: fadeIn 0.4s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
</style>