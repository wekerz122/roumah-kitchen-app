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
const trackOrderButton = document.getElementById("trackOrderButton");
const backToMenu = document.getElementById("backToMenu");

const qrisBackdrop = document.getElementById("qrisBackdrop");
const qrisModal = document.getElementById("qrisModal");
const closeQris = document.getElementById("closeQris");
const afterQrisButton = document.getElementById("afterQrisButton");
const backFromQris = document.getElementById("backFromQris");
const qrisTotal = document.getElementById("qrisTotal");
const copyNominalButton = document.getElementById("copyNominalButton");

function escapeHtml(value) {
  return String(value ?? "")
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;");
}

function slugify(text) {
  return String(text ?? "")
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "");
}

function buildCartKey(menuId, spicyLevel, toppings) {
  const toppingKey = Array.isArray(toppings)
    ? toppings
        .map((t) => `${t.id || slugify(t.name)}-${Number(t.price) || 0}`)
        .sort()
        .join("|")
    : "";

  return `${menuId}__${slugify(spicyLevel || "normal")}__${toppingKey}`;
}

function ensureToppingModalStyles() {
  if (document.getElementById("toppingModalStyles")) return;

  const style = document.createElement("style");
  style.id = "toppingModalStyles";
  style.textContent = `
    .topping-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(17, 24, 39, 0.5);
      z-index: 60;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 16px;
    }

    .topping-modal {
      width: min(480px, 100%);
      max-height: 90vh;
      overflow-y: auto;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 18px 50px rgba(0, 0, 0, 0.18);
    }

    .topping-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      gap: 12px;
      padding: 16px;
      border-bottom: 1px solid #e5e7eb;
    }

    .topping-title {
      margin: 0;
      font-size: 18px;
      font-weight: 700;
      color: #1f2937;
    }

    .topping-subtitle {
      margin: 6px 0 0;
      font-size: 13px;
      color: #6b7280;
    }

    .topping-close {
      border: none;
      background: #f3f4f6;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 18px;
    }

    .topping-body {
      padding: 16px;
    }

    .topping-section {
      margin-bottom: 18px;
    }

    .topping-section-title {
      margin: 0 0 10px;
      font-size: 15px;
      font-weight: 700;
      color: #111827;
    }

    .topping-option {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      padding: 12px 14px;
      margin-bottom: 10px;
      cursor: pointer;
      background: #fff;
    }

    .topping-option-left {
      display: flex;
      align-items: center;
      gap: 10px;
      min-width: 0;
    }

    .topping-option input {
      margin: 0;
      width: auto;
    }

    .topping-option-label {
      font-size: 14px;
      color: #1f2937;
      line-height: 1.4;
      word-break: break-word;
    }

    .topping-option-price {
      font-size: 13px;
      font-weight: 700;
      color: #ea580c;
      white-space: nowrap;
    }

    .topping-qty-row {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .topping-qty-btn {
      border: none;
      width: 34px;
      height: 34px;
      border-radius: 10px;
      background: #f3f4f6;
      font-size: 18px;
      cursor: pointer;
      font-weight: 700;
    }

    .topping-qty-value {
      min-width: 24px;
      text-align: center;
      font-weight: 700;
      color: #111827;
    }

    .topping-summary-box {
      background: #fff8f1;
      border: 1px solid #ffe1c0;
      border-radius: 14px;
      padding: 14px;
      margin-top: 14px;
    }

    .topping-summary-row {
      display: flex;
      justify-content: space-between;
      gap: 12px;
      margin-bottom: 8px;
      font-size: 14px;
      color: #374151;
    }

    .topping-summary-row:last-child {
      margin-bottom: 0;
      padding-top: 10px;
      border-top: 1px dashed #f6b375;
      font-size: 16px;
      font-weight: 700;
      color: #111827;
    }

    .topping-actions {
      display: flex;
      flex-direction: column;
      gap: 10px;
      margin-top: 16px;
    }

    .topping-primary-btn,
    .topping-secondary-btn {
      border: none;
      width: 100%;
      border-radius: 14px;
      padding: 14px 16px;
      cursor: pointer;
      font-size: 15px;
      font-weight: 700;
    }

    .topping-primary-btn {
      background: #ef6c00;
      color: #fff;
    }

    .topping-secondary-btn {
      background: #eef2ff;
      color: #1e40af;
    }

    .cart-item-meta {
      font-size: 12px;
      color: #666;
      line-height: 1.5;
      margin-bottom: 6px;
    }
  `;
  document.head.appendChild(style);
}

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
      <div class="menu-image">
        <img src="${menu.image ? menu.image : 'image/default.png'}" alt="${escapeHtml(menu.name)}">
      </div>
      <div class="menu-content">
        <h3 class="menu-name">${escapeHtml(menu.name)}</h3>
        <p class="menu-desc">${escapeHtml(menu.description || "")}</p>
        <div class="menu-footer">
          <div class="price">${rupiah(menu.price)}</div>
          <button class="add-btn">Tambah</button>
        </div>
      </div>
    `;

    card
      .querySelector(".add-btn")
      .addEventListener("click", () => {
        const selectedMenu = menus.find(
          (m) => Number(m.id) === Number(menu.id)
        );
        if (!selectedMenu) return;
        openToppingModal(selectedMenu);
      });

    menuGrid.appendChild(card);
  });
}

function addToCartWithOptions(menu, spicyLevel, toppings, qty) {
  const selectedToppings = Array.isArray(toppings) ? toppings : [];
  const safeQty = Math.max(1, Number(qty) || 1);
  const extraTotal = selectedToppings.reduce(
    (sum, item) => sum + Number(item.price || 0),
    0
  );
  const finalPrice = Number(menu.price) + extraTotal;
  const cartKey = buildCartKey(menu.id, spicyLevel, selectedToppings);

  const existing = cart.find((item) => item.cart_key === cartKey);

  if (existing) {
    existing.qty += safeQty;
  } else {
    cart.push({
      cart_key: cartKey,
      id: Number(menu.id),
      name: menu.name,
      category: menu.category,
      description: menu.description,
      image: menu.image || "",
      base_price: Number(menu.price),
      price: finalPrice,
      spicy_level: spicyLevel || "",
      toppings: selectedToppings,
      qty: safeQty,
    });
  }

  renderCart();
}

function updateQty(cartKey, change) {
  cart = cart
    .map((item) =>
      item.cart_key === cartKey
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

function getCartMetaHtml(item) {
  const parts = [];

  if (item.spicy_level) {
    parts.push(`🌶 ${escapeHtml(item.spicy_level)}`);
  }

  if (Array.isArray(item.toppings) && item.toppings.length > 0) {
    item.toppings.forEach((t) => {
      parts.push(
        `+ ${escapeHtml(t.name)}${
          Number(t.price) > 0 ? ` (${rupiah(t.price)})` : ""
        }`
      );
    });
  }

  if (parts.length === 0) return "";
  return `<div class="cart-item-meta">${parts.join("<br>")}</div>`;
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
        <p class="cart-item-name">${escapeHtml(item.name)}</p>
        ${getCartMetaHtml(item)}
        <div class="cart-item-price">${rupiah(item.price)} x ${item.qty}</div>
        <div class="qty-box">
          <button class="qty-btn minus">-</button>
          <strong>${item.qty}</strong>
          <button class="qty-btn plus">+</button>
        </div>
      </div>
      <strong>${rupiah(Number(item.price) * item.qty)}</strong>
    `;

    row
      .querySelector(".minus")
      .addEventListener("click", () => updateQty(item.cart_key, -1));

    row
      .querySelector(".plus")
      .addEventListener("click", () => updateQty(item.cart_key, 1));

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

