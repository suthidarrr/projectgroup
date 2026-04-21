<template>
  <div class="app-home animate-fade-in">
    <div class="hero-banner py-5 mb-5 shadow-sm">
      <div class="container text-center py-5">
        <h1 class="display-4 fw-bold text-white mb-3 text-shadow">Explore the Beauty of Thailand 🌴</h1>
        <p class="lead text-white-50 mb-4 fw-medium">Discover the best domestic tours and create unforgettable memories with us.</p>
        <router-link to="/attraction" class="btn btn-light btn-lg px-5 rounded-pill fw-bold shadow text-primary">
          Explore Tours
        </router-link>
      </div>
    </div>

    <div class="container">
      <div v-if="loading" class="text-center my-5">
        <div class="spinner-grow text-primary" role="status"></div>
        <p class="mt-3 text-muted fw-bold">Finding the best destinations for you...</p>
      </div>

      <div v-else-if="error" class="alert alert-danger text-center shadow-sm rounded-4">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ error }}
      </div>

      <div v-else class="mb-5">
        <div class="d-flex justify-content-between align-items-end mb-4">
          <div>
            <h2 class="fw-bold mb-0 border-start border-primary border-4 ps-3">Recommended Destinations</h2>
            <p class="text-muted mb-0">Handpicked popular spots just for you.</p>
          </div>
          <router-link to="/attraction" class="text-decoration-none fw-bold small text-uppercase">View All →</router-link>
        </div>

        <div class="row">
          <div 
            class="col-lg-4 col-md-6 mb-4" 
            v-for="attraction in Alldata" 
            :key="attraction.att_id" 
          >
            <div class="card attraction-card h-100 border-0 shadow-sm overflow-hidden rounded-4">
              <div class="image-wrapper">
                <img 
                  :src="getImage(attraction.image)" 
                  class="card-img-top"
                  :alt="attraction.att_name"
                />
                <div class="price-tag shadow-sm">{{ parseFloat(attraction.price).toLocaleString() }} ฿</div>
                <div class="seat-badge shadow-sm" v-if="attraction.Seat > 0">
                  <i class="bi bi-people-fill me-1"></i> ว่าง {{ attraction.Seat }}
                </div>
              </div>

              <div class="card-body p-4">
                <h5 class="fw-bold mb-2">
                  <router-link 
                    :to="{ name: 'attraction_detail', query: { id: attraction.att_id } }"
                    class="text-dark text-decoration-none hover-primary"
                  >
                    {{ attraction.att_name }}
                  </router-link>
                </h5>
                <p class="card-text text-muted small lh-base" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                  {{ attraction.description || 'Experience the unique charm of this destination.' }}
                </p>
              </div>

              <div class="card-footer bg-white border-0 p-4 pt-0">
                <router-link 
                  :to="{ name: 'attraction_detail', query: { id: attraction.att_id } }"
                  class="btn btn-primary w-100 rounded-pill fw-bold py-2 shadow-sm"
                >
                  Book Now
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <footer class="footer mt-5 py-4 bg-white border-top">
      <div class="container text-center">
        <p class="mb-0 fw-medium text-muted small">
          © 2026 Domestic Tour Booking System. All Rights Reserved.
        </p>
      </div>
    </footer>
  </div>
</template>

<script>
import { ref, onMounted } from "vue";

export default {
  name: "Home",
  setup() {
    const Alldata = ref([]);
    const loading = ref(true);
    const error = ref(null);

    const getImage = (image) => {
      if (!image) return "https://via.placeholder.com/400x300?text=No+Preview";
      return `http://localhost/projectgroup/php_api/uploads/${image}`;
    };

    const fetchData = async () => {
      try {
        const response = await fetch("http://localhost/projectgroup/php_api/show_attraction_home.php");
        const data = await response.json();
        // รองรับทั้งข้อมูลแบบ Array และแบบที่มี { success, data }
        Alldata.value = data.success ? data.data : data;
      } catch (err) {
        error.value = "Unable to load travel packages.";
      } finally {
        loading.value = false;
      }
    };

    onMounted(fetchData);
    return { Alldata, loading, error, getImage };
  }
};
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.8s ease-out; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }

.text-shadow { text-shadow: 2px 2px 4px rgba(0,0,0,0.3); }

.hero-banner {
  background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), 
              url('http://localhost/projectgroup/php_api/pic/banner.jpg');
  background-size: cover; background-position: center; 
  border-radius: 0 0 40px 40px;
}

.attraction-card { 
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
}
.attraction-card:hover { 
  transform: translateY(-10px); 
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1) !important; 
}

.image-wrapper { position: relative; height: 220px; overflow: hidden; }
.image-wrapper img { height: 100%; width: 100%; object-fit: cover; transition: transform 0.5s ease; }
.attraction-card:hover .image-wrapper img { transform: scale(1.1); }

.price-tag { 
  position: absolute; bottom: 15px; left: 15px; 
  background-color: #0d6efd; color: white; 
  padding: 4px 12px; border-radius: 8px; 
  font-weight: 700; font-size: 0.9rem; 
}

.seat-badge {
  position: absolute; top: 15px; right: 15px;
  background-color: rgba(255, 255, 255, 0.9);
  color: #198754; padding: 4px 10px; border-radius: 50px;
  font-size: 0.75rem; font-weight: 800;
}

.hover-primary:hover { color: #0d6efd !important; }
</style>