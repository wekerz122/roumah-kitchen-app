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
      <div class="brand-wrap">
        <img src="image/logo-roumah.png" class="brand-logo-small" alt="Logo Roumah Kitchen">

        <div class="brand-text">
          <div class="brand-title">Roumah Kitchen</div>
          <div class="brand-sub">X Coffeein Nusantara</div>
        </div>
      </div>

      <button class="cart-button" id="topCartButton">🛒 0</button>
    </header>

    <section class="hero">
      <span class="badge">Order tanpa login</span>
      <h2>Masakan rumahan hangat, siap dinikmati</h2>
      <p>Pilih menu favorit atau yang Anda inginkan dan pesan sekarang. Kami siapkan dengan dengan sepenuh hati.</p>
    </section>

    <main class="section">
      <h2 class="section-title">Kategori</h2>
      <div class="category-scroll" id="categoryList"></div>

      <h2 class="section-title">Menu Pilihan</h2>
      <div class="menu-grid" id="menuGrid"></div>

      <section class="track-home-section">
        <div class="track-home-card">
          <div>
            <h2 class="track-home-title">Cek Status Pesanan</h2>
            <p class="track-home-text">
              Sudah pesan? Lacak pesanan Anda dengan kode order dan nomor WhatsApp.
            </p>
          </div>
          <a href="track_order.php" class="track-home-btn">Lacak Pesanan</a>
        </div>
      </section>
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
            <option value="QRIS">QRIS</option>
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
      <button class="secondary-btn" id="trackOrderButton">Lacak Pesanan</button>
      <button class="secondary-btn" id="backToMenu">Kembali ke menu</button>
    </div>
  </section>

  <div class="modal-backdrop" id="qrisBackdrop"></div>
  <section class="modal" id="qrisModal">
    <div class="panel-header">
      <h3 class="panel-title">Pembayaran QRIS</h3>
      <button class="close-btn" id="closeQris">×</button>
    </div>
    <div class="panel-body qris-body">
      <p class="qris-text">
        Silakan scan QRIS di bawah ini untuk membayar pesanan Anda.
      </p>

      <img src="image/qris.png" alt="QRIS Roumah Kitchen" class="qris-image" id="qrisImage">

      <div class="summary">
        <div class="summary-row">
          <span>Total Bayar</span>
          <strong id="qrisTotal">Rp 0</strong>
        </div>
      </div>

      <div class="qris-actions">
        <button type="button" class="secondary-btn" id="copyNominalButton">Copy Nominal</button>
        <a href="image/qris.png" download="qris-roumah-kitchen.png" class="secondary-btn qris-download-btn">Download QRIS</a>
      </div>

      <p class="qris-note">
        Setelah selesai membayar, klik tombol <strong>Saya Sudah Bayar</strong> untuk lanjut ke pelacakan pesanan dan upload bukti pembayaran.
      </p>

      <button type="button" class="primary-btn" id="afterQrisButton">Saya Sudah Bayar</button>
      <button type="button" class="secondary-btn" id="backFromQris">Kembali</button>
    </div>
  </section>

  <script src="roumah.js?v=3"></script>
  <footer class="app-footer">
  <div class="footer-content">
    
    <div class="footer-brand">
      <strong>Roumah Kitchen</strong>
      <span>X Coffeein Nusantara</span>
    </div>

    <div class="footer-info">
      <p>Masakan rumahan & kopi terbaik untuk Anda</p>
      <p>📍 Kontrakan Pondok Ranau Rantau No 44, Pengasinan, Rawalumbu, Kota Bekasi (17115)</p>
    </div>

    <div class="footer-contact">
      <a href="https://coffeeinnusantara.com/" target="_blank">
        www.coffeeinnusantara.com
      </a>
    </div>

    <div class="footer-copy">
      © 2026 DewanDn. All rights reserved.
    </div>

  </div>
</footer>
</body>
</html>