function openQrisModal(total) {
  qrisTotal.textContent = rupiah(total);
  qrisBackdrop.style.display = "block";
  qrisModal.style.display = "block";
}

function closeQrisModal() {
  qrisBackdrop.style.display = "none";
  qrisModal.style.display = "none";
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
    <div class="summary-row"><span>Kode Order</span><strong>${escapeHtml(order.code)}</strong></div>
    <div class="summary-row"><span>Nama</span><span>${escapeHtml(order.name)}</span></div>
    <div class="summary-row"><span>WhatsApp</span><span>${escapeHtml(order.phone)}</span></div>
    <div class="summary-row"><span>Tipe</span><span>${order.orderType === "pickup" ? "Ambil di tempat" : "Delivery"}</span></div>
    ${order.orderType === "delivery" ? `<div class="summary-row"><span>Alamat</span><span style="max-width: 180px; text-align: right;">${escapeHtml(order.address)}</span></div>` : ""}
    <div class="summary-row"><span>Bayar</span><span>${escapeHtml(order.payment)}</span></div>
    <div class="summary-row"><span>Total</span><strong>${rupiah(order.total)}</strong></div>
  `;
}

function buildWhatsappItemText(item) {
  const details = [];

  if (item.spicy_level) {
    details.push(`%0A  Level Pedas: ${encodeURIComponent(item.spicy_level)}`);
  }

  if (Array.isArray(item.toppings) && item.toppings.length > 0) {
    item.toppings.forEach((t) => {
      details.push(
        `%0A  + ${encodeURIComponent(t.name)}${
          Number(t.price) > 0 ? ` (${encodeURIComponent(rupiah(t.price))})` : ""
        }`
      );
    });
  }

  return `- ${encodeURIComponent(item.name)} x${item.qty} = ${encodeURIComponent(
    rupiah(Number(item.price) * item.qty)
  )}${details.join("")}`;
}

function waMessage(order) {
  const items = order.items.map((item) => buildWhatsappItemText(item)).join("%0A");

  const addressLine =
    order.orderType === "delivery"
      ? `%0AAlamat: ${encodeURIComponent(order.address)}`
      : `%0ATipe Pesanan: Ambil di tempat`;

  const noteLine = order.note
    ? `%0ACatatan: ${encodeURIComponent(order.note)}`
    : "";

  const trackingBaseUrl = window.location.pathname.includes("roumah-kitchen")
  ? `${window.location.origin}/roumah-kitchen/track_order.php`
  : `${window.location.origin}/track_order.php`;

  const trackingLink =
    `${trackingBaseUrl}?order_code=${encodeURIComponent(order.code)}&customer_phone=${encodeURIComponent(order.phone)}`;

  return `Halo Roumah Kitchen,%0ASaya sudah membuat pesanan.%0A%0AKode Order: ${encodeURIComponent(order.code)}%0ANama: ${encodeURIComponent(order.name)}%0ANo WA: ${encodeURIComponent(order.phone)}%0A${items}${addressLine}%0APembayaran: ${encodeURIComponent(order.payment)}%0ATotal: ${encodeURIComponent(rupiah(order.total))}${noteLine}%0A%0ACek status pesanan di sini:%0A${encodeURIComponent(trackingLink)}`;
}

function openToppingModal(menu) {
  ensureToppingModalStyles();

  const spicyLevels =
    Array.isArray(menu.spicy_levels) && menu.spicy_levels.length > 0
      ? menu.spicy_levels.map((item) => item.name)
      : [];

  const extras =
    Array.isArray(menu.toppings) && menu.toppings.length > 0
      ? menu.toppings.map((item) => ({
          id: Number(item.id),
          name: item.name,
          price: Number(item.price) || 0,
        }))
      : [];

  const defaultSpicy = spicyLevels.length > 0 ? spicyLevels[0] : "";
  let selectedSpicy = defaultSpicy;
  let selectedExtras = [];
  let qty = 1;

  const backdrop = document.createElement("div");
  backdrop.className = "topping-backdrop";
  backdrop.id = "toppingModalBackdrop";

  backdrop.innerHTML = `
    <div class="topping-modal" role="dialog" aria-modal="true" aria-label="Pilih topping">
      <div class="topping-header">
        <div>
          <h3 class="topping-title">${escapeHtml(menu.name)}</h3>
          <p class="topping-subtitle">Atur level pedas, topping, dan jumlah pesanan.</p>
        </div>
        <button type="button" class="topping-close" id="toppingCloseBtn">×</button>
      </div>

      <div class="topping-body">
        ${
          spicyLevels.length > 0
            ? `
          <div class="topping-section">
            <p class="topping-section-title">Level Pedas</p>
            ${spicyLevels
              .map(
                (level, index) => `
              <label class="topping-option">
                <div class="topping-option-left">
                  <input type="radio" name="spicyLevelOption" value="${escapeHtml(level)}" ${index === 0 ? "checked" : ""}>
                  <span class="topping-option-label">${escapeHtml(level)}</span>
                </div>
                <span class="topping-option-price">Gratis</span>
              </label>
            `
              )
              .join("")}
          </div>
        `
            : ""
        }

        ${
          extras.length > 0
            ? `
          <div class="topping-section">
            <p class="topping-section-title">Topping Tambahan</p>
            ${extras
              .map(
                (extra, index) => `
              <label class="topping-option">
                <div class="topping-option-left">
                  <input type="checkbox" class="extraOptionCheckbox" data-index="${index}">
                  <span class="topping-option-label">${escapeHtml(extra.name)}</span>
                </div>
                <span class="topping-option-price">${
                  Number(extra.price) > 0 ? `+ ${rupiah(extra.price)}` : "Gratis"
                }</span>
              </label>
            `
              )
              .join("")}
          </div>
        `
            : ""
        }

        <div class="topping-section">
          <p class="topping-section-title">Jumlah</p>
          <div class="topping-qty-row">
            <button type="button" class="topping-qty-btn" id="qtyMinusBtn">-</button>
            <span class="topping-qty-value" id="toppingQtyValue">1</span>
            <button type="button" class="topping-qty-btn" id="qtyPlusBtn">+</button>
          </div>
        </div>

        <div class="topping-summary-box">
          <div class="topping-summary-row">
            <span>Harga Menu</span>
            <strong>${rupiah(menu.price)}</strong>
          </div>
          <div class="topping-summary-row">
            <span>Total Topping</span>
            <strong id="toppingExtraTotalText">${rupiah(0)}</strong>
          </div>
          <div class="topping-summary-row">
            <span>Total</span>
            <strong id="toppingGrandTotalText">${rupiah(menu.price)}</strong>
          </div>
        </div>

        <div class="topping-actions">
          <button type="button" class="topping-primary-btn" id="confirmAddToCartBtn">Tambah ke Keranjang</button>
          <button type="button" class="topping-secondary-btn" id="cancelToppingModalBtn">Batal</button>
        </div>
      </div>
    </div>
  `;

  document.body.appendChild(backdrop);

  const closeModal = () => {
    const el = document.getElementById("toppingModalBackdrop");
    if (el) el.remove();
  };

  const updateModalTotal = () => {
    const extraTotal = selectedExtras.reduce(
      (sum, item) => sum + Number(item.price || 0),
      0
    );
    const grandTotal = (Number(menu.price) + extraTotal) * qty;

    document.getElementById("toppingExtraTotalText").textContent = rupiah(
      extraTotal * qty
    );
    document.getElementById("toppingGrandTotalText").textContent = rupiah(
      grandTotal
    );
    document.getElementById("toppingQtyValue").textContent = String(qty);
  };

  backdrop.querySelector("#toppingCloseBtn").addEventListener("click", closeModal);
  backdrop
    .querySelector("#cancelToppingModalBtn")
    .addEventListener("click", closeModal);

  backdrop.addEventListener("click", (event) => {
    if (event.target === backdrop) closeModal();
  });

  const spicyInputs = backdrop.querySelectorAll('input[name="spicyLevelOption"]');
  spicyInputs.forEach((input) => {
    input.addEventListener("change", (event) => {
      selectedSpicy = event.target.value;
    });
  });

  const extraInputs = backdrop.querySelectorAll(".extraOptionCheckbox");
  extraInputs.forEach((input) => {
    input.addEventListener("change", (event) => {
      const index = Number(event.target.dataset.index);
      const extra = extras[index];
      if (!extra) return;

      if (event.target.checked) {
        selectedExtras.push({
          id: extra.id,
          name: extra.name,
          price: Number(extra.price) || 0,
        });
      } else {
        selectedExtras = selectedExtras.filter((item) => item.id !== extra.id);
      }

      updateModalTotal();
    });
  });

  backdrop.querySelector("#qtyMinusBtn").addEventListener("click", () => {
    qty = Math.max(1, qty - 1);
    updateModalTotal();
  });

  backdrop.querySelector("#qtyPlusBtn").addEventListener("click", () => {
    qty += 1;
    updateModalTotal();
  });

  backdrop
    .querySelector("#confirmAddToCartBtn")
    .addEventListener("click", () => {
      addToCartWithOptions(menu, selectedSpicy, selectedExtras, qty);
      closeModal();
    });

  updateModalTotal();
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

closeQris.addEventListener("click", closeQrisModal);
qrisBackdrop.addEventListener("click", closeQrisModal);
backFromQris.addEventListener("click", closeQrisModal);

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

copyNominalButton.addEventListener("click", async () => {
  const total = customerOrder ? customerOrder.total : 0;

  try {
    await navigator.clipboard.writeText(String(total));
    alert("Nominal berhasil disalin: " + total);
  } catch (error) {
    alert("Gagal menyalin nominal.");
    console.error(error);
  }
});

afterQrisButton.addEventListener("click", () => {
  if (!customerOrder) return;

  closeQrisModal();

  const orderCode = encodeURIComponent(customerOrder.code);
  const phone = encodeURIComponent(customerOrder.phone);

  window.location.href = `track_order.php?order_code=${orderCode}&customer_phone=${phone}`;
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

    if (payment === "QRIS") {
      openQrisModal(order.total);
    } else {
      openSuccess();
    }

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

function buildWhatsappMessage(order) {
  let itemsText = "";

  (order.items || []).forEach((item) => {
    itemsText += `\n- ${item.name} x${item.qty}`;

    if (item.spicy_level) {
      itemsText += ` (${item.spicy_level})`;
    }

    let toppings = item.toppings;

    if (typeof toppings === "string") {
      try {
        toppings = JSON.parse(toppings);
      } catch (e) {
        toppings = [];
      }
    }

    if (Array.isArray(toppings) && toppings.length > 0) {
      toppings.forEach((t) => {
        itemsText += `\n   + ${t.name || t}`;
      });
    }
  });

  return encodeURIComponent(
    `Halo admin, saya sudah membuat pesanan.\n\n` +
    `Kode Order: ${order.code}\n` +
    `Nama: ${order.name}\n` +
    `No WA: ${order.phone}\n\n` +
    `Pesanan:${itemsText}\n\n` +
    `Total: ${rupiah(order.total)}`
  );
}

waButton.addEventListener("click", () => {
  if (!customerOrder) return;

  const phoneNumber = "62895323655609";
  const message = buildWhatsappMessage(customerOrder);

  window.open(`https://wa.me/${phoneNumber}?text=${message}`, "_blank");
});

trackOrderButton.addEventListener("click", () => {
  if (!customerOrder) return;

  const orderCode = encodeURIComponent(customerOrder.code);
  const phone = encodeURIComponent(customerOrder.phone);

  window.location.href = `track_order.php?order_code=${orderCode}&customer_phone=${phone}`;
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