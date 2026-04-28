<template>
  <div class="app-home animate-fade-in">
    <div class="hero-banner py-5 mb-5 shadow-sm position-relative overflow-hidden">
      <div class="container text-center py-5 position-relative z-index-1">
        <h1 class="display-4 fw-bolder text-white mb-3 text-shadow tracking-tight">Explore the Beauty of Thailand</h1>
        <p class="lead text-white mb-5 fw-medium mx-auto" style="max-width: 600px;">
          Discover the best domestic tours, hidden gems, and create unforgettable memories with our premium packages.
        </p>
        
        <div class="row justify-content-center mb-4">
          <div class="col-md-8 col-lg-6">
            <div class="input-group shadow-lg rounded-pill p-1 bg-white">
              <span class="input-group-text bg-transparent border-0 ps-3">
                <i class="bi bi-search text-muted"></i>
              </span>
              <input 
                v-model="searchQuery" 
                type="text" 
                class="form-control border-0 shadow-none bg-transparent" 
                placeholder="Search by ID or destination name..." 
              />
              <button class="btn btn-primary rounded-pill px-4 fw-bold" @click="searchQuery = searchQuery">
                Search
              </button>
            </div>
          </div>
        </div>
      </div>
      <div class="overlay-gradient"></div>
    </div>

    <div class="container">
      <div v-if="loading" class="text-center my-5 py-5">
        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;"></div>
        <p class="mt-3 text-muted fw-bold">Curating the best destinations for you...</p>
      </div>

      <div v-else-if="error" class="alert alert-danger text-center shadow-sm rounded-4 p-4 border-0">
        <i class="bi bi-exclamation-triangle-fill fs-3 d-block mb-2 text-danger"></i> 
        <span class="fw-medium">{{ error }}</span>
      </div>

      <div v-else class="mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
          <div>
            <div class="d-flex align-items-center gap-2 mb-1">
              <i class="bi bi-geo-alt text-primary fs-5"></i>
              <h2 class="fw-bold mb-0">Recommended Destinations</h2>
            </div>
            <p class="text-muted mb-0 ps-4">Handpicked popular spots just for you.</p>
          </div>
          <router-link to="/attraction" class="btn btn-outline-primary rounded-pill px-4 fw-semibold btn-sm">
            View All Tours <i class="bi bi-arrow-right ms-1"></i>
          </router-link>
        </div>

        <div class="row g-4">
          <div 
            class="col-lg-4 col-md-6" 
            v-for="attraction in filteredAttractions" 
            :key="attraction.att_id" 
          >
            <div class="card attraction-card h-100 border-0 shadow-sm overflow-hidden rounded-4">
              <div class="image-wrapper">
                <img 
                  :src="getImage(attraction.image)" 
                  class="card-img-top"
                  :alt="attraction.att_name"
                />
                <div class="category-tag shadow-sm" v-if="attraction.category_name">
                  <i class="bi bi-tag-fill me-1"></i> {{ attraction.category_name }}
                </div>
                
                <div class="price-tag shadow-sm d-flex align-items-center gap-1">
                  <span class="fs-6 fw-bolder">{{ parseFloat(attraction.price).toLocaleString() }}</span> 
                  <span class="small opacity-75">฿</span>
                </div>
                
                <div class="seat-badge shadow-sm d-flex align-items-center gap-1" v-if="attraction.Seat > 0">
                  <i class="bi bi-person-check-fill text-success"></i> 
                  <span class="text-dark">Available: {{ attraction.Seat }}</span>
                </div>
              </div>

              <div class="card-body p-4 d-flex flex-column">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h5 class="fw-bold mb-0 text-truncate w-75">
                    <router-link 
                      :to="{ name: 'attraction_detail', query: { id: attraction.att_id } }"
                      class="text-dark text-decoration-none hover-primary"
                    >
                      {{ attraction.att_name }}
                    </router-link>
                  </h5>
                </div>
                
                <p class="card-text text-muted small lh-base mb-4 flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  {{ attraction.description || 'Experience the unique charm of this destination and create unforgettable memories.' }}
                </p>

                <div class="d-flex gap-3 mb-4 pt-3 border-top border-light">
                  <div class="small text-muted d-flex align-items-center gap-1">
                    <i class="bi bi-clock text-primary"></i> 1 Day Trip
                  </div>
                  <div class="small text-muted d-flex align-items-center gap-1">
                    <i class="bi bi-shield-check text-primary"></i> Insured
                  </div>
                </div>

                <router-link 
                  :to="{ name: 'attraction_detail', query: { id: attraction.att_id } }"
                  class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm d-flex justify-content-center align-items-center gap-2 mt-auto btn-book"
                >
                  Book Now <i class="bi bi-calendar2-check"></i>
                </router-link>
              </div>
            </div>
          </div>

          <div v-if="filteredAttractions.length === 0 && !loading" class="col-12 text-center py-5">
            <div class="p-5 bg-light rounded-4 border border-dashed">
              <i class="bi bi-search fs-1 text-muted mb-3 d-block"></i>
              <h5 class="fw-bold text-dark">No destinations found</h5>
              <p class="text-muted">We couldn't find any tours matching "{{ searchQuery }}".</p>
              <button @click="searchQuery = ''" class="btn btn-outline-primary rounded-pill mt-2">Clear Search</button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <footer class="footer mt-5 py-4 bg-white border-top">
      <div class="container text-center">
        <p class="mb-0 fw-medium text-muted small d-flex justify-content-center align-items-center gap-2">
          <i class="bi bi-c-circle"></i> 2026 PAI TIEW GUN. All Rights Reserved.
        </p>
      </div>
    </footer>
  </div>
