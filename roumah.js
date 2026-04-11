const categories = ["Semua", "Nasi", "Minuman", "Snack"];

let menus = [];
let activeCategory = "Semua";
let cart = [];
let customerOrder = null;

const rupiah = (value) =>
  new Intl.NumberFormat("id-ID", {
    style: "currency",
    currency: "IDR",
    maximumFractionDigits: 0,
  }).format(value);

const categoryList = document.getElementById("categoryList");
const menuGrid = document.getElementById("menuGrid");
const floatingCart = document.getElementById("floatingCart");
const floatingCount = document.getElementById("floatingCount");
const floatingTotal = document.getElementById("floatingTotal");
const topCartButton = document.getElementById("topCartButton");
const drawerBackdrop = document.getElementById("drawerBackdrop");
const cartDrawer = document.getElementById("cartDrawer");
const closeDrawer = document.getElementById("closeDrawer");
const cartContent = document.getElementById("cartContent");
const subtotalText = document.getElementById("subtotalText");
const totalText = document.getElementById("totalText");
const goCheckout = document.getElementById("goCheckout");
const checkoutBackdrop = document.getElementById("checkoutBackdrop");
const checkoutModal = document.getElementById("checkoutModal");
const closeCheckout = document.getElementById("closeCheckout");
const checkoutForm = document.getElementById("checkoutForm");
const checkoutTotal = document.getElementById("checkoutTotal");
const addressField = document.getElementById("addressField");
const customerAddress = document.getElementById("customerAddress");
const pickupCard = document.getElementById("pickupCard");
const deliveryCard = document.getElementById("deliveryCard");
const useLocation = document.getElementById("useLocation");
const successBackdrop = document.getElementById("successBackdrop");
const successModal = document.getElementById("successModal");
const closeSuccess = document.getElementById("closeSuccess");
const successSummary = document.getElementById("successSummary");
const waButton = document.getElementById("waButton");
const backToMenu = document.getElementById("backToMenu");

function renderCategories() {
  categoryList.innerHTML = "";
  categories.forEach((category) => {
    const button = document.createElement("button");
    button.className = `chip ${activeCategory === category ? "active" : ""}`;
    button.textContent = category;
    button.addEventListener("click", () => {
      activeCategory = category;
      renderCategories();
      renderMenus();
    });
    categoryList.appendChild(button);
  });
}

function renderMenus() {
  const filteredMenus =
    activeCategory === "Semua"
      ? menus
      : menus.filter((item) => item.category === activeCategory);

  menuGrid.innerHTML = "";

  filteredMenus.forEach((menu) => {
    const card = document.createElement("article");
    card.className = "menu-card";
    card.innerHTML = `
      <div class="menu-image">🍽️</div>
      <div class="menu-content">
        <h3 class="menu-name">${menu.name}</h3>
        <p class="menu-desc">${menu.description}</p>
        <div class="menu-footer">
          <div class="price">${rupiah(menu.price)}</div>
          <button class="add-btn">Tambah</button>
        </div>
      </div>
    `;
    card.querySelector(".add-btn").addEventListener("click", () => addToCart(Number(menu.id)));
    menuGrid.appendChild(card);
  });
}

function addToCart(menuId) {
  const found = cart.find((item) => Number(item.id) === Number(menuId));

  if (found) {
    found.qty += 1;
  } else {
    const menu = menus.find((item) => Number(item.id) === Number(menuId));
    if (!menu) return;
    cart.push({ ...menu, qty: 1 });
  }

  renderCart();
}

function updateQty(menuId, change) {
  cart = cart
    .map((item) =>
      Number(item.id) === Number(menuId)
        ? { ...item, qty: item.qty + change }
        : item
    )
    .filter((item) => item.qty > 0);

  renderCart();
}

function cartTotal() {
  return cart.reduce((sum, item) => sum + Number(item.price) * item.qty, 0);
}

function cartCount() {
  return cart.reduce((sum, item) => sum + item.qty, 0);
}

