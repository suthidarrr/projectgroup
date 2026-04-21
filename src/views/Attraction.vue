<template>
  <div class="container mt-5 animate-fade-in">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 gap-3">
      <div>
        <h2 class="fw-bold text-dark mb-1">🌍 Explore Destinations</h2>
        <p class="text-muted mb-0">ค้นพบสถานที่ท่องเที่ยวที่น่าสนใจสำหรับทริปของคุณ</p>
      </div>

      <div class="search-container shadow-sm">
        <i class="bi bi-search text-muted ms-3"></i>
        <input 
          type="text" 
          v-model="searchQuery" 
          class="search-input" 
          placeholder="ค้นหาสถานที่หรือทัวร์..."
        />
      </div>
    </div>

    <div class="row g-4">
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
                alt="attraction image"
              >
              <div class="price-badge shadow-sm">
                {{ parseFloat(data.price).toLocaleString() }} ฿
              </div>
            </div>

            <div class="card-content p-3 text-center">
              <h5 class="fw-bold text-dark mb-1">{{ data.att_name }}</h5>
              <div class="text-primary small fw-semibold">
                คลิกดูรายละเอียด <i class="bi bi-arrow-right-short"></i>
              </div>
            </div>
          </div>
        </router-link>
      </div>
    </div>

    <div v-if="filteredData.length === 0 && !loading" class="text-center my-5 py-5 text-muted">
      <i class="bi bi-geo-alt fs-1 d-block mb-3 opacity-50"></i>
      <h5>ไม่พบสถานที่ท่องเที่ยวที่คุณค้นหา</h5>
    </div>

    <div v-if="loading" class="text-center my-5">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3 text-muted">กำลังโหลดข้อมูลสถานที่ท่องเที่ยว...</p>
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
        // แนะนำให้ใช้ไฟล์ที่ดึงข้อมูลครบถ้วน
        const response = await fetch("http://localhost/projectgroup/php_api/attraction_crud.php");
        const result = await response.json();
        
        // ตรวจสอบว่า API ส่งกลับมาในรูปแบบไหน (Array หรือ {success, data})
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

    const filteredData = computed(() => {
      return Alldata.value.filter((item) => {
        const searchTerm = searchQuery.value.toLowerCase();
        return (item.att_name || "").toLowerCase().includes(searchTerm);
      });
    });

    onMounted(fetchData);

    return { filteredData, searchQuery, loading };
  }
};
</script>

<style scoped>
/* CSS สวยๆ ของคุณคงเดิมค่ะ */
.attraction-card {
  background: white;
  border-radius: 20px;
  overflow: hidden;
  border: 1px solid #f1f5f9;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}
.attraction-card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1) !important;
}
.image-wrapper { position: relative; height: 200px; }
.card-img-top { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
.attraction-card:hover .card-img-top { transform: scale(1.1); }
.price-badge {
  position: absolute; top: 15px; right: 15px; background: white; 
  color: #2d3748; padding: 6px 14px; border-radius: 50px; font-weight: 800; font-size: 0.9rem;
}
.search-container {
  display: flex; align-items: center; background: white; 
  border: 1px solid #e2e8f0; border-radius: 50px; min-width: 300px; height: 48px;
}
.search-input { border: none; outline: none; background: transparent; width: 100%; padding: 0 15px; }
.animate-fade-in { animation: fadeIn 0.5s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
</style>