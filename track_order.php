<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Pesanan - Roumah Kitchen</title>
    <link rel="stylesheet" href="roumahstyle.css">
</head>
<body>

    <div class="track-page">
        <div class="track-box">
            <h1 class="track-title">Cek Status Pesanan</h1>
            <p class="track-subtitle">Masukkan kode order dan nomor WhatsApp Anda.</p>

            <form id="trackForm" class="track-form">
                <div class="field">
                    <label for="orderCode">Kode Order</label>
                    <input type="text" id="orderCode" placeholder="Contoh: RK-20260411-619" required>
                </div>

                <div class="field">
                    <label for="customerPhone">No. WhatsApp</label>
                    <input type="text" id="customerPhone" placeholder="Contoh: 081234567890" required>
                </div>

                <button type="submit" class="track-btn">Cek Status</button>
            </form>

            <div id="trackResult" class="track-result"></div>

            <div style="margin-top:16px; display:flex; flex-direction:column; gap:10px;">
                <a href="track_order.php" class="track-btn" style="display:block;text-align:center;text-decoration:none;">
                    Cek Status Lagi
                </a>
                <a href="index.php" class="track-btn" style="display:block;text-align:center;text-decoration:none; background:#e5e7eb; color:#111827;">
                    ← Kembali ke Menu
                </a>
            </div>
        </div>
    </div>

    <script>
    const trackForm = document.getElementById("trackForm");
    const trackResult = document.getElementById("trackResult");
    const orderCodeInput = document.getElementById("orderCode");
    const customerPhoneInput = document.getElementById("customerPhone");

    function escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text ?? "";
        return div.innerHTML;
    }

    function buildWhatsappMessage(order) {
        const waPhone = "62895323655609"; 

        let itemsText = "";

        order.items.forEach(item => {
            itemsText += `- ${item.name} x${item.qty}`;

            if (item.spicy_level) {
                itemsText += ` (${item.spicy_level})`;
            }

            itemsText += `%0A`;

            if (item.toppings && item.toppings.length > 0) {
                item.toppings.forEach(t => {
                    itemsText += `  + ${t.name}`;
                    if (Number(t.price) > 0) {
                        itemsText += ` (Rp ${Number(t.price).toLocaleString("id-ID")})`;
                    }
                    itemsText += `%0A`;
                });
            }
        });

        const waText =
            `Halo Roumah Kitchen,%0A` +
            `Saya sudah melakukan pembayaran.%0A%0A` +
            `Kode Order: ${encodeURIComponent(order.order_code)}%0A` +
            `Nama: ${encodeURIComponent(order.customer_name)}%0A` +
            `No WA: ${encodeURIComponent(order.customer_phone)}%0A%0A` +
            `Pesanan:%0A${itemsText}%0A` +
            `Total: ${encodeURIComponent("Rp " + Number(order.total).toLocaleString("id-ID"))}%0A%0A` +
            `Saya sudah upload bukti pembayaran. Mohon dicek ya 🙏`;

        return `https://wa.me/${waPhone}?text=${waText}`;
    }

    async function checkOrderStatus(orderCode, customerPhone) {
        trackResult.innerHTML = "<p>Sedang mencari pesanan...</p>";

        try {
            const response = await fetch("api/track_order.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    order_code: orderCode,
                    customer_phone: customerPhone
                })
            });

            const result = await response.json();

            if (result.status !== "success") {
                trackResult.innerHTML = `<div class="track-error">${escapeHtml(result.message)}</div>`;
                return;
            }

            const order = result.order;

            let itemsHtml = "";
            order.items.forEach(item => {
                let detailHtml = "";

                if (item.spicy_level) {
                    detailHtml += `<div style="font-size:12px;color:#666;margin-top:4px;">🌶 ${escapeHtml(item.spicy_level)}</div>`;
                }

                if (item.toppings && item.toppings.length > 0) {
                    item.toppings.forEach(t => {
                        detailHtml += `<div style="font-size:12px;color:#666;">+ ${escapeHtml(t.name)}${Number(t.price) > 0 ? ` (Rp ${Number(t.price).toLocaleString("id-ID")})` : ""}</div>`;
                    });
                }

                itemsHtml += `
                    <div class="track-item-row">
                        <div>
                            <span>${escapeHtml(item.name)} x${Number(item.qty)}</span>
                            ${detailHtml}
                        </div>
                        <span>Rp ${Number(item.price * item.qty).toLocaleString("id-ID")}</span>
                    </div>
                `;
            });

            let proofHtml = "";
            if (order.payment_proof) {
                proofHtml = `
                    <div class="track-proof-box">
                        <strong>Bukti Pembayaran:</strong><br>
                        <a href="${escapeHtml(order.payment_proof)}" target="_blank" class="track-proof-link">
                            Lihat Bukti Pembayaran
                        </a>
                    </div>
                `;
            }

            let uploadProofHtml = "";
            if (order.payment_method === "QRIS" && order.payment_status !== "paid") {
                uploadProofHtml = `
                    <div class="upload-proof-box">
                        <strong>Upload Bukti Pembayaran</strong>
                        <form id="uploadProofForm" class="upload-proof-form">
                            <input type="hidden" name="order_code" value="${escapeHtml(order.order_code)}">
                            <input type="hidden" name="customer_phone" value="${escapeHtml(order.customer_phone)}">
                            <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.webp" required>
                            <button type="submit" class="track-btn">Upload Bukti Bayar</button>
                        </form>
                        <div id="uploadProofMessage"></div>
                    </div>
                `;
            }

            trackResult.innerHTML = `
                <div class="track-card">
                    <div class="track-status-badge status-${escapeHtml(order.status)}">
                        ${escapeHtml(order.status)}
                    </div>

                    <div class="track-info"><strong>Kode Order:</strong> ${escapeHtml(order.order_code)}</div>
                    <div class="track-info"><strong>Nama:</strong> ${escapeHtml(order.customer_name)}</div>
                    <div class="track-info"><strong>WhatsApp:</strong> ${escapeHtml(order.customer_phone)}</div>
                    <div class="track-info"><strong>Tipe:</strong> ${escapeHtml(order.order_type)}</div>
                    <div class="track-info"><strong>Pembayaran:</strong> ${escapeHtml(order.payment_method)}</div>
                    <div class="track-info"><strong>Status Pembayaran:</strong> ${escapeHtml(order.payment_status ?? "-")}</div>
                    <div class="track-info"><strong>Total:</strong> Rp ${Number(order.total).toLocaleString("id-ID")}</div>

                    ${order.address ? `<div class="track-info"><strong>Alamat:</strong> ${escapeHtml(order.address)}</div>` : ""}
                    ${order.note ? `<div class="track-info"><strong>Catatan:</strong> ${escapeHtml(order.note)}</div>` : ""}

                    ${proofHtml}
                    ${uploadProofHtml}

                    <div class="track-items">
                        <strong>Item Pesanan:</strong>
                        ${itemsHtml}
                    </div>
                </div>
            `;

            const uploadProofForm = document.getElementById("uploadProofForm");

            if (uploadProofForm) {
                uploadProofForm.addEventListener("submit", async function(e) {
                    e.preventDefault();

                    const uploadMessage = document.getElementById("uploadProofMessage");
                    const formData = new FormData(uploadProofForm);

                    uploadMessage.innerHTML = "<p>Sedang upload bukti pembayaran...</p>";

                    try {
                        const uploadResponse = await fetch("api/upload_payment_proof.php", {
                            method: "POST",
                            body: formData
                        });

                        const uploadResult = await uploadResponse.json();

                        if (uploadResult.status !== "success") {
                            uploadMessage.innerHTML = `<div class="track-error">${escapeHtml(uploadResult.message)}</div>`;
                            return;
                        }

                        const waLink = buildWhatsappMessage(order);

                        uploadMessage.innerHTML = `
                            <div class="track-success">${escapeHtml(uploadResult.message)}</div>
                            <a href="${waLink}" target="_blank" class="track-btn" style="display:block; text-align:center; margin-top:10px; text-decoration:none; background:#25D366;">
                                Chat WhatsApp Sekarang
                            </a>
                        `;

                        // Kalau mau otomatis buka WhatsApp, buka komentar di bawah:
                        setTimeout(() => {
                             window.open(waLink, "_blank");
                             }, 500);

                        setTimeout(() => {
                            checkOrderStatus(order.order_code, order.customer_phone);
                        }, 1500);

                    } catch (error) {
                        uploadMessage.innerHTML = `<div class="track-error">Upload gagal. Silakan coba lagi.</div>`;
                        console.error(error);
                    }
                });
            }

        } catch (error) {
            trackResult.innerHTML = `<div class="track-error">Terjadi kesalahan saat mengambil data.</div>`;
            console.error(error);
        }
    }

    trackForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const orderCode = orderCodeInput.value.trim();
        const customerPhone = customerPhoneInput.value.trim();

        if (!orderCode || !customerPhone) {
            trackResult.innerHTML = `<div class="track-error">Kode order dan nomor WhatsApp wajib diisi.</div>`;
            return;
        }

        await checkOrderStatus(orderCode, customerPhone);
    });

    const params = new URLSearchParams(window.location.search);
    const orderCodeFromUrl = params.get("order_code");
    const customerPhoneFromUrl = params.get("customer_phone");

    if (orderCodeFromUrl && customerPhoneFromUrl) {
        orderCodeInput.value = orderCodeFromUrl;
        customerPhoneInput.value = customerPhoneFromUrl;
        checkOrderStatus(orderCodeFromUrl, customerPhoneFromUrl);
    }
    </script>

</body>
</html>