function renderCart() {
  const total = cartTotal();
  const count = cartCount();

  topCartButton.textContent = `🛒 ${count}`;
  floatingCount.textContent = `${count} item`;
  floatingTotal.textContent = rupiah(total);
  subtotalText.textContent = rupiah(total);
  totalText.textContent = rupiah(total);
  checkoutTotal.textContent = rupiah(total);

  floatingCart.style.display = count > 0 ? "flex" : "none";

  if (cart.length === 0) {
    cartContent.innerHTML = `<div class="empty-state">Keranjang masih kosong.<br />Silakan pilih menu terlebih dahulu.</div>`;
    goCheckout.disabled = true;
    goCheckout.style.opacity = 0.5;
    goCheckout.style.cursor = "not-allowed";
    return;
  }

  goCheckout.disabled = false;
  goCheckout.style.opacity = 1;
  goCheckout.style.cursor = "pointer";
  cartContent.innerHTML = "";

  cart.forEach((item) => {
    const row = document.createElement("div");
    row.className = "cart-item";
    row.innerHTML = `
      <div>
        <p class="cart-item-name">${item.name}</p>
        <div class="cart-item-price">${rupiah(item.price)} x ${item.qty}</div>
        <div class="qty-box">
          <button class="qty-btn minus">-</button>
          <strong>${item.qty}</strong>
          <button class="qty-btn plus">+</button>
        </div>
      </div>
      <strong>${rupiah(Number(item.price) * item.qty)}</strong>
    `;

    row.querySelector(".minus").addEventListener("click", () => updateQty(item.id, -1));
    row.querySelector(".plus").addEventListener("click", () => updateQty(item.id, 1));

    cartContent.appendChild(row);
  });
}

function openDrawer() {
  drawerBackdrop.style.display = "block";
  cartDrawer.style.display = "block";
}

function closeCartDrawer() {
  drawerBackdrop.style.display = "none";
  cartDrawer.style.display = "none";
}

function openCheckout() {
  if (cart.length === 0) return;
  closeCartDrawer();
  checkoutBackdrop.style.display = "block";
  checkoutModal.style.display = "block";
}

function closeCheckoutModal() {
  checkoutBackdrop.style.display = "none";
  checkoutModal.style.display = "none";
}

function openSuccess() {
  successBackdrop.style.display = "block";
  successModal.style.display = "block";
}

function closeSuccessModal() {
  successBackdrop.style.display = "none";
  successModal.style.display = "none";
}

function updateOrderTypeUI() {
  const selected = document.querySelector('input[name="orderType"]:checked').value;

  if (selected === "delivery") {
    deliveryCard.classList.add("active");
    pickupCard.classList.remove("active");
    addressField.classList.remove("hidden");
    customerAddress.setAttribute("required", "required");
  } else {
    pickupCard.classList.add("active");
    deliveryCard.classList.remove("active");
    addressField.classList.add("hidden");
    customerAddress.removeAttribute("required");
    customerAddress.value = "";
  }
}

function generateOrderCode() {
  const now = new Date();
  return `RK-${now.getFullYear()}${String(now.getMonth() + 1).padStart(2, "0")}${String(now.getDate()).padStart(2, "0")}-${Math.floor(Math.random() * 900 + 100)}`;
}

function showSuccessSummary(order) {
  successSummary.innerHTML = `
    <div class="summary-row"><span>Kode Order</span><strong>${order.code}</strong></div>
    <div class="summary-row"><span>Nama</span><span>${order.name}</span></div>
    <div class="summary-row"><span>WhatsApp</span><span>${order.phone}</span></div>
    <div class="summary-row"><span>Tipe</span><span>${order.orderType === "pickup" ? "Ambil di tempat" : "Delivery"}</span></div>
    ${order.orderType === "delivery" ? `<div class="summary-row"><span>Alamat</span><span style="max-width: 180px; text-align: right;">${order.address}</span></div>` : ""}
    <div class="summary-row"><span>Bayar</span><span>${order.payment}</span></div>
    <div class="summary-row"><span>Total</span><strong>${rupiah(order.total)}</strong></div>
  `;
}