</template>

<script>
import { ref, onMounted, computed } from "vue";

export default {
  name: "Home",
  setup() {
    const Alldata = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const searchQuery = ref("");

    const getImage = (image) => {
      if (!image) return "https://via.placeholder.com/400x300?text=No+Preview";
      return `http://localhost/projectgroup/php_api/uploads/${image}`;
    };

    const fetchData = async () => {
      try {
        const response = await fetch("http://localhost/projectgroup/php_api/show_attraction_home.php");
        const data = await response.json();
        Alldata.value = data.success ? data.data : data;
      } catch (err) {
        error.value = "Unable to load travel packages. Please check your connection.";
      } finally {
        loading.value = false;
      }
    };

    // ✅ ส่วนที่แก้ไขแล้ว: ดักจับ Error และค้นหาได้ครบทุกฟิลด์
    const filteredAttractions = computed(() => {
      // 1. ถ้าไม่ได้พิมพ์ค้นหา ให้โชว์แค่ 3 อันดับแรก (Top 3)
      if (!searchQuery.value) {
         return Alldata.value.slice(0, 3); 
      }
      
      // 2. ถ้ามีการพิมพ์ค้นหา ให้หาจากข้อมูล "ทั้งหมด"
      const query = searchQuery.value.toLowerCase().trim();
      
      return Alldata.value.filter(attraction => {
        const idMatch = attraction.att_id ? String(attraction.att_id).includes(query) : false;
        const nameMatch = attraction.att_name ? attraction.att_name.toLowerCase().includes(query) : false;
        const descMatch = attraction.description ? attraction.description.toLowerCase().includes(query) : false;
        const catMatch = attraction.category_name ? attraction.category_name.toLowerCase().includes(query) : false;

        return idMatch || nameMatch || descMatch || catMatch;
      });
    });

    onMounted(fetchData);
    
    return { 
      Alldata, 
      loading, 
      error, 
      getImage,
      searchQuery,
      filteredAttractions
    };
  }
};
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

.text-shadow { text-shadow: 2px 2px 8px rgba(0,0,0,0.5); }
.tracking-tight { letter-spacing: -0.5px; }

.hero-banner {
  background: url('http://localhost/projectgroup/php_api/pic/banner.jpg');
  background-size: cover; 
  background-position: center; 
  border-radius: 0 0 40px 40px;
  min-height: 450px;
  display: flex;
  align-items: center;
}

.overlay-gradient {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(to bottom, rgba(15, 23, 42, 0.7) 0%, rgba(15, 23, 42, 0.4) 100%);
  z-index: 0;
}

.z-index-1 { position: relative; z-index: 1; }

.attraction-card { 
  transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); 
  border: 1px solid rgba(0,0,0,0.05) !important;
}
.attraction-card:hover { 
  transform: translateY(-8px); 
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08) !important; 
}

.image-wrapper { position: relative; height: 240px; overflow: hidden; }
.image-wrapper img { height: 100%; width: 100%; object-fit: cover; transition: transform 0.6s ease; }
.attraction-card:hover .image-wrapper img { transform: scale(1.08); }

.price-tag { 
  position: absolute; bottom: 15px; right: 15px; 
  background-color: #0d6efd; color: white; 
  padding: 6px 14px; border-radius: 12px; 
}

.category-tag {
  position: absolute; top: 15px; left: 15px;
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  color: white; padding: 4px 12px; border-radius: 6px;
  font-size: 0.75rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;
}

.seat-badge {
  position: absolute; top: 15px; right: 15px;
  background-color: rgba(255, 255, 255, 0.95);
  padding: 5px 12px; border-radius: 50px;
  font-size: 0.75rem; font-weight: 700;
}

.hover-primary:hover { color: #0d6efd !important; }

.btn-book { transition: all 0.3s; }
.attraction-card:hover .btn-book { background-color: #0b5ed7; transform: scale(1.02); }

.border-dashed { border-style: dashed !important; border-width: 2px !important; }
</style>