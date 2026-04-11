<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Roumah Kitchen</title>
  <link rel="stylesheet" href="roumahstyle.css" />
</head>
<body>
  <div class="app">
    <header class="topbar">
      <div>
        <h1 class="brand-title">Roumah Kitchen</h1>
        <p class="brand-sub">Scan, pesan, nikmati.</p>
      </div>
      <button class="cart-button" id="topCartButton">🛒 0</button>
    </header>

    <section class="hero">
      <span class="badge">Order tanpa login</span>
      <h2>Masakan rumahan yang cepat dipesan</h2>
      <p>Customer cukup scan QR, pilih menu, isi data singkat, lalu checkout.</p>
    </section>

    <main class="section">
      <h2 class="section-title">Kategori</h2>
      <div class="category-scroll" id="categoryList"></div>

      <h2 class="section-title">Menu Pilihan</h2>
      <div class="menu-grid" id="menuGrid"></div>
    </main>
  </div>

  <div class="floating-cart" id="floatingCart">
    <div>
      <strong id="floatingCount">0 item</strong>
      <div style="font-size: 13px; opacity: 0.9; margin-top: 2px;">Lihat keranjang Anda</div>
    </div>
    <strong id="floatingTotal">Rp 0</strong>
  </div>

  <div class="drawer-backdrop" id="drawerBackdrop"></div>
  <aside class="drawer" id="cartDrawer">
    <div class="panel-header">
      <h3 class="panel-title">Keranjang</h3>
      <button class="close-btn" id="closeDrawer">×</button>
    </div>
    <div class="panel-body">
      <div id="cartContent"></div>
      <div class="summary">
        <div class="summary-row"><span>Subtotal</span><strong id="subtotalText">Rp 0</strong></div>
        <div class="summary-row"><span>Biaya lain</span><span>Rp 0</span></div>
        <div class="summary-row"><span>Total</span><strong id="totalText">Rp 0</strong></div>
      </div>
      <button class="primary-btn" id="goCheckout">Checkout</button>
    </div>
  </aside>

  <div class="modal-backdrop" id="checkoutBackdrop"></div>
  <section class="modal" id="checkoutModal">
    <div class="panel-header">
      <h3 class="panel-title">Checkout</h3>
      <button class="close-btn" id="closeCheckout">×</button>
    </div>
    <div class="panel-body">
      <form id="checkoutForm">
        <div class="field">
          <label for="customerName">Nama</label>
          <input id="customerName" type="text" placeholder="Masukkan nama Anda" required />
        </div>

        <div class="field">
          <label for="customerPhone">No. WhatsApp</label>
          <input id="customerPhone" type="tel" placeholder="08xxxxxxxxxx" required />
        </div>

        <div class="field">
          <label>Tipe Pesanan</label>
          <div class="radio-group">
            <label class="radio-card active" id="pickupCard">
              <input type="radio" name="orderType" value="pickup" checked /> Ambil di tempat
            </label>
            <label class="radio-card" id="deliveryCard">
              <input type="radio" name="orderType" value="delivery" /> Antar ke alamat
            </label>
          </div>
        </div>

        <div class="field hidden" id="addressField">
          <label for="customerAddress">Alamat</label>
          <textarea id="customerAddress" placeholder="Contoh: Jl. Melati No 10, Bekasi, dekat Indomaret"></textarea>
          <button type="button" class="secondary-btn" id="useLocation">Gunakan lokasi saya</button>
        </div>

        <div class="field">
          <label for="customerNote">Catatan</label>
          <textarea id="customerNote" placeholder="Contoh: pedas level 2, tanpa sambal (opsional)"></textarea>
        </div>

        <div class="field">
          <label for="paymentMethod">Metode Pembayaran</label>
          <select id="paymentMethod" required>
            <option value="COD">COD</option>
            <option value="Transfer">Transfer</option>
          </select>
        </div>

        <div class="summary">
          <div class="summary-row"><span>Total Pesanan</span><strong id="checkoutTotal">Rp 0</strong></div>
        </div>

        <button type="submit" class="primary-btn">Pesan Sekarang</button>
      </form>
    </div>
  </section>

  <div class="success-backdrop" id="successBackdrop"></div>
  <section class="success-modal" id="successModal">
    <div class="panel-header">
      <h3 class="panel-title">Pesanan Berhasil</h3>
      <button class="close-btn" id="closeSuccess">×</button>
    </div>
    <div class="panel-body">
      <div class="success-icon">✓</div>
      <h2 class="success-title">Pesanan berhasil dibuat</h2>
      <p class="success-text">Terima kasih. Admin akan segera memproses pesanan Anda.</p>
      <div class="summary" id="successSummary"></div>
      <button class="primary-btn" id="waButton">Chat WhatsApp</button>
      <button class="secondary-btn" id="backToMenu">Kembali ke menu</button>
    </div>
  </section>

  <script src="roumah.js"></script>
</body>
</html>