function waMessage(order) {
  const items = order.items
    .map((item) => `- ${item.name} x${item.qty} = ${rupiah(Number(item.price) * item.qty)}`)
    .join("%0A");

  const addressLine =
    order.orderType === "delivery"
      ? `%0AAlamat: ${encodeURIComponent(order.address)}`
      : `%0ATipe Pesanan: Ambil di tempat`;

  const noteLine = order.note
    ? `%0ACatatan: ${encodeURIComponent(order.note)}`
    : "";

  return `Halo Roumah Kitchen,%0ASaya sudah membuat pesanan.%0A%0AKode Order: ${order.code}%0ANama: ${encodeURIComponent(order.name)}%0ANo WA: ${encodeURIComponent(order.phone)}%0A${items}${addressLine}%0APembayaran: ${encodeURIComponent(order.payment)}%0ATotal: ${encodeURIComponent(rupiah(order.total))}${noteLine}`;
}

topCartButton.addEventListener("click", openDrawer);
floatingCart.addEventListener("click", openDrawer);
closeDrawer.addEventListener("click", closeCartDrawer);
drawerBackdrop.addEventListener("click", closeCartDrawer);
goCheckout.addEventListener("click", openCheckout);
closeCheckout.addEventListener("click", closeCheckoutModal);
checkoutBackdrop.addEventListener("click", closeCheckoutModal);
closeSuccess.addEventListener("click", closeSuccessModal);
successBackdrop.addEventListener("click", closeSuccessModal);

document
  .querySelectorAll('input[name="orderType"]')
  .forEach((input) => input.addEventListener("change", updateOrderTypeUI));

useLocation.addEventListener("click", () => {
  if (!navigator.geolocation) {
    alert("Browser Anda tidak mendukung lokasi.");
    return;
  }

  navigator.geolocation.getCurrentPosition(
    (position) => {
      const { latitude, longitude } = position.coords;
      customerAddress.value = `Lokasi GPS: ${latitude.toFixed(6)}, ${longitude.toFixed(6)}`;
    },
    () => alert("Lokasi tidak berhasil diambil. Silakan isi alamat manual.")
  );
});

checkoutForm.addEventListener("submit", async (event) => {
  event.preventDefault();

  const name = document.getElementById("customerName").value.trim();
  const phone = document.getElementById("customerPhone").value.trim();
  const orderType = document.querySelector('input[name="orderType"]:checked').value;
  const address = customerAddress.value.trim();
  const note = document.getElementById("customerNote").value.trim();
  const payment = document.getElementById("paymentMethod").value;

  if (!name || !phone) {
    alert("Nama dan nomor WhatsApp wajib diisi.");
    return;
  }

  if (orderType === "delivery" && !address) {
    alert("Alamat wajib diisi jika memilih delivery.");
    return;
  }

  if (cart.length === 0) {
    alert("Keranjang masih kosong.");
    return;
  }

  const order = {
    code: generateOrderCode(),
    name,
    phone,
    orderType,
    address,
    note,
    payment,
    total: cartTotal(),
    items: [...cart],
  };

  try {
    const response = await fetch("api/create_order.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(order),
    });

    const result = await response.json();

    if (!response.ok || result.status !== "success") {
      throw new Error(result.message || "Gagal menyimpan pesanan");
    }

    customerOrder = order;
    showSuccessSummary(order);
    closeCheckoutModal();
    openSuccess();

    checkoutForm.reset();
    document.querySelector('input[name="orderType"][value="pickup"]').checked = true;
    updateOrderTypeUI();

    cart = [];
    renderCart();
  } catch (error) {
    console.error("Checkout error:", error);
    alert("Pesanan gagal disimpan: " + error.message);
  }
});

waButton.addEventListener("click", () => {
  if (!customerOrder) return;
  const phoneNumber = "6281234567890";
  window.open(`https://wa.me/${phoneNumber}?text=${waMessage(customerOrder)}`, "_blank");
});

backToMenu.addEventListener("click", () => {
  closeSuccessModal();
  window.scrollTo({ top: 0, behavior: "smooth" });
});

fetch("api/get_menu.php")
  .then((response) => response.json())
  .then((data) => {
    menus = data;
    renderMenus();
  })
  .catch((error) => {
    console.error("Gagal mengambil menu:", error);
  });

renderCategories();
renderCart();
updateOrderTypeUI();