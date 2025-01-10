// URL API
const apiUrl = "http://localhost:8080/weblaptop/api.php?table=laptops"; // Sesuaikan dengan path yang benar

// Fungsi untuk menampilkan data laptop dalam tabel
function renderTable(data) {
  const tableBody = document.getElementById("data-table");
  tableBody.innerHTML = ""; // Kosongkan tabel sebelumnya
  data.forEach((laptop) => {
    const row = document.createElement("tr");
    row.innerHTML = `
      <td>${laptop.laptop_id}</td>
      <td>${laptop.brand}</td>
      <td>${laptop.model}</td>
      <td>${laptop.specifications}</td>
      <td>${laptop.price}</td>
      <td>${laptop.stock}</td>
      <td>
        <button onclick="editLaptop(${laptop.laptop_id})">Edit</button>
        <button onclick="deleteLaptop(${laptop.laptop_id})">Delete</button>
      </td>
    `;
    tableBody.appendChild(row);
  });
}

// Fungsi untuk mengambil data dari API
async function fetchData() {
  try {
    const response = await fetch(apiUrl);
    const data = await response.json();
    renderTable(data);
  } catch (error) {
    console.error("Error fetching data:", error);
  }
}

// Fungsi untuk menambah data laptop
async function addLaptop(event) {
  event.preventDefault();

  const formData = new FormData(document.getElementById("data-form"));
  const laptopData = Object.fromEntries(formData);

  const response = await fetch(apiUrl, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(laptopData),
  });

  const result = await response.json();
  if (result.message) {
    alert(result.message);
    fetchData(); // Refresh data
  } else {
    alert("Failed to add laptop");
  }
}

// Fungsi untuk mengedit data laptop
async function editLaptop(id) {
  const response = await fetch(`${apiUrl}&id=${id}`);
  const laptop = await response.json();

  document.getElementById("id").value = laptop.laptop_id;
  document.getElementById("brand").value = laptop.brand;
  document.getElementById("model").value = laptop.model;
  document.getElementById("specifications").value = laptop.specifications;
  document.getElementById("price").value = laptop.price;
  document.getElementById("stock").value = laptop.stock;
}

// Fungsi untuk mengupdate data laptop
async function updateLaptop(event) {
  event.preventDefault();

  const formData = new FormData(document.getElementById("data-form"));
  const laptopData = Object.fromEntries(formData);

  const response = await fetch(apiUrl, {
    method: "PUT",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify(laptopData),
  });

  const result = await response.json();
  if (result.message) {
    alert(result.message);
    fetchData(); // Refresh data
  } else {
    alert("Failed to update laptop");
  }
}

// Fungsi untuk menghapus data laptop
async function deleteLaptop(id) {
  if (confirm("Are you sure you want to delete this laptop?")) {
    const response = await fetch(apiUrl, {
      method: "DELETE",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ laptop_id: id }),
    });

    const result = await response.json();
    if (result.message) {
      alert(result.message);
      fetchData(); // Refresh data
    } else {
      alert("Failed to delete laptop");
    }
  }
}

// Event listener untuk submit form
document.getElementById("data-form").addEventListener("submit", function (event) {
  if (document.getElementById("id").value) {
    updateLaptop(event);
  } else {
    addLaptop(event);
  }
});

// Ambil data laptop saat halaman dimuat
fetchData();
