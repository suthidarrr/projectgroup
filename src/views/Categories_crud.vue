<template>
  <div class="container mt-5 animate-fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold text-dark">📂 Manage Categories</h2>
      </div>
      <button class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold" @click="openAddModal">
        <i class="bi bi-plus-circle me-2"></i> Add New Category
      </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="bg-light">
            <tr>
              <th class="ps-4">No.</th>
              <th>ID</th>
              <th>Category Name</th>
              <th class="text-center">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(cat, index) in categories" :key="cat.category_id">
              <td class="ps-4 text-muted">{{ index + 1 }}</td>
              <td class="fw-bold text-primary">#{{ cat.category_id }}</td>
              <td class="fw-semibold">{{ cat.category_name }}</td>
              <td class="text-center">
                <div class="btn-group shadow-sm">
                  <button class="btn btn-outline-warning btn-sm px-3" @click="openEditModal(cat)">Edit</button>
                  <button class="btn btn-outline-danger btn-sm px-3" @click="deleteCategory(cat.category_id)">Delete</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <div class="modal fade" id="categoryModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
          <div class="modal-header border-0 pb-0">
            <h5 class="fw-bold">{{ isEditMode ? "📝 Edit Category" : "🆕 Add Category" }}</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body py-4">
            <form @submit.prevent="saveCategory">
              <div class="mb-4">
                <label class="form-label fw-bold small">CATEGORY NAME</label>
                <input v-model="currentCat.category_name" type="text" class="form-control" required>
              </div>
              <div class="d-grid">
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
import { ref, onMounted } from "vue";

export default {
  setup() {
    const categories = ref([]);
    const loading = ref(true);
    const error = ref(null);
    const currentCat = ref({ category_name: "" });
    const isEditMode = ref(false);
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
      if (!confirm("ลบหมวดหมู่นี้?")) return;
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

    return { categories, loading, error, currentCat, isEditMode, openAddModal, openEditModal, saveCategory, deleteCategory };
  }
};
